<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if(''!==$errmsg):?>

<div class="error"><?=$errmsg?></div>

<?php else:?>
<style>
    .disabled {
    	opacity : 0.2;
    }
</style>
<script id="forTopnav" type="text/html">
<div class="header">
	多米代理
	<a href="/user" class="back"></a>
</div>
</script>
<script>
show_topnav($('#forTopnav').html());
</script>
<?php if($isAgent):?>
<div class="agency_become">
	<table cellspacing="0" cellpadding="0" class="become_a_tab">	
		<tr>
			<td><img src="<?=$user->logo ?>"></td>
			<td>
				<p class="become_name"><?=$user->nickname ?>
				<?php $img = AgentPayment::getAgentIconByLevel($user->level); ?>
				<?php if($img):?>
				<img src="<?=$img ?>" class="jin_z">
				<?php endif;?>
				</p>
				<p class="become_name_id">多米号：<?=$user->uid ?></p>
			</td>
			<?php if($agent->pid && !$agent->premium_id):?>
			<td><p class="get_become_meal" onclick="window.location.href='/distribution/agent/package';">领取<?=AgentPayment::getAgentPaidMoney($user->level) ?>元套餐</p></td>
			<?php endif;?>
		</tr>
	</table>
</div>
<?php else:?>
<div class="agency_is">
	<img src="<?=$user->logo ?>">
	<span>您还未代理多米分销！</span>
</div>
<?php endif; ?>
<div class="agency_navig">
	<div id="switchTab" class="<?php if(!$shopOpend):?>navig_next<?php else:?>navig_two_next<?php endif;?>">
		<ul>
			<li id="li1" class="navig_on" onclick="agency_switch(1)">金牌代理</li><li id="li2" onclick="agency_switch(2)">银牌代理</li><?php if (!$shopOpend):?><li id="li3" onclick="agency_switch(3)">成为商家</li><?php endif;?>
		</ul>
	</div>
</div>

<div class="agency_common" id="list1">
	<img src="/themes/mobiles/img/jpdl.png" style="max-width:100%;">
</div>

<div class="agency_common" id="list2" style="display:none;">
	<img src="/themes/mobiles/img/ypdl.png" style="max-width:100%;">
</div>

<script>
function agency_switch(a){
	if(a == 3){
		window.location.href='<?=U('user/merchant/checkin') ?>';
		return;
	}
	var _li = "#li" + a;
	var _list = "#list" + a;
	var _agency = "#agency" + a ;
	
	$(".agency_common").hide();
	$(".agency_pay").hide();
	$("#switchTab li").removeClass("navig_on");
	
	$(_li).addClass("navig_on");
	$(_list).show();
	$(_agency).show();
}
</script>
<?php if(!$isAgent):?>
<script id="forMnav" type="text/html">
<div class="agency_bottom_next"> 
	<div class="agency_money gold_common agency_pay" id="agency1">
		<span class="left_agency fl_d">金牌代理</span>
		<span class="right_buy_btn fr btn-wxpay" data-val='<?=$gold_agent ?>' data-type='3'>立即支付</span>
		<span class="right_money fr">￥698.00</span>
	</div>
	<div class="agency_money gold_common agency_pay" id="agency2" style="display:none;">
		<span class="left_agency fl_d">银牌代理</span>
		<span class="right_buy_btn fr btn-wxpay" data-val='<?=$silver_agent ?>' data-type='4'>立即支付</span>
		<span class="right_money fr">￥398.00</span>
	</div>
</div>
</script>

<script>
$(function(){
	show_mnav($('#forMnav').html(), -1);
});
</script>
<?php elseif (Users::isSilverAgent($user->level)):?>
<script id="forMnav" type="text/html">
<div class="agency_bottom_next"> 
	<div class="agency_money gold_common" id="agency1">
		<span class="left_agency fl_d">升级为金牌代理</span>
		<span class="right_buy_btn fr upgrade btn-wxpay" data-val='<?=$gold_agent ?>' data-type='3'>立即支付</span>
		<span class="right_money fr">￥698.00</span>
	</div>
</div>
</script>

<script>
$(function(){
	show_mnav($('#forMnav').html(), -1);
});
</script>
<?php endif;?>

<?php form_order_script()?>

<?php include T('inc/add_as_friend');?>
<script>
$(function(){
	<?php if(!$isAgent):?>
	new AddFriend().showAgencyTips();
	<?php endif;?>
	
	$(document).on('click', '.btn-wxpay', function(){
		var _this = $(this);
		if(_this.hasClass("upgrade")){
			<?php if($agent->pid && !$agent->premium_id):?>
			weui_alert('请先领取完398套餐后再升级购买金牌代理。');
			return;
			<?php endif;?>
		}
		if(_this.hasClass("disabled")){
			return;
		}
		var order_sn = $('#frm_order_sn').val();
		var pay_id   = 2;
		var goods_id = _this.data('val');
		var goods_number = 1;

		var order_type = _this.data('type');
		var paybtn = $(".btn-wxpay");
		paybtn.text('支付中, 请稍候...').addClass('disabled',true);
		F.post('<?php echo U('trade/order/submit_item')?>',
				{"order_sn":order_sn,"item_id":goods_id,"item_number":goods_number,"pay_id":pay_id,"order_msg":""},
				function(ret){
          			 if (ret.flag=='SUC') {
           				paybtn.text('支付跳转中...');
        
        				wxpayJsApiCall(ret.js_api_params,ret.order_id,function(flag){
        	  				if(flag=='OK'){
        						var data ={'order_id':ret.order_id,'order_sn':ret.order_sn}
        						window.location.href='/distribution/agent/paid/succ?order_type='+order_type;
         					}else{
								window.location.href='<?=U('trade/order/record') ?>';
         					}
        				});	
  					}else{
  						paybtn.text('立即支付').removeClass('disabled');
  						weui_alert(ret.msg);
          			}
	  			});
		return false;
	});
});
</script>
<?php endif;?>
