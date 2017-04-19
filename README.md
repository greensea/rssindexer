# rssindexer
索引 BT RSS 资源，提供搜索服务，实现 RSS 大法

## 生产站点

http://moe4sale.in


## 部署方法

1. 在库目录下创建 archive 和 torrent 目录，并保证 PHP 有权读写这两个目录
1. 创建一个网站，网站根目录就是仓库目录
1. 创建网址重写规则：rss.php -> rss.xml
1. 创建 CRON 任务，每隔 30 分钟（建议值）通过 PHP-CLI 在库目录下运行 indexer.php。请保证用户具有对 archive 目录的写权限
1. 创建 CRON 任务，每隔 5 分钟（建议值）通过 PHP-CLI 在库目录下运行 popgo_helper.php。请保证用户具有对 torrent 目录的写权限
1. 使用 db_definition.sql 创建数据库
1. 将 config.sample.php 重命为 config.php，并根据实际情况修改配置

### 注意事项

1. popgo_helper.php 和 seed.php 均会在 torrent 目录下创建子目录，前者作为 CLI 运行，后者作为网页脚本运行。建议使用相同的用户运行 CLI 和网页脚本，避免出现奇怪的权限问题。
1. 如果出现了奇怪的问题，请在 config.php 中配置日志文件，然后查阅日志。如果已经在 config.php 中填写了日志文件地址，但没有看到日志文件，请检查 PHP 是否具有创建和写入日志文件的权限。
