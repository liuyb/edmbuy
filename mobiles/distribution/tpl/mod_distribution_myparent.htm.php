<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if(''!==$errmsg):?>

<div class="error"><?=$errmsg?></div>

<?php else:?>
<div class="header">
	我的推荐人
	<a href="javascript:history.back();" class="back"></a>
</div>

<div class="my_referrer_tit">
	<img src="<?=$parent->logo ?>">
	<p class="refer_name"><?=$parent->nickname ?></p>
	<button class="refer_btn"><?=AgentPayment::getAgentNameByLevel($parent->level) ?></button>
</div>

<div class="refer_common">
	<span class="refer_name">多米号：</span>
	<i calss="refer_text"><?=$parent->uid ?></i>
</div>
<div class="refer_common">
	<span class="refer_name">手机号：</span>
	<i calss="refer_text"><?=$parent->mobilephone ?></i>
</div>
<!-- <div class="refer_common">
	<span class="refer_name">微信号：</span>
	<i calss="refer_text">138465323232</i>
</div> -->

<script>
$("#Mbody").css('background-color', '#fff');
</script>
<?php endif;?>