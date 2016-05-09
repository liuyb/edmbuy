<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if(''!==$errmsg):?>

<div class="error"><?=$errmsg?></div>

<?php else:?>

<script id="forTopnav" type="text/html">
<div class="header">
	领取套餐
    <a href="/trade/order/agent" class="back"></a>
</div>
</script>
<script>
show_topnav($('#forTopnav').html());
</script>

<div class="meal_j">
	<p>您已经成为益多米<?=AgentPayment::getAgentNameByLevel($user->level) ?><br>您可以领取下面任意一份<?=AgentPayment::getAgentPaidMoney($user->level) ?>元套餐</p>
</div>

<?php foreach ($packages as $pack):?>
<div class="meal_infos">
	<div class="infos_comms">
		<img src="<?=$pack['logo'] ?>" style='height:150px;'>
		<p class="meal_names"><?=$pack['name'] ?></p>
		<p class="meal_get_now">
			<button class="get_now_btn" onclick="confirmPackage(<?=$pack['pid'] ?>);">立即领取</button>
			<span class="see_meal_infos arrow_down_bg">查看套餐详情</span>
		</p>
		<?php if($pack['goodslist']):
		$count = count($pack['goodslist']);
		$pos = 0;
		?>
		<div class="meal_infos_lsit">
    		<table cellspacing="0" cellpadding="0" class="meal_list_tab">
    			<tr>
    				<?php foreach ($pack['goodslist'] as $good):
    				$pos ++;
    				?>
    				<td class="big_img">
    					<img src="<?=$good['goods_img'] ?>" onclick="gotoItem(<?=$good['goods_id'] ?>)">
    					<p class="tea_info_title" style="text-align:left;"><?=$good['goods_name'] ?>  </p>
    					<!-- <p class="tea_info_price" style="text-align:left;"><span></span></p>  -->
    				</td>
    				
    				<?php endforeach;?>
    			</tr>
    		</table>
        </div>
        <?php endif;?>
	</div>
</div>
<?php endforeach;?>

<script>
$(function(){
	$(".meal_infos").on('click', '.see_meal_infos', function(){
		var $this = $(this);
		var $goods = $this.closest('.meal_get_now').next("div.meal_infos_lsit");
		if($goods.length){
			if($this.hasClass("arrow_down_bg")){
				$goods.show();
				$this.removeClass("arrow_down_bg").addClass("arrow_up_bg");
			}else{
				$goods.hide();
				$this.removeClass("arrow_up_bg").addClass("arrow_down_bg");
			}
		}
	});
});

function gotoItem(itemId){
	window.location.href='/item/'+itemId;
}

function confirmPackage(pid){
	var cf = window.confirm('您是否确定领取此套餐？套餐领取后无法更改！');
	if(cf){
    	window.location.href='/trade/order/package/confirm?pid='+pid;
	}
}
</script>

<?php endif;?>