<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if(''!==$errmsg):?>

<div class="error"><?=$errmsg?></div>

<?php else:?>

<?php if(!isset($merchant) || empty($merchant)):?>
<div class="no_store_c">
	<img src="/themes/mobiles/img/wdp.png">
	<p><button onclick="window.location.href='/user/merchant/checkin'">我要开店</button></p>
</div>
<script type="text/javascript">
$(function(){
	$("#Mbody").css("background", "#fff");
});
</script>
<?php else:?>
<div class="s_manage_top">
	<div class="manage_top_r">
		<img src="<?=$merchant->logo ?>" class="r_img">
		<p class="top_r_title"><span class="r_span"><?=$merchant->facename ?></span></p>
		<div class="top_r_type">	
			<ul>
				<li><img src="/themes/mobiles/img/yiduomi-inc.png" style="width:79px;margin-left:166%;"></li>
				<!-- <li><img src="/themes/mobiles/img/danbao.png">益多米担保</li>
				<li><img src="/themes/mobiles/img/shiming.png">实名认证</li>
				<li><img src="/themes/mobiles/img/changjia.png">厂家直销</li> -->
				<div class="clear"></div>
			</ul>
		</div>
	</div>
</div>

<div class="manage_type_list">
	<ul>
		<li><p class="type_li_font"><?=$all_goods ?></p><p>全部商品</p></li>
		<li><p class="type_li_font"><?=$all_orders ?></p><p>订单总数</p></li>
		<li><p class="type_li_font"><?=$sale_amount ?></p><p>总销售额</p></li>
		<li><p class="type_li_font"><?=$wait_pay_orders ?></p><p>代付款订单</p></li>
		<li><p class="type_li_font"><?=$wait_ship_orders ?></p><p>代发货订单</p></li>
		<li><p class="type_li_font"><?=$wait_return_orders ?></p><p>退换货订单</p></li>
		<div class="clear"></div>
		<span class="line1"></span>
		<span class="line2"></span>
		<span class="line3"></span>
		<span class="line4"></span>
	</ul>
</div>

<div class="code_t" id="showQrcode" style="display:none;">
	<div class="close_top" onclick="$('#showQrcode').hide();">
		<img src="/themes/mobiles/img/gbh.png">
	</div>
	<div class="z_code_s">
		<?php if($merchant->wxqr):?>
		<p style="margin:20px 0 10px;">关注公众号二维码</p>
		<p><img src="<?=$merchant->wxqr ?>"></p>
		<?php endif;?>
		
		<?php if($merchant->shop_qcode):?>
		<p style="margin:20px 0 10px;">关注店铺二维码</p>
		<p><img src="<?=$merchant->shop_qcode ?>"></p>
		<?php endif;?>
		
		<?php if(!$merchant->wxqr && !$merchant->shop_qcode):?>
		<p style="margin:20px 0 10px;">还没有二维码</p>
		<?php endif;?>
	</div>
</div>

<div class="manage_btn_list">
	<button class="enter_store_btn" onclick="window.location.href='/shop/<?=$merchant->uid ?>'">进入店铺</button>
	<button class="enter_store_btn" onclick="$('#showQrcode').show()">店铺二维码</button>
</div>

<div style="text-align:center;margin-bottom:59px;">
	<p style="font-size:12px;color:#8c8c8c;">电脑登录网址：<?=C('env.site.merchant') ?></p>
</div>
<?php endif;?>
<?php endif;?>