<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if(''!==$errmsg):?>

<div class="error"><?=$errmsg?></div>

<?php else:?>


<div class="header">
	米商专属特权
	<a href="javascript:history.back();" class="back"></a>
</div>

<div class="rice_bg">
	<img src="/themes/mobiles/img/rice_01.png">
</div>
	
<div class="rice_info">
	<p><img src="/themes/mobiles/img/rice_05.png" style="margin-top:15px;"></p>
	<p><img src="/themes/mobiles/img/rice_03.png"></p>
	<p><img src="/themes/mobiles/img/rice_06_2.png"></p>
	<p><img src="/themes/mobiles/img/rice_04.png"></p>
</div>
<div class="list_tit_img"><img src="/themes/mobiles/img/rice_07.png"></div>

<div class="rice_list_tjproduct">
	<div class="list_product_info index_product_info">
		<ul>
		<?php foreach ($result as $good): 
		      $mp = $good['market_price'] ? ('￥'.$good['market_price']) : "";
		  ?>
			<li>
				<a href="/item/<?=$good['goods_id'] ?>" class="product_info_pc">
					<img src="<?=$good['goods_img'] ?>" style="float:right">
					<p class="list_table_tit"><?=$good['goods_name'] ?></p>
					<p class="list_table_price"><span>￥<?=$good['shop_price'] ?></span><b><?=$mp ?></b></p>
				</a>
			</li>
		<?php endforeach;?>	
		</ul>
	</div><div class="clear"></div>
</div>

<?php include T('inc/followbox');?>
<?php endif;?>
