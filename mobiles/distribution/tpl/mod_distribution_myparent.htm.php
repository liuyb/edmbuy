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
	<p class="refer_name"><span><?=$parent->nickname ?></span>
	<?php if ($parent->level == Users::USER_LEVEL_3 || $parent->level == Users::USER_LEVEL_5):?>
	<img src='/themes/mobiles/img/jinpai1.png' style='width:17px;height:19px;vertical-align:middle;margin:0px 0px 3px 3px;'/>
	<?php elseif ($parent->level == Users::USER_LEVEL_4):?>
	<img src='/themes/mobiles/img/yinpai2.png' style='width:17px;height:19px;vertical-align:middle;margin:0px 0px 3px 3px;'/>
	<?php endif;?>
	</p>
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