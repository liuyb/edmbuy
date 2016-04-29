<?php defined('IN_SIMPHP') or die('Access Denied'); ?>
<div class="pay_logo">
    <img src="/themes/mobiles/img/icon.png">
</div>

<div class="pay_price">
    <p class="pay_tit">支付成功</p>
    <p class="pay_tit">多米分销系统</p>
    <p class="pay_p"><span class="price_sucee">金额:￥<?=$order_price?></span></p>
</div>

<div id="wx_success_pay"  style="margin-top:30px;">
    <button id="sus_pay">完成</button>
</div>
<script>
    $("#sus_pay").click(function(){
        window.location.href="<?php echo U('user/merchant/dosuccess');?>";
    })
</script>