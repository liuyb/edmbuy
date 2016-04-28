<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if (in_array($nav_flag1, ['home','cart','cart_mnav','partner','user','merchant','dmfx'])):?>
<?php if(preg_match('/^cart/', $nav_flag1)):?>
		<div id="nav_cart_tool"></div>
		<div class="clear"></div>
	<?php endif;?>
		<?php if(preg_match('/^merchant/', $nav_flag1)):?>
		<div class="clear"></div>
	<?php endif;?>
	<?php if('merchant'==$nav_flag1):?>
		<div class="store_bottom_comm">
		<ul>
			<li><p class="store_up <?php if(isset($isCollect)):?>store_up_on<?php endif;?>">收藏(<?=$num?>)</p></li>
			<li><p class="two_code">二维码</p></li>
			<li><p class="shop_cart_store">购物车</p></li>
		</ul>
		</div>
	<?php elseif('dmfx'==$nav_flag1):?>
		<div class="agency_bottom">
        	<div class="agency_bottom_menu">
        		<ul>
        			<ul>
        				<a href="/distribution/merchants"><li id="s_sjlm" <?php if('merchants' == $nav_flag2):?>class="on"<?php endif;?>></li></a>
        				<a href="/distribution/agent"><li id="s_dl" <?php if('agency' == $nav_flag2):?>class="on"<?php endif;?>></li></a>
        				<a href="/distribution/shop"><li id="s_dp" <?php if('shop' == $nav_flag2):?>class="on"<?php endif;?>></li></a>
        				<a href="/distribution/my"><li id="s_wd" <?php if('my' == $nav_flag2):?>class="on"<?php endif;?>></li></a>
        			</ul>
        		</ul>
        	</div>
        </div>
	<?php elseif('cart'!=$nav_flag1):?>
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