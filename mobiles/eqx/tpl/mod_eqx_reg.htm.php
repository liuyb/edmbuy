<?php defined('IN_SIMPHP') or die('Access Denied');?>
<?php add_css('eqx.css',['scope'=>'module', 'mod'=>'eqx']);?>
<!--[HEAD_CSS]-->
<style>
.login_index {border-width: 0 0 1px 0;}
</style>
<!--[/HEAD_CSS]-->
<script type="text/javascript">var inapp='<?=$inapp?>',refer='<?=$refer?>',referee_uid=parseInt('<?=$referee_uid?>');</script>
<?php if(1==$step):?>

<script type="text/html" id="forTopNav">
<div class="header"><?php if('edm'==$inapp):?>欢迎加入益多米<span class="find_password" onclick="show_login_dlg()">登录</span><?php else:?>欢迎加入一起享<span class="find_password" onclick="location.href='<?php echo U('eqx/login')?>'">登录</span><?php endif;?></div>
</script>
<script type="text/javascript">show_topnav($('#forTopNav').html());</script>

<div class="login_index">
	<div class="login_wirte_phone">
		<input type="text" value="" placeholder="请输入手机号" id="write_phone">
	</div>
	<div class="login_not_code del_bottom">
		<input type="text" value="" placeholder="请输入短信验证码" id="write_vcode">
		<button class="get_note_code" id="get_note_code">获取验证码</button>
	</div>
</div>

<div class="prompt_code" id="prompt_code"><p>验证码已发送至手机，请注意查收！</p></div>

<div class="bottom_common" style="margin:0">
	<button class="btn_common_bg" id="btn_next">下一步</button>
</div>
<div class="use_protocol prot_on_ok">我已阅读并同意<?php if($inapp=='edm'):?>《益多米用户服务协议》<?php else:?>《一起享用户服务协议》<?php endif;?></div>

<!-- 
<div class="quertion_phone">
	遇到问题：<a href="tel://0755-86720209"><i>0755-86720209</i></a>
</div>
-->
<script>
var num = 60;
var count = num;
var timer;
var ajaxing = 0;
function refresh(){
	if (typeof(refresh._ncode)=='undefined') {
		refresh._ncode = $("#get_note_code");
	}
	--num;
	var msg = num + "秒";
	refresh._ncode.html(msg);
	if(num == 0){
		refresh_stop();
		return false;
  } 
}
function refresh_stop() {
	clearInterval(timer);
	timer = null;
	num = count;
	refresh._ncode.html("重新获取");
	ajaxing = 0;
}
function check_mobile_input() {
	if(typeof(check_mobile_input._mobi)=='undefined') {
		check_mobile_input._mobi = $('#write_phone');
	}
	var mobi= check_mobile_input._mobi.val().trim();
	if (''==mobi) {
		weui_alert('请填写手机号');
		return false;
	}
	else if (!/^1\d{10}$/.test(mobi)) {
		weui_alert('手机号不对');
		return false;
	}
	return mobi;
}
$(function () {
	$("#get_note_code").bind("click",function(){
		if (ajaxing) return;
		var mobi = check_mobile_input();
		if (!mobi) return;
		var $ptip = $('#prompt_code p');
		ajaxing = 1;
		refresh();
		timer = setInterval("refresh();", 1000);
		F.post('<?php echo U('eqx/get_vcode','inapp='.$inapp)?>',{mobile: mobi},function(ret){
			if (ret.flag=='SUCC') {
				$ptip.show();
				setTimeout(function(){
					$ptip.hide();
				},60*1000);
				$("#write_vcode").focus();
			}
			else {
				refresh_stop();
				weui_alert(ret.msg);
			}
		});
	});
	
	$('#btn_next').bind('click',function(){
		var mobi = check_mobile_input();
		if (!mobi) return;
		var vcode= $('#write_vcode').val().trim();
		if (''==vcode) {
			weui_alert('请输入短信验证码');
			return;
		}else if(vcode.length!=6) {
			weui_alert('短信验证码错误');
			return;
		}

		var _this = this;
		$(_this).attr('disabled',true);
		weui_toast('loading',0,'验证中...');
		F.post('<?php echo U('eqx/reg','step=1&inapp='.$inapp)?>',{mobile: mobi,vcode: vcode,parent_id: referee_uid},function(ret){
			weui_toast_hide('loading');
			if(ret.flag=='SUCC') {
				weui_toast('finish',1,'手机验证通过',function(d){
					location.href = '<?php echo U('eqx/reg','step=2&inapp='.$inapp.'&refer='.$refer)?>';
				});
			}
			else {
				$(_this).attr('disabled',false);
				weui_alert(ret.msg);
			}
		});
		
	});
});
</script>

<?php else:?>

<script type="text/html" id="forTopNav">
<div class="header">
	注册
	<a href="javascript:history.back();" class="back">返回</a>
	<span class="find_password" onclick="location.href='<?php echo U('eqx/findpass','inapp='.$inapp.'&refer='.$refer)?>'">找回密码</span>
</div>
</script>
<script type="text/javascript">show_topnav($('#forTopNav').html());</script>


<div class="login_index">
	<div class="login_password">
		<input type="password" value="" placeholder="请设置登录密码" id="write_password">
	</div>
	<div class="login_password del_bottom">
		<input type="password" value="" placeholder="请再次输入密码" id="rewrite_password">
	</div>
</div>

<div class="bottom_common">
	<button class="btn_common_bg" id="btn_finreg">完成注册</button>
</div>

<script>
$(function(){

	$('#btn_finreg').bind('click',function(){
		var _pwd  = $('#write_password').val().trim();
		var _repwd= $('#rewrite_password').val().trim();
		if (_pwd=='') {
			weui_alert('请输入密码');
			return;
		}
		else if(_pwd.length<6) {
			weui_alert('密码需6位或以上');
			return;
		}
		if (_pwd!=_repwd) {
			weui_alert('再次输入密码不匹配');
			return;
		}
		
		var _this = this;
		$(_this).attr('disabled',true);
		weui_toast('loading',0,'注册中...');
		F.post('<?php echo U('eqx/reg','step=2&inapp='.$inapp)?>',{passwd: _pwd, parent_id: referee_uid},function(ret){
			weui_toast_hide('loading');
			if (ret.flag=='SUCC') {
				weui_toast('finish',1,'注册成功！',function(d){
					if (refer!='') {
						location.href = refer;
					}
					else {
						if ('edm'==inapp) {
							location.href = '<?php echo U('user')?>';
						}
						else {
							location.href = '<?php echo U('eqx/home')?>';
						}
					}
				});
			}
			else {
				$(_this).attr('disabled',false);
				weui_alert(ret.msg);
			}
		});
	});
	
});

</script>

<?php endif;?>