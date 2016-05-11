<?php defined('IN_SIMPHP') or die('Access Denied');?>
<?php add_css('eqx.css',['scope'=>'module', 'mod'=>'eqx']);?>

<script type="text/html" id="forTopNav">
<div class="header">一起享<!--<a href="javascript:history.back();" class="back">返回</a>--></div>
</script>
<script type="text/javascript">show_topnav($('#forTopNav').html());</script>

<div class="what_top">
	<img src="<?=$user->logo?>" alt="ulogo"/>
	<span><?=$user->nickname?></span>
</div>

<div class="what_center">
	<a href="<?php echo U('eqx/letter','nologin=1')?>"><img src="<?=$contextpath?>mobiles/eqx/img/whats.png"></a>
</div>

<div class="what_bottom">
	<div class="what_commom"><a href="<?php echo U('partner/my/child')?>" class="blka"><img src="<?=$contextpath?>mobiles/eqx/img/wdrm.png"><span>我的人脉</span></a></div>
	<div class="what_commom"><a href="javascript:;" class="blka" onclick="gotui()"><img src="<?=$contextpath?>mobiles/eqx/img/qtg.png"><span>去推广</span></a></div>
</div>

<div class="wx_bottom"><span class="what_wx">客服微信：<i style="color:#d3a492;">3398961935</i></span></div>

<script>
var is_agent = parseInt('<?=$is_agent?>');
function cancel_act(obj) {
	weui_dialog_close();
}
function do_act(obj) {
	location.href = '<?php echo U('trade/order/agent')?>';
	weui_dialog_close();
}
function gotui() {
	/*
	if (gUser.uid!=104&&gUser.uid!=114111&&gUser.uid!=7058) {
		weui_alert('恭喜您获得一起享优先推广权的机会，请先前往“我的人脉”查看人网关系是否正确');
		return false;
	}
	*/
	if (is_agent) {
		location.href = '<?php echo U('eqx/tui')?>';
	}
	else {
		var html = '<p>恭喜您获得一起享优先推广权的机会，前往多米商城购买套餐立即获得推广权</p><div class="home_dlg_btns"><button class="home_btn register_btn" onclick="cancel_act(this)">我再看看</button><button class="home_btn login_btn" onclick="do_act(this)">立即前往</button></div>';
		weui_dialog(html,'<span style="color:red">提示</span>');
	}
}
/*
$(function(){
	if (!is_agent) {
		setTimeout(function(){
			gotui();
		},500);
	}
});
*/
</script>