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

}

?>
