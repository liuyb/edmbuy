<?php defined('IN_SIMPHP') or die('Access Denied');?>
<?php add_css('eqx.css',['scope'=>'module', 'mod'=>'eqx']);?>

<script type="text/html" id="forTopNav">
<div class="header">登录<span class="find_password hide" onclick="location.href='<?php echo U('eqx/findpass')?>'">找回密码</span></div>
</script>
<script type="text/javascript">show_topnav($('#forTopNav').html());</script>

<div class="login_phone">
	<span class="phone_left">手机帐号：</span>
	<input class="write_phone" id="write_phone" type="text" value="" placeholder="请输入您的手机号">
</div>

<div class="login_phone login_pass">
	<span class="phone_left">密<i class="width_j"></i>码：</span>
	<input class="write_phone" id="write_password" type="password" value="" placeholder="请输入您的密码">
</div>

<div class="login_bottom">
	<button class="login_btn" id="login_btn">登录</button>
	<!-- 
	<div class="register_btn" onclick="location.href='<?php echo U('eqx/reg')?>'">注册</div>
	 -->
</div>

<div class="quertion_phone">
	遇到问题：<i>400-0755-888</i>
</div>

<script type="text/javascript">
$(function(){
	$('#login_btn').bind('click',function(){
		var _mobi = $('#write_phone').val().trim();
		var _pass = $('#write_password').val().trim();
		if (''==_mobi || !/^1\d{10}$/.test(_mobi)) {
			weui_alert('请填写正确的手机号');
			return;
		}
		if (''==_pass) {
			weui_alert('密码不能为空');
			return;
		}

		var _this = this;
		$(_this).attr('disabled',true);
		weui_toast('loading',0,'登录中...');
		F.post('<?php echo U('eqx/login')?>',{mobile: _mobi, passwd: _pass},function(ret){
			weui_toast_hide('loading');
			$(_this).attr('disabled',false);
			if (ret.flag=='SUCC') {
				weui_toast('finish',1,'登录成功',function(){
					location.href = '<?php echo U('eqx/home')?>';
				});
			}
			else {
				weui_alert(ret.msg);
			}
		});
	});
});
</script>
