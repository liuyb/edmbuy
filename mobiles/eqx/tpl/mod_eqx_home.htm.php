<?php defined('IN_SIMPHP') or die('Access Denied');?>
<?php add_css('eqx.css',['scope'=>'module', 'mod'=>'eqx']);?>

<script type="text/html" id="forTopNav">
<div class="header">一起享<!--<a href="javascript:history.back();" class="back">返回</a>--></div>
</script>
<script type="text/javascript">show_topnav($('#forTopNav').html());</script>
<!--{HEAD_CSS}-->
<style type="text/css">
.gicon_btn_qr { bottom: 140px; }
.gicon_btn_gotop { display: none; }
</style>
<!--{/HEAD_CSS}-->
<div class="what_top">
	<img src="<?=$user->logo?>" alt="ulogo" class="ulogo"/>
	<span><?=$user->nickname?></span>
	<img src="<?=AgentPayment::getAgentIconByLevel($user->level) ?>" class="jin_z">
	<a href="javascript:;" class="blka wytuia" onclick="gotui()">我要推广</a>
</div>
<div class="what_referee">
<?php if($my_parent->is_exist()):?>
我的推荐人：<?=$my_parent->mobile?> <span><?=$my_parent->nickname?></span>
<?php else:?>
我的推荐人：无（挂顶层）
<?php endif;?>
</div>

<div class="what_bottom">
	<div class="what_commom"><a href="<?php echo U('partner/my/child')?>" class="blka"><img src="<?=$contextpath?>mobiles/eqx/img/wdrm.png"><span>我的人脉</span></a></div>
	<div class="what_commom"><a href="javascript:;" class="blka" onclick="gotui()"><img src="<?=$contextpath?>mobiles/eqx/img/qtg.png"><span>去推广</span></a></div>
</div>

<div class="wx_bottom"><span class="what_wx">客服微信：<i style="color:#d3a492;">3398961935</i></span></div>

<div class="fixed-wrap what_center">
	<a href="<?php echo U('eqx/letter','nologin=1')?>" class="blka"><img src="<?=$contextpath?>mobiles/eqx/img/whats.png"></a>
</div>
<?php include T('inc/popqr')?>
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
	if (is_agent) {
		location.href = '<?php echo U('eqx/tui')?>';
	}
	else {
		var html = '<p>恭喜您获得一起享优先推广权的机会，前往多米商城购买套餐立即获得推广权</p><div class="home_dlg_btns"><button class="home_btn register_btn" onclick="cancel_act(this)">我再看看</button><button class="home_btn login_btn" onclick="do_act(this)">立即前往</button></div>';
		weui_dialog(html,'<span style="color:red">提示</span>');
	}
}
</script>