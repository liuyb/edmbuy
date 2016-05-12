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

<div class="comeon"><img src="<?=$qrimg?>?_=<?=$user->randver?>" alt="QR Code"/></div>
<?php if(!empty($promoter)):?>
<div class="bbsizing promoter">
你的推荐人：<em><?=$promoter['nickname']?></em>　推荐人多米号：<em><?=$promoter['uid']?></em>
</div>
<?php endif;?>

<?php endif;?>