<?php defined('IN_SIMPHP') or die('Access Denied');?>

<script type="text/html" id="forGlobalLogin">
<div class="login_index">
	<div class="login_name"><input type="text" name="frm_mobile" id="global_frm_mobile" value="" placeholder="请输入您的手机号"></div>
	<div class="login_password del_bottom"><input type="password" name="frm_password" id="global_frm_passwd" value="" placeholder="请输入您的密码"></div>
</div>
<div class="bottom_common">
	<button class="btn_common_bg" id="login_btn" onclick="__go_login(this)">登录</button>
	<button class="btn_common_nbg bbsizing" id="reg_btn" onclick="__go_reg(this)">还没有账号？快速注册</button>
</div>
</script>

<script type="text/html" id="forGlobalReg">
<h1>注册</h1>
</script>

<script type="text/javascript">
var __login_refer = '';
function __go_login(obj) {
	var _mobi = $('#global_frm_mobile').val().trim();
	var _pass = $('#global_frm_passwd').val().trim();
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
		$(_this).attr('disabled',false);
		weui_toast_hide('loading');
		if (ret.flag=='SUCC') {
			__sync_login(ret.sync_login_url);
			weui_toast('finish',1,'登录成功',function(){
				if (!__login_refer) {
					location.reload(true);
				}
				else {
					location.href=__login_refer;
				}
			});
		}
		else {
			if(ret.code && ret.code == -3){
				weui_confirm('您输入的手机号还未注册！点击确定快速注册。', '', function(){
					__go_reg();
				});
			}else{
				weui_alert(ret.msg);
			}
		}
	});
}
function __go_reg(obj) {
	location.href='<?php echo U('eqx/reg','refer='.Request::uri())?>';
}
function __sync_login_cb(ret){
	
}
function __sync_login(url) {
	var head = document.getElementsByTagName('head')[0];
	var script = document.createElement('script');
	script.charset = "UTF-8";
	script.type = "text/javascript";
	script.src = url;
	head.appendChild(script);
}
function show_login_dlg(){
	show_popdlg('登录益多米',$('#forGlobalLogin').html());
	return false;
}
function required_account_logined(touri) {
	if (typeof(touri)=='undefined' || !touri) {
		__login_refer = '';
	}
	else {
		__login_refer = touri;
	}
	if(!gUser.uid) {
		show_popdlg('登录益多米',$('#forGlobalLogin').html());
		return true;
	}
	return false;
}
</script>