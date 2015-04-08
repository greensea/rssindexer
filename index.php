<?php $__t1 = microtime(TRUE);?>
<DOCTYPE html>
<html lang="zh-CN">

  <head>
    
    <meta charset="utf-8">
    <title>KOTOMI RSS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Site CSS -->
    <link href="//cdn.bootcss.com/bootstrap/3.3.4/css/bootstrap.min.css" rel="stylesheet">
    <link href="//cdn.bootcss.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="//static.bootcss.com/www/assets/css/site.min.css?v5" rel="stylesheet">
    
    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="/assets/js/html5shiv.min.js?v=f3008b4099"></script>
      <script src="/assets/js/respond.min.js?v=f3008b4099"></script>
    <![endif]-->

    
    <link rel="alternate" type="application/rss+xml" title="KOTOMI RSS 页面" href="//kotomi-rss.com/rss.xml" />
  </head>
  <body>
      
      <div class="container">
        <div class="row">
            <div class="col-sm-12 text-center" style="font-size: 4em;">
                KOTOMI RSS
            </div>
            <div class="col-sm-12 text-center" style="color: #888; font-size: 1.5em;">
                Anime RSS 索引站
            </div>
        </div>
      </div>
      
      <form action="" method="get" role="form" style="margin-top: 2em;">
        <div class="form-group">
            <div class="col-sm-5 col-sm-offset-3">
                <input class="form-control" type="text" placeholder="输入关键词" name="kw" value="<?php echo htmlspecialchars(@$_GET['kw']);?>" />
            </div>
            <button class="btn btn-primary" type="submit">搜索</button>
        </div>
      
      </form>


<?php
require_once('header.php');
$kw = isset($_GET['kw']) ? $_GET['kw'] : '';
$kw = str_replace('　', ' ', $kw);
$kw = trim($kw);

$result = array();
if ($kw == '') {
    $result = search($kw, 20);
}
else {
    $result = search($kw, 100);
}
?>
        
      <div class="text-primary text-center text-large" style="font-size: 1.2em;">
      <?php
      if ($kw == '') {
          echo '最新更新的资源列表';
      }
      else {
          $str = htmlspecialchars($_GET['kw']);
          echo "“{$str}”的搜索结果（共 " . count($result) . " 个）";
      }
      ?>
      </div>

        
      <?php if ($kw != '') { ?>
      <div class="pull-right">
        <a href="rss.xml?kw=<?php echo htmlspecialchars(@$_GET['kw']);?>">RSS 订阅搜索结果</a>
      </div>
      <?php } ?>
      

    <table class="table table-hover table-bordered">
        <tr class="info">
            <th>发布时间</th>
            <th>种子名称</th>
            <th style="width: 3em;">下载</th>
            <th style="width: 4em;">原页面</th>
        </tr>
    <?php
    foreach ($result as $res) {
        
    ?>
        <tr>
            <td><?php echo date('Y-m-d H:i', $res['pubDate']);?></td>
            <td><?php echo htmlspecialchars($res['title']);?></td>
            <td><a href="<?php echo htmlspecialchars($res['guid']);?>">下载</a></td>
            <td><a href="<?php echo htmlspecialchars($res['link']);?>">原页面</a></td>
        </tr>
        
    <?php } ?>
    </table>

    <div class="row">
        <div class="col-sm-12 text-center text-muted">页面执行时间：<?php printf('%0.3f', 1000 * (microtime(TRUE) - $__t1));?>ms</div>
    </div>

    </body>
</html>
