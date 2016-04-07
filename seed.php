<?php
require_once('header.php');
require_once('indexer_popgo.php');
require_once('indexer_dmhy.php');

$btih = isset($_GET['btih']) ? $_GET['btih'] : '';

$path = get_torrent_relative_path($btih);


/// 0. 种子已保存到本地，直接返回种子文件，并结束
if (file_exists($path)) {
    $ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    
    logDownload($btih);
    
    if ($RSSOWL_WORKAROUND != TRUE || preg_match('/rssowl/i', $ua) <= 0) {
        /// 直接将用户重定向到种子地址
        header('Location: ' . $path);
    }
    else {
        /// 针对 RSSOWL 的特殊处理（详情见 config.sample.php 中的注释）
        @ob_clean();
        header("Content-Disposition: attachment; filename={$btih}.torrent");
        header('Content-Type: application/x-bittorrent');
        
        echo file_get_contents($path);
    }
    
    
    die();
    exit(0);
}


/// 1. 种子文件还未下载回本地，到源网站去下载
$err = 0;
$path = download_torrent($btih, $err);
if ($err == -1) {
    LOGD("btih 非法：“{$btih}”，向用户返回 404");
    header('HTTP/1.1 404 Not Found');
    die('<h1>404 Not Found</h1>');
}
else if ($err == -2) {
    /// BTIH 为 {$btih} 的资源不存在
    header('HTTP/1.1 404 Not Found');
    die('<h1>404 Not Found</h1> <h2>BTIH not exists</h2>');
}
else if ($err == -3) {
    /// 无法获得 BTIH 为 {$btih} 的资源原始种子地址
    header('HTTP/1.1 404 Not Found');
    die('<h1>404 Not Found</h1> <h2>Could not get source torrent URL</h2>');
}
else if ($err == -4) {
    /// 无法从漫游下载种子（{$url}）;
    header('HTTP/1.1 500 Internal Error');
    die('<h1>500 Internal Error</h1>');
}


/// 2. 检查下载的内容是否是种子，漫游在发生错误的时候仍会返回 HTTP 200，所以我们需要通过 MIME 来进行检查
if (!$path || mime_content_type($path) != 'application/x-bittorrent') {
    if ($path) {
        unlink($path);
    }
    
    LOGD("从漫游下载到的种子不是合法的 （{$url}）:" . curl_error($ch));
    
    header('HTTP/1.1 500 Internal Error');
    die('<h1>500 Internal Error</h1>');
}


header("Content-Disposition: attachment; filename={$btih}.torrent");
header('Content-Type: application/x-bittorrent');
ob_clean();

logDownload($btih);
echo file_get_contents($path);

?>
