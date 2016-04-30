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
	米商代理
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
				<img src="<?=AgentPayment::getAgentIconByLevel($user->level) ?>" class="jin_z">
				</p>
				<p class="become_name_id">多米号：<?=$user->uid ?></p>
			</td>
			<?php if(!$agent->premium_id):?>
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
	<div class="navig_next">
		<ul>
			<li id="li1" class="navig_on" onclick="agency_switch(1)">金牌代理</li><li id="li2" onclick="agency_switch(2)">银牌代理</li>
		</ul>
	</div>
	<input type='button' value='测试-支付成功后点击' onclick="testBuy();" />
	<input type='button' value='测试-清除购买记录' onclick="clearAgent();" />
</div>

<div class="agency_common" id="list1">
	<div class="gold_medal_agency">
		<table cellspacing="0" cellpadding="0" class="agency_tab">	
			<tr>
				<td><img src="/themes/mobiles/img/1.png"></td>
				<td>免费领取<i class="agency_font">698元</i>商品套餐</td>
			</tr>
			<tr>
				<td><img src="/themes/mobiles/img/2.png"></td>
				<td>本人购物享有锁所购买商品佣金<i class="agency_font">30%</i>的返利， 分销商品即享下两级购物佣金：一层40%，二 层30%。</td>
			</tr>
			<tr>
				<td><img src="/themes/mobiles/img/3.png"></td>
				<td>发展金牌代理商，总佣金为<i class="agency_font">198元</i>。198元的三 层分佣比例：一层45%，二层30%，三层25%。</td>
			</tr>
			<tr>
				<td><img src="/themes/mobiles/img/4_0.png"></td>
				<td>推荐商家入驻分佣比例：一层<i class="agency_font">30%</i>，二层<i class="agency_font">10%</i>， 三层<i class="agency_font">5%</i>。</td>
			</tr>
			<tr>
				<td style="border-bottom:0;"><img src="/themes/mobiles/img/5.png"></td>
				<td style="border-bottom:0;">推荐商家销售产生总佣金<i class="agency_font">2%</i>的终身提点。</td>
			</tr>
		</table>
	</div>
</div>

<div class="agency_common" id="list2" style="display:none;">
	<div class="gold_medal_agency">
		<table cellspacing="0" cellpadding="0" class="agency_tab">	
			<tr>
				<td><img src="/themes/mobiles/img/1.png"></td>
				<td>免费领取<i class="agency_font">398元</i>商品套餐</td>
			</tr>
			<tr>
				<td><img src="/themes/mobiles/img/2.png"></td>
				<td>本人购物享有锁所购买商品佣金<i class="agency_font">30%</i>的返利， 分销商品即享下两级购物佣金：一层40%，二 层30%。</td>
			</tr>
			<tr>
				<td><img src="/themes/mobiles/img/3.png"></td>
				<td>发展金牌代理商，总佣金为<i class="agency_font">98元</i>。98元的三 层分佣比例：一层45%，二层30%，三层25%。</td>
			</tr>
			<tr>
				<td><img src="/themes/mobiles/img/4_0.png"></td>
				<td>推荐商家入驻分佣比例：一层<i class="agency_font">20%</i>，二层<i class="agency_font">5%</i>， 三层<i class="agency_font">5%</i>。</td>
			</tr>
			<tr>
				<td style="border-bottom:0;"><img src="/themes/mobiles/img/5.png"></td>
				<td style="border-bottom:0;">推荐商家销售产生总佣金<i class="agency_font">1%</i>的终身提点。</td>
			</tr>
		</table>
	</div>
</div>

<script>
function agency_switch(a){
	var _li = "#li" + a;
	var _list = "#list" + a;
	var _agency = "#agency" + a ;
	
	$(".agency_common").hide();
	$(".gold_common").hide();
	$(".navig_next li").removeClass("navig_on");
	
	$(_li).addClass("navig_on");
	$(_list).show();
	$(_agency).show();
}
</script>
<script>
function testBuy(){
	F.get('/distribution/test/buy', null, function(ret){
		if(ret.flag == 'SUC'){
			window.location.href='/distribution/agent/paid/succ?order_type='+ret.type;
		}else{
			alert(ret.msg);
		}
	});
}
function clearAgent(){
	F.get('/distribution/test/clear', null, function(ret){
		window.location.reload();
	});
}
</script>
<?php if(!$isAgent):?>
<script id="forMnav" type="text/html">
<div class="agency_bottom_next"> 
	<div class="agency_money gold_common" id="agency1">
		<span class="left_agency fl">金牌代理</span>
		<span class="right_buy_btn fr btn-wxpay" data-val='<?=$gold_agent ?>' data-type='3'>立即支付</span>
		<span class="right_money fr">￥698.00</span>
	</div>
	<div class="silver_money gold_common" id="agency2" style="display:none;">
		<span class="left_agency fl">银牌代理<br><i style="font-size:10px;color: #8c8c8c;">不支持补差价升级为金牌代理</i></span>
		<span class="right_buy_btn fr btn-wxpay" data-val='<?=$silver_agent ?>' data-type='4'>立即支付</span>
		<span class="right_money_silver">￥398.00</span>
	</div>
</div>
</script>

<?php form_order_script()?>

<script>
$(function(){
	 show_mnav($('#forMnav').html(), -1);
	
	$(document).on('click', '.btn-wxpay', function(){
		var _this = $(this);
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
         					}
        				});	
  					}else{
  						paybtn.text('立即支付').removeClass('disabled');
          				myAlert(ret.msg);
          			}
	  			});
		return false;
	});
});
</script>
<?php endif;?>
<?php endif;?>
