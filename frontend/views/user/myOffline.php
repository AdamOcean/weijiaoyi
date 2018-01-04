<?php common\components\View::regCss('iconfont/iconfont.css') ?>
<?php common\components\View::regCss('mine.css') ?>
<?php common\components\View::regCss('page.css') ?>
<style type="text/css">
/*body {background:#fff;}*/
.head {
    background-color:#e6262e;
    text-align: center;
    box-shadow: 5px 5px 3px #BD5252;
}
.title {
    color: #fff;
    display: block;
    font-size: 15px;
    padding: 10px 0;    
    font-weight: 500px;
    font-family: '微软雅黑';
}
.lineright{border-right:1px solid #fff;}
.list {
    color: #333;
    margin-top: 10px;
    text-align: center;
}
.bg_h {
    background-color: #fff;
    height: 400px;
    color:#333;
}
.content {
    padding: 10px 0;
    margin: 0 6px;
    border-bottom: 1px solid red;
}
.checkbutton{
        background: #f00;
    width: 70%;
    margin: 10px auto;
    color: #fff;
    padding: 10px 0;
    border-radius: 25px;
    font-size: 16px;
}
</style>
<!--头部导航-->
<div class="container bg_h">
    <div class="row head trace">
        <div class="col-xs-4 title lineright"><span>手机号</span></div>
        <div class="col-xs-4 title lineright"><span>充值总额</span></div>
        <div class="col-xs-4 title"><span>返点总额</span></div>
    </div>
    <?= $this->render('_myOffline', compact('data')) ?>
    <?= self::linkPager() ?>

    <!--底部导航 begin-->
    <div class="nav navbar-fixed-bottom clearfix">
        <ul class="footer_nav" style="margin-bottom:-10px;">
            <li>
                <a href="<?= url('site/index') ?>" class="img-foot">
                    <img src="/images/mine_1.png" width="20" height="22" class="img1">
                    <img src="/images/index_1.png" width="20" height="22" class="img2" style="display:none;">
                    <p>分析</p>
                </a>
            </li>
            <li>
                <a href="<?= url('order/position') ?>" class="img-foot">
                    <img src="/images/mine_2.png" width="18" height="22" class="img1">
                    <img src="/images/cqd_4.png" width="18" height="22" class="img2" style="display:none;">
                    <p>持仓单</p>
                </a>
            </li>
            <li>
                <a href="<?= url('user/share') ?>" class="img-foot">
                    <img src="/images/mine_3.png" width="22" height="22" class="img1">
                    <img src="/images/mian_15.png" width="22" height="22" class="img2" style="display:none;">
                    <p>邀请</p>
                </a>
            </li>
            <li>
                <a class="img-foot loginBtn" data-user="<?= user()->isGuest ?>">
                    <img src="/images/cqd_5.png" width="19" height="22" class="img1" style="display:none;">
                    <img src="/images/main_11.png" width="19" height="22" class="img2">
                    <p style="color:#e6262e;">个人中心</p>
                </a>
            </li>
        </ul>
    </div>
</div>