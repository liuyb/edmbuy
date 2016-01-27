<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if(''!==$errmsg):?>

<div class="error"><?=$errmsg?></div>

<?php else:?>
<script id="forTopnav" type="text/html">
<div class="header">
	订单详情
<a href="/trade/order/record" class="back"></a>
<a href="/user" class="back_r"></a>
</div>
</script>
<script>show_topnav($('#forTopnav').html())</script>
<!-- 
<div class="order_number">
	<p class="number_info">
		<span class="number_name">物流编号：</span>
		<span class="number_id">123456789554</span>
		<span class="number_exp">（韵达快递）</span>
	</p>
</div>
 -->
<div class="order_adderss">
	<div class="address_name">
		<span class="name_c"><?=$item['consignee'] ?></span>
		<span class="phone_c"><?=$item['mobile'] ?></span>
	</div>
	<div class="addresss_info">
		<span class="address_pro"><?=$item['province'].$item['city'].$item['district'] ?></span><span class="address_country"><?=$item['address'] ?></span>
	</div>
</div>

<div class="buyer_remark">
	<table class="remark_tab">
		<tr>
			<td class="remark_tit">买家备注:</td>
			<td class="remark_info"><?=$item['how_oos'] ?></td>
		</tr>
	</table>
</div>
<div class="order_list">
	<?php if (!empty($merchant_goods)):
	      $total_amount = 0;$total_goods = 0;
	?>
	<div class="order_tit">
		<span class="tit_logo"><img src="/themes/mobiles/img/shop_logo.png"></span>
		<span class="tit_name">
		<?=$merchant_goods['facename'] ?></span>
	</div>
	<div class="order_info">
		<table cellspacing="0" cellpadding="0" class="order_info_tab">
			<?php foreach ($merchant_goods['goods'] as $gd):
			 $total_amount = doubleval($total_amount) + doubleval($gd['goods_price']);
			 ++$total_goods; 
			?>
			<tr>
				<td class="info_td1" >
					<a href="<?php echo U('item/'.$gd['goods_id'])?>"><img src="<?=$gd['goods_thumb'] ?>"></a>
				</td>
				<td class="info_td2">
					<p class="info_name"><a href="<?php echo U('item/'.$gd['goods_id'])?>"><?=$gd['goods_name'] ?></a></p>
					<!-- <p class="ifno_etalon">颜色：蓝色；尺码：45</p> -->
				</td>
				<td class="info_td3">
					<p class="info_price">￥<?=$gd['goods_price'] ?></p>
					<p class="info_num">x<?=$gd['goods_number'] ?></p>	
				</td>
			</tr>
			<?php endforeach;?>
		</table>
	</div>
	<div class="order_price">
		<p>
			<span style="padding-right:12px;">共<span><?=$total_goods ?></span>件商品 </span>
			合计：￥ <span class="price_p"><?=doubleval($total_amount) ?></span> （含运费：￥0.00）
		</p>
	</div>
	<div class="order_serve">
		<ul>
			<li class="online_serve"><a href="tel:<?=$merchant_goods['telphone'] ?>">在线客服</a></li>
			<li class="after_serve"><a href="<?=$merchant_goods['kefu'] ?>">售后服务</a></li>
		</ul>
	</div>
	<?php endif;?>
</div>

<div class="order_info_time">
	<p>微信交易号:<span class="time_color_comm"><?=$item['pay_trade_no'] ?></span></p>
	<p>创建时间:<span class="time_color_comm"><?=date('Y-m-d H:i:s', simphp_gmtime2std($item['add_time'])) ?></span></p>
	<p>付款时间:<span class="time_color_comm"><?php if($item['pay_time']):?><?=date('Y-m-d H:i:s', simphp_gmtime2std($item['pay_time'])) ?><?php endif?></span></p>
</div>

<div class="order_type_btn">
<?php if ($order->order_status==OS_CANCELED):?>
<button class="order_but_l del_success_order" data-order_id="<?=$order_id?>">已取消</button>
<?php elseif($order->pay_status!=PS_PAYED):?>
<button class="order_but_l del_success_order" data-order_id="<?=$order_id?>" onclick="cancel_order(this)">取消订单</button>
<?php endif;?>
	
	<button class="order_but_l return_order" onclick="return_product(this)">
	<?php if ($order->shipping_status==SS_UNSHIPPED):?>
	退款
	<?php else:?>
	退货
	<?php endif;?>
	</button>
<!--
	<button class="order_but_r again_buy" onclick="location.href='/item/'">再次购买</button>
-->
</div>
<script type="text/javascript">
function cancel_order(obj) {
	var _self = cancel_order;
	if (typeof (_self.ajaxing) != 'undefined') {
		_self.ajaxing = 0;
	} 
	var order_id = $(obj).attr('data-order_id');
	order_id = parseInt(order_id);
	_self.ajaxing = 1;
	if (confirm('确定取消该订单么？')) {
  		var pdata = {"order_id": order_id};
  		F.post('<?php echo U('trade/order/cancel')?>',pdata,function(ret){
  			_self.ajaxing = 0;
  			if (ret.flag=='SUC') {
  				window.location.reload();
  			}
  		});
		}
}
function return_product(obj) {
	myAlert('请联系在线客服');
}
</script>
<?php endif;?>