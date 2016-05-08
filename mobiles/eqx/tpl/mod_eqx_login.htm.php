<?php defined('IN_SIMPHP') or die('Access Denied');?>
<?php add_css('eqx.css',['scope'=>'module', 'mod'=>'eqx']);?>

<script type="text/html" id="forTopNav">
<div class="header">登录<span class="find_password">找回密码</span></div>
</script>
<script type="text/javascript">show_topnav($('#forTopNav').html());</script>

<div class="login_phone">
	<span class="phone_left">手机账号：</span>
	<input class="write_phone" id="write_phone" type="text" value="" placeholder="请输入您的手机号">
</div>

<div class="login_phone login_pass">
	<span class="phone_left">密<i class="width_j"></i>码：</span>
	<input class="write_phone" id="write_password" type="text" value="" placeholder="请输入您的密码">
</div>

<div class="login_bottom">
	<div class="login_btn">登录</div>
	<div class="register_btn">注册</div>
</div>

<div class="quertion_phone">
	遇到问题：<i>400-0755-888</i>
</div>
