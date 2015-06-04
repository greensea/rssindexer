<?php
require_once('header.php');

$kw = isset($_GET['kw']) ? $_GET['kw'] : '';

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
$limit = max($limit, 1);
$limit = min($limit, 100);

$result = search($kw, 0, $limit);

$title = 'KOTOMI RSS';
if (!empty($kw)) {
    $title = "$kw - $title";
}
$title = htmlspecialchars($title);
    

$date = date(DATE_RSS);

header('Content-Type: text/xml');

echo <<<EOF
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
    foreach ($res as $k => $v) {
        if ($k == 'description') {
            continue;
        }
        $res[$k] = htmlspecialchars($v);
        
        $link = $res['guid'];

        $btih = popgo_get_btih_from_link($res['link']);        
        if ($USE_LOCAL_SEED == TRUE && $btih != '') {
            $link = '/' . btih_seed_url($btih);
            $link = mkurl($link);
        }
    }
    
    echo <<<EOF
        <item>
            <title>{$res['title']}</title>
            <guid isPermaLink="false">{$link}</guid>
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
