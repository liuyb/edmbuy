<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if(''!==$errmsg):?>

<div class="error"><?=$errmsg?></div>

<?php else:?>
<style>
.get_meal_success{
	margin:0 10px;
	text-align:center;
}
.get_meal_success img{
	width:60px;
	height:60px;
	margin-top:50px;
}
</style>

<script id="forTopnav" type="text/html">
<div class="header">
	领取成功
</div>
</script>
<script>
show_topnav($('#forTopnav').html());
</script>

<div class="get_meal_success">
	<img src="/themes/mobiles/img/sss1.png">
	<p style="margin:20px 0 35px 0;">您已成功领取<i class="agency_font"><?=$money ?>元</i>超值礼包</p>
	<div class="at_get_combo" onclick="window.location.href='/'">返回首页</div>
	<div class="agency_my_develop" onclick="window.location.href='<?=U('trade/order/record') ?>'">查看订单</div>
</div>

<?php endif;?>