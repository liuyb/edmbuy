<?php defined('IN_SIMPHP') or die('Access Denied');?>
<?php add_css('eqx.css',['scope'=>'module', 'mod'=>'eqx']);?>

<?php add_css($contextpath.'misc/js/ext/slideunlock/jquery.slideunlock.css',['scope'=>'global', 'ver'=>'0.1.0']);?>
<?php add_js($contextpath.'misc/js/ext/slideunlock/jquery.slideunlock.min.js',['pos'=>'current','ver'=>'0.1.0']);?>
<script type="text/html" id="forTopNav">
<div class="header">
	找回密码
	<a href="javascript:history.back();" class="back">返回</a>
</div>
</script>
<script type="text/javascript">show_topnav($('#forTopNav').html());</script>

<div class="send_note_code">
	发送验证短信到1234*****12315,请注意查收
</div>

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
<div class="clear"></div>

<div class="login_bottom">
	<div class="login_btn">下一步</div>
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
