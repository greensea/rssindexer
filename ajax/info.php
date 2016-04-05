<?php
require_once('../header.php');
require_once('../indexer_popgo.php');
require_once('../indexer_dmhy.php');
require_once('../lib/lightbenc.php');

/// 1. 获取资源
$btih = isset($_GET['btih']) ? $_GET['btih'] : '';

/// 2. 获取 BT 文件
$path = get_torrent_path($btih);

if (!file_exists($path)) {
    /// 种子不存在，下载之
    $err = 0;
    $path = download_torrent($btih, $err);
    if (!$path) {
        apiout(-1, '无法获取种子文件信息');
    }
}


/// 3. 解码并输出
$lb = new lightbenc();
$raw = $lb->bdecode(file_get_contents($path));

if (!$raw) {
    apiout(-2, '无法解析种子文件');
}

$data = [];
foreach (['announce', 'announce-list', 'created by', 'creation date'] as $k) {
    if (isset($raw[$k])) {
        $data[$k] = $raw[$k];
    }
}

/// 如果 info 中没有 files 字段，则种子文件只包含一个共享文件，且该共享文件信息直接放在 info 字段中

if (!isset($raw['info']['files'])) {
    $raw['info']['files'] = [$raw['info']];
}

$data['files'] = [];
foreach ($raw['info']['files'] as $file) {
    if (!isset($file['path']) && isset($file['name'])) {
        $file['path'] = $file['name'];
    }
    
    if (!is_array($file['path'])) {
        $file['path'] = [$file['path']];
    }
    
    $f = [
        'path' => implode('/', $file['path']),
        'size' => $file['length'],
    ];
    
    /// 过滤比特彗星的垃圾文件
    if ($FILTER_BITCOMET_PADDING_FILE == TRUE) {
        if (strpos($f['path'], '_____padding_file_') === 0 && stripos($file['path'], 'BitComet') !== FALSE) {
            $f = NULL;
        }
    }
    
    if ($f) {
        $data['files'][] = $f;
    }
}


apiout(0, '', $data);
    
?>
