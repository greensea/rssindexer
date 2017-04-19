<?php
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
    
    $dir = "/archive/${dir}";
    $dir = __DIR__ . $dir;

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
    $dir = __DIR__ . '/torrent/' . substr($btih, 0, 2) . '/' . substr($btih, 2, 2) . '/';
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
    
    LOGI("保存种子文件到`{$path}'");
    
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
 * 根据 btih 获取种子文件的本地路径
 */
function get_torrent_path($btih) {
    $path = __DIR__ . '/' . get_torrent_relative_path($btih);    
    return $path;
}

/**
 * 根据 btih 获取种子文件相对于 rssindexer 根目录的路径
 */
function get_torrent_relative_path($btih) {
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
 * 获取当前最热门的搜索关键字
 * 
 * @param int   获取的关键字数量，传入 NULL 表示获取所有记录
 * @param int   热度限制
 */
function get_popular_kws($offset = 0, $limit = 5, &$cnt = '__DO_NOT_COUNT__') {
    $offset = (int)$offset;
    $limit = (int)$limit;
    global $POPULARITY_HALFLIFE_DAYS;
    
    $POPULARITY_HALFLIFE_DAYS = (double)$POPULARITY_HALFLIFE_DAYS;
    
    /**
     * 取查询条件 popularity>0.0005，不显示热门度太低的关键字（实际热度会显示为 0.000 热门度的关键字）
     * 另取查询条件 LOG(2, 0.0049999999) < -1 * (UNIX_TIMESTAMP() - pmtime) / 86400 / POPULARITY_HALFLIFE_DAYS)，不显示实际热度会显示为 0.000 的资源。该条件的意思是，假设一个资源的 pmtime 为 1，那么经过 LOG(2, 0.0049999999) 个半衰期后，该资源的计算出来的热度将显示为 0.000
     *  为使用索引，该查询条件变形为 pmtime > 86400 * POPULARITY_HALFLIFE_DAYS * LOG(2, 0.0049999999) + UNIX_TIMESTAMP()
     */
    $sql = "SELECT *, popularity * POW(2, -1 * (UNIX_TIMESTAMP() - pmtime) / 86400 / $POPULARITY_HALFLIFE_DAYS) AS popularity2
    FROM b_keyword_popularity 
    WHERE popularity>0.0005
    AND pmtime > 86400 * $POPULARITY_HALFLIFE_DAYS * LOG(2, 0.0049999999) + UNIX_TIMESTAMP()
    ORDER BY popularity2 DESC, pmtime DESC LIMIT {$offset},{$limit}";
    
    $res = db_query($sql);
    if (!$res) {
        LOGW("数据库查询出错");
        return [];
    }
    
    
    $ret = [];
    while ($row = $res->fetch_assoc()) {
        $ret[] = $row;
    }
    
    
    /// 计算行数
    if ($cnt != '__DO_NOT_COUNT__') {
        $sql = "SELECT COUNT(*) AS cnt FROM b_keyword_popularity
        WHERE popularity>0.0005
        AND LOG(2, 0.0049999999) < -1 * (UNIX_TIMESTAMP() - pmtime) / 86400 / $POPULARITY_HALFLIFE_DAYS";
        
        $result = db_query($sql);
        if (!$result) {
            LOGE("数据库查询出错");
            die();
        }
        else {
            $row = $result->fetch_assoc();
            $cnt = $row['cnt'];
        }
    }
    
    return $ret;
}


/**
 * 获取最受欢迎的资源
 */
function get_popular_resources($offset = 0, $limit = 50, &$cnt = '__DO_NOT_COUNT__') {
    $offset = (int)$offset;
    $limit = (int)$limit;
    global $POPULARITY_HALFLIFE_DAYS;
    
    $POPULARITY_HALFLIFE_DAYS = (double)$POPULARITY_HALFLIFE_DAYS;
    
    /// 此处的两个查询条件的说明详见 get_popular_kws 函数中的注释
    $sql = "SELECT *, popularity * POW(2, -1 * (UNIX_TIMESTAMP() - pmtime) / 86400 / $POPULARITY_HALFLIFE_DAYS) AS popularity2
    FROM b_resource
    WHERE popularity >= 0.0005
    AND pmtime > 86400 * $POPULARITY_HALFLIFE_DAYS * LOG(2, 0.0049999999) + UNIX_TIMESTAMP()
    ORDER BY popularity2 DESC, pmtime DESC LIMIT {$offset},{$limit}";
    
    $res = db_query($sql);
    if (!$res) {
        LOGW("数据库查询出错");
        return [];
    }
    
    
    $ret = [];
    while ($row = $res->fetch_assoc()) {
        $ret[] = $row;
    }
    
    
    /// 计算行数
    if ($cnt != '__DO_NOT_COUNT__') {
        $sql = "SELECT COUNT(*) AS cnt FROM b_resource WHERE
        popularity >= 0.0005
        AND pmtime > 86400 * $POPULARITY_HALFLIFE_DAYS * LOG(2, 0.0049999999) + UNIX_TIMESTAMP()";
        $result = db_query($sql);
        if (!$result) {
            LOGE("数据库查询出错");
            die();
        }
        else {
            $row = $result->fetch_assoc();
            $cnt = $row['cnt'];
        }
    }
    
    return $ret;
}


/**
 * 获取当前用户的 IP 地址
 */
function get_ip() {
    $ip = '';
    if (isset($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    
    return $ip;
}

/**
 * 获取当前用户的 UserAgent
 */
function get_useragent() {
    $useragent = '';
    if (isset($_SERVER['HTTP_USER_AGENT'])) {
        $useragent = $_SERVER['HTTP_USER_AGENT'];
    }
    return $useragent;
}


/**
 * 解析 POPGO 的 HTML 页面，提取 link，btih，magnet，并自动生成 guid 等信息
 * 
 * @return array    成功返回数组，失败时会输出错误信息，并返回空数组
 */
function popgo_parse_html($content) {
    require_once('lib/phpQuery/phpQuery.php');
    
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
    $result = db_query($sql);
    if (!$result) {
        LOGE("数据库查询失败");
        return FALSE;
    }
    
    
    /// 3. 更新资源热度（如果对应的 btih 存在）
    $sql = "SELECT * FROM b_resource WHERE btih='{$btih}'";
    $result = db_query($sql);
    
    if ($result->num_rows <= 0) {
        /// 资源不存在，无需更新
        return TRUE;
    }
    else {
        $res = $result->fetch_assoc();
        if ($res['popularity'] < 0) {
            LOGI("初始化资源 {$btih} 的热度");
            init_popularity($btih);
        }
        
        return update_dht_popularity($res, $ctime);
    }
}


/**
 * 记录一次下载操作，同时更新对应的资源热度
 */
function logDownload($btih) {
    /// 1. 检验数据合法性
    $pattern = '/^[0-9a-f]{40}$/i';
    if (!preg_match($pattern, $btih)) {
        LOGW("传入的 btih({$btih}) 格式不合法");
        return FALSE;
    }
    
    $sql = "SELECT * FROM b_resource WHERE btih='{$btih}'";
    $result = db_query($sql);
    
    if ($result->num_rows <= 0) {
        /// 资源不存在，无法记录
        LOGW("资源`{$btih}'不存在，无法记录下载");
        return FALSE;
    }
    $res = $result->fetch_assoc();
    
    
    
    /// 2. 记录此次 announce_peer
    $ctime = time();
    $ip = db_escape(get_ip());
    $useragent = db_escape(get_useragent());
    
    $sql = "INSERT INTO b_download_log (btih, ctime, ip, useragent) VALUES (UNHEX('{$btih}'), $ctime, '{$ip}', '{$useragent}')";
    $result = db_query($sql);
    if (!$result) {
        LOGE("数据库查询失败");
        return FALSE;
    }
    
    
    /// 3. 更新资源热度（如果对应的 btih 存在）
    if ($res['popularity'] < 0) {
        LOGI("初始化资源 {$btih} 的热度");
        init_popularity($btih);
    }
    
    return update_download_popularity($res, $ctime);
}


/**
 * 记录一个用户搜索操作
 */
function logSearch($kw) {
    $kw = trim($kw);
    if ($kw == '') {
        return TRUE;
    }
    if (mb_strlen($kw) > 256) {
        LOGN("关键字“{$kw}”长度超过 256 个字，不会记录此关键字");
        return FALSE;
    }
    
    $kw_qs = db_escape($kw);
    $ip = db_escape(get_ip());
    $useragent = db_escape(get_useragent());
    $ts = time();
    
    $sql = "INSERT INTO b_keyword_log (ctime, ip, useragent, kw) VALUES ($ts, '{$ip}', '{$useragent}', '{$kw_qs}')";
    
    $result = db_query($sql);
    if (!$result) {
        LOGE("数据库查询失败");
        return FALSE;
    }
    

    return update_kw_popularity($kw, $ts);   
}



/**
 * 给定一个 DHT announce peer 发生时间，更新指定资源的热度
 * 
 * 该方法已弃用，但注意，在删除该方法之前，请转移该方法中关于热度计算算法的说明，因为 update_download/kw_popularity 函数使用了相同的算法，并且没有重复算法说明。如果要删除此函数，请记得将算法说明移动到 update_download/kw_popularity 函数中.
 * 
 * @param array    当前资源数据
 * @param int      在该参数指定的时刻，我们记录了一次 DHT 网络中关于此资源的 announce_peer 消息，于是需要更新 b_resource 表中的缓存
 */
function update_dht_popularity($res, $ts) {
    global $POPULARITY_HALFLIFE_DAYS;
    
    /**
     * 热度算法说明：
     * 热度计算基于下面的假设：
     *  1. 每发生一次下载，就给对应资源增加 1 的热度
     *  2. 每个下载给资源贡献的热度按指数衰减，半衰期为 $POPULARITY_HALFLIFE_DAYS 天。例如：下载刚刚发生时，该下载给资源贡献的热度为 1，下载发生 $POPULARITY_HALFLIFE_DAYS 天过后，该下载对资源贡献的热度就变为 0.5
     *      例如，现在有 3 个下载，分别发生在 1、1.5、5 天前，那么当前这个资源的热度就是：
     *          2^(-1) + 2^(-1.5) + 2^(-5)
     * 算法具体实现：
     *  为了节省资源，我们不会对每一次下载计算其当前对资源热度的贡献，我们会在数据库中缓存资源热度的对数，以便读取资源时快速计算资源热度。
     *  下面演示一下具体的实现：
     *  1. 首次计算热度时，直接计算每个下载对资源热度的贡献，然后求和，并将结果和最后下载时间保存到 popularity 和 pmtime 中
     *  2. 当监听到一次下载时，需要更新资源热度，更新方法是：
     *      popularity * e^(-k * (current_time - pmtime)) + 1
     *     这个公式中的　popularity 是数据库中的 popularity 字段，e 是自然对数，-k 是系数，current_time 和 pmtime 是以日为单位的时间.
     *     在实际计算时，为方便起见，直接使用 2 为底数，这样就省略了 k 参数
     *     利用这个公式，我们就可以递增地计算所有下载对资源的贡献的热度之和，证明很简单，此就处不详述了
     */
    
    /// 0. 检查本次下载是否是一次有效下载（相同的 node_id 在 60s 内对同一个资源的下载记录视为无效记录）
    $sql = "SELECT * FROM b_dht_log WHERE ctime>{$ts} - 60 AND ctime<{$ts} LIMIT 1";
    $result = db_query($sql);
    if (!$result) {
        LOGW("数据库查询出错");
        return FALSE;
    }
    if ($result->num_rows > 0) {
        /// 这是一次重复下载，不会更新热度
        //LOGD("这是一次重复下载，不会更新热度");
        returN TRUE;
    }
    
    
    /// 1. 计算热度
    $days_elapsed = ($ts - $res['pmtime']) / 86400;
    $popularity = $res['popularity'] * pow(2, -1 * ($days_elapsed / $POPULARITY_HALFLIFE_DAYS));
    $popularity += 1;
    
    
    /// 2. 更新数据库
    //LOGD("更新资源 {$res['title']}({$res['btih']}) 的热度为 ${popularity}");
    
    $ts = (int)$ts;
    $sql = "UPDATE b_resource SET popularity={$popularity}, pmtime={$ts} WHERE btih='{$res['btih']}'";
    return db_query($sql);   
}

/**
 * 给定一个下载发生的时间，更新指定资源的热度。
 * 
 * 该函数的算法类似 update_dht_popularity，但使用 b_download_log 表记录的下载信息来辅助计算，其算法与 update_dht_popularity 中的算法相同，算法的具体说明可参考 update_dht_popularity 函数
 * 
 * @param array    当前资源数据
 * @param int      在该参数指定的时刻，发生了一次下载，于是需要更新 b_resource 表中的缓存
 */
function update_download_popularity($res, $ts) {
    global $POPULARITY_HALFLIFE_DAYS;
    
    /// 0. 检查本次下载是否是一次有效下载（相同的 ip 和 useragent 在 10min 内对同一个资源的下载记录视为无效记录）
    $ip = db_escape(get_ip());
    $useragent = db_escape(get_useragent());
    
    $sql = "SELECT * FROM b_download_log WHERE ctime>{$ts} - 600 AND ctime<{$ts} AND btih=UNHEX('{$res['btih']}') AND ip='{$ip}' AND useragent='{$useragent}' LIMIT 1";
    $result = db_query($sql);
    if (!$result) {
        LOGW("数据库查询出错");
        return FALSE;
    }
    if ($result->num_rows > 0) {
        /// 这是一次重复下载，不会更新热度
        //LOGD("这是一次重复下载，不会更新热度");
        returN TRUE;
    }
    
    
    /// 1. 计算热度
    $days_elapsed = ($ts - $res['pmtime']) / 86400;
    $popularity = $res['popularity'] * pow(2, -1 * ($days_elapsed / $POPULARITY_HALFLIFE_DAYS));
    $popularity += 1;
    
    
    /// 2. 更新数据库
    //LOGD("更新资源 {$res['title']}({$res['btih']}) 的热度为 ${popularity}");
    
    $ts = (int)$ts;
    $sql = "UPDATE b_resource SET popularity={$popularity}, pmtime={$ts} WHERE btih='{$res['btih']}'";
    return db_query($sql);   
}


/**
 * 给定一个搜索发生的时间，更新指定关键词的热度
 * 
 * 该函数的算法类似 update_dht_popularity，但使用 b_download_log 表记录的下载信息来辅助计算，其算法与 update_dht_popularity 中的算法相同，算法的具体说明可参考 update_dht_popularity 函数
 * 
 * @param array    当前资源数据
 * @param int      在该参数指定的时刻，发生了一次下载，于是需要更新 b_resource 表中的缓存
 */
function update_kw_popularity($kw, $ts) {
    global $POPULARITY_HALFLIFE_DAYS;
    
    /// 0. 检查本次下载是否是一次有效下载（相同的 ip 和 useragent 在 10min 内对同一个关键字的搜索记录视为无效记录）
    $ip = db_escape(get_ip());
    $useragent = db_escape(get_useragent());
    $kw_qs = db_escape($kw);
    
    $sql = "SELECT * FROM b_keyword_log WHERE ctime>{$ts} - 600 AND ctime<{$ts} AND kw='{$kw_qs}' AND ip='{$ip}' AND useragent='{$useragent}' LIMIT 1";
    $result = db_query($sql);
    if (!$result) {
        LOGW("数据库查询出错");
        return FALSE;
    }
    if ($result->num_rows > 0) {
        /// 这是一次重复搜索，不会更新热度
        LOGD("这是一次重复搜索，不会更新热度");
        returN TRUE;
    }
    
    
    /// 1. 视情况初始化热度
    $result = db_query("SELECT * FROM b_keyword_popularity WHERE kw='{$kw_qs}'");
    $res = NULL;
    if ($result->num_rows == 0) {
        $sql = "INSERT INTO b_keyword_popularity (kw, pmtime, popularity) VALUES ('{$kw_qs}', $ts, 0)";
        $ret = db_query($sql);
        if (!$ret) {
            LOW("数据库查询出错");
            return FALSE;
        }
        $res = [
            'pmtime' => $ts,
            'popularity' => 0,
            'kw' => $kw
        ];
    }
    else {
        $res = $result->fetch_assoc();
    }
    
    
    
    /// 2. 计算热度
    $days_elapsed = ($ts - $res['pmtime']) / 86400;
    $popularity = $res['popularity'] * pow(2, -1 * ($days_elapsed / $POPULARITY_HALFLIFE_DAYS));
    $popularity += 1;
    
    
    /// 3. 更新数据库
    //LOGD("更新搜索词 {$kw} 的热度为 ${popularity}");
    
    $ts = (int)$ts;
    $sql = "UPDATE b_keyword_popularity SET popularity={$popularity}, pmtime={$ts} WHERE kw='{$kw_qs}'";
    return db_query($sql);   
}



/**
 * 重新（初始化）计算一个资源的热度
 */
function init_popularity($btih) {
    global $POPULARITY_HALFLIFE_DAYS;
    
    /// 0. 检验数据合法性
    $pattern = '/^[0-9a-f]{40}$/i';
    if (!preg_match($pattern, $btih)) {
        LOGW("传入的 btih({$btih}) 格式不合法");
        return FALSE;
    }
    
    /// 1. 查询数据
    $ts = time() - $POPULARITY_HALFLIFE_DAYS * 20 * 86400;  /// 只查询 20 个半衰期之内的数据，避免数据量过大
    $sql = "SELECT ip, useragent, ctime FROM b_download_log WHERE btih=UNHEX('{$btih}') AND ctime>${ts} ORDER BY ctime ASC";
    $result = db_query($sql);
    if (!$result) {
        LOGW("数据库查询出错");
        return FALSE;
    }
    
    $popularity = 0;
    $ips = [];
    $uas = [];
    $pmtime = time();
    
    while ($row = $result->fetch_assoc()) {
        if (isset($ips[$row['ip']]) && isset($uas[$row['useragent']]) && ($row['ctime'] - $ips[$row['ip']]['ctime'] < 60)) {
            /// 1 分钟之内的 IP 相同且 UserAgent 相同的请求认为是同一次下载，不予记录
            /// 什么都不用做
        }
        else {
            $days = (time() - $row['ctime']) / 86400;
            $popularity += pow(2, -1 * ($days / $POPULARITY_HALFLIFE_DAYS));
            $pmtime = $row['ctime'];
        }
        
        $ips[$row['ip']] = $row;
        $uas[$row['useragent']] = $row;
    }
    
    
    /// 3. 更新数据库
    LOGD("资源 {$btih} 初始化的热度为 {$popularity}");
    
    $sql = "UPDATE b_resource SET popularity=${popularity}, pmtime={$pmtime} WHERE btih='{$btih}'";
    return db_query($sql);
}



/**
 * 进行一次 SQL 查询，如果连接断开，则自动重新尝试
 */
function db_query($sql) {
    global $mysqli;
    global $DB_HOST;
    global $DB_USER;
    global $DB_PASSWORD;
    global $DB_DATABASE;
    
    //$t1 = microtime(TRUE);
    $result = $mysqli->query($sql);
    //$elapsed = microtime(TRUE) - $t1;
    //LOGW("SQL 查询耗时 $elapsed 秒: {$sql}");
    
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
    
    return $result;
}

/**
 * SQL 转义
 */
function db_escape($str) {
    global $mysqli;
    return $mysqli->real_escape_string($str);
}


/**
 * 到原始网站去将种子文件下载回本地
 * 
 * @param string    BTIH
 * @param int       错误代码，发生错误时使用此错误代码表明错误：0 表示成功，-1 表示传入的 btih 非法，-2 表示源网站返回 404，-3 表示无法获取原始种子下载地址，-4 表示 cURL 发生错误
 * @return string   成功时返回种子文件保存的地址，失败时返回 FALSE
 */
function download_torrent($btih, &$err) {
    global $USER_AGENT;
    
    /// 检查 btih 合法性，btih 应该是一个长度为 40 的哈希字符串
    $ret = preg_match('/^[0-9a-z]{40}$/', $btih);
    if (!$ret) {
        LOGW("传入的 btih 参数非法({$btih})");
        $err = -1;
        return FALSE;
    }
    else {
        $btih = strtolower($btih);
    }


    /// 2. 种子文件不存在，试图去源网站下载并保存
    /// 2.1 解析种子地址
    LOGD("请求的种子文件“{$btih}”不存在，尝试去源网站下载");
    $url = "";
    $res = get_by_btih($btih);
    if (!$res) {
        LOGW("BTIH 为 {$btih} 的资源不存在");
        $err = -2;
        return FALSE;
    }
    
    switch ($res['src']) {
        case 'popgo':
            $url = Indexer_Popgo::getSrcSeedURL($btih);
            break;
        case 'dmhy':
            $url = Indexer_DMHY::getSrcSeedURL($btih);
            break;
        default:
            LOGE("代码不应执行到此处");
            $url = FALSE;
            break;
    }
    if (!$url) {
        LOGW("无法获得 BTIH 为 {$btih} 的资源原始种子地址");
        $err = -3;
        return FALSE;

    }

    /// 2.2 开始下载
    $content = NULL;

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_ENCODING, ''); 
    curl_setopt($ch, CURLOPT_USERAGENT, $USER_AGENT);
    curl_setopt($ch, CURLOPT_REFERER, '');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 5);

    $content = curl_exec($ch);

    if (!$content || curl_error($ch)) {
        LOGD("无法从漫游下载种子（{$url}）:" . curl_error($ch));
        $err = -4;
        return FALSE;
    }


    LOGI("保存种子“{$btih}”");
    $path = archive_torrent($content, $btih);
    
    return $path;
}


/**
 * 使用 webkit 内核去抓取一个网页的源码
 * 
 * @param url       要抓取的网页地址
 * @param keywork   要抓取的网页地址中出现了这个关键词才会返回
 * @param timeout   超时时间，单位：秒
 * @return mixed    成功时返回抓取到的网页的源码，失败时返回空值
 */
 
function webkit_fetch_url($url, $keyword = '', $timeout = 30) {
    global $PHANTOMJS_PATH;
    
    $webkit_crawl = __DIR__ . '/webkit_crawl.js';
    
    $descriptorspec = array(
       0 => array("pipe", "r"),  // 标准输入，子进程从此管道中读取数据
       1 => array("pipe", "w"),  // 标准输出，子进程向此管道中写入数据
       2 => array("pipe", "w")  // 标准错误
    );
    
    $pipes = NULL;

    $cmd = $PHANTOMJS_PATH;
    $cmd .= ' ' . escapeshellarg($webkit_crawl);
    $cmd .= ' ' . escapeshellarg($url);
    $cmd .= ' ' . escapeshellarg($keyword);
    $cmd .= ' ' . escapeshellarg($timeout);
    
    LOGI("执行 phantomjs 命令: `{$cmd}'");
    $process = proc_open($cmd, $descriptorspec, $pipes, '/tmp');

    if (is_resource($process)) {
        stream_set_blocking ( $pipes[1] , FALSE );
        stream_set_blocking ( $pipes[2] , FALSE );
    }
    else {
        LOGW("执行 phantomjs 命令失败: `{$cmd}'");
        return FALSE;
    }
    
    $output = '';

    while (1) {
        $fd_r = [$pipes[1], $pipes[2]];
        $fd_w = [];
        $fd_e = [];
        
        $changed_num = stream_select($fd_r, $fd_w, $fd_e, 1);
        
        if ($changed_num) {
            foreach ($fd_r as $fd) {
                if ($fd == $pipes[1]) {
                    $output .= fgets($pipes[1]);
                }
                else if ($fd == $pipes[2]) {
                    $line = fgets($pipes[2]);
                    if ($line) {
                        LOGI("phantomjs 日志: " . $line);
                    }
                }
            }
        }
        
        
        /// 判断 phantomjs 是否已经执行完毕了，并视情况结束运行
        $status = proc_get_status($process);   
        
        if (!$status || !$status['running']) {
            foreach ($pipes as $pipe) {
                fclose($pipe);
            }
            $ret = proc_close($process);
            
            LOGD("phantomjs 结束运行，退出代码是 {$status['exitcode']}");
            break;
        }
    }


    return $output;
}


function apiout($code, $message = NULL, $data = NULL) {
    header('Content-Type: application/json');
    
    $output = array(
        'code' => $code
    );
    if ($message !== NULL) {
        $output['message'] = $message;
    }
    if ($data !== NULL) {
        $output['data'] = $data;
    }
    
    $output = json_encode($output, JSON_UNESCAPED_UNICODE);
    echo $output;
    
    die();
}

?>
