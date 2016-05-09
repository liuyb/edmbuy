<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if(''!==$errmsg):?>

<div class="error"><?=$errmsg?></div>

<?php else:?>
<script id="forTopnav" type="text/html">
<div class="header">
	确认领取
    <a href="/trade/order/agent" class="back"></a>
</div>
</script>
<script>
show_topnav($('#forTopnav').html());
</script>

<?php include T('inc/user_ship_addr');?>

<div class="white_bg">
	<div class="settle_pro">
		结账商品
	</div>
	<div class="order_info">
		<table cellspacing="0" cellpadding="0" class="order_info_tab">
			<tr>
				<td class="info_td1" >
					<img src="<?=isset($packages['logo'])? $packages['logo'] : ''  ?>">
				</td>
				<td class="info_td2">
					<p class="info_name"><?=isset($pack['name'])?$packages['name']:'' ?></p>
				</td>
				<td class="info_td3">
					<p class="info_price" style="color:#f65d00;">￥<?=isset($packages['sale_price'])?$packages['sale_price']:0 ?></p>
					<p class="info_num" style="text-decoration: line-through;">￥<?=isset($packages['actual_price']) ? $packages['actual_price'] : 0 ?></p>	
				</td>
			</tr>
		</table>
	</div>
	
	<div class="settle_tell">
		<input type="text" maxlength="200"  placeholder="有话更商家说..." value="" id="order-message" name="order_msg">
	</div>
	
	<div class="settle_price">
		总价：<span class="pro_price" style="color:#f65d00;">￥<?=sprintf('%.2f', isset($packages['sale_price'])? $packages['sale_price']: 0) ?></span>
	</div>
</div>

<div class="now_get_btn" onclick="recPremiumPackage(this);">立即免费领取</div>

<script>
var pid = <?=isset($packages['pid'])?$packages['pid']:0 ?>

function recPremiumPackage(obj){
	$obj = $(obj);
	if($obj.hasClass("buying")){
		return;
	}
	var addr_id  = parseInt($('#express-it').attr('data-addrid'));
	var order_msg= $('#order-message').val();
	if (!addr_id) {
		myAlert('请填写收货地址');
		return false;
	}
	$obj.addClass("buying").text("领取中...");
	F.post('/distribution/agent/premium/buy', {address_id : addr_id, order_msg : order_msg, package_id : pid}, function(ret){
		if(ret.flag == 'SUC'){
			window.location.href='/distribution/agent/premium/succ?level=<?=$packages['type'] ?>';
		}else{
			$obj.removeClass("buying").text("立即免费领取");
			myAlert(ret.msg);
		}
	});
}
</script>
<?php endif;?>