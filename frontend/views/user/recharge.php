<?php $this->regCss('iconfont/iconfont.css') ?>
<?php $this->regCss('mine.css') ?>
<?php $this->regCss('common.css') ?>
<style type="text/css">body{background:#fff;}</style>

<div class="container " style="padding:0;">
    <p class="selecthe">选择充值面额（元）</p>
    <?php $form = self::beginForm(['showLabel' => false, 'action' => url(['user/pay']), 'id' => 'payform']) ?>
    <div class="boxflex1 paystyle" style="padding: 10px 15px 0;">
        <div class="group_btn group clearfloat">
            <div class="btn_re"  >
                <a class="btn_money">50</a>
            </div>
            <div class="btn_re" >
                <a class="btn_money">100</a>
            </div>
            <div class="btn_re" >
                <a class="btn_money">300</a>
            </div>
            <div class="btn_re" >
                <a class="btn_money">500</a>
            </div>
        </div>
        <div class="group_btn group clearfloat">
            <div class="btn_re"  >
                <a class="btn_money">1000</a>
            </div>
            <div class="btn_re" >
                <a class="btn_money">2000</a>
            </div>
            <div class="btn_re" >
                <a class="btn_money">3000</a>
            </div>
            <div class="btn_re" >
                <a class="btn_money">4800</a>
            </div> 
        </div>
        <input type="hidden" id="amount" name="amount" value="1">
        <input type="hidden" id="type" name="type" value="2">
    </div>
    <div class="boxflex1">
        <div class="moneyhead">充值方式</div>
    </div>
    <div class="boxflex1 paystyle checkImg1" style="border-top:0;">
        <img src="/images/icon-chat.png" style="width: 20px;">
        <span>微信扫码支付</span>
        <img src="/images/seleted.png" alt="" style="float:right;" class="check-1" >
    </div>
    <div class="boxflex1 paystyle checkImg2" >
        <img src="/images/alipay.png" style="width: 20px;">
        <span>支付宝扫码支付</span>
        <img src="/images/notseleted.png" alt="" style="float:right;" class="check-2" >
    </div>
    <div class="boxflex1 paystyle checkImg3">
        <img src="/images/icon-chat.png" style="width: 20px;">
        <span>微信H5支付</span>
        <img src="/images/notseleted.png" alt="" style="float:right;" class="check-3" >
    </div>
    
    <div class="recharge-btn" id="payBtn">立即充值</div>

    <?php self::endForm() ?>
    <div class="row">
        <!-- <div class="col-xs-12 text-center font_14 remain">跳转至微信安全支付网页，微信转账说明</div> -->
<!--         <div class="col-xs-12 text-center font_12">
            <font>注1：暂时只能使用借记卡充值</font>
            <br>
            <font>注2：为了管控资金风险，单日充值限额20000元</font>
        </div> -->
    </div>
</div>
<script>
$(function() {
    $('#type').val(1);
    $(".btn_money").click(function() {
        $(".on").removeClass("on");
        $(this).addClass("on");
        $('#amount').val($(this).html());
    });

    $('#payBtn').on('click', function(){
        var amount = $('#amount').val();
        if(!amount || isNaN(amount) || amount <= 0){
            alert('金额输入不合法!');
            return false;
        }
        $("#payform").submit();
    });

    $(".checkImg1").click(function(){
        $('#type').val(1);

        $(this).find('.check-1').attr({
            "src":"/images/seleted.png"
        })
        $(this).siblings().find('img[class^="check"]').attr({
            "src":"/images/notseleted.png"
        })
    })

    $(".checkImg2").click(function(){
        $('#type').val(2);

        $(this).find('.check-2').attr({
            "src":"/images/seleted.png"
        })
       
        $(this).siblings().find('img[class^="check"]').attr({
            "src":"/images/notseleted.png"
        })   
    })
    
    $(".checkImg3").click(function(){
        $('#type').val(3);
        $(this).find('.check-3').attr({
            "src":"/images/seleted.png"
        })
        $(this).siblings().find('img[class^="check"]').attr({
            "src":"/images/notseleted.png"
        })     
    })
    
})
</script>

        