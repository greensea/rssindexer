<DOCTYPE html>
<html lang="zh-CN">

<?php
require_once('header.php');

$kw = isset($_GET['kw']) ? $_GET['kw'] : '';
$kw = str_replace('　', ' ', $kw);
$kw = trim($kw);

$page = isset($_GET['page']) ? $_GET['page'] : 1;
$page = (int)max($page, 1);

$result = search($kw, ($page - 1) * $PAGE_SIZE, $PAGE_SIZE, $cnt);

$voted_ids = get_voted_ids(array_column($result, 'resource_id'), $_COOKIE['user_identity'] ?? '');


/// 如果没有传入 page 参数但传入了 kw 参数，则说明这是一次搜索
if (!isset($_GET['page']) && isset($_GET['kw'])) {
    logSearch($kw);
}

?>

  <head>
    
    <meta charset="utf-8">
    <title>KOTOMI RSS - Anime RSS 索引站</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <link href="//cdn.bootcdn.net/ajax/libs/twitter-bootstrap/3.3.4/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/index.css" rel="stylesheet">
    
    <link rel="alternate" type="application/rss+xml" title="KOTOMI RSS 页面" href="//moe4sale.in/rss.xml" />
  </head>
  <body>

<!--     
<div class="alert alert-danger text-center">公告：由于动漫花园使用了 CloudFare 的 DDoS 保护机制，我们无法抓取到动漫花园的种子地址，所以来自动漫花园的资源的种子可能无法下载，请换用磁力链接下载资源</div> 
-->
      <?php require_once('nav.tpl.php'); ?>
      
      <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12 text-center head-title">
                KOTOMI RSS
                <!--<sup class="text-muted beta">BETA</sup>-->
            </div>
            <div class="col-sm-12 text-center head-subtitle">
                Anime RSS 索引站，将你的搜索结果订阅为 RSS 源
            </div>
        </div>
      </div>
      
      <?php require_once('search.tpl.php');?>

        
      <div class="container-fluid text-primary text-center text-large" style="font-size: 1.2em; margin-top: 1.5em;">
      <?php
      if ($kw == '') {
          echo '最新更新的资源列表';
      }
      else {
          $str = htmlspecialchars($_GET['kw']);
          echo "“<strong>{$str}</strong>”的搜索结果（共 " . $cnt . " 个）";
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
            <th style="width: 4em;"><abbr title="根据近期下载次数计算而得">热度</abbr></th>
            <th style="width: 4em;">种子</th>
            <th style="width: 4em;">磁力链</th>
            <th style="width: 4em;">源页面</th>
        </tr>
    <?php
    $abuse = 0;
    foreach ($result as $res) {
        $btih = $res['btih'];
        if (in_array($btih, $ABUSES)) {
            $abuse++;
            continue;
        }
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
            $seedurl = btih_seed_url($btih);
        }
        
        $popularity = '未知';
        if ($res['popularity'] >= 0) {
            $decays = (time() - $res['pmtime']) / 86400 / $POPULARITY_HALFLIFE_DAYS;
            $popularity = $res['popularity'] * pow(2, -1 * $decays);
            $popularity = round($popularity);
        }
        
        $voted_rel = "like";
        $voted_class = "";
        if (in_array($res['resource_id'], $voted_ids)) {
            $voted_rel = "like";
            $voted_class = "heartAnimation";
        }
    ?>
        <tr>
            <td class="pubDate"><?php echo date('Y-m-d H:i:s', $res['pubDate']);?></td>
            
            
            <td class="favicon-<?php echo $res['src'];?>">
                <?php if (trim($res['description'], " \r\n") != ''): ?>
                    <a href="<?php echo btih_desc_url($res['btih']);?>"><?php echo htmlspecialchars($res['title']);?></a>
                <?php else: ?>
                    <?php echo htmlspecialchars($res['title']);?>
                <?php endif;?>
                
                <div class="like-icon" id="like-icon-<?php printf("%d", $res['resource_id']);?>" style="float: right;">
                    <div class="icon <?php echo $voted_class;?>" rel="<?php echo $voted_rel;?>" data-id="<?php printf("%d", $res['resource_id']);?>"></div>
                    <div class="count"><?php printf("%d", $res['vote_score']);?></div>
                </div>
            </td>
            
            <td class="popularity"><?php echo $popularity;?></td>
            
            <?php if ($seedurl): ?>
                <td class="guid"><a href="<?php echo htmlspecialchars($seedurl);?>">种子</a></td>
            <?php else: ?>
                <td class="guid"><abbr title="请使用磁力链接下载">无种子</abbr></td>
            <?php endif; ?>
            
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
    <?php if ($abuse): ?>
   <div class="alert alert-warning">由于收到版权方要求，<?php echo $abuse;?>个资源已经从列表中移除</div> 
    <?php endif; ?>
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
