<?php defined('IN_SIMPHP') or die('Access Denied');?>
<?php add_css('eqx.css',['scope'=>'module', 'mod'=>'eqx']);?>

<script type="text/html" id="forTopNav">
<div class="header">
一起享
<?php if(Users::is_account_logined()):?>
<a href="javascript:history.back();" class="back">返回</a>
<?php endif;?>
</div>
</script>
<script type="text/javascript">show_topnav($('#forTopNav').html());</script>

<div class="index_img">
	<img src="<?=$contextpath?>mobiles/eqx/img/login_1.png">
	<img src="<?=$contextpath?>mobiles/eqx/img/login_2.png">
	<img src="<?=$contextpath?>mobiles/eqx/img/login_3.png">
</div>
<!-- 
<div class="index_bottom"><a href="<?php echo U('eqx/reg')?>" class="blka">入驻一起享</a></div>
 -->
<script type="text/html" id="forMNav">
<a href="<?php echo U('eqx/reg')?>" class="index_btm blka">入驻一起享</a>
</script>
<script type="text/javascript">show_mnav($('#forMNav').html());</script>