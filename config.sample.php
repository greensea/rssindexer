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
