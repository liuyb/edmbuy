<?php defined('IN_SIMPHP') or die('Access Denied');?>
<div id="sus_money">
	<img src="/themes/mobiles/img/sss1.png">
	<p id="sus_tit" style="padding-bottom:20px;">
		<?php if(isset($_SESSION['step']) && $_SESSION['step']): unset($_SESSION['step'])?>恭喜您，完成开通<?php else:?>你已经是入驻商家<?php endif;?>
	</p>
</div>
<div id="act_infos">
	<div class="act_tit">注册信息如下：</div>

	<div id="login_address">
		<div style="padding-top:10px;">登录地址：<span class="comp_address"><?=$url?></span></div>
		<div style="color:#666;font-size:12px;">(请前往电脑端登录)</div>
	</div>

	<div id="login_address">
		<div style="padding-top:10px;">登录账号：<span class="comp_address"><?=$mobile?></span></div>
		<div style="color:#666;font-size:12px;">(您注册的手机号码)</div>
	</div>

	<!-- <div style="font-size:14px;color:#f65d00;margin-top:10px;">激活信息已发送至您的手机，请注意查收</div> -->
</div>
<script>
	$(function(){
		$("#Mbody").css("background","#fff");
	})
</script>
