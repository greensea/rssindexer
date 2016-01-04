<?php
require_once('header.php');

$kw = isset($_GET['kw']) ? $_GET['kw'] : '';
$kw = trim($kw);

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
$limit = max($limit, 1);
$limit = min($limit, 100);

$result = search($kw, 0, $limit);

$title = 'KOTOMI RSS';
if (strlen($kw) > 0) {
    $title = "$kw - $title";
}
$title = htmlspecialchars($title);
    

$date = date(DATE_RSS);

header('Content-Type: text/xml');

echo <<<EOF
<?xml version="1.0" encoding="UTF-8" ?>
<rss xmlns:atom="http://www.w3.org/2005/Atom" version="2.0">
    <channel>
        <title>{$title}</title>
        <link>https://kotomi-rss.moe/</link>
        <description>KOTOMI RSS 资源页</description>
        <language>zh-cn</language>
        <copyright>版权属于原作者所有，本站仅作索引。</copyright>
        <pubDate>${date}</pubDate>

EOF;


foreach ($result as $res) {
    /// 对于漫游，使用 guid 作为资源链接，对于花园，使用 magnet 作为资源链接
    $link = $res['guid'];
    
    $btih = $res['btih'];
    if ($USE_LOCAL_SEED == TRUE && $btih != '') {
        $link = '/' . btih_seed_url($btih);
        $link = mkurl($link);
    }
    
    
    /**
     * 为向后兼容，对 guid 的特殊处理
     * 在 popgo-gone 版本之前，当 $USE_LOCAL_SEED == TRUE 时，对于漫游资源，我们会输出本地种子地址作为 guid
     * 而现在，我们会直接使用原始源的 RSS guid 作为 guid，也就是说，如果 USE_LOCAL_SEED == TRUE 时，如果我们不做处理，
     * 那么输出的 RSS 中的漫游资源的 guid 会改变，这会导致重复下载。
     * 为了解决此问题，在此对 guid 做特殊处理：
     * 1. 如果资源是漫游的，则按照旧方法对 guid 进行处理
     * 2. 如果资源不是漫游的，则直接输出原始 RSS 中的 guid
     */
    $guid = $res['guid'];
    if ($res['src'] == 'popgo') {
        $guid = $link;
    }
    
    

    /// 统一进行 HTML 转义
    foreach ($res as $k => $v) {
        if ($k == 'description') {
            continue;
        }
        $res[$k] = htmlspecialchars($v);        
    }
    $link = htmlspecialchars($link);
    $guid = htmlspecialchars($guid);
    
    /// 输出
    echo <<<EOF
        <item>
            <title>{$res['title']}</title>
            <guid isPermaLink="false">$guid</guid>
            <link>{$res['link']}</link>
            <enclosure url="{$link}" type="application/x-bittorrent" />
            <description><![CDATA[ {$res['description']} ]]></description>
        </item>

EOF;
}

echo <<<EOF
    </channel>
</rss>
EOF;
?>
