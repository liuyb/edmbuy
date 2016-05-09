<?php defined('IN_SIMPHP') or die('Access Denied');?>

<script id="forTopnav" type="text/html">
<div class="header h_order" style="padding:0 0 0 20px;">
	<ul class="header_order">
<?php if(!isset($_GET['spm']) || !preg_match('/^user\.(\d+)\.merchant$/i', $_GET['spm'])):?>
    <li class="noon"><a href="javascript:history.back();" class="btna back">&nbsp;</a></li>
<?php endif;?>
		<li <?php if($status == '' || $status == 'all'): ?> class="header_order_on" <?php endif;?> data_status="all">全部</li>
		<li <?php if($status == 'wait_pay'): ?> class="header_order_on" <?php endif;?> data_status="wait_pay">待付款</li>
		<li <?php if($status == 'wait_ship'): ?> class="header_order_on" <?php endif;?> data_status="wait_ship">待发货</li>
		<li <?php if($status == 'wait_recv'): ?> class="header_order_on" <?php endif;?> data_status="wait_recv">待收货</li>
		<li <?php if($status == 'finished'): ?> class="header_order_on" <?php endif;?> data_status="finished">已完成</li>
	</ul>
</div>
</script>
<script>show_topnav($('#forTopnav').html())</script>
<script>
$(function(){
	$('.header_order li').bind('click',function(){
		if(!$(this).hasClass('noon')) {
			$(this).parent().find('li').removeClass('header_order_on');
			$(this).addClass('header_order_on');
			var url = "<?php echo U('trade/order/record','spm='.(isset($_GET['spm'])?$_GET['spm']:''))?>&status="+$(this).attr('data_status');
			window.location = url;
		}
	});
});
</script>

<?php if (!$orders_num):?>

<div class="list-empty">
  <?php if(''==$errmsg):?>
  <?php if('all'==$status):?>
  <h1 class="list-empty-header">居然还没买过东西╮(╯﹏╰）╭</h1>
  <div class="list-empty-content"><a href="<?php echo U('/')?>">去逛逛</a></div>
  <?php else:?>
  <h1 class="list-empty-header">当前列表没有记录</h1>
  <?php endif;?>
  <?php else:?>
  <h1 class="list-empty-header"><?=$errmsg?></h1>
  <?php endif;?>
</div>

<?php else :?>

<?php foreach ($orders AS $ord): 
    $count = 0?>
<div class="order_list">
	<div class="order_title" <?php if($ord['merchant_id']):?>onclick="window.location.href='<?=U('/shop/'.$ord['merchant_id']) ?>'"<?php endif;?>>
		<span class="tit_logo"><img src="/themes/mobiles/img/shop_logo.png"></span>
		<span class="title_name"><?=$ord['facename'] ?></span>
		<span class="tit_type"><?=$ord['status_txt'] ?></span>
	</div>
	<?php foreach($ord['order_goods'] AS $g):
	   $count ++;
	?>
		<div class="order_info" data-rid="<?=$g['order_id']?>" data-gid="<?=$g['goods_id']?>">
			<table cellspacing="0" cellpadding="0" class="order_info_tab">
				<tr>
					<td class="info_td1" >
						<img src="<?=$g['goods_thumb']?>">
					</td>
					<td class="info_td2">
						<p class="info_name"><?=$g['goods_name']?></p>
						<p class="ifno_etalon"><?=$g['attr_txt'] ?></p>
					</td>
					<td class="info_td3">
						<p class="info_price">￥<?=$g['goods_price']?></p>
						<p class="info_num">x<?=$g['goods_number']?></p>
						<?php if($ord['shipping_status'] == SS_RECEIVED  && !$g['has_comment']):?>
						<p style="margin-top: 15px;font-size:16px;color:#ff0101;" class='goods_comment'>晒单评价</p>
						<?php endif;?>
					</td>
				</tr>
			</table>
		</div>
	<?php endforeach;?>	
	<div class="order_price">
		<p>
			<span style="padding-right:12px;">共<span><?=$count ?></span>件商品 </span>
			合计：￥ <span class="price_p"><?php if ($ord['pay_status']==PS_PAYED): echo $ord['money_paid']; else: echo $ord['order_amount']; endif;?></span> （含运费：￥<?=$ord['shipping_fee']?>）
		</p>
	</div>
	<?php if($ord['show_status_html']):?>
	<div class="order_serve order_right order_operation" data-orderid="<?=$ord['order_id'] ?>">
		<?=$ord['show_status_html'] ?>
	</div>
	<?php endif;?>
</div>

<?php endforeach;?>

<?php include T('inc/order_operation');?>
<script>
$(function(){
	$('.order_info').click(function(){
		window.location.href = '/order/'+$(this).attr('data-rid')+'/detail'+'<?php echo (isset($_GET['spm']) ? '?spm='.$_GET['spm'] : '')?>';
		return false;
	});
});
</script>
<?php endif;?>