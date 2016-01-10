<DOCTYPE html>
<html lang="zh-CN">

<?php
require_once('header.php');

/// CSP 控制，避免 desc 中带有恶意代码
header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline' maxcdn.bootstrapcdn.com; img-src *; media-src *; script-src http://hm.baidu.com 'nonce-{$CSP_NONCE}'");

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
?>

  <head>
    
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($res['title']);?> - KOTOMI RSS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/index.css" rel="stylesheet">
    
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
      
      <div class="container-fluid">
      <form action="/" method="get" role="form" class="search">
        <div class="form-group">
            <div class="col-sm-5 col-sm-offset-3">
                <input class="form-control" type="text" name="kw" value="<?php echo htmlspecialchars(@$_GET['kw']);?>" placeholder="<?php echo htmlspecialchars($tip);?>" />
            </div>
            <button class="btn btn-primary" type="submit">搜索</button>
        </div>
      </form>
      </div>


        
      <div class="container-fluid text-primary text-center text-large search-result-sub-title">
        <?php echo htmlspecialchars($res['title']);?>
      </div>

      <div class="container-fluid desc">
        <div class="row">
          
          <div class="col-sm-12">
            <div class="alert content">
              <?php echo $res['description'];?>
            </div>
          </div>
          
          
          <div class="col-sm-12">
            <div class="alert content">
              <?php
              $seed_url = btih_seed_url($res['btih']);
              $seed_url = htmlspecialchars($seed_url);
              $magnet = htmlspecialchars($res['magnet']);
              $popularity = '未知';
              if ($res['popularity'] >= 0) {
                $popularity = round($res['popularity']);
              }
              ?>
              <p>资源来源: <a href="<?php echo htmlspecialchars($res['link']);?>"><?php echo htmlspecialchars($res['src']);?></p></a>
              <p>索引建立时间: <?php echo date('Y-m-d H:i:s', $res['ctime']);?></p>
              <p><abbr title="最近 7 日下载次数的估计">热门程度</abbr>: <?php echo $popularity;?></p>
              <p>种子地址(BT): <a href="<?php echo $seed_url;?>"><?php echo htmlspecialchars($res['title']);?></a></p>
              <p>磁力链接(magnet): <a href="<?php echo $magnet;?>"><?php echo htmlspecialchars($res['title']);?></a></p>
            </div>
          </div>

        </div>
      </div>
      
    
    <?php require('footer.tpl.php'); ?>

    </body>
</html>
