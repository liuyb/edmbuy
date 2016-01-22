<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if (in_array($nav_flag1, ['home','cart','cart_mnav','partner','user'])):?>
<?php if(preg_match('/^cart/', $nav_flag1)):?>
<div id="nav_cart_tool"></div>
<div class="clear"></div>
<?php endif;?>
<?php if('cart'!=$nav_flag1):?>
<ul>
	<li><a href="/" id="f_sy" rel="home" <?php if('home'==$nav_flag1):?>class="on"<?php endif;?>></a></li>
	<li><a href="<?php echo U('trade/cart/list','mnav=1')?>" id="f_gwc" rel="cart" <?php if('cart_mnav'==$nav_flag1):?>class="on"<?php endif;?>><?php if($user_cart_num):?><span class="f_num"><?=$user_cart_num?></span><?php endif;?></a></li>
	<li><a href="<?php echo U('partner')?>" id="f_ms" rel="partner" <?php if('partner'==$nav_flag1):?>class="on"<?php endif;?>>&nbsp;</a></li>
	<li><a href="<?php echo U('user')?>" id="f_hy" rel="user" <?php if('user'==$nav_flag1):?>class="on"<?php endif;?>>&nbsp;</a></li>
</ul>
<?php endif;?>
<script type="text/javascript">$('#Mnav').on('click','a',function(){
	var rel = $(this).attr('rel');
	var href= $(this).attr('href');
	if( (rel=='partner'||rel=='user') && required_uinfo_empty()) {
		if(confirm('请求该页面需要微信授权，是否重定向到微信授权页？')) {
			window.location.href = "<?php echo U('user/go_detail_oauth')?>?refer="+href;
		}
		return false;
	}
	$(this).parents('.nav').find('a').removeClass('on');
	$(this).addClass('on');
	return true;
});
</script>
<?php endif;?>