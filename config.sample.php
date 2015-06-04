<?php
$DB_HOST = 'localhost';
$DB_USER = 'rssindexer';
$DB_PASSWORD = 'xxx';
$DB_DATABASE = 'rssindexer';

/**
 * 进行 HTTP 请求时使用的用户代理字串
 */
$USER_AGENT = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN; rv:1.9.2.4410) Gecko/20110902 Firefox/3.6';

$LOG_PATH = '';     /// 运行日志的地址，如果不需要日志请留空

$RSS_FEED = 'http://share.popgo.org/rss/rss.xml';     /// 填写 RSS 发布页的地址

$PAGE_SIZE = 100;       /// 每页显示的结果数

$BAIDU_STAT_ID = '';    /// 百度网站统计的账号，如果没有请留空

/**
 * 种子下载链接是否使用本站地址。如果 TRUE 则提供本站的种子下载链接，如果 FALSE 则提供漫游的种子下载地址。
 * 该参数最好在部署到生产环境前确定，部署到生产环境后尽量不要修改。一旦修改改参数，RSS 输出中的种子链接就会改变，这会导致用户的订阅客户端重新下载以前已经下载过的种子。
 */
$USE_LOCAL_SEED = FALSE; 


/**
 * 搜索时是否使用全文索引。
 * 如果设为 TRUE，则你必须手动为 MySQL 配置全文索引，如果你不了解 MySQL 的全文索引，请不要打开此选项。（提醒：MySQL 5.7.6 以前的版本的全文索引不支持中文）
 * 如果使用全文索引，需要在 title 字段上建立全文索引
 */
$USE_FULLTEXT = TRUE;


/**
 * 是否使用静态地址替代 seed.php （种子链接下载地址）
 * 如果开启了此选项，请自行在服务器上配置 URL 重写规则：
 *  /seed/{$btih}.torrent  --> /seed.php?btih={$btih}
 * 其中，{$btih} 是一个 40 字节的字符串，仅包含小写英文字母和数字
 * 
 * Nginx 示例：
 *  rewrite /seed/([a-z0-9]+).torrent /seed.php?btih=$1
 */
$STATIC_SEED_URL = FALSE;


/**
 * rssowl 存在一个问题，在下载种子时，如果服务器发送了 Location HTTP 头要求进行重定向（HTTP 301/302 重定向），那么 rssowl 将无法正确识别下载文件的文件名。
 * 如果没有开启静态种子下载地址功能（$STATIC_SEED_URL == TRUE），那么 rssowl 会将下载回来的种子文件命名为 seed.php，在批量下载时，后下载的文件会覆盖先前下载的文件，导致无法完整下载。
 * 开启此选项后，对 RSSOWL 的下载请求将由 PHP 进行处理，这会增加服务器负担。
 * 如果开启了静态种子下载地址功能，则可以关闭此选项。
 * 
 * 
 * 如果你不理解上述说明，或者你不知道你在做什么，请保持此开关开启
 */
$RSSOWL_WORKAROUND = TRUE;


/**
 * 小提示，可以在首页搜索框中随机显示一些小提示，如果不需要显示小提示，请留空。
 * 小提示只能是纯文本，不支持 HTML
 * 如果你想让某个提示出现的次数更多一些，可以把这条提示复制几次
 */
$TIPS = array(
    '小提示：在 RSS 源的链接中增加 limit 参数可以控制 RSS 输出条目的数量',
    '输入关键词',
    '输入关键词',
    '输入关键词',
    '输入关键词',
);

?>
