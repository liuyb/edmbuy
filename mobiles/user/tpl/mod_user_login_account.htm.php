<?php defined('IN_SIMPHP') or die('Access Denied');?>
<div class="login_index">
	<div class="login_name"><input type="text" value="" placeholder="请输入用户名/手机号"></div>
	<div class="login_password del_bottom"><input type="text" value="" placeholder="请输入密码"></div>
</div>

<div class="bottom_common">
	<div class="btn_common_bg" id="login_btn">登录</div>
	<div class="btn_common_nbg" id="reg_btn">手机快速注册</div>
</div>

<script>
$(function(){
	$('#login_btn').click(function(){
		weui_toast('finish',1);
		//weui_alert('这是好好看哦','',{fn:function(d){alert('ok call: '+d.name);},args:{name: 'Gavin'}});
	});
	$('#reg_btn').click(function(){
		weui_toast('loading',1);
		//weui_confirm('确定这样做么？','确定？',{fn:function(d){alert('ok call: '+d.name);},args:{name: 'Gavin'}},{fn:function(d){alert('cancel call: '+d.name);},args:{name: 'Yancy'}});
	});
});
</script>