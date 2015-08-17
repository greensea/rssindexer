<?php
/**
 * 利用下载好的漫游 HTML 页面重建漫游以前的资源
 * 
 * 首先你需要自行将漫游资源列表页面下载回来，放到同一个目录中，并明明为 1.html, 2.html, 3.html 等。
 * 该脚本会遍历指定目录下的 html 文件，解析其中的资源，并判断数据库中是否已有该资源，如果没有，则将资源添加到数据库中
 */
 
/// 漫游 HTML 页面保存目录
$popgo_html_archive_dir = '../popgo_html_archive';
$DRY_RUN = FALSE;	/// 是否仅测试运行（即不修改数据库）

require_once('header.php');

if ($DRY_RUN) {
    LOGI("目前运行在测试模式，所有的操作都不会保存");
    $mysqli->autocommit(0);
}


$cnt_new = 0;
$cnt_count = 0;

for ($i = 1; file_exists("${popgo_html_archive_dir}/${i}.html"); $i++) {
    $path = "${popgo_html_archive_dir}/${i}.html";
    $content = file_get_contents($path);
    
    if (!$content) {
        LOGW("无法读取 `{$path}' 文件的内容，跳过该文件");
        continue;
    }
    
    LOGD("解析文件：`{$path}'");
    
    $resources = popgo_parse_html($content);
    if (empty($resources)) {
        LOGW("无法解析 `{$path}' 文件的内容，跳过该文件");
        continue;
    }
    
    LOGI("`{$path}' 中共有 " . count($resources) . " 个资源");
    $cnt_count += count($resources);
    
    foreach ($resources as $res) {
        LOGI("检查“{$res['title']}”是否已经在数据库中");
        
        
        /// 1. 检查数据库中是否有同名，且 btih 为空的资源
        $title = $mysqli->real_escape_string($res['title']);
        $sql = "SELECT COUNT(*) AS cnt FROM b_resource WHERE title='${title}' AND btih=''";
        $result = $mysqli->query($sql);
        if (!$result) {
            LOGW("数据库查询出错，跳过这个资源：" . $mysqli->error);
            continue;
        }
        $row = $result->fetch_assoc();
        if ($row['cnt'] > 0) {
            LOGI("数据库中已经有同名且 btih 为空的资源了，跳过这个资源");
            continue;
        }
        
        
        /// 2. 检查数据库中是否存在相同 btih 的资源
        $btih = $mysqli->real_escape_string($res['btih']);
        $sql = "SELECT COUNT(*) AS cnt FROM b_resource WHERE btih='{$btih}'";
        $result = $mysqli->query($sql);
        if (!$result) {
            LOGW("数据库查询出错，跳过这个资源：" . $mysqli->error);
            continue;
        }
        $row = $result->fetch_assoc();
        if ($row['cnt'] > 0) {
            LOGI("数据库中已经存在相同 btih 的资源了，跳过这个资源");
            continue;
        }
        
        
        /// 3. 将这个资源添加到数据库中
        LOGI("将“{$title}”保存到数据库中");
        
        $guid = $mysqli->real_escape_string($res['guid']);
        $link = $mysqli->real_escape_string($res['link']);
        $pubDate = (int)$res['pubDate'];
        $description = '';
        $ctime = time();
        
        $sql = "INSERT INTO b_resource(title, guid, link, description, btih, pubDate, ctime)
            VALUES('${title}', '${guid}', '{$link}', '{$description}', '{$btih}', ${pubDate}, ${ctime})";
        $ret = $mysqli->query($sql);
        if ($ret === FALSE) {
            LOGW("数据库查询出错：" . $mysqli->error);
        }
        else {
            $cnt_new++;
        }

    }
}

if ($DRY_RUN) {
    LOGI("目前运行在测试模式，将回滚数据库");
    $mysqli->rollback();
}

LOGI("资源索引完成，分析得到 {$cnt_count} 个资源，共新添加了 {$cnt_new} 个资源");
 
?>
