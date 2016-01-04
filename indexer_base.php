<?php
abstract class Indexer_Base {
    
    /**
     * 获取最新的资源，返回结果是资源列表，每个资源是一个数组，包含以下字段：
     *  btih
     *  title
     *  guid 网站自己提供的唯一标识
     *  link 资源页面
     *  description
     *  pubDate
     *  magnet  如果网站没有提供 magnet，则为空
     */
    abstract public function fetch();
    
    /**
     * 根据 BTIH 获取源站点的种子下载地址
     */
    abstract static public function getSrcSeedURL($btih);
    
    
    /**
     * 下载资源列表，并返回资源数组
     */
    public function _fetch() {
        $rawrss = $this->_fetchRss($this->FEED_URL);
        if (!$rawrss) {
            LOGW("无法获取 RSS 列表");
            return array();
        }
        
        $rs = $this->rawrss2arr($rawrss);
        if (empty($rs)) {
            LOGN("没有从 RSS 列表中获取到资源");
            return array();
        }
        
        return $rs;
        
    }
    
    /**
     * 下载 RSS 内容
     */
    protected function _fetchRss($url) {
        global $USER_AGENT;
        
        LOGI("正在获取 $url");

        $content = NULL;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_ENCODING, ''); 
        curl_setopt($ch, CURLOPT_USERAGENT, $USER_AGENT);
        $content = curl_exec($ch);

        if (!$content) {
            LOGE("无法抓取 RSS：`${RSS_FEED}'");
            return FALSE;
        }


        /// 2. 归档原始数据
        LOGI("正在归档数据");

        /// FIXME: 将 archive_raw 做成类成员函数
        archive_raw($content);
        
        return $content;
    }
    
    
    /**
     * 将原始的 RSS 内容解析成资源数组
     */
    public function rawrss2arr($content) {
        $xml = simplexml_load_string($content, NULL, LIBXML_NOCDATA);
        if (!$xml) {
            return FALSE;
        }
        
        
        if (!isset($xml->channel)) {
            echo "RSS 资源中没有 channel 字段\n";
            return FALSE;
        }
        
        if (!isset($xml->channel->item)) {
            echo "RSS 资源中没有 item 字段\n";
            return FALSE;
        }
        
        $items = array();
        if (!is_array($xml->channel->item)) {
            $items = array($xml->channel->item);
        }
        else {
            $items = $xml->channel->item;
        }
        
        
        
        $ret = array();
        
        foreach ($xml->channel->item as $item) {
            $enclosure = '';
            if (isset($item->enclosure)) {
                $attrs = $item->enclosure->attributes();
                if (isset($attrs['url'])) {
                    $enclosure = $attrs['url'];
                }
            }
            
            $ret[] = array(
                'title' => (string)$item->title,
                'guid' => (string)$item->guid,
                'pubDate' => (string)$item->pubDate,
                'link' => (string)$item->link,
                'description' => (string)$item->description,
                'enclosure' => (string)$enclosure,
            );
            
        }
        
        return $ret;

    }
}


if (0) {

    /**
     * 该文件应该定期使用 PHP-CLI 运行，建议添加到 CRON 任务中
     * 
     * 建议每隔 30 分钟运行一次
     * 
     * 该脚本会自动访问 $RSS_FEED，并将 RSS 内存保存到数据库中
     */
    require_once('header.php');

    /// 3. 解析资源
    LOGI("正在解析资源\n");

    $resources = parse_rss($content);
    if (!$resources) {
        LOGE('无法解析 RSS 资源：' . $content);
        die('');
    }

    LOGI(sprintf("共 %d 个资源", count($resources)));


    /// 4. 将资源丢进数据库
    foreach ($resources as $res) {    
        
        $title = $mysqli->real_escape_string($res['title']);
        $guid = $mysqli->real_escape_string($res['guid']);
        $link = $mysqli->real_escape_string($res['link']);
        $description = $mysqli->real_escape_string($res['description']);
        $pubDate = strtotime($res['pubDate']);
        
        $btih = '';
        $match = array();
        preg_match('([0-9a-f]{40})', $res['link'], $match);
        if (!empty($match)) {
            $btih = $match[0];
            $btih = $mysqli->real_escape_string($btih);
        }
        else {
            LOGW("警告：无法从 `{$res['link']}' 中解析出 BTIH");
        }
        
        
        $ctime = time();
        
        $mysqli->query('start transaction');
        
        $sql = "SELECT * FROM b_resource WHERE guid='{$guid}' LIMIT 1";
        $result = $mysqli->query($sql);
        
        if (!$result) {
            LOGE("数据库查询失败: " . $mysqli->error);
            continue;
        }
        
        if ($result->num_rows > 0) {
            LOGI("{$res['title']} 已存在");
            $mysqli->query('rollback');
            
            continue;
        }

        LOGI("保存数据：{$res['title']}");
        
        $sql = "INSERT INTO b_resource(title, guid, link, description, btih, pubDate, ctime)
                VALUES('${title}', '${guid}', '{$link}', '{$description}', '{$btih}', ${pubDate}, ${ctime})";
        $ret = $mysqli->query($sql);
        if ($ret === FALSE) {
            LOGE("无法保存数据: " . $mysqli->error . "，原 SQL: " . $sql);
        }
        
        $mysqli->query('commit');
    }

    LOGI("索引完成");

}
?>
