<?php defined('IN_SIMPHP') or die('Access Denied');?>
<?php if(''!==$errmsg):?>

<?php if('required_oauth'==$errmsg):?>
<script>
$(function(){
	myAlert('为了更好的展示效果，<br/>此页面需要<em style="color:#44b549">微信授权</em>登录！', function(){
		window.location.href = "<?=$go_oauth_uri?>";
	});
});
</script>
<?php else:?>
<div class="error"><?=$errmsg?></div>
<?php endif;?>

<?php else:?>

<div class="comeon">
<img src="<?=$qrimg?>" alt="QR Code"/>
<div class="promoter">
<?php if(!empty($promoter)):?>
你的推荐人是：<em><?=$promoter['nickname']?>(<?=$promoter['uid']?>)</em><br/>
当前链接属于：<em><?php if(!empty($curr_promoter)):?><?=$curr_promoter['nickname']?>(<?=$curr_promoter['uid']?>)
<?php else:?>
平台
<?php endif;?>
</em>
<?php endif;?>
</div>
</div>

<?php endif;?>