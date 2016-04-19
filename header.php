<?php
$__t1 = microtime(TRUE);

if (!file_exists(__DIR__ . '/config.php')) {
    die("缺少 config.php 文件，可以尝试 mv config.sample.php config.php");
}
else {
    require_once(__DIR__ . '/config.php');
}


/// 创建数据库连接
$mysqli = new Mysqli($DB_HOST, $DB_USER, $DB_PASSWORD, $DB_DATABASE);
if (!$mysqli) {
    LOGE('无法创建 MySQL 连接: ' . $mysqli->error);
    die();
}
$mysqli->query("set NAMES 'utf8'");

$CSP_NONCE = uniqid() . 'X' . mt_rand();



require_once(__DIR__ . '/func.php');

function ob_i18n_handler($in) {
    $pattern = <<<EOF
(<([a-zA-Z]+) [^<]*rel=["|']i18n["|'][^>]*>)(.+)<\/\2>/gsU'
EOF;
    $matches = NULL;
    preg_match($pattern, $in, $matches);
}
?>
