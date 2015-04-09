<?php
/**
 * 该文件应该定期使用 PHP-CLI 运行，建议添加到 CRON 任务中
 * 
 * 建议每隔 30 分钟运行一次
 * 
 * 该脚本会自动访问 $RSS_FEED，并将 RSS 内存保存到数据库中
 */
require_once('header.php');

/// 1. 获取资源
echo "正在获取 $RSS_FEED\n";

$content = NULL;

$ch = curl_init($RSS_FEED);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch, CURLOPT_ENCODING, ''); 
curl_setopt($ch, CURLOPT_USERAGENT, $USER_AGENT);
$content = curl_exec($ch);

if (!$content) {
    die("无法抓取 RSS：`${RSS_FEED}'");
}


/// 2. 归档原始数据
echo "正在归档数据\n";

archive_raw($content);


/// 3. 解析资源
echo "正在解析资源\n";

$resources = parse_rss($content);
if (!$resources) {
    die('无法解析 RSS 资源：' . $content);
}

printf("共 %d 个资源\n", count($resources));


/// 4. 将资源丢进数据库
foreach ($resources as $res) {    
    
    $title = $mysqli->real_escape_string($res['title']);
    $guid = $mysqli->real_escape_string($res['guid']);
    $link = $mysqli->real_escape_string($res['link']);
    $description = $mysqli->real_escape_string($res['description']);
    $pubDate = strtotime($res['pubDate']);
    
    $btih = '';
    $match = array();
    preg_match('([0-9a-f]{40})', $res['link'], $match);
    if (!empty($match)) {
        $btih = $match[0];
        $btih = $mysqli->real_escape_string($btih);
    }
    else {
        echo "警告：无法从 `{$res['link']}' 中解析出 BTIH\n";
    }
    
    
    $ctime = time();
    
    $mysqli->query('start transaction');
    
    $sql = "SELECT * FROM b_resource WHERE guid='{$guid}' LIMIT 1";
    $result = $mysqli->query($sql);
    
    if ($result->num_rows > 0) {
        echo "{$res['title']} 已存在\n";
        $mysqli->query('rollback');
        
        continue;
    }

    echo "保存数据：{$res['title']}\n";
    
    $sql = "INSERT INTO b_resource(title, guid, link, description, btih, pubDate, ctime)
            VALUES('${title}', '${guid}', '{$link}', '{$description}', '{$btih}', ${pubDate}, ${ctime})";
    $ret = $mysqli->query($sql);
    if ($ret === FALSE) {
        echo $mysqli->error . "\n";
    }
    
    $mysqli->query('commit');
}

echo "索引完成\n";

?>
