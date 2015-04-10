<?php
require_once('header.php');

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


?>
