<?php
/**
 * 欢迎使用 rssindexer，请仔细阅读以下的所有配置，并按照你自己的需求修改配置项。
 * 如果有任何问题，可以在 github.com 上创建 issue 询问。
 */

/**
 * MySQL 数据库连接信息
 */
$DB_HOST = 'localhost';
$DB_USER = 'rssindexer';
$DB_PASSWORD = 'xxx';
$DB_DATABASE = 'rssindexer';

/**
 * 进行 HTTP 请求时使用的用户代理字串（抓取外站，如漫游的资源时）
 * 如果你不知道这是什么，请不要修改这项配置
 */
$USER_AGENT = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN; rv:1.9.2.4410) Gecko/20110902 Firefox/3.6';

$LOG_PATH = '';     /// 运行日志的地址，如果不需要日志请留空

/// 在 popgo-gone 版本之后不再有效
//$RSS_FEED = 'http://share.popgo.org/rss/rss.xml';     /// 填写源 RSS 发布页的地址

$PAGE_SIZE = 100;       /// 每页显示的结果数

$GOOGLE_ANALYTICS_ID = ''; /// 谷歌分析的跟踪 ID，如果没有请留空

$BAIDU_STAT_ID = '';    /// 百度网站统计的账号，如果没有请留空

/**
 * 种子下载链接是否使用本站地址。如果 TRUE 则提供本站的种子下载链接，如果 FALSE 则提供漫游的种子下载地址。
 * 该参数最好在部署到生产环境前确定，部署到生产环境后尽量不要修改。一旦修改改参数，RSS 输出中的种子链接就会改变，这会导致用户的订阅客户端重新下载以前已经下载过的种子。
 */
$USE_LOCAL_SEED = TRUE; 



/**
 * 是否使用静态地址替代 seed.php （种子链接下载地址）。
 * 仅 $USE_LOCAL_SEED == TRUE 时该配置项才有效。
 * 
 * 该参数最好在部署到生产环境前确定，部署到生产环境后尽量不要修改。一旦修改改参数，RSS 输出中的种子链接就会改变，这会导致用户的订阅客户端重新下载以前已经下载过的种子。
 * 
 * 如果开启了此选项，请自行在服务器上配置 URL 重写规则：
 *  /seed-{$btih}.torrent  --> /seed.php?btih={$btih}
 * 其中，{$btih} 是一个 40 字节的字符串，仅包含小写英文字母和数字
 * 
 * Nginx 示例：
 *  rewrite /seed-([a-z0-9]+).torrent /seed.php?btih=$1
 */
$STATIC_SEED_URL = FALSE;


/**
 * 是否在全站使用静态链接
 * 
 * 如果设定为 TRUE，资源详情页面（desc.php）的链接会被显示为 info-{$BTIH}.html
 * 你需要自行设定服务器的 URL 重写规则
 * 
 * Nginx 示例：
 *  rewrite /info-([a-z0-9]+).html /desc.php?btih=$1
 */
$STATIC_URL = TRUE;


/**
 * rssowl 存在一个问题，在下载种子时，如果服务器发送了 Location HTTP 头要求进行重定向（HTTP 301/302 重定向），那么 rssowl 将无法正确识别下载文件的文件名。
 * 如果没有开启静态种子下载地址功能（$STATIC_SEED_URL == TRUE），那么 rssowl 会将下载回来的种子文件命名为 seed.php，在批量下载时，后下载的文件会覆盖先前下载的文件，导致无法完整下载。
 * 开启此选项后，对 RSSOWL 的下载请求将由 PHP 进行处理，这可以保证 rssowl 正确识别下载文件的名字，但会增加服务器负担。
 * 如果开启了静态种子下载地址功能，则可以关闭此选项。
 * 
 * 仅 $USE_LOCAL_SEED == TRUE 时该配置项才有效。
 * 
 * 如果你不理解上述说明，或者你不知道你在做什么，请保持此开关开启
 */
$RSSOWL_WORKAROUND = TRUE;

/**
 * 是否过滤比特彗星（BitComet）的提示升级文件。
 * 比特彗星在创建种子时，会在种子文件中添加一些没有用的共享文件，这些没有用的共享文件的文件名是一些提醒用户升级软件的信息，比特彗星借助此手段提醒用户更新软件，以便使用比特彗星自创的“文件按分块大小对齐”功能。
 * 这些文件的文件名类似这样：_____padding_file_9_如果您看到此文件，请升级到BitComet(比特彗星)0.85或以上版本____.!mv
 * 如果关闭此开关，那么资源详情页面中的种子文件列表中就会将比特彗星的升级提醒文件一并显示出来，建议保持此开关开启。
 * 
 * 在此顺便强烈鄙视一下比特彗星这种损人利己的行为，比特彗星真是不愧对“国产软件”（Liu Mang Ruan Jian）的名头。
 */
$FILTER_BITCOMET_PADDING_FILE = TRUE;


/**
 * 搜索时是否使用全文索引。
 * 如果设为 TRUE，则你必须手动为 MySQL 配置全文索引，如果你不了解 MySQL 的全文索引，请不要打开此选项。（提醒：MySQL 5.7.6 以前的版本的全文索引不支持中文）
 * 如果使用全文索引，需要在 title 字段上建立全文索引
 */
$USE_FULLTEXT = TRUE;


/**
 * 资源热度半衰期
 * 
 * 考虑到新番一般是每周一播，将半衰期设为 7，那么上一周的下载贡献的热度正好小于 0.5，四舍五入后正好为 0，意为上周下载对资源热度的贡献正好变为 0
 */
$POPULARITY_HALFLIFE_DAYS = 7;



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


/**
 * 是否使用 webkit 去抓取动漫花园的网页地址。
 * 动漫花园使用了 CloudFare 的 DDoS 保护机制，我们无法直接使用程序抓取动漫花园的资源页。
 * 为了绕开这个保护，我们需要模拟浏览器的行为，如果开启此选项，则将使用 webkit 去抓取动漫花园的资源页内容。
 * 注意：开启此选项需要安装 nodejs 的 phantomjs 模块。要检查安装是否成功，请在本文件目录下执行下面的命令，看看是否能够抓取到网页：
 * 
 * phantomjs webkit_crawl.js http://www.baidu.com baidu.com
 * 
 * 如果能看到百度的源代码，则说明抓取成功。
 */
$DMHY_FETCH_WORKAROUND = TRUE;


/**
 * 如果使用 webkit 去抓取动漫花园的网页地址，需要定义 phantomjs 的路径。
 * 如果不知道 phantomjs 安装在哪里，可以先执行一次 phantomjs 命令，然后再执行 hash 命令，这样就可以看到 phantomjs 的路径了。
 */
$PHANTOMJS_PATH = '/usr/local/bin/phantomjs';

?>
