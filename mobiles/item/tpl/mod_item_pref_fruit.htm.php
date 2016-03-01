<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if(''!==$errmsg):?>

<div class="error"><?=$errmsg?></div>

<?php else:?>
<?php 
$page_head_pic = '/themes/mobiles/img/fruit3.png';
$category = 'fruit';

include T('inc/pref_popup_menu');?>

<div class="teawinecomment">
	<div class="tea_pro_list">
		<!-- <a href="javescript:;"><img src="/themes/mobiles/img/fruit1.png"></a> -->
		<a href="javascript:gotoItem(1045);"><img src="/themes/mobiles/img/fruit2.png"></a>
		<!-- <span class="fruit_prices1">￥28.00</span> -->
		<span class="fruit_prices2">￥88.00</span>
	</div>
	
	<div class="tea_info_list" style="margin-top:10px;">
		<ul id="goods_list">
		</ul>
		<div class="clear"></div>
	</div>
</div>

<div class="click_more" data-cat='fruit' onclick="pulldata(this);">
	点击加载更多......
</div>
<script>
$(document).ready(function(){
	getGoodsList(1, 'fruit', true);
});

function contructGoodsHTML(ret, isinit){
	if(!ret || !ret.result || !ret.result.length){
		//var emptyTR = "<li style=\"text-align:center;width: 100%;margin: 0px;line-height: 50px;\">还没有商品！</li>";
		//handleGoodsListAppend(emptyTR);
		return;
	}
	var LI = "";
	var result = ret.result;
	for(var i = 0,len=result.length; i < len; i++){
		var good = result[i];
		var spread = good.market_price - good.shop_price;
		spread = spread ? spread.toFixed(2) :0.00;
		var goodimg = good.goods_img;
		LI += "<a href='javascript:gotoItem(\""+good.goods_id+"\");'>";
		LI += "<li>";
		LI += "<img src=\"<?php echo ploadingimg()?>\" data-loaded=\"0\" onload=\"imgLazyLoad(this,'"+goodimg+"')\">";
		LI += "<p class=\"tea_info_title\">"+good.goods_name+"</p>";
		LI += "<p class=\"tea_info_price\"><span>￥"+good.shop_price+"</span><b>￥"+good.market_price+"</b></p>";
		LI += "</li></a>";
	}
	handleGoodsListAppend(LI);
	setTimeout(function(){
		var _width =( $(window).width() - 30 ) / 2;
		$(".tea_info_list img,.tea_info_list li").width(_width);
	}, 0);
}
</script>

<?php endif;?>