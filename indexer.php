<?php
/**
 * 该文件应该定期使用 PHP-CLI 运行，建议添加到 CRON 任务中
 * 
 * 建议每隔 30 分钟运行一次
 * 
 * 该脚本会自动访问 $RSS_FEED，并将 RSS 内存保存到数据库中
 */
require_once('header.php');
require_once('indexer_popgo.php');
require_once('indexer_dmhy.php');


/// 载入索引器
$indexers = array(
    'popgo' => new Indexer_Popgo(),
    'dmhy' => new Indexer_DMHY(),
);


foreach ($indexers as $src => $indexer) {
    $resources = $indexer->fetch();

    LOGI(sprintf("从 %s 获取了 %d 个资源", $src, count($resources)));

    /// 将资源丢进数据库
    foreach ($resources as $res) {    
        $title = $mysqli->real_escape_string($res['title']);
        $guid = $mysqli->real_escape_string($res['guid']);
        $link = $mysqli->real_escape_string($res['link']);
        $description = $mysqli->real_escape_string($res['description']);
        $pubDate = $res['pubDate'];
        $btih = $mysqli->real_escape_string($res['btih']);
        $magnet = $mysqli->real_escape_string($res['magnet']);
        $src = $mysqli->real_escape_string($src);
        
        
        $ctime = time();
        
        $mysqli->query('start transaction');
        
        $sql = "SELECT * FROM b_resource WHERE btih='{$btih}' LIMIT 1";
        $result = $mysqli->query($sql);
        
        if (!$result) {
            LOGE("数据库查询失败: " . $mysqli->error);
            continue;
        }
        
        if ($result->num_rows > 0) {
            LOGI("{$res['title']} 已存在 (因为已存在相同的 btih，btih={$btih}， src={$res['src']})");
            $mysqli->query('rollback');
            
            continue;
        }

        LOGI("保存来自 {$src} 的数据：{$res['title']}");
        
        $sql = "INSERT INTO b_resource(title, guid, link, description, btih, pubDate, src, magnet, ctime)
                VALUES('${title}', '${guid}', '{$link}', '{$description}', '{$btih}', ${pubDate}, '{$src}', '{$magnet}', ${ctime})";
        $ret = $mysqli->query($sql);
        if ($ret === FALSE) {
            LOGE("无法保存数据: " . $mysqli->error . "，原 SQL: " . $sql);
        }
        
        $mysqli->query('commit');
    }
}

LOGI("索引完成");

?>
