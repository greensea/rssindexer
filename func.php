<?php
require_once('header.php');

/**
 * 将原始数据保存到 archive/年/月/日 目录下
 */
function archive_raw($content) {
    $dir = date('Y/m/d/');
    
    if (!is_dir($dir)) {
        mkdir($dir, 0755, TRUE);
    }
    
    $path = 'archive/' . $dir . sprintf('%.6f', microtime(TRUE)) . '.xml';
    
    file_put_contents($path, $content);
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
function search($kw) {
    global $mysqli;
    
    $kw = str_replace('　', ' ', $kw);
    $kw = str_replace('+', ' ', $kw);
    $kws = explode(' ', $kw);
    
    $conds = array();
    
    foreach ($kws as $k) {
        $k = trim($k);
        $k = mysql_real_escape_string($k);
        
        $conds[] = "(title LIKE '%{$k}%' OR description LIKE '%{$k}%')";
    }
    
    $sql = 'SELECT * FROM b_resource WHERE ' . implode(' AND ', $conds) . ' ORDER BY resource_id DESC LIMIT 100';
    
    $result = $mysqli->query($sql);
    if (!$result) {
        die($mysqli->error);
    }
    
    var_dump($result);
    
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    
    return $rows;
}

//var_dump(search('['));
?>
