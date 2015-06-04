<?php
require_once('header.php');

$btih = isset($_GET['btih']) ? $_GET['btih'] : '';

$path = get_torrent_path($btih);

/// 种子已保存到本地，直接返回种子文件，并结束
if (file_exists($path)) {
    
    if ($RSSOWL_WORKAROUND != TRUE) {
        /// 直接将用户重定向到种子地址
        header('Location: ' . htmlspecialchars($path));
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



/// 检查 btih 合法性，btih 应该是一个长度为 40 的哈希字符串
$ret = preg_match('/^[0-9a-z]{40}$/', $btih);
if (!$ret) {
    LOGD("btih 非法：“{$btih}”，向用户返回 404");
    header('HTTP/1.1 404 Not Found');
    die('<h1>404 Not Found</h1>');
}
else {
    $btih = strtolower($btih);
}

/// 种子文件不存在，试图去漫游下载并保存
LOGD("请求的种子文件“{$btih}”不存在，尝试去漫游下载");
$url = "http://share.popgo.org/downseed.php?hash={$btih}";


$content = NULL;

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch, CURLOPT_ENCODING, ''); 
curl_setopt($ch, CURLOPT_USERAGENT, $USER_AGENT);
curl_setopt($ch, CURLOPT_REFERER, '');
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_MAXREDIRS, 5);

$content = curl_exec($ch);

if (!$content) {
    LOGD("无法从漫游下载种子（{$url}）:" . curl_error($ch));
    header('HTTP/1.1 500 Internal Error');
    die('<h1>500 Internal Error</h1>');
}


LOGI("保存种子“{$btih}”，并将用户跳转到下载地址");
archive_torrent($content, $btih);


header("Content-Disposition: attachment; filename={$btih}.torrent");
header('Content-Type: application/x-bittorrent');
ob_clean();
echo $content;

?>
