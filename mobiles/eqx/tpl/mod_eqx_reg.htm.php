<?php defined('IN_SIMPHP') or die('Access Denied');?>
<?php add_css('eqx.css',['scope'=>'module', 'mod'=>'eqx']);?>

<?php if(1==$step):?>

<?php add_css($contextpath.'misc/js/ext/slideunlock/jquery.slideunlock.css',['scope'=>'global', 'ver'=>'0.1.0']);?>
<?php add_js($contextpath.'misc/js/ext/slideunlock/jquery.slideunlock.min.js',['pos'=>'current','ver'=>'0.1.0']);?>
<script type="text/html" id="forTopNav">
<div class="header">欢迎加入一起享<span class="find_password">登录</span></div>
</script>
<script type="text/javascript">show_topnav($('#forTopNav').html());</script>

<div class="login_phone">
	<span class="phone_left">手机账号：</span>
	<input class="write_phone" id="write_phone" type="text" value="" placeholder="请输入您的手机号">
</div>

<div class="login_phone login_pass">
	<span class="phone_left" style="float:left;">验<i class="width_j"></i>证：</span>
	<div id="slide-wrapper">
	<input type="hidden" value="" id="lockable">
	<div id="slider">
		<span id="label"></span>
		<span id="slider_swip"></span>
		<span id="slider_font">按住滑块,拖动到最右边</span>
		<span id="lableTip"></span>
	</div>
</div>
</div>

<div class="login_bottom">
	<div class="login_btn">下一步</div>
</div>

<div class="quertion_phone">
注册即表示同意：《一起享用户服务协议》
</div>

<div class="quertion_phone">
	遇到问题：<i>400-0755-888</i>
</div>


<script>
$(function () {
	var slider = new SliderUnlock("#slider", {}, function(){
	}, function(){});
	slider.init();

})
</script>

<?php else:?>

<script type="text/html" id="forTopNav">
<div class="header">
	登录
	<a href="javascript:history.back();" class="back">返回</a>
	<span class="find_password">找回密码</span>
</div>
</script>
<script type="text/javascript">show_topnav($('#forTopNav').html());</script>

<div class="login_phone">
	<span class="phone_left">短信验证：</span>
	<input class="write_phone" id="note_code" type="text" value="" placeholder="请输入您的短信验证码">
	<button class="get_note_code">获取验证码</button>
</div>

<div class="login_phone login_pass">
	<span class="phone_left">密<i class="width_j"></i>码：</span>
	<input class="write_phone" id="write_password" type="text" value="" placeholder="请输入您的密码">
</div>

<div class="login_bottom">
	<div class="login_btn">完成注册</div>
</div>

<div class="quertion_phone">
	遇到问题：<i>400-0755-888</i>
</div>

<script>
var num = 60;
var count = num;
var time;

function refresh(){
	
	num = num - 1;
	var msg = num + "秒";
	 
	$(".get_note_code").html(msg);
		
    if(num == 0){
    	num = count;
    	$(".get_note_code").removeClass("sending").html("重新获取");
    	clearInterval(time);
    	return false;
    }
}

$(".get_note_code").on("click",function(){
	refresh();
	time = setInterval("refresh();", 1000);
	$(".prompt_code p").show();
})

$(".use_protocol").on("click",function(){
	if($(this).hasClass("prot_on_ok")){
		$(this).removeClass("prot_on_ok").addClass("prot_on_no");
	}else{
		$(this).removeClass("prot_on_no").addClass("prot_on_ok");
	}
})
</script>

<?php endif;?>