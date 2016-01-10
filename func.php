<?php
require_once('header.php');


/// 打印日志到指定的文件中
function LOGS($log) {
    global $LOG_PATH;
    
    $bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
    $pid = posix_getpid();
    
    $log = $log . sprintf(' (%d,%s:%d)', $pid, basename(@$bt[1]['file']), @$bt[1]['line']);
        
    
    
    //syslog(LOG_INFO, $log);
    
    /// 附加日期
    $log = '[' . date(DATE_RFC822) . '] ' . $log . "\n";
    
    
    if (php_sapi_name() == 'cli') {
        echo $log;
    }
    
    if ($LOG_PATH) {
        file_put_contents($LOG_PATH, $log, FILE_APPEND);
    }
}

/// 打印 Warning 级别的日志到 Syslog
function LOGW($log) {
    LOGS($log);
}

/// 打印 Error 级别的日志到 Syslog
function LOGE($log) {
    LOGS($log);
}

/// 打印 Notice 级别的日志到 Syslog
function LOGN($log) {
    LOGS($log);
}

/// 打印 Info 级别的日志到 Syslog
function LOGI($log) {
    LOGS($log);
}

/// 打印 DEBUG 级别的日志到 Syslog
function LOGD($log) {
    LOGS($log);    
}



/**
 * 将原始数据保存到 archive/年/月/日 目录下
 */
function archive_raw($content) {
    $dir = date('Y/m/d/');
    
    $dir = "archive/${dir}";
    if (!is_dir($dir)) {
        mkdir($dir, 0755, TRUE);
    }
    
    $path = $dir . sprintf('%.6f', microtime(TRUE)) . '.xml';
    
    file_put_contents($path, $content);
}


/**
 * 将一个种子文件进行归档
 */
function archive_torrent($raw, $btih) {
    /// FIXME: 应该使用 get_torrent_path 来获取路径
    $dir = 'torrent/' . substr($btih, 0, 2) . '/' . substr($btih, 2, 2) . '/';
    $path = $dir . $btih . '.torrent';
    
    if (!is_dir($dir)) {
        $umask = umask();
        umask(0002);
        
        $ret = mkdir($dir, 0775, TRUE);
        
        umask($umask);
        
        if (!$ret) {
            LOGE("无法创建目录“{$dir}”: " . var_export(error_get_last(), TRUE));
        }
    }
    
    echo "保存种子文件到`{$path}'\n";
    
    $ret = file_put_contents($path, $raw);
    if (!$ret) {
        LOGW("无法保存种子文件到`{$path}': " . var_export(error_get_last(), TRUE));
        return FALSE;
    }
    else {
        return $path;
    }
}

/**
 * 根据 btih 获取种子文件的路径
 */
function get_torrent_path($btih) {
    $dir = 'torrent/' . substr($btih, 0, 2) . '/' . substr($btih, 2, 2) . '/';
    $path = $dir . $btih . '.torrent';
    
    return $path;
}

/**
 * 将原始 RSS 数据解析为资源数组
 */
function parse_rss($content) {
    $xml = simplexml_load_string($content);
    if (!$xml) {
        return FALSE;
    }
    
    
    
    if (!isset($xml->channel)) {
        echo "RSS 资源中没有 channel 字段\n";
        return FALSE;
    }
    
    if (!isset($xml->channel->item)) {
        echo "RSS 资源中没有 item 字段\n";
        return FALSE;
    }
    
    $items = array();
    if (!is_array($xml->channel->item)) {
        $items = array($xml->channel->item);
    }
    else {
        $items = $xml->channel->item;
    }
    
    $ret = array();
    
    foreach ($xml->channel->item as $item) {
        $ret[] = array(
            'title' => $item->title,
            'guid' => $item->guid,
            'pubDate' => $item->pubDate,
            'link' => $item->link,
            'description' => $item->description
        );
    }
    
    return $ret;
}


/**
 * 根据给定的关键字搜索资源
 */
function search($kw, $offset = 0, $limit = 100, &$count = '__DO_NOT_COUNT__') {
    global $mysqli;
    global $USE_FULLTEXT;
    
    $kw = str_replace('　', ' ', $kw);
    $kw = str_replace('+', ' ', $kw);
    $kws = explode(' ', $kw);
    
    $conds = array();
    
    foreach ($kws as $k) {
        $k = trim($k);
        if ($k == '') {
            continue;
        }
        
        $k = $mysqli->real_escape_string($k);
        
        
        if ($USE_FULLTEXT) {
            /// 全文索引的查询条件
            $conds[] = "(MATCH(title) AGAINST ('{$k}' IN BOOLEAN MODE))";
        }
        else {
            /// 非全文索引的查询条件
            //$conds[] = "(title LIKE '%{$k}%' OR description LIKE '%{$k}%')";
            $conds[] = "(title LIKE '%{$k}%')";
        }
    }
    
    $where = '';
    if (!empty($conds)) {
        $where = ' WHERE ' . implode(' AND ', $conds);
    }
    
    /// 查询资源
    $sql = "SELECT * FROM b_resource {$where} ORDER BY pubDate DESC LIMIT {$offset},${limit}";
    $result = $mysqli->query($sql);
    if (!$result) {
        LOGE($mysqli->error);
        die();
    }
    
    $rows = array();
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    
    
    /// 查询总行数
    if ($count !== '__DO_NOT_COUNT__') {
        $sql = "SELECT COUNT(*) AS cnt FROM b_resource {$where}";
        $result = $mysqli->query($sql);
        if (!$result) {
            LOGE($mysqli->error);
            die();
        }
        else {
            $row = $result->fetch_assoc();
            $count = $row['cnt'];
        }
    }
    
    
    return $rows;
}


/**
 * 根据 BTIH 获取一个资源在数据库中的数据
 */
function get_by_btih($btih) {
    global $mysqli;
    
    $btih_qs = $mysqli->real_escape_string($btih);
    $sql = "SELECT * FROM b_resource WHERE btih='{$btih_qs}'";
    
    $result = $mysqli->query($sql);
    
    if (!$result) {
        LOGE($mysqli->error);
        return FALSE;
    }
    else {
        return $result->fetch_assoc();
    }
}
    

/**
 * 解析 POPGO 的 HTML 页面，提取 link，btih，magnet，并自动生成 guid 等信息
 * 
 * @return array    成功返回数组，失败时会输出错误信息，并返回空数组
 */
function popgo_parse_html($content) {
    require_once('phpQuery/phpQuery.php');
    
    $dom = phpQuery::newDocumentHTML($content);
    if (!$dom) {
        LOGE("无法解析漫游页面，原始内容：" . $content);
        return array();
    }
    
    $ret = array();
    
    for ($i = 0; $i < $dom->find('#index_maintable tr')->length(); $i++) {
        $pubDate = $dom->find("#index_maintable tr")->eq($i)->find("td")->eq(1)->text();
        $title = $dom->find("#index_maintable tr")->eq($i)->find("td.inde_tab_seedname")->text();
        $magnet = $dom->find("#index_maintable tr")->eq($i)->find("td")->eq(9)->find("a")->attr("href");
        $link = $dom->find("#index_maintable tr")->eq($i)->find("td")->eq(3)->find("a")->attr("href");
        $btih = popgo_get_btih_from_link($link);
        
        /// 针对 pubDate 格式的一点调整
        $pubDate = '20' . substr($pubDate, 0, 8) . ' ' . substr($pubDate, 8);
        
        if (stripos($title, '置顶') !== FALSE) {
            continue;
        }
        if ($title == '') {
            continue;
        }
        
        $ret[] = array(
            'title' => $title,
            'magnet' => $magnet,
            'link' => 'http://share.popgo.org' . $link,
            'guid' => popgo_get_seed_url($btih),            /// FIXME: 使用 Indexer_Popgo::getSrcSeedURL 函数替代之
            'pubDate' => strtotime($pubDate),
            'btih' => $btih,
        );
    }
    
    return $ret;
}

function popgo_get_btih_from_link($link) {
    $match = array();
    preg_match('([0-9a-f]{40})', $link, $match);
    
    if (!empty($match)) {
        return $match[0];
    }
    else {
        return NULL;
    }
}

/**
 * 根据 BTIH 生成漫游的种子下载链接
 */
function popgo_get_seed_url($btih) {
    return sprintf('http://share.popgo.org/downseed.php?hash=%s', $btih);
}


/**
 * 将一个相对地址转换成本站的绝对地址
 */
function mkurl($relpath) {
    $schema = 'http://';
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']) {
        $schema = 'https://';
    }
    
    if (substr($relpath, 0, 1) != '/') {
        $relpath = '/' . $relpath;
    }
    
    return $schema . $_SERVER['HTTP_HOST'] . $relpath;
}

/**
 * 根据一个 BTIH 值，生成下载地址
 * 根据配置文件中的 STATIC_SEED_URL 选项的配置，该函数会返回类似 seed.php?btih=xxx 的动态地址或静态地址
 */
function btih_seed_url($btih) {
    global $STATIC_SEED_URL;
    
    if ($STATIC_SEED_URL == TRUE) {
        return "seed-{$btih}.torrent";
    }
    else {
        return "seed.php?btih={$btih}";
    }
}

/**
 * 生成 desc 页面的 URL
 */
function btih_desc_url($btih) {
    global $STATIC_URL;
    
    $btih = urlencode($btih);
    
    if ($STATIC_URL == TRUE) {
        return "info-{$btih}.html";
    }
    else {
        return "desc.php?btih=${btih}";
    }
}


/**
 * 将字符串以可读的十六进制格式输出（小写）
 */
function hexdump($s) {
    $s = array_map('ord', str_split($s, 1));
    $s = array_map('dechex', $s);
    $ret = '';
    foreach ($s as $v) {
        $ret .= str_pad($v, 2, '0', STR_PAD_LEFT);
    }
    
    return $ret;
}


/**
 * 将数据保存到数据库中
 */
function save2db($table, $data) {
    global $mysqli;
    
    $keys = [];
    $values = [];
    foreach ($data as $k => $v) {
        $keys[] = $mysqli->real_escape_string($k);
        $values[] = "'" . $mysqli->real_escape_string($v) . "'";
    }
    
    $table = $mysqli->real_escape_string($table);
    
    $sql = "INSERT INTO {$table} ( " . implode(',', $keys) . ") VALUES (" . implode(',', $values) . ")";
    
    $result = $mysqli->query($sql);
    if (!$result) {
        LOGW("第一次查询数据库失败，尝试重连后再次插入. SQL: ${sql}");
        
        $mysqli->ping();
        $result = $mysqli->query($sql);
        if (!$result) {
            LOGW("第二次查询失败, SQL: {$sql}");
            return FALSE;
        }
    }
    
    return TRUE;
}

/**
 * 保存一个 announce_peer 数据，同时更新资源的 7 日下载数
 */
function logDHTAnnouncePeer($node_id, $btih) {
    global $mysqli;
    global $DB_HOST;
    global $DB_USER;
    global $DB_PASSWORD;
    global $DB_DATABASE;
    
    /// 1. 检验数据合法性
    $pattern = '/^[0-9a-f]{40}$/i';
    if (!preg_match($pattern, $node_id)) {
        LOGW("传入的 node_id({$node_id}) 格式不合法");
        return FALSE;
    }
    if (!preg_match($pattern, $btih)) {
        LOGW("传入的 btih({$btih}) 格式不合法");
        return FALSE;
    }
    
    
    /// 2. 记录此次 announce_peer
    $ctime = time();
    $sql = "INSERT INTO b_dht_log (node_id, btih, ctime) VALUES (UNHEX('{$node_id}'), UNHEX('{$btih}'), $ctime)";
    $result = $mysqli->query($sql);
    if (!$result) {
        LOGN("第一次查询 MySQL 失败，重试后准备继续: " . $mysqli->error . ". SQL: ${sql}");
        
        /// 重连 MySQL
        /// FIXME: 连接 MySQL 应该独立成一个函数
        $ret = $mysqli->real_connect($DB_HOST, $DB_USER, $DB_PASSWORD, $DB_DATABASE);
        if (!$ret) {
            LOGE('无法创建 MySQL 连接: ' . $mysqli->error);
            return FALSE;
        }
        $mysqli->query("set NAMES 'utf8'");
        
        
        $result = $mysqli->query($sql);
        if (!$result) {
            LOGW("第二次查询 MySQL 失败: " . $mysqli->error . ". SQL: ${sql}");
            return FALSE;
        }
    }
    
    
    /// 3. 更新资源热度（如果对应的 btih 存在）
    $sql = "SELECT * FROM b_resource WHERE btih='{$btih}'";
    $result = $mysqli->query($sql);
    if ($result->num_rows <= 0) {
        /// 资源不存在，无需更新
        return TRUE;
    }
    
    $popularity = 0;
    $nodes = [];
    
    $ts = time() - 86400 * 7;   /// 查询最近 7 日的记录
    $sql = "SELECT node_id, ctime FROM b_dht_log WHERE btih=UNHEX('{$btih}') AND ctime>{$ts} ORDER BY ctime ASC";
    $result = $mysqli->query($sql);
    
    while ($row = $result->fetch_assoc()) {
        if (isset($nodes[$row['node_id']]) && ($row['ctime'] - $nodes[$row['node_id']]['ctime'] < 60)) {
            /// 1 分钟之内的相同 node_id 请求的相同的资源认为是同一次下载，不予记录
            /// 什么都不用做
        }
        else {
            $popularity++;
        }
        
        $nodes[$row['node_id']] = $row;
    }
    
    $sql = "UPDATE b_resource SET popularity={$popularity} WHERE btih='{$btih}'";
    $mysqli->query($sql);
    
    return TRUE;
}

?>
