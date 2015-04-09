<?php
$__t1 = microtime(TRUE);

if (!file_exists('config.php')) {
    die("缺少 config.php 文件，可以尝试 mv config.sample.php config.php");
}
else {
    require_once('config.php');
}

require_once('func.php');


/// 创建数据库连接
$mysqli = new Mysqli($DB_HOST, $DB_USER, $DB_PASSWORD, $DB_DATABASE);
$mysqli->query("set NAMES 'utf8'");
?>
