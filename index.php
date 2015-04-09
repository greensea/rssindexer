<?php $__t1 = microtime(TRUE);?>
<DOCTYPE html>
<html lang="zh-CN">

  <head>
    
    <meta charset="utf-8">
    <title>KOTOMI RSS - Anime RSS 索引站</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css" rel="stylesheet">
    
    
    <link rel="alternate" type="application/rss+xml" title="KOTOMI RSS 页面" href="//moe4sale.in/rss.xml" />
  </head>
  <body>
      
      <?php require_once('nav.tpl.php'); ?>
      
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
    $result = search($kw, 50);
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

        
      <?php
      $rss_text = 'RSS 订阅';
      if ($kw != '') {
          $rss_text = 'RSS 订阅搜索结果';
      }
      ?>
      <div class="container">
      <div class="pull-right">
        <a href="rss.xml?kw=<?php echo htmlspecialchars(@$_GET['kw']);?>"><?php echo $rss_text; ?></a>
      </div>
      </div>
      
    <table class="table table-hover table-bordered">
        <tr class="info">
            <th>发布时间</th>
            <th>种子名称</th>
            <th style="width: 5em;">种子链接</th>
            <th style="width: 5em;">磁力链接</th>
            <th style="width: 4em;">源页面</th>
        </tr>
    <?php
    foreach ($result as $res) {
        
    ?>
        <tr>
            <td><?php echo date('Y-m-d H:i', $res['pubDate']);?></td>
            <td><?php echo htmlspecialchars($res['title']);?></td>
            <td><a href="<?php echo htmlspecialchars($res['guid']);?>">下载</a></td>
            <td>
                <?php
                $link = $res['magnet'];
                if ($link == '') {
                    $match;
                    $hash = preg_match('([0-9a-f]{32})', $res['guid'], $match);
                    if ($match) {
                        $link = 'magnet:?xt=urn:btih:' . $match[0];
                    }
                }
                ?>
                <?php if ($link != '') { ?>
                    <a href="<?php echo htmlspecialchars($link);?>">链接</a>
                <?php } else { ?>
                    暂无
                <?php } ?>
            </td>
            <td><a href="<?php echo htmlspecialchars($res['link']);?>">源页面</a></td>
        </tr>
        
    <?php } ?>
    </table>

    <div class="row text-center">
        本站由这些<del>赞助商</del>有爱人士提供支援：
        <a href="http://www.moe4sale.com">螺丝岛</a>
    </div>

    <div class="row">
        <div class="col-sm-12 text-center text-muted">页面执行时间：<?php printf('%0.3f', 1000 * (microtime(TRUE) - $__t1));?>ms</div>
    </div>

    </body>
</html>
