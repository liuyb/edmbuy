<?php defined('IN_SIMPHP') or die('Access Denied');?>
<?php add_css('eqx.css',['scope'=>'module', 'mod'=>'eqx']);?>

<script type="text/html" id="forTopNav">
<div class="header"><span class="find_password lt" onclick="<?php echo backscript(TRUE)?>"><img src="http://eqx.edmbuy.com/web/img/com_back.png" alt="" style="width:7px;margin-right:2px;" />返回</span>登录益多米<span class="find_password" onclick="location.href='<?=$reset_passwd_url?>'">找回密码</span></div>
</script>
<script type="text/javascript">show_topnav($('#forTopNav').html());var refer='<?=$refer?>';</script>

<div class="login_phone">
	<span class="phone_left">手机账号：</span>
	<input class="write_phone" id="write_phone" type="text" value="" placeholder="请输入您的手机号">
</div>

<div class="login_phone login_pass">
	<span class="phone_left">密<i class="width_j"></i>码：</span>
	<input class="write_phone" id="write_password" type="password" value="" placeholder="请输入您的密码">
</div>

<div class="login_bottom">
	<button class="login_btn" id="login_btn">登录</button>
	<div class="register_btn" onclick="location.href='<?php echo U('eqx/reg')?>'">注册</div>
</div>
<!-- 
<div class="quertion_phone">
	遇到问题请致电：<a href="tel://0755-86720209"><i>0755-86720209</i></a>
</div>
-->
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
				__sync_login(ret.sync_login_url);
				weui_toast('finish',1,'登录成功',function(){
					location.href = refer;
				});
			}
			else {
				if(ret.code == -3){
					weui_confirm('您输入的手机号还未注册！点击确定去注册。', '', function(){
						location.href='<?php echo U('eqx/reg')?>';
					});
				}
				else if(ret.code == -4) {
					weui_confirm('输入的密码不正确，请返回重试，<br>如果忘记密码，可以去重置密码。', '', function(){
						location.href='<?=$reset_passwd_url?>';
					},null,{ok_text:'重置密码',cancel_text:'返回重试'});
				}
				else{
    			weui_alert(ret.msg);
				}
			}
		});
	});
});
</script>
