<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if (in_array($nav_flag1, ['home','cart','cart_mnav','partner','user','merchant','merchant_lm'])):?>
<?php if(preg_match('/^cart/', $nav_flag1)):?>
		<div id="nav_cart_tool"></div>
		<div class="clear"></div>
	<?php endif;?>
	<?php if(preg_match('/^merchant/', $nav_flag1)):?>
		<div class="clear"></div>
	<?php endif;?>
	<?php if('merchant' != $nav_flag1 && 'cart' != $nav_flag1):?>
    	<div class="agency_bottom">
        	<div class="agency_bottom_menu">
        		<ul>
        			<ul>
        				<a href="/" id="f_sy" rel="home"><li id="s_sy" <?php if('home'==$nav_flag1):?>class="on"<?php endif;?>></li></a>
        				<a href="<?php echo U('trade/cart/list','mnav=1')?>" id="f_gwc" rel="cart"><li id="s_gwc" <?php if('cart_mnav'==$nav_flag1):?>class="on"<?php endif;?>><?php if($user_cart_num):?><span class="f_num"><?=$user_cart_num?></span><?php endif;?></li></a>
        				<a href="<?php echo U('distribution/merchants')?>" id="f_sj" rel="merchant_lm"><li id="s_lmsj" <?php if('merchant_lm'==$nav_flag1):?>class="on"<?php endif;?>></li></a>
        				<a href="<?php echo U('user')?>" id="f_hy" rel="user"><li id="s_hyzx" <?php if('user'==$nav_flag1):?>class="on"<?php endif;?>></li></a>
        			</ul>
        		</ul>
        	</div>
        </div>
    <?php endif;?>
    
<script type="text/javascript">$('#Mnav').on('click','a',function(){
	var rel = $(this).attr('rel');
	var href= $(this).attr('href');
	$(this).parents('.nav').find('a').removeClass('on');
	$(this).addClass('on');
	return true;
});
</script>
<?php endif;?>