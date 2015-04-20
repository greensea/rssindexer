<DOCTYPE html>
<html lang="zh-CN">

  <head>
    
    <meta charset="utf-8">
    <title>KOTOMI RSS - Anime RSS 索引站</title>
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
                <sup class="text-muted beta">BETA</sup>
            </div>
            <div class="col-sm-12 text-center head-subtitle">
                Anime RSS 索引站，将你的搜索结果订阅为 RSS 源
            </div>
        </div>
      </div>
      
      <div class="container-fluid">
      <form action="" method="get" role="form" style="margin-top: 2em;">
        <div class="form-group">
            <div class="col-sm-5 col-sm-offset-3">
                <input class="form-control" type="text" placeholder="输入关键词" name="kw" value="<?php echo htmlspecialchars(@$_GET['kw']);?>" />
            </div>
            <button class="btn btn-primary" type="submit">搜索</button>
        </div>
      </form>
      </div>


<?php
require_once('header.php');

$kw = isset($_GET['kw']) ? $_GET['kw'] : '';
$kw = str_replace('　', ' ', $kw);
$kw = trim($kw);

$page = isset($_GET['page']) ? $_GET['page'] : 1;
$page = max($page, 1);

$result = array();
if ($kw == '') {
    $result = search($kw, ($page - 1) * $PAGE_SIZE, $PAGE_SIZE, $cnt);
}
else {
    $result = search($kw, ($page - 1) * $PAGE_SIZE, $PAGE_SIZE, $cnt);
}
?>
        
      <div class="container-fluid text-primary text-center text-large" style="font-size: 1.2em;">
      <?php
      if ($kw == '') {
          echo '最新更新的资源列表';
      }
      else {
          $str = htmlspecialchars($_GET['kw']);
          echo "“{$str}”的搜索结果（共 " . $cnt . " 个）";
      }
      ?>
      </div>

        
      <?php
      $rss_text = 'RSS 订阅';
      if ($kw != '') {
          $rss_text = 'RSS 订阅搜索结果';
      }
      ?>
      <div class="container-fluid text-right">
        <a href="rss.xml<?php echo ($kw == '') ? '' : '?kw=' . htmlspecialchars($kw); ?>">
		<i class="glyphicon glyphicon-signal"></i>
		<?php echo $rss_text; ?>
	</a>
      </div>
    <div class="container-fluid">
    <table class="table table-hover table-bordered resources">
        <tr class="info">
            <th>发布时间</th>
            <th>种子名称</th>
            <th style="width: 5em;">种子链接</th>
            <th style="width: 5em;">磁力链接</th>
            <th style="width: 4em;">源页面</th>
        </tr>
    <?php
    foreach ($result as $res) {
        $btih = $res['btih'];
        
        $link = $res['magnet'];
        if ($link == '') {
            $match;
            $hash = preg_match('([0-9a-f]{40})', $res['guid'], $match);
            if ($match) {
                $link = 'magnet:?xt=urn:btih:' . $match[0];
                
                if ($btih == '') {
                    $btih = $match[0];
                }
            }
        }


        $seedurl = $res['guid'];
        if ($USE_LOCAL_SEED == TRUE && $btih != '') {
            $seedurl = "seed.php?btih={$btih}";
        }
    ?>
        <tr>
            <td class="pubDate"><?php echo date('Y-m-d H:i:s', $res['pubDate']);?></td>
            <td><?php echo htmlspecialchars($res['title']);?></td>
            <td class="guid"><a href="<?php echo htmlspecialchars($seedurl);?>">种子</a></td>
            <td class="link">
                <?php if ($link != '') { ?>
                    <a href="<?php echo htmlspecialchars($link);?>">磁力</a>
                <?php } else { ?>
                    暂无
                <?php } ?>
            </td>
            <td class="source"><a href="<?php echo htmlspecialchars($res['link']);?>">源页面</a></td>
        </tr>
        
    <?php } ?>
    </table>
    </div>
    
    <div class="container-fluid">
        <nav class="pull-right">
          <ul class="pagination">
            <?php
            $pages = array();
            $page_count = max(1, ceil($cnt / $PAGE_SIZE));
            
            /// 显示前后 4 页
            for ($i = $page; $i >= 1 && $i >= $page - 4; $i--) {
                $pages[] = $i;
            }
            for ($i = $page; $i <= $page_count && $i <= $page + 4; $i++) {
                $pages[] = $i;
            }
            $pages = array_unique($pages);
            sort($pages);
            ?>
            <li><a href="?kw=<?php echo htmlspecialchars($kw);?>&page=1">首页</a></li>
            
            <?php foreach ($pages as $i) { ?>
            <li <?php if ($i == $page) { ?>class="active"<?php } ?>><a href="?kw=<?php echo htmlspecialchars($kw);?>&page=<?php echo $i;?>"><?php echo $i;?></a></li>    
            <?php } ?>
            
            <li><a href="?kw=<?php echo htmlspecialchars($kw);?>&page=<?php echo $page_count;?>">末页</a></li>
          </ul>
        </nav>
    </div>
    
    
    <?php require('footer.tpl.php'); ?>

    </body>
</html>
