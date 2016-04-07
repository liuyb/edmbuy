<?php defined('IN_SIMPHP') or die('Access Denied');?>

微信支付页面

<div class="order-topay">
  <div class="row"><button class="btn btn-block btn-green" id="btn-wxpay" data-payid="2">微信安全支付</button></div>
</div>

<script>
$(function(){
	$('#btn-wxpay').click(function(){
		var pay_id   = parseInt($(this).attr('data-payid'));
		var addr_id  = parseInt($('#express-it').attr('data-addrid'));
		var order_msg= $('#order-message').val();
		var cart_rids= $('#cart-list-body').attr('data-cart_rids');
		if (!addr_id) {
			myAlert('请填写收货地址');
			return false;
		}

		var _this = this;
		$(this).text('努力加载中, 请稍候...').attr('disabled',true);
		F.post('<?php echo U('trade/order/submit')?>',{"address_id":addr_id,"cart_rids":cart_rids,"order_msg":order_msg,"pay_id":pay_id},function(ret){
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