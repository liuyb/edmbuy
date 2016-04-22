<?php defined('IN_SIMPHP') or die('Access Denied');?>
<div id="sus_money">
	<img src="/themes/mobiles/img/sss1.png">
	<p id="sus_tit" style="padding-bottom:20px;">
		<?php if(!$is_complate):?>
			恭喜您，完成开通
			<?php else: ?>
			已经开通
		<?php endif; ?>
	</p>
</div>
<div id="act_infos">
	<div class="act_tit">激活信息如下：</div>

	<div id="login_address">
		<div style="padding-top:10px;">登录地址：<span class="comp_address"><?=$url?></span></div>
		<div style="color:#666;font-size:12px;">(请前往电脑端登录)</div>
	</div>

	<div id="login_address">
		<div style="padding-top:10px;">登录账号：<span class="comp_address"><?=$mobile?></span></div>
		<div style="color:#666;font-size:12px;">(您注册的手机号码)</div>
	</div>

	<?php if(!$is_complate):?>
	<div id="login_address" style="height:auto;">
		<div style="padding-top:10px;">初始密码：<span class="comp_address"><?=$pwd?></span></div>
		<div style="color:#666;font-size:12px;padding-bottom:10px;">(为了您的账户安全，请尽快到益多米商家登录页面修改密码)</div>
	</div>
	<?php endif;?>

	<div style="font-size:14px;color:#f65d00;margin-top:10px;">激活信息已发送至您的手机，请注意查收</div>
</div>
