    <div class="row text-center">
        本站由这些<del>赞助商</del>有爱人士提供支援：
        <a href="http://www.moe4sale.com">螺丝岛</a>
    </div>

    <div class="row">
        <div class="col-sm-12 text-center text-muted">页面执行时间：<?php printf('%0.3f', 1000 * (microtime(TRUE) - $__t1));?>ms</div>
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
