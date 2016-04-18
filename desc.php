<DOCTYPE html>
<html lang="zh-CN">

<?php
require_once('header.php');

/// CSP 控制，避免 desc 中带有恶意代码
header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline' cdn.bootcss.com; font-src cdn.bootcss.com; img-src *; media-src *; script-src 'self' cdn.bootcss.com hm.baidu.com www.google-analytics.com 'nonce-{$CSP_NONCE}'");

/// 获取资源
$btih = isset($_GET['btih']) ? $_GET['btih'] : '';
$btih = strtolower($btih);
$btih_qs = $mysqli->real_escape_string($btih);
$result = $mysqli->query("SELECT * FROM b_resource WHERE btih='{$btih_qs}'");

if (!$result) {
    die($mysqli->error());
}
$res = $result->fetch_assoc();
if (!$res) {
    die('资源不存在');
}

$TIPS[] = '输入关键词';
shuffle($TIPS);
$tip = array_pop($TIPS);
?>

  <head>
    
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($res['title']);?> - KOTOMI RSS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <link href="//cdn.bootcss.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/index.css" rel="stylesheet">
    
    <script type="text/javascript" src="//cdn.bootcss.com/jquery/2.2.1/jquery.min.js"></script>
    
    <link rel="alternate" type="application/rss+xml" title="KOTOMI RSS 页面" href="//moe4sale.in/rss.xml" />
  </head>
  <body>
      
      <?php require_once('nav.tpl.php'); ?>
      
      <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12 text-center head-title">
                KOTOMI RSS
            </div>
            <div class="col-sm-12 text-center head-subtitle">
                Anime RSS 索引站，将你的搜索结果订阅为 RSS 源
            </div>
        </div>
      </div>
      
      <?php require_once('search.tpl.php');?>


        
      <div class="container-fluid text-primary text-center text-large search-result-sub-title">
        <?php echo htmlspecialchars($res['title']);?>
      </div>

      <div class="container-fluid desc">
        <div class="row">
          
          <div class="col-sm-12">
            <div class="alert content">
              <h4><span class="text-primary">内容介绍</span></h4>
              <?php
              $description = $res['description'];
              $description = str_replace('<a ', '<a rel="nofollow" ', $description);
              echo $description;
              ?>
            </div>
          </div>
          
          
          <div class="col-sm-12">
            <div class="alert content">
              <h4><span class="text-primary">资源信息</span></h4>
              
              <?php
              $seed_url = btih_seed_url($res['btih']);
              $seed_url = htmlspecialchars($seed_url);
              $magnet = htmlspecialchars($res['magnet']);
              $popularity = '未知';
              if ($res['popularity'] >= 0) {
                $decays = (time() - $res['pmtime']) / 86400 / $POPULARITY_HALFLIFE_DAYS;
                $popularity = round($res['popularity'] * pow(2, -1 * $decays));
              }
              ?>
              <p>资源来源: <a href="<?php echo htmlspecialchars($res['link']);?>"><?php echo htmlspecialchars($res['src']);?></p></a>
              <p>索引建立时间: <?php echo date('Y-m-d H:i:s', $res['ctime']);?></p>
              <p><abbr title="根据近期下载次数计算而得">热门程度</abbr>: <?php echo $popularity;?></p>
              <p>种子地址(BT): <a href="<?php echo $seed_url;?>"><?php echo htmlspecialchars($res['title']);?></a></p>
              <p>磁力链接(magnet): <a href="<?php echo $magnet;?>"><?php echo htmlspecialchars($res['title']);?></a></p>
            </div>
          </div>

          <div class="col-sm-12">
            <div class="alert content files">
              <h4><span class="text-primary">文件列表</span></h4>
              <div class="files-loading">正在加载文件列表…</div>
              <table class="table-hover" data-btih="<?php echo htmlspecialchars($res['btih']);?>">
                <tbody>
                </tbody>
              </table>
            </div>
          </div>
          <script type="text/javascript" src="/js/desc.js" async defer></script>


        </div>
        
        
      </div>
      
    
    <?php require('footer.tpl.php'); ?>

    </body>
</html>
