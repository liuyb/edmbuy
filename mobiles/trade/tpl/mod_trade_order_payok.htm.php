<?php defined('IN_SIMPHP') or die('Access Denied');?>

<div class="pay_logo">
	<img src="/themes/mobiles/img/icon.png">
</div>

<div class="pay_price">
	<p class="pay_tit">订单支付成功</p>
	<p class="pay_p">订单金额：<span class="price_sucee">￥<?=$order_amount?></span></p>
<div>
<?php if($user_level>0):?>
<div class="becmoe_rice">恭喜您，已成为米商!</div>
<?php endif;?>
<div class="pay_okgo">
	<button class="pay_check_order_but" onclick="location.href='<?php echo U('order')?>'">查看订单</button>
	<button class="pay_m_plan_but" onclick="location.href='<?php echo U('riceplan')?>'">米商计划</button>
</div>

<?php if($user->mobilephone==''):?>
<div class="pay_tier">
	<div class="tier_c">
		<p class="c_tit">绑定手机号，追踪物流</p>
		<p class="c_tit1">如果不绑定，会追踪不到物流</p>
		<p class="c_input"><input class="input_val" type="text" maxlength="15" placeholder="请输入你的手机号" value="" ></p>
		<div class="tier_tit"><img src="/themes/mobiles/img/zwl.png"></div>
	</div>
	<div class="tier_but"><a href="javascript:;" class="btna" style="color: #fff">确认手机号</a></div>
</div>
<script>
	$(function(){
		$(".pay_tier").show();
	});
	
	$(".tier_but").bind("click",function(){
		var text = $(".input_val").val().trim();
		if(text == ''){
			boxalert("请输入电话号码");
		}else{
			F.post("<?php echo U('user/chmobile')?>",{mobile: text},function(ret){
				if (ret.flag=='SUCC') {
					$(".pay_tier").hide();
				}
				else {
					boxalert(ret.msg);
				}
			});
		}
		
	});
</script>
<?php endif;?>