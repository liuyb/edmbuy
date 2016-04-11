<?php defined('IN_SIMPHP') or die('Access Denied');?>

微信支付页面

<div class="order-topay">
  <div class="row"><button class="btn btn-block btn-green" id="btn-wxpay" data-payid="2">微信安全支付</button>
  <?php form_ordersn_script()?>
  </div>
</div>

<script>
$(function(){
	$('#btn-wxpay').click(function(){
		var order_sn = parseInt($('#frm_order_sn').val());
		var pay_id   = parseInt($(this).attr('data-payid'));
		var goods_id = parseInt('<?=$item_id?>');
		var goods_number = 1;

		var _this = this;
		$(this).text('努力加载中, 请稍候...').attr('disabled',true);
		F.post('<?php echo U('trade/order/submit_item')?>',{"order_sn":order_sn,"item_id":goods_id,"item_number":goods_number,"pay_id":pay_id},function(ret){
  			if (ret.flag=='SUC') {
  				$(_this).text('支付跳转中...');
  				form_topay_submit(ret.order_id, 'wxpay');
  			}
  			else{
  				$(_this).text('微信安全支付').removeAttr('disabled');
  				myAlert(ret.msg);
  			}
	  });
		
		return false;
	});
});
</script>