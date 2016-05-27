<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if(''!==$errmsg):?>

<div class="error"><?=$errmsg?></div>
<?php else:?>
<style>
.rer_tit{
	margin:0 10px;
}
.rer_tit p{
	height:44px;
	line-height:44px;
	font-size:16px;
	color:#333;
	border-bottom:1px solid #E6E6E6;
}
.rer_tit li{
	padding: 12px 0px;
	font-size:12px;
	color:#333;
	border-bottom:1px solid #E6E6E6;
}
.rer_tit span{
	font-size:12px;
	color:#8c8c8c;	
}
.rer_btn{
	padding:10px;
}
.rer_btn button{
	height:36px;
	line-height:36px;
	background:#f65d00;
	font-size:14px;
	color:#fff;
	padding:0 5px;
	margin-right:5px;
	border-radius:4px;
}
</style>

<script id="forTopnav" type="text/html">
<div class="header">
	退款详情
<a href="javascript:goBack('<?php echo U("user") ?>');" class="back"></a>
</div>
</script>
<script>show_topnav($('#forTopnav').html())</script> 

<div style="background:#fff;">
<?php if($refund && $refund['rec_id']):?>
	<?php if($refund['check_status'] < 1):?>
	<div class="rer_tit">
		<p>等待商家处理退款申请</p>
		<ul>
			<li>如果商家同意：<span>申请将达成，退款将原路退回</span></li>
			<li>如果商家拒绝：<span>将需要您修改退款申请</span></li>
			<li>如果商家未处理：<span>超过3天平台将会介入处理</span></li>
		</ul>
	</div>
	<?php elseif ($refund['check_status'] == 1 && $refund['wx_status'] == 1):?>
	<div class="rer_tit">
		<p>退款成功，退款将会原路退回到您的账户</p>
		<ul>
			<li>退款金额：<span><?php echo $refund['refund_money']?></span></li>
			<li>退款时间：<span><?php echo $refund['succ_time']?></span></li>
		</ul>
	</div>
	<?php elseif($refund['check_status'] == 1 && $refund['wx_status'] == 2):?>
	<div class="rer_tit">
		<p>商家已通过你的退款请求，微信退款失败</p>
		<ul>
			<li>失败原因：<span><?php echo $refund['wx_response'] ?></span></li>
			<li>失败说明：<span>微信退款失败，请您对照失败原因，完善资料后及时重新提交退款申请。</span></li>
		</ul>
	</div>
	<?php elseif($refund['check_status'] == 2):?>
	<div class="rer_tit">
		<p>退款申请被拒绝</p>
		<ul>
			<li>商家拒绝时间：<span><?php echo date('Y-m-d H:i:s', $refund['refuse_time'])?></span></li>
			<li>如果买家未处理：<span>超过3天订单将会变回待发货状态</span></li>
		</ul>
	</div>
	<?php endif;?>
	
	<?php if($refund['wx_status'] != 1):?>
	<div class="rer_btn">
		<?php if($refund['check_status']>0):?><button onclick="showRefundDialog();">重新提交申请</button><?php endif;?><button onclick="cancelRefund();">撤销退款申请</button><button onclick="showKefu();">申请客服介入</button>
	</div>
	<?php endif;?>
	
</div>

<div style="background:#fff;margin-top:10px;">
	<div class="rer_tit">
		<p>协商详情</p>
		<ul>
			<li>买家退款原因： <span><?php echo $refund['refund_reason']?></span></li>
			<?php if($refund['check_status'] == 2):?>
			<li>商家拒绝原因：<span><?php echo $refund['refuse_txt']?></span></li>
			<?php endif;?>
			<li>退款状态：<span><?php echo OrderRefund::getRefundStatus($refund['check_status'], $refund['wx_status'])?></span></li>
			<li class="del_bottom">退款申请时间： <span><?php echo $refund['refund_time']?></span></li>
		</ul>
	</div>
</div>

<script>
function showRefundDialog(){
	var order_id = <?=$refund['order_id']?>;
	var dialog = "<textarea style=\"width: 95%;height: 100px;\" maxlength='500' class='refuseTxt'></textarea><div style='margin-left:10px;color:red;' class='errMsg'></div>";
	dialog +="<div style='margin:10px 5px 10px 10px;text-align:center;'><input type='button' value='关闭' onclick='weui_dialog_close();' class='edmbuy_button second_btn'/><input type='button' value='提交' style='margin-left:10px;' class='edmbuy_button primary_btn' onclick='modifyRefund(this,"+order_id+");'></div>";
	weui_dialog($(dialog),'请输入退款理由');
}

function modifyRefund(obj, order_id){
	var $this = $(obj).closest('.weui_dialog_bd').first();
	var txt = $this.find('.refuseTxt').val();
	F.post('/order/refund/again',{order_id : order_id, rec_id : <?=$refund['rec_id']?>, refund_reason:txt},function(ret){
		if(ret.flag == 'SUC'){
			weui_dialog_close();
			alert('退款申请已提交，等待商家审核。');
			window.location.reload();
		}else{
			$this.find('.errMsg').html(ret.msg);
		}
	});
}

function cancelRefund(){
	weui_confirm('确认要撤销退款申请吗？', '',function(){
    	F.postWithLoading('/order/refund/cancel', {rec_id : <?=$refund['rec_id']?>}, function(ret){
    		if(ret.flag == 'SUCC'){
    			alert('操作成功');
    			window.location.href='<?php echo U('order/'.$refund['order_id'].'/detail')?>';
    		}else{
    			weui_alert(ret.msg);
    		}
    	});
	});
}

function showKefu(){
	var $qrdom = $("<div class='add_friend wxqr_friend' style='position:inherit;display:block;background:none;height:220px;width:100%;'><div class='add_f_img' style='margin:0px;'><img src='/themes/mobiles/img/ydm.png'></div><div class='w_wx_colse close_f' onclick='weui_dialog_close();'><img src='/themes/mobiles/img/gub.png'></div></div>");
	weui_dialog($qrdom,'长按图中二维码联系客服');
}
</script>
<?php else:?>
<div style="background:#fff;">
<div class="rer_tit">
还没有退款详情数据。
</div>
</div>
<?php endif;?>
<?php endif;?>