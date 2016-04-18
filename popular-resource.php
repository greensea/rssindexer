<DOCTYPE html>
<html lang="zh-CN">

<?php
require_once('header.php');


$page = isset($_GET['page']) ? $_GET['page'] : 1;
$page = max($page, 1);

$result = get_popular_resources(($page - 1) * $PAGE_SIZE, $PAGE_SIZE, $cnt);

?>

  <head>
    
    <meta charset="utf-8">
    <title>热门资源排行 - KOTOMI RSS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/index.css" rel="stylesheet">
    
  </head>
  <body>
      
      <?php require_once('nav.tpl.php'); ?>
      
      <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12 text-center head-title">
                KOTOMI RSS
            </div>
        </div>
      </div>
      

      <?php require_once('search.tpl.php');?>
      
        
      <div class="container-fluid text-primary text-center text-large" style="font-size: 1.2em; margin-top: 1em;">
            资源下载热度排行
      </div>

    <div class="container-fluid">
    <table class="table table-hover table-bordered">
        <tr class="info">
            <th style="min-width: 6em;"><abbr title="根据近期下载次数计算而得">热度</abbr></th>
            <th>资源</th>
            <th>发布时间</th>
            <th>最后被下载时间</th>
        </tr>
    <?php
    foreach ($result as $res) {
        $popularity = '未知';
        if ($res['popularity2'] >= 0) {
            $popularity = sprintf('%0.3f', round($res['popularity2'], 3));
        }
    ?>
        <tr>
            
            <td class="popularity"><?php echo $popularity;?></td>
            
            <td class="favicon-<?php echo $res['src'];?>">
                <a href="<?php echo btih_desc_url($res['btih']);?>"><?php echo htmlspecialchars($res['title']);?></a>
            </td>
            
            

            <td><?php echo date('Y-m-d H:i:s', $res['pubDate']);?></td>
            <td><?php echo date('Y-m-d H:i:s', $res['pmtime']);?></td>
        </tr>
        
    <?php } ?>
    </table>
    </div>
    
    <div class="container-fluid">
        <nav style="text-align: center;">
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
            <li><a href="?page=1">首页</a></li>
            
            <?php foreach ($pages as $i) { ?>
            <li <?php if ($i == $page) { ?>class="active"<?php } ?>><a href="?page=<?php echo $i;?>"><?php echo $i;?></a></li>    
            <?php } ?>
            
            <li><a href="?page=<?php echo $page_count;?>">末页</a></li>
          </ul>
        </nav>
    </div>
    
    
    <?php require('footer.tpl.php'); ?>

    </body>
</html>
