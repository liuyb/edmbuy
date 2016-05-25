<?php form_topay_script(U('trade/order/payok'));?>
<script>
$(function(){
	var thisctx = {};
	
	$(".goods_comment").click(function(e){
		e.stopPropagation();
		var $parent = $(this).closest('.order_info').first();
		var order_id = $parent.data("rid");
		var goods_id = $parent.data("gid");
		window.location.href = '/item/comment/page?order_id='+order_id+'&goods_id='+goods_id;
	});
	$('.btn-order-cancel').click(function(){
		if (typeof(thisctx.ajaxing_cancel)=='undefined') {
			thisctx.ajaxing_cancel = 0;
		}
		if (thisctx.ajaxing_cancel) return false;
		thisctx.ajaxing_cancel = 1;
		
		if (confirm('确定取消该订单么？')) {
  		var pdata = {"order_id": parseInt(getOrderId(this))};
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
	$('.btn-order-delete').click(function(){
		var $this = $(this);
		if (confirm('确定删除该订单么？')) {
      		var pdata = {"order_id": parseInt(getOrderId(this))};
      		F.post('<?php echo U('trade/order/delete')?>',pdata,function(ret){
      			if (ret.flag=='SUC') {
          			if($this.hasClass("indetail")){
          				window.location.href = "<?php echo U('trade/order/record',['status'=>'all'])?>";
              		}else{
              			window.location.reload();
              		}
      			}
      		});
		}
		return false;
	});
	$('.btn-order-topay').click(function(){
		form_topay_submit(getOrderId(this));
	});
	$(".btn-ship-view").click(function(){
		var order_id = getOrderId(this)
		window.location.href = '/order/'+order_id+'/express';
	});
	$('.btn-ship-confirm').click(function(){
		if (typeof(thisctx.ajaxing_confirm)=='undefined') {
			thisctx.ajaxing_confirm = 0;
		}
		if (thisctx.ajaxing_confirm) return false;
		thisctx.ajaxing_confirm = 1;

		if (confirm('确定收货么？')) {
  		var pdata = {"order_id": parseInt(getOrderId(this))};
  		F.post('<?php echo U('trade/order/confirm_shipping')?>',pdata,function(ret){
  			thisctx.ajaxing_confirm = undefined;
  			if (ret.flag=='SUC') {
  				window.location.href = "<?php echo U('trade/order/record',['status'=>'finished'])?>";
  			}
  			else {
  				weui_alert(ret.msg);
  			}
  		});
		}
		else {
			thisctx.ajaxing_confirm = undefined;
		}
		return false;
	});
	$(".btn_refund_money").bind('click', function(){
		var order_id = parseInt(getOrderId(this));
		weui_confirm('确认要申请退款吗？', '', function(){
    		var dialog = "<textarea style=\"width: 95%;height: 100px;\" class='refuseTxt'></textarea><div style='margin-left:10px;color:red;' class='errMsg'></div>";
    		dialog +="<div style='margin:10px 5px 10px 10px;text-align:right;'><input type='button' value='关闭' onclick='weui_dialog_close();' class='edmbuy_button second_btn'/><input type='button' value='提交' style='margin-left:10px;' class='edmbuy_button primary_btn' onclick='orderRefund(this,"+order_id+");'></div>";
    		weui_dialog($(dialog),'请输入退款理由');
		});
	});
	
});

function orderRefund(obj,order_id){
	var $this = $(obj).closest('.weui_dialog_bd').first();
	var txt = $this.find('.refuseTxt').val();
	F.post('/trade/order/refund',{order_id : order_id,refund_reason:txt},function(ret){
		if(ret.flag == 'SUC'){
			weui_dialog_close();
			weui_alert('退款申请已提交，等待商家审核。');
			//window.location.reload();
		}else{
			$this.find('.errMsg').html(ret.msg);
		}
	});
}

function getOrderId(obj){
	return $(obj).closest(".order_operation").data("orderid");
}
</script>