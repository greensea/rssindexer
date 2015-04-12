    <div class="container-fluid text-center">
        <div class="row">
        本站资源索引自：<a href="http://share.popgo.org">漫游 BT 发布页</a>
        </div>
        
        <div class="row">
        本站由这些<del>赞助商</del>有爱人士提供支援：
        <a href="https://m-b.science/">麻痹科学网</a>
        </div>
    </div>

    <div class="container-fluid footer">
        <div class="col-sm-12 text-center text-muted">
            <span>页面执行时间：<span class="text-info"><?php printf('%0.3f', 1000 * (microtime(TRUE) - $__t1));?>ms</span></span>
            <span>全文索引：<?php echo $USE_FULLTEXT ? '<span class="text-success">已启用</span>' : '未启用';?></span>
        </div>
    </div>

    <?php if ($BAIDU_STAT_ID != '') { ?>
    <script>
    var _hmt = _hmt || [];
    (function() {
      var hm = document.createElement("script");
      hm.src = "//hm.baidu.com/hm.js?<?php echo $BAIDU_STAT_ID;?>";
      var s = document.getElementsByTagName("script")[0]; 
      s.parentNode.insertBefore(hm, s);
    })();
    </script>
    <?php } ?>
