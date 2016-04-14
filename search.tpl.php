<?php
/// 获取搜索框提示
$TIPS[] = '输入关键词';
shuffle($TIPS);
$tip = array_pop($TIPS);

/// 获取热门搜索关键字
$popular_kws = get_popular_kws(0, 7);
foreach ($popular_kws as $k => $v) {
    /*
    $popularity = 0;
    if ($v['popularity'] >= 0) {
        $decays = (time() - $v['pmtime']) / 86400 / $POPULARITY_HALFLIFE_DAYS;
        $popularity = $v['popularity'] * pow(2, -1 * $decays);
    }
    */
    if ($v['popularity2'] < 1.001) {
        unset($popular_kws[$k]);
    }
}
?>
<div class="container-fluid search-block">
    <form action="/" method="get" role="form" style="margin-top: 2em;">
        <div class="form-group">
            <div class="col-sm-5 col-sm-offset-3">
                <input class="form-control" type="text" name="kw" value="<?php echo htmlspecialchars(@$_GET['kw']);?>" placeholder="<?php echo htmlspecialchars($tip);?>" />
            </div>
            <button class="btn btn-primary" type="submit">搜索</button>
        </div>
        
        <?php if (!empty($popular_kws)): ?>
        <div class="form-group popular-keywords">
            <div class="col-sm-6 col-sm-offset-3 text-muted">
                热门搜索：
                <?php foreach ($popular_kws as $v): ?>
                    <a href="/?page=1&kw=<?php echo urlencode($v['kw']);?>"><?php echo htmlspecialchars($v['kw']);?></a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
    </form>
</div>
