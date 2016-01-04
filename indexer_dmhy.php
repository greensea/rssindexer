<?php
require_once('indexer_base.php');
require_once('base32.php');
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
        
        /// 1. 从数据库中查询原始页面链接
        $res = get_by_btih($btih);
        if (!$res) {
            LOGW("BTIH 为 {$btih} 的资源在数据库中不存在");
            return FALSE;
        }
        
        /// 2. 获取 link 页面内容
        LOGI("正在获取动漫花园的资源页面内容: ${res['link']}");

        $content = NULL;

        $ch = curl_init($res['link']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_ENCODING, ''); 
        curl_setopt($ch, CURLOPT_USERAGENT, $USER_AGENT);
        $content = curl_exec($ch);

        if (!$content) {
            LOGE("无法抓取动漫花园的资源页面: ${res['link']}'");
            return FALSE;
        }

        /// 3. 解析 BT 地址
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
