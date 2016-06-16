<?php defined('IN_SIMPHP') or die('Access Denied');?>

<!--[HEAD_CSS]-->
<style>
.pay_logo{
	text-align:center;
	margin:40px 0 30px;
}
.pay_tit{
	font-size:18px;
	color:#000;
	margin-bottom:15px;
}
.new_order_money{
	font-size:14px;
	color:#333;
	margin-bottom:20px;
}
.new_order_money i{
	font-size:14px;
	color:#FF0101;	
}
.pay_t{
	font-size:16px;
	color:#000;
	margin-bottom:20px;	
}
.new_check_order_btn{
	width:128px;
	height:40px;
	line-height:40px;
	border-radius:5px;
	background:#fff;
	font-size:16px;
	color:#333;
	border:1px solid #e6e6e6;
	margin-bottom:40px;
}
.weix_code_n{
	width:128px;
	height:128px;
}
.pay_t_b{
	font-size:14px;
	color:#333;
}
#Mbody{
	background:#fff;
}
</style>
<!--[/HEAD_CSS]-->

<div class="pay_logo">
	<img src="/themes/mobiles/img/icon.png">
</div>

<div class="pay_price">
	<p class="pay_tit">购物成功</p>
	<p class="new_order_money">订单金额：<i><?=$order_amount?>元</i></p>
	<?php if(isset($msgap)):?>
	<p class="pay_t">您还差<?php echo $msgap ?>元，即可成为米商。</p>
	<?php endif;?>
	<?php if($user->subscribe):?>
	<p><button class="new_check_order_btn" onclick="location.href='<?php echo U('trade/order/record', ['from'=>'trade'])?>'">查看订单</button></p>
	<?php else:?>
	<p style="font-size:16px;color:#3e8427;">查看订单？</p>
	<p><img src="/themes/mobiles/img/zhiyin.png" width="30px" height="41px"></p>
	<?php endif;?>
	<img src="/themes/mobiles/img/ydm.png" class="weix_code_n">
	<p class="pay_t_b">关注益多米公众号</p>
</div>
