<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if(''!==$errmsg):?>

<div class="error"><?=$errmsg?></div>

<?php else:?>
<script id="forTopnav" type="text/html">
<div class="header deve_prouct" style="padding:0 0 0 20px;">
<ul class="header_order">
	<li class="header_order_on">热卖推荐</li>
	<li>店主推荐</li>
	<li>平台推荐</li>
	<li>爆品推荐</li>
	<a href="javascript:history.back();" class="back"></a>
</ul>
</div>
</script>
<script>show_topnav($('#forTopnav').html())</script>

<div class="deve_product_list">
	<table cellspacing="0" cellpadding="0" class="deve_product_tab">
		<tr>
			<td class="deve_td1" >
				<img src="/themes/mobiles/img/pro.jpg">
			</td>
			<td class="deve_td2">
				<div class="deve_td2_tit">【热卖爆款】美骆世家那女那女包那等上写出售</div>
				<div class="deve_td2_price">
					<p>
						<span class="price_x">￥320.00</span>
						<span class="price_y">￥320.00</span>
						<span class="sales_num">销量:10000</span>
					</p>
					<p style="font-size: 11px;color:#999;">店铺平均返佣：25%</p>
				</div>
			</td>
		</tr>
		<tr>
			<td class="deve_td1" >
				<img src="/themes/mobiles/img/pro.jpg">
			</td>
			<td class="deve_td2">
				<div class="deve_td2_tit">【热卖爆款】美骆世家那女那女包那等上写出售</div>
				<div class="deve_td2_price">
					<p>
						<span class="price_x">￥320.00</span>
						<span class="price_y">￥320.00</span>
						<span class="sales_num">销量:10000</span>
					</p>
					<p style="font-size: 11px;color:#999;">店铺平均返佣：25%</p>
				</div>
			</td>
		</tr>
		<tr>
			<td class="deve_td1" >
				<img src="/themes/mobiles/img/pro.jpg">
			</td>
			<td class="deve_td2">
				<div class="deve_td2_tit">【热卖爆款】美骆世家那女那女包那等上写出售</div>
				<div class="deve_td2_price">
					<p>
						<span class="price_x">￥320.00</span>
						<span class="price_y">￥320.00</span>
						<span class="sales_num">销量:10000</span>
					</p>
					<p style="font-size: 11px;color:#999;">店铺平均返佣：25%</p>
				</div>
			</td>
		</tr>
	</table>
</div>

<script>
/* $(".header_order li").live("click",function(){
	if(! $(this).hasClass("header_order_on")){
		$(".header_order li").removeClass("header_order_on");
		$(this).addClass("header_order_on");
	}
})
$(".log_info").live("click",function(){
	window.location.href = 'ydm_track.html';
}) */

</script>
<?php endif;?>