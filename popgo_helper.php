<?php
/**
 * 该脚本可以补充漫游源的资源信息
 * 
 * 通常情况下，indexer 只会保存 RSS 中包含的信息，BT 种子和漫游提供的磁力链接是不会保存的，故我们需要一个漫游助手来补全这些信息
 * 
 * 每次运行该脚本时，脚本会从数据库中查询 magnet 为空的，且来源为漫游的资源，访问漫游资源页面，提取磁力链接，将磁力链接填入其中，另外还会下载种子到种子文件夹中
 * 
 * 脚本会随机延迟 1～10 秒运行后再访问漫游资源页，并在访问漫游资源页后 10～30 秒后再下载 BT 种子
 */
require_once('header.php');

/// 1. 获取未填补的资源
$sql = "SELECT * FROM b_resource WHERE magnet='' AND link LIKE '%popgo%' ORDER BY rand() LIMIT 1";
$result = $mysqli->query($sql);
if (!$result) {
    die('数据库查询出错:' . $mysqli->error);
}

if ($result->num_rows <= 0) {
    die('没有需要更新的数据');
}

$res = $result->fetch_assoc();



/// 2. 访问漫游资源页面
$r = rand() % 10 + 1;
echo "等待 {$r} 秒后去访问漫游页面：{$res['link']}\n";
sleep($r);

$content = NULL;

$ch = curl_init($res['link']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch, CURLOPT_ENCODING, ''); 
curl_setopt($ch, CURLOPT_USERAGENT, $USER_AGENT);
$content = curl_exec($ch);

if (!$content) {
    die("无法访问漫游资源页面：`${res['link']}'");
}


$match = array();
$ret = preg_match('(magnet([^"]+))', $content, $match);
if (empty($match)) {
    die("无法从漫游资源页面中找到磁力链接，原始内容如下：\n" . $content);
}

$magnet = $match[0];

$magnet = $mysqli->real_escape_string($magnet);
$sql = "UPDATE b_resource SET magnet='{$magnet}' WHERE resource_id={$res['resource_id']}";
$mysqli->query($sql);

echo "已保存磁力链接: ${magnet}\n";


/// 3. 下载 BT 种子文件
$r = rand() % 20 + 10;
echo "等待 {$r} 秒后去下载种子文件\n";
sleep($r);


$content = NULL;

$ch = curl_init($res['guid']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch, CURLOPT_ENCODING, ''); 
curl_setopt($ch, CURLOPT_USERAGENT, $USER_AGENT);
curl_setopt($ch, CURLOPT_REFERER, $res['link']);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_MAXREDIRS, 5);

$content = curl_exec($ch);

if (!$content) {
    die("无法下载种子文件：`${res['guid']}'");
}

$btih = $res['btih'];
if ($btih == '') {
    $match = array();
    preg_match('([0-9a-f]{40})', $res['link'], $match);
    $btih = $match[0];
}

if ($btih == '') {
    die('无法获得种子文件的 BTIH，无法保存种子文件');
}

archive_torrent($content, $btih);

echo "“{$res['title']}”处理完毕\n";

?>
