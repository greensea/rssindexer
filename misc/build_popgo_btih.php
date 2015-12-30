<?php
/**
 * popgo-gone 版本之后，b_resource 表中的 btih 字段变为 UNIQUE 索引，该脚本用于更新 btih 为空的行，以便修改 btih 索引为 UNIQUE 索引
 */
require_once('../header.php');

$sql = "SELECT * FROM b_resource WHERE src='popgo' AND btih=''";
$res = $mysqli->query($sql) or die($mysqli->error);

if (!$res) {
    LOGI("已经没有需要更新的资源了");
    exit(0);
}

LOGI(sprintf("需要更新 %d 个资源", $res->num_rows));

while ($row = $res->fetch_assoc()) {
    $btih = popgo_get_btih_from_link($row['guid']);
    $sql = "UPDATE b_resource SET btih='{$btih}' WHERE resource_id={$row['resource_id']}";
    $mysqli->query($sql) or die($mysqli->error);
}


?>
