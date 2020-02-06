    <div class="container-fluid text-center">
        <div class="row">
        本站资源索引自：<a href="http://share.popgo.org">漫游 BT 发布页</a>
        </div>
        
        <div class="row">
        本站由这些<del>赞助商</del>有爱人士提供支援：
        <a href="https://m-b.science/">麻痹科学网</a>
        </div>
        
        
        <div class="row">
            本站由 reCAPTCHA 提供保护，并遵守相关的谷歌<a href="https://policies.google.com/privacy" rel="nofollow">隐私政策</a>和<a href="https://policies.google.com/terms" rel="nofollow">使用条款</a>
        </div>
        
        <script src="https://www.google.com/recaptcha/api.js?render=<?php echo $RECAPTCHA_CLIENT_KEY;?>"></script>
        <script nonce="<?php echo $CSP_NONCE;?>">
        window.kotomirss_recaptcha_key = "<?php echo $RECAPTCHA_CLIENT_KEY;?>";
        </script>
        <style type="text/css">
        .grecaptcha-badge { 
            visibility: hidden;
        }
        </style>
        
        
    </div>

    <div class="container-fluid footer">
        <div class="col-sm-12 text-center text-muted">
            <span>页面执行时间：<span class="text-info"><?php printf('%0.3f', 1000 * (microtime(TRUE) - $__t1));?>ms</span></span>
            <span>全文索引：<?php echo $USE_FULLTEXT ? '<span class="text-success">已启用</span>' : '未启用';?></span>
        </div>
    </div>


    <script type="text/javascript" src="/js/zepto.min.js"></script>
    <script type="text/javascript" src="/js/zepto.cookie.min.js" ></script>
    <script type="text/javascript" src="/js/index.js"></script>
    
    

    <?php
    if ($BAIDU_STAT_ID != '') {
        if (!$CSP_NONCE) {
            $CSP_NONCE = mt_rand() . microtime(TRUE);
        }
    ?>
        <script nonce="<?php echo $CSP_NONCE;?>">
        var _hmt = _hmt || [];
        (function() {
          var hm = document.createElement("script");
          hm.src = "//hm.baidu.com/hm.js?<?php echo $BAIDU_STAT_ID;?>";
          var s = document.getElementsByTagName("script")[0]; 
          s.parentNode.insertBefore(hm, s);
        })();
        </script>
    <?php } ?>
    
    <?php 
    if ($GOOGLE_ANALYTICS_ID != '') {
        if (!$CSP_NONCE) {
            $CSP_NONCE = mt_rand() . microtime(TRUE);
        }
    ?>
    <script nonce="<?php echo $CSP_NONCE;?>">
      (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
      (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
      m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
      })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

      ga('create', '<?php echo $GOOGLE_ANALYTICS_ID;?>', 'auto');
      ga('send', 'pageview');

    </script>
    <?php } ?>
