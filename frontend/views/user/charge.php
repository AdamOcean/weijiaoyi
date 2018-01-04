<?php $this->regCss('geren.css') ?>
<div class="personal">
    <p class="charge-header"> <a href="javascript:window.history.back()" style="float: left;"><img src="/images/arrow-left.png" style="width:40px;"></a><span>充值</span></p>
    <div class="boxflex boxflex1">
        <div class="img-wrap"><img class="userimage" src="<?= u()->face ?>"></div>
        <div class="box_flex_1">
            <div class="p_zichan"><?= u()->nickname ?></div>
        </div>
    </div>
    <?php $form = self::beginForm(['showLabel' => false, 'action' => url('user/pay/'), 'id' => 'payform']) ?>
    <div class="boxflex1 mt10">
        <div class="moneyhead">充值金额</div>
        <div class="group_btn clearfloat">
            <div class="btn_re">
                <a class="btn_money active">5000</a>
            </div>
            <div class="btn_re btn_center">
                <a class="btn_money">2000</a>
            </div>
            <div class="btn_re btn_center">
                <a class="btn_money">1000</a>
            </div>
            <div class="btn_re">
                <a class="btn_money">500</a>
            </div>
            <div class="btn_re">
                <a class="btn_money">300</a>
            </div>
        </div>
        <div class="group_btn group clearfloat">
            <div class="btn_re">
                <a class="btn_money">100</a>
            </div>
            <div class="btn_re btn_center">
                <a class="btn_money">50</a>
            </div>
            <div class="btn_re btn_center">
                <a class="btn_money">20</a>
            </div>
            <div class="btn_re">
                <a class="btn_money">10</a>
            </div>
            <input type="hidden" id="amount" name="amount" value="5000">
            <input type="hidden" id="type" name="type" value="2">
        </div>
    </div>
    <div class="boxflex1 mt10">
        <div class="moneyhead">充值金额</div>
    </div>
    <div class="boxflex1" style="border-top:none">
        <img src="/images/icon-chat.png" />
        <span>微信支付</span>
        <img src="/images/seleted.png" alt="" style="float:right;padding: 5px 0;">
    </div>
    <!-- <div class="recharge-btn mt10 payMoney">立即充值</div> -->
    <div class="recharge-btn mt10" id="payBtn">充值</div>
    <?php self::endForm() ?>
</div>

<script type="text/javascript">
    $(".btn_money").click(function(){
        $('.clearfloat .btn_money').removeClass("active");
        $('#amount').val($(this).html());
        $(this).addClass("active");
    });

    $('#payBtn').on('click', function(){
        $("#payform").submit();
    });
</script>
