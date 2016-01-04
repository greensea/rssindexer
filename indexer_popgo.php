<?php
require_once('indexer_base.php');
class Indexer_Popgo Extends Indexer_Base {
    protected $FEED_URL = 'http://share.popgo.org/rss/rss.xml';
    
    public function fetch() {
        $rs = $this->_fetch();
        
        $ret = array();
        foreach ($rs as $r) {
            $match = NULL;
            $btih = '';
            preg_match('([0-9a-f]{40})', $r['guid'], $match);
            if ($match) {
                $btih = $match[0];
            }
            else {
                LOGW("无法解析资源的 BTIH, r = " . var_export($r, TRUE));
                continue;
            }
            
            $ret[] = array(
                'btih' => $btih,
                'title' => $r['title'],
                'guid' => $r['guid'],
                'link' => $r['link'],
                'description' => $r['description'],
                'pubDate' => strtotime($r['pubDate']),
                'magnet' => '',
            );
        }
        
        return $ret;
    }


    static public function getSrcSeedURL($btih) {
        return popgo_get_seed_url($btih);
    }
}


?>
