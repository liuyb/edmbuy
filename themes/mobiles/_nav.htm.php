<?php defined('IN_SIMPHP') or die('Access Denied');?>

<nav id="nav" class="nav no-bounce nav-<?=$nav_no?>"></nav>
<script>
$(function(){
	$('#nav a').click(function(){
		$(this).parents('.nav').find('a').removeClass('cur');
		$(this).addClass('cur');
	});
});
</script>

<!-- 微信操作提示 -->
<div id="cover-wxtips" class="cover"><img alt="" src="<?=$contextpath;?>themes/mobiles/img/guide.png"/></div>