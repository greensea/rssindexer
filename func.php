<?php
require_once('header.php');


/// 打印日志到指定的文件中
function LOGS($log) {
    global $LOG_PATH;
    
    $bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
    $pid = posix_getpid();
    
    $log = $log . sprintf(' (%d,%s:%d)', $pid, basename(@$bt[1]['file']), @$bt[1]['line']);
        
    
    
    //syslog(LOG_INFO, $log);
    
    /// 附加日期
    $log = '[' . date(DATE_RFC822) . '] ' . $log . "\n";
    
    
    echo $log;
    
    if ($LOG_PATH) {
        file_put_contents($LOG_PATH, $log, FILE_APPEND);
    }
}

/// 打印 Warning 级别的日志到 Syslog
function LOGW($log) {
    LOGS($log);
}

/// 打印 Error 级别的日志到 Syslog
function LOGE($log) {
    LOGS($log);
}

/// 打印 Notice 级别的日志到 Syslog
function LOGN($log) {
    LOGS($log);
}

/// 打印 Info 级别的日志到 Syslog
function LOGI($log) {
    LOGS($log);
}

/// 打印 DEBUG 级别的日志到 Syslog
function LOGD($log) {
    LOGS($log);    
}



/**
 * 将原始数据保存到 archive/年/月/日 目录下
 */
function archive_raw($content) {
    $dir = date('Y/m/d/');
    
    $dir = "archive/${dir}";
    if (!is_dir($dir)) {
        mkdir($dir, 0755, TRUE);
    }
    
    $path = $dir . sprintf('%.6f', microtime(TRUE)) . '.xml';
    
    file_put_contents($path, $content);
}


/**
 * 将一个种子文件进行归档
 */
function archive_torrent($raw, $btih) {
    $dir = 'torrent/' . substr($btih, 0, 2) . '/' . substr($btih, 2, 2) . '/';
    $path = $dir . $btih . '.torrent';
    
    if (!is_dir($dir)) {
        mkdir($dir, 0755, TRUE);
    }
    
    echo "保存种子文件到`{$path}'\n";
    
    file_put_contents($path, $raw);
}

/**
 * 将原始 RSS 数据解析为资源数组
 */
function parse_rss($content) {
    $xml = simplexml_load_string($content);
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
        $ret[] = array(
            'title' => $item->title,
            'guid' => $item->guid,
            'pubDate' => $item->pubDate,
            'link' => $item->link,
            'description' => $item->description
        );
    }
    
    return $ret;
}


/**
 * 根据给定的关键字搜索资源
 */
function search($kw, $limit = 100) {
    global $mysqli;
    
    $kw = str_replace('　', ' ', $kw);
    $kw = str_replace('+', ' ', $kw);
    $kws = explode(' ', $kw);
    
    $conds = array();
    
    foreach ($kws as $k) {
        $k = trim($k);
        if ($k == '') {
            continue;
        }
        
        $k = $mysqli->real_escape_string($k);
        
        //$conds[] = "(title LIKE '%{$k}%' OR description LIKE '%{$k}%')";
        $conds[] = "(title LIKE '%{$k}%')";
    }
    
    $where = '';
    if (!empty($conds)) {
        $where = ' WHERE ' . implode(' AND ', $conds);
    }
    
    $sql = "SELECT * FROM b_resource {$where} ORDER BY pubDate DESC LIMIT ${limit}";
    $result = $mysqli->query($sql);
    if (!$result) {
        die($mysqli->error);
    }
    
    $rows = array();
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    
    return $rows;
}


/**
 * 解析 POPGO 的 HTML 页面，提取 link，btih，magnet，并自动生成 guid 等信息
 * 
 * @return array    成功返回数组，失败时会输出错误信息，并返回空数组
 */
function popgo_parse_html($content) {
    require_once('phpQuery/phpQuery.php');
    
    $dom = phpQuery::newDocumentHTML($content);
    if (!$dom) {
        LOGE("无法解析漫游页面，原始内容：" . $content);
        return array();
    }
    
    $ret = array();
    
    for ($i = 0; $i < $dom->find('#index_maintable tr')->length(); $i++) {
        $pubDate = $dom->find("#index_maintable tr")->eq($i)->find("td")->eq(1)->text();
        $title = $dom->find("#index_maintable tr")->eq($i)->find("td.inde_tab_seedname")->text();
        $magnet = $dom->find("#index_maintable tr")->eq($i)->find("td")->eq(9)->find("a")->attr("href");
        $link = $dom->find("#index_maintable tr")->eq($i)->find("td")->eq(3)->find("a")->attr("href");
        $btih = popgo_get_btih_from_link($link);
        
        /// 针对 pubDate 格式的一点调整
        $pubDate = '20' . substr($pubDate, 0, 8) . ' ' . substr($pubDate, 8);
        
        if (stripos($title, '置顶') !== FALSE) {
            continue;
        }
        if ($title == '') {
            continue;
        }
        
        $ret[] = array(
            'title' => $title,
            'magnet' => $magnet,
            'link' => 'http://share.popgo.org' . $link,
            'guid' => sprintf('http://share.popgo.org/downseed.php?hash=%s', $btih),
            'pubDate' => strtotime($pubDate),
            'btih' => $btih,
        );
    }
    
    return $ret;
}

function popgo_get_btih_from_link($link) {
    $match = array();
    preg_match('([0-9a-f]{40})', $link, $match);
    
    if (!empty($match)) {
        return $match[0];
    }
    else {
        return NULL;
    }
}

?>
