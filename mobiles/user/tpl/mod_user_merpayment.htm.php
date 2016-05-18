<?php defined('IN_SIMPHP') or die('Access Denied');?>
<div id="pay_top">
	<img src="/themes/mobiles/img/bzb.png">
</div>
<form action="<?=U('user/merchant/openshop') ?>" method="post">
<div>
	<table cellspacing="0" cellpadding="0" class="pay_tab">	
		<tr>
			<td><img src="/themes/mobiles/img/fenxiao.png">三级分销系统（1套） </td>
			<td><img src="/themes/mobiles/img/dianpu.png">自有店铺（1个）</td>
		</tr>
		<tr>
			<td><img src="/themes/mobiles/img/tixian.png">自动提现系统（1套）</td>
			<td><img src="/themes/mobiles/img/muban.png">精美店铺模版</td>
		</tr>
		<tr>
			<td><img src="/themes/mobiles/img/gongju.png">多功能营销辅助工具</td>
			<td><img src="/themes/mobiles/img/fuzhu.png">多米商城销售辅助</td>
		</tr>
		<tr>
			<td><img src="/themes/mobiles/img/gongxiang.png">百万分销商资源共享</td>
			<td><img src="/themes/mobiles/img/kefu1.png">全程客服支持</td>
		</tr>
		<tr>
			<td><img src="/themes/mobiles/img/dianhua.png">免费赠送400电话</td>
		</tr>
	</table>
</div>
<div id="how_much">
	优惠价：<b data-type="699">￥999/年</b>
</div>
<div id="wx_success_pay">
	<button id="sus_pay" type="submit">立即开通</button>
	<span id="red_deal">我已阅读，同意接受《益多米商家入驻协议》</span>
</div>
</form>
<script>
$(function(){
	$("#Mbody").css("background","#fff");
});
</script>

