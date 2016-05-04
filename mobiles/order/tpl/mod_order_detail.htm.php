<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if(''!==$errmsg):?>

<div class="error"><?=$errmsg?></div>

<?php else:?>
<script id="forTopnav" type="text/html">
<div class="header">
	订单详情
<?php if(!isset($_GET['spm']) || !preg_match('/^user\.(\d+)\.merchant$/i', $_GET['spm'])):?>
<a href="/trade/order/record" class="back"></a>
<a href="/user" class="back_r"></a>
<?php endif;?>
</div>
</script>
<script>show_topnav($('#forTopnav').html())</script> 
<?php if($order->shipping_name && $order->invoice_no):?>
<a href="<?php echo U("order/$order_id/express") ?>">
<div class="order_number">
	<p class="number_info">
		<span class="number_name">物流编号：</span>
		<span class="number_id"><?=htmlentities($order->invoice_no) ?></span>
		<span class="number_exp">（<?=$order->shipping_name ?>）</span>
	</p>
</div>
</a>
<?php endif;?>
<div class="order_adderss">
	<div class="address_name">
		<span class="name_c"><?=$order->consignee ?></span>
		<span class="phone_c"><?=$order->mobile ?></span>
	</div>
	<div class="addresss_info">
		<span class="address_pro"><?=$order->order_region ?></span><span class="address_country"><?=$order->address ?></span>
	</div>
</div>

<div class="buyer_remark">
	<table class="remark_tab">
		<tr>
			<td class="remark_tit">买家备注:</td>
			<td class="remark_info"><?php echo ($order->postscript ? : '无');?></td>
		</tr>
	</table>
</div>
<div class="order_list">
	<?php $first_goods_id=0; if (!empty($merchant_goods)):
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
			 $total_amount = doubleval($total_amount) + (doubleval($gd['goods_price']) * intval($gd['goods_number']));
			 $total_goods += intval($gd['goods_number']);
			 if(!$first_goods_id) $first_goods_id = $gd['goods_id'];
			?>
			<tr>
				<td class="info_td1" >
					<a href="<?php echo U('item/'.$gd['goods_id'])?>"><img src="<?=$gd['goods_thumb'] ?>"></a>
				</td>
				<td class="info_td2">
					<p class="info_name"><a href="<?php echo U('item/'.$gd['goods_id'])?>"><?=$gd['goods_name'] ?></a></p>
					<p class="ifno_etalon"><?=$gd['goods_attr'] ?></p>
				</td>
				<td class="info_td3">
					<p class="info_price">￥<?=$gd['goods_price'] ?></p>
					<p class="info_num">x<?=$gd['goods_number'] ?></p>	
					<?php if($order->shipping_status == SS_RECEIVED && !$gd['has_comment']):?>
					<p style="margin-top: 10px;font-size:16px;color:#ff0101;" onclick="orderGoodsComment('<?=$gd['order_id']?>','<?=$gd['goods_id']?>');">晒单评价</p>
					<?php endif;?>
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
			<li class="online_serve"><a href="<?=$merchant_goods['kefu'] ?>">在线客服</a></li>
			<li class="after_serve"><a href="tel:<?php echo $merchant_goods['mobile']?:$merchant_goods['telphone'] ?>">售后服务</a></li>
		</ul>
	</div>
	<?php endif;?>
</div>

<div class="order_info_time">
	<p><span class="order_info_ph">订单号　</span>:<span class="time_color_comm"><?=$order->order_sn?></span></p>
	<p><span class="order_info_ph">交易号　</span>:<span class="time_color_comm"><?=$order->pay_trade_no ?></span></p>
	<p><span class="order_info_ph">创建时间</span>:<span class="time_color_comm"><?=date('Y-m-d H:i:s', simphp_gmtime2std($order->add_time)) ?></span></p>
	<p><span class="order_info_ph">付款时间</span>:<span class="time_color_comm"><?php if($order->pay_time):?><?=date('Y-m-d H:i:s', simphp_gmtime2std($order->pay_time)) ?><?php endif?></span></p>
</div>


<?php 
function is_cft_over_7ds($cftime){
    $cf_over_7ds = false;
    if($cftime){
        $diff = (time() - simphp_gmtime2std($cftime)) / 86400;
        if($diff >= 7){
            $cf_over_7ds = true;
        }
    }
    return $cf_over_7ds;
}
?>
<div class="order_type_btn order_operation" data-orderid="<?=$order_id?>">
<!-- 未支付 （立即付款、取消订单）-->
<?php if ($order->pay_status == PS_UNPAYED):?>
<button class="order_but_l btn-order-cancel">取消订单</button>
<button class="order_but_r btn-order-topay">立即付款</button>
<!-- 已支付未发货（退款、继续购买） -->
<?php elseif ($order->pay_status == PS_PAYED && in_array($order->shipping_status, [SS_UNSHIPPED, SS_PREPARING, SS_SHIPPED_ING]) ): ?>
<button class="order_but_l btn_refund_money">退款</button>
<button class="order_but_r btn_rebuy_good">继续购买</button>
<!-- 已支付已发货 （确认收货、继续购买）-->
<?php elseif ($order->pay_status == PS_PAYED && in_array($order->shipping_status, [SS_SHIPPED, SS_SHIPPED_PART, OS_SHIPPED_PART]) ): ?>
<button class="order_but_l btn_rebuy_good">继续购买</button>
<button class="order_but_r btn-ship-confirm">确认收货</button>
<!-- 已支付已发货已确认收货 - 七天内支持退货（退货、继续购买） -->
<?php elseif ($order->shipping_status == SS_RECEIVED && !is_cft_over_7ds($order->shipping_confirm_time)):?>
<button class="order_but_l btn_refund_order">退货</button>
<button class="order_but_r btn_rebuy_good">继续购买</button>
<!-- 其他-->
<?php else:?>
<button class="order_but_l btn-order-delete indetail">删除</button>
<button class="order_but_r btn_rebuy_good">继续购买</button>
<?php endif;?>
</div>
<?php include T('inc/order_operation');?>
<script type="text/javascript">
$().ready(function(){
	$(".btn_refund_order").bind('click', function(){
		return_product(this);
	});
	$(".btn_rebuy_good").bind('click', function(){
		window.location.href='<?php echo $first_goods_id ? (U('/item/'.$first_goods_id)) : 'javascript:;'?>';
	});
});
function return_product(obj) {
	myAlert('请联系在线客服');
}

function orderGoodsComment(order_id, goods_id){
	window.location.href = '/item/comment/page?order_id='+order_id+'&goods_id='+goods_id;
}
</script>
<?php endif;?>