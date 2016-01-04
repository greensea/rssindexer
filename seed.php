<?php
require_once('header.php');
require_once('indexer_popgo.php');
require_once('indexer_dmhy.php');

$btih = isset($_GET['btih']) ? $_GET['btih'] : '';

$path = get_torrent_path($btih);


/// 0. 种子已保存到本地，直接返回种子文件，并结束
if (file_exists($path)) {
    $ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    
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


/// 2. 种子文件不存在，试图去源网站下载并保存
/// 2.1 解析种子地址
LOGD("请求的种子文件“{$btih}”不存在，尝试去源网站下载");
$url = "";
$res = get_by_btih($btih);
if (!$res) {
    LOGW("BTIH 为 {$btih} 的资源不存在");
    header('HTTP/1.1 404 Not Found');
    die('<h1>404 Not Found</h1> <h2>BTIH not exists</h2>');
}
switch ($res['src']) {
    case 'popgo':
        $url = Indexer_Popgo::getSrcSeedURL($btih);
        break;
    case 'dmhy':
        $url = Indexer_DMHY::getSrcSeedURL($btih);
        break;
    default:
        LOGE("代码不应执行到此处");
        $url = FALSE;
        break;
}
if (!$url) {
    LOGW("无法获得 BTIH 为 {$btih} 的资源原始种子地址");
    header('HTTP/1.1 404 Not Found');
    die('<h1>404 Not Found</h1> <h2>Could not get source torrent URL</h2>');

}

/// 2.2 开始下载
$content = NULL;

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch, CURLOPT_ENCODING, ''); 
curl_setopt($ch, CURLOPT_USERAGENT, $USER_AGENT);
curl_setopt($ch, CURLOPT_REFERER, '');
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_MAXREDIRS, 5);

$content = curl_exec($ch);

if (!$content || curl_error($ch)) {
    LOGD("无法从漫游下载种子（{$url}）:" . curl_error($ch));
    header('HTTP/1.1 500 Internal Error');
    die('<h1>500 Internal Error</h1>');
}


LOGI("保存种子“{$btih}”，并将用户跳转到下载地址");
$path = archive_torrent($content, $btih);


/// 3. 检查下载的内容是否是种子，漫游在发生错误的时候仍会返回 HTTP 200，所以我们需要通过 MIME 来进行检查
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
echo $content;

?>
