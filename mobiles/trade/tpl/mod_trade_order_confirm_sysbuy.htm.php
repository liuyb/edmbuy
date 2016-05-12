<?php defined('IN_SIMPHP') or die('Access Denied');?>
<?php if(isset($_SESSION['step']) && $_SESSION['step'] == 3):?>
<div id="sus_flow">
	<ul>
		<li>商家资料</li>
		<li class="li_cs"><img src="/themes/mobiles/img/back.png"></li>
		<li>设置密码</li>
		<li class="li_cs"><img src="/themes/mobiles/img/back.png"></li>
		<li class="li_co">支付</li>
		<li class="li_cs"><img src="/themes/mobiles/img/back.png"></li>
		<li>完成开通</li>
	</ul>
	<div class="clear"></div>
</div>
<div id="sus_info" style="height:150px;">
	<div class="pay_n" style="padding-top:30px"><img src="/themes/mobiles/img/sss1.png"></div>
	<div class="pay_n">订单提交成功</div>
</div>
<?php endif;?>
<div class="pay_order_info">订单详情</div>

<div id="sus_info">
	<table cellspacing="0" cellpadding="0" class="buy_time_info">
		<tr>
			<th>名称</th><th>有效期</th><th>总金额</th>
		</tr>
		<tr>
			<td>多米分销系统</td><td>1年</td><td>￥999.00</td>
		</tr>
	</table>
</div>
<!-- 
<div id="sus_info">
	<textarea class="to_us_tell" placeholder="有话更益多米说..."></textarea>
</div>
 -->
<div class="order-topay">
  <div class="row"><button class="btn btn-block btn-green" id="btn-wxpay" data-payid="2">微信安全支付</button>
  <?php form_order_script()?>
  </div>
</div>
<script>
$(function(){
	$("#Mbody").css("background","#fff");
	$('#btn-wxpay').click(function(){
		var order_sn = $('#frm_order_sn').val();
		var pay_id   = parseInt($(this).attr('data-payid'));
		var goods_id = parseInt('<?=$item_id?>');
		var goods_number = 1;

		var _this = this;
		$(_this).text('支付中, 请稍候...').attr('disabled',true);
		F.post('<?php echo U('trade/order/submit_item')?>',
				{"order_sn":order_sn,"item_id":goods_id,"item_number":goods_number,"pay_id":pay_id,"order_msg":""},
				function(ret){
  			if (ret.flag=='SUC') {
  				$(_this).text('支付跳转中...');

				/* var data = {'order_id': ret.order_id, 'order_sn': ret.order_sn}
				F.post("/user/merchant/paysuc", data, function (ret) {
					window.location.href = ret.url;
				});
					return false; */
				wxpayJsApiCall(ret.js_api_params,ret.order_id,function(flag) {
					if (flag == 'OK') {
						window.location.href='/user/merchant/dosuccess';
					}else{
						window.location.href='<?=U('trade/order/record') ?>';
 					}
				});
				//form_topay_submit(ret.order_id, 'wxpay');
			} else{
				$(_this).text('微信安全支付').removeAttr('disabled');
				alert(ret.msg);
			}
	 });

		return false;
	});
});
</script>