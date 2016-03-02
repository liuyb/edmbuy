<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if(''!==$errmsg):?>

<div class="error"><?=$errmsg?></div>

<?php else:?>
<?php 
$page_head_pic = '/themes/mobiles/img/98_02.png';
$category = '98';

include T('inc/pref_popup_menu');?>

<div class="pro_list_info">
	<ul id="goods_list">
	</ul>
</div>

<div class="click_more" data-cat='98' onclick="pulldata(this);">
	点击加载更多......
</div>
<script>
$(document).ready(function(){
	getGoodsList(1, '98', true);
	//监听点击继续购买
	$("#goods_list").on('click','li', function(){
		var goodid = $(this).attr("data-goodid");
		gotoItem(goodid);
	});
	$("#activePage > .scrollArea").css('background','#da284c');
});

function contructGoodsHTML(ret, isinit){
	if(!ret || !ret.result || !ret.result.length){
		var emptyTR = "<li style=\"text-align:center;width: 100%;margin: 0px;line-height: 50px;\">还没有商品！</li>";
		handleGoodsListAppend(emptyTR);
		return;
	}
	var LI = "";
	var result = ret.result;
	for(var i = 0,len=result.length; i < len; i++){
		var good = result[i];
		var spread = good.market_price - good.shop_price;
		spread = spread ? spread.toFixed(2) :0.00;
		var goodimg = good.goods_img;
		LI += "<li data-goodid='"+good.goods_id+"'><div class=\"pro_info_l\"><img class='hwspeed' src=\"<?php echo ploadingimg()?>\" data-loaded=\"0\" onload=\"imgLazyLoad(this,'"+goodimg+"')\"></div>";
		LI += "<div class=\"pro_info_r\">";
		LI += "<p class=\"pro_info_t\">"+good.goods_name+"</p>";
		LI += "<p class=\"pro_info_i\">"+good.goods_brief+"</p>";
		LI += "<p class=\"pro_info_p\">￥"+good.shop_price+"</p>";
		LI += "<p class=\"pro_info_s\">比网上省"+spread+"元</p>";
		LI += "<p><button class=\"pro_info_btn\">立即购买</button></p>";
		LI += "</div><div class=\"clear\"></div></li>";
	}
	handleGoodsListAppend(LI);
	setTimeout(function(){
		var _width = $(".pro_list_info li").width() - 134;
		$(".pro_info_r").width(_width);
	}, 0);
}
</script>

<?php endif;?>