<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if(''!==$errmsg):?>

<div class="error"><?=$errmsg?></div>
<?php else:?>
		<style>
			body{
				font-family: "Helvetica Neue",Helvetica,Arial,sans-serif !important;
			}
		</style>
	<?php $leve=['1'=>'一级人脉','2'=>'二级人脉','3'=>'三级人脉'];?>
	<div class="my_income_tit">
		<img src="/themes/mobiles/img/beijing.png">
	</div>
	<div class="my_in_name">
		<?=$user->nickname?>
		<img src="<?=$user->logo?>">
	</div>
	<div class="my_name_info">
		<div>购<i class="width_i"></i>买<i class="width_i"></i>人：<?=$userInfo['nick_name']?><span class="name_info_p">(<?=$ismBusiness?>)</span> </div>
		<div>人脉层级：<?=$leve[$level]?></div>
		<div style="padding-bottom:15px;">订单金额：￥<?=$userInfo['money_paid']?></div>
	</div>
	<div class="get_broke">
		<span>获得佣金：￥<?=$userInfo['commision']?></span>
		<?php if($is_user):?>
		<span class="ck_broke">查看佣金明细</span>
		<?php endif;?>
	</div>

	<div class="my_name_order">
		订单编号：<?=$userInfo['order_sn']?>
	</div>
	<div class="m_order_infos">
		<table cellspacing="0" cellpadding="0" class="m_n_table">
			<?php
				$totalPrice=0;
				$totalNumber=count($goodsInfo);
			foreach ($goodsInfo AS $info):?>

			<tr>
				<td width="70px;"><img src="<?=$info['goods_thumb']?>"></td>
				<td>
					<p class="m_n_tit"><?=$info['goods_name']?></p>
					<p><button class="tit_btn_q"><?=$info['shipping_status']?></button></p>
				</td>
				<td width="70px;">
					<p class="my_n_price">￥<?=$info['goods_price']?></p>
					<?php $totalPrice +=$info['goods_price'] ?>
					<p class="my_n_nums">x<?=$info['goods_number']?></p>
				</td>
			</tr>
			<?php endforeach;?>
		</table>
	</div>
	<div class="income_nums">
		<span>共<?=$totalNumber?>件商品</span><span  class="income_nums_r">合计：￥<?=$totalPrice?> (含运费：￥10)</span>
	</div>
		<?php if($is_user):?>
	<div class="my_income_btnc">
		<button class="btnc_share">分享此页面</button>
		<button class="btnc_plan">米商计划</button>
	</div>
		<?php endif;?>
	<div class="my_income_foot">
		<img src="/themes/mobiles/img/beijing.png">
	</div>
<?php endif;?>
<script>
	$(function(){
		var _width =( $(window).width() - 60) / 2;
		$(".btnc_share,.btnc_plan").width(_width);
	});
	$(".my_income_btnc .btnc_share").click(function(){
			$('#cover-wxtips').show();
	});
</script>

