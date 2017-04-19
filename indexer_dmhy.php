<?php
require_once('indexer_base.php');
require_once('lib/base32.php');
require_once('func.php');

class Indexer_DMHY Extends Indexer_Base {
    protected $FEED_URL = 'https://share.dmhy.org/topics/rss/rss.xml';
    
    public function fetch() {
        $rs = $this->_fetch();
        
        $ret = array();
        foreach ($rs as $r) {
            $match = NULL;
            $btih = '';
            preg_match('([0-9A-Z]{32})', $r['enclosure'], $match);
            if ($match) {
                $btih = hexdump(Base32::decode($match[0]));
            }
            if ($btih == '') {
                LOGW("无法解析资源的 BTIH, r = " . var_export($r, TRUE));
            }
            
            
            
            $ret[] = array(
                'btih' => $btih,
                'title' => $r['title'],
                'guid' => $r['guid'],
                'link' => $r['link'],
                'description' => $r['description'],
                'pubDate' => strtotime($r['pubDate']),
                'magnet' => $r['enclosure'],
            );
        }
        
        return $ret;
    }


    static public function getSrcSeedURL($btih) {
        global $USER_AGENT;
        global $DMHY_FETCH_WORKAROUND;
        
        
        /// 1. 从数据库中查询原始页面链接
        $res = get_by_btih($btih);
        if (!$res) {
            LOGW("BTIH 为 {$btih} 的资源在数据库中不存在");
            return FALSE;
        }
        
        /// 2. 获取 link 页面内容
        LOGI("正在获取动漫花园的资源页面内容: ${res['link']}");

        $content = NULL;
        $url = NULL;

        /// 2.1 尝试直接通过 curl 获取
        $ch = curl_init($res['link']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_ENCODING, ''); 
        curl_setopt($ch, CURLOPT_USERAGENT, $USER_AGENT);
        $content = curl_exec($ch);
        
        if (!$content) {
            LOGE("无法通过 cURL 抓取动漫花园的资源页面: ${res['link']}'");
        }
        else {
            $url = self::getTorrentURLFromHTML($content);
            LOGD("无法从动漫花园的 HTML 源页面中解析出 torrent 地址");
        }
        
        if ($url) {
            return $url;
        }
        else if (!$DMHY_FETCH_WORKAROUND) {
            return FALSE;
        }
        
        
        /// 2.2 尝试通过 webkit 获取
        LOGD("由于无法通过 cURL 获取动漫花园的源页面，尝试使用 webkit 获取");
        
        set_time_limit(60);
        $content = webkit_fetch_url($res['link'], "{$btih}.torrent", 60);
        
        if (!$content) {
            LOGE("无法通过 webkit 抓取动漫花园的资源页面: ${res['link']}'");
        }
        else {
            $url = self::getTorrentURLFromHTML($content);
            
            if (!$url) {
                LOGD("无法从动漫花园的 HTML 源页面中解析出 torrent 地址");
            }
        }
        
        
        return $url;
    }
    
    
    /**
     * 从 HTML 源代码中获取种子下载地址
     * 
     * @param content   HTML 源代码内容
     */
    static public function getTorrentURLFromHTML($content) {
        $matches = [];
        $pattern = '/\/\/.+[a-f0-9]{40}\.torrent/';
        $ret = preg_match($pattern, $content, $matches);
        
        if ($ret >= 1) {
            return 'http:' . $matches[0];
        }
        else {
            return FALSE;
        }
    }
}

?>
