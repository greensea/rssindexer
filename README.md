# rssindexer
索引 BT RSS 资源，提供搜索服务，实现 RSS 大法

## 生产站点

http://moe4sale.in


## 部署方法

1. 在库目录下创建 archive 和 torrent 目录
1. 创建一个网站，网站根目录就是仓库目录
1. 创建网址重写规则：rss.php -> rss.xml
1. 创建 CRON 任务，每隔 30 分钟（建议值）通过 PHP-CLI 在库目录下运行 indexer.php。请保证用户具有对 archive 目录的写权限
1. 创建 CRON 任务，每隔 5 分钟（建议值）通过 PHP-CLI 在库目录下运行 popgo_helper.php。请保证用户具有对 torrent 目录的写权限
1. 使用 db_definition.sql 创建数据库
1. 将 config.sample.php 重命为 config.php，并根据实际情况修改配置
