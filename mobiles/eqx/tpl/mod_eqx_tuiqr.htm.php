<?php defined('IN_SIMPHP') or die('Access Denied');?>
<?php add_css('eqx.css',['scope'=>'module', 'mod'=>'eqx']);?>

<script type="text/html" id="forTopNav">
<div class="header">推广二维码
<?php if(Users::is_account_logined()):?>
<a href="javascript:history.back();" class="back">返回</a>
<?php endif;?>
</div>
</script>
<script type="text/javascript">show_topnav($('#forTopNav').html());</script>


<div class="code_top"> 
	<span class="code_top_left">长按保存二维码图片到手机</span>
	<a href="javascript:;" onclick="remove_me(this)"><img src="<?=$contextpath?>mobiles/eqx/img/close.png"></a>
</div>

<div class="code_center">
<!-- 
	<img src="<?=$contextpath?>mobiles/eqx/img/top1.png" alt="QR Code">
 -->
	<img src="<?=$qrcode_tui?>" alt="QR Code"/>
</div>

<script>
function remove_me(obj) {
	$(obj).parent().remove();
}
</script>