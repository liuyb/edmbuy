<?php defined('IN_SIMPHP') or die('Access Denied');?>

<script id="forTopnav" type="text/html">
<div class="header h_order" style="padding:0 0 0 20px;">
	<ul class="header_order">
    <li class="noon"><a href="<?php echo U('user')?>" class="btna back">&nbsp;</a></li>
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
			var url = "<?php echo U('trade/order/record')?>?status="+$(this).attr('data_status');
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

<?php foreach ($orders AS $ord):?>

<div class="list-container">
  
  <div class="list-head <?php if ($ord['active_order']):echo 'list-head-blue';else:echo 'list-head-gray';endif;?> list-head-order">
    <span class="order-date"><?php echo date('Y-m-d',$ord['add_time'])?></span><span class="order-sn">订单号：<em><?=$ord['order_sn']?></em></span>
  </div>
  
  <div class="list-body list-body-order">
  
  <?php foreach($ord['order_goods'] AS $g):?>
    <div class="it clearfix" data-url="<?=$g['goods_url']?>" data-rid="<?=$g['order_id']?>">
      <div class="c-24-6 col-2 withclickurl"><img src="<?=$g['goods_thumb']?>" alt="" class="goods_pic" /></div>
      <div class="c-24-11 col-3 withclickurl">
        <p><?=$g['goods_name']?></p>
        <p class="price-txt">(￥<?=$g['goods_price']?> x<?=$g['goods_number']?>)</p>
      </div>
    </div>
  <?php endforeach;?>
    <div class="right-merge">
      <p>￥<span class="gprice"><?php if ($ord['pay_status']==PS_PAYED): echo $ord['money_paid']; else: echo $ord['order_amount']; endif;?></span></p>
      <div class="order-status"><?=$ord['show_status_html']?></div>
    </div>
    
  </div>
  
</div>

<?php endforeach;?>

<?php form_topay_script(U('trade/order/payok'));?>
<?php require_scroll2old();?>
<script>
$(function(){
	var $lbod = $('.list-body');
	var thisctx = {};
	
	$('.withclickurl',$lbod).click(function(){
		//window.location.href = $(this).parent().attr('data-url')+'?ref=/trade/order/record';
		window.location.href = '/order/'+$(this).parent().attr('data-rid')+'/detail';
		return false;
	});
	$('.btn-order-cancel',$lbod).click(function(){
		if (typeof(thisctx.ajaxing_cancel)=='undefined') {
			thisctx.ajaxing_cancel = 0;
		}
		if (thisctx.ajaxing_cancel) return false;
		thisctx.ajaxing_cancel = 1;

		if (confirm('确定取消该订单么？')) {
  		var pdata = {"order_id": parseInt($(this).attr('data-order_id'))};
  		F.post('<?php echo U('trade/order/cancel')?>',pdata,function(ret){
  			thisctx.ajaxing_cancel = undefined;
  			if (ret.flag=='SUC') {
  				window.location.reload();
  			}
  		});
		}
		else {
			thisctx.ajaxing_cancel = undefined;
		}
		return false;
	});
	$('.btn-order-topay',$lbod).click(function(){
		form_topay_submit($(this).attr('data-order_id'));
	});
	$('.btn-ship-confirm',$lbod).click(function(){
		if (typeof(thisctx.ajaxing_confirm)=='undefined') {
			thisctx.ajaxing_confirm = 0;
		}
		if (thisctx.ajaxing_confirm) return false;
		thisctx.ajaxing_confirm = 1;

		if (confirm('确定收货么？')) {
  		var pdata = {"order_id": parseInt($(this).attr('data-order_id'))};
  		F.post('<?php echo U('trade/order/confirm_shipping')?>',pdata,function(ret){
  			thisctx.ajaxing_confirm = undefined;
  			if (ret.flag=='SUC') {
  				//window.location.reload();
  				window.location.href = "<?php echo U('trade/order/record',['status'=>'finished'])?>";
  			}
  			else {
  	  		myAlert(ret.msg);
  			}
  		});
		}
		else {
			thisctx.ajaxing_confirm = undefined;
		}
		return false;
	});
});

</script>

<?php endif;?>