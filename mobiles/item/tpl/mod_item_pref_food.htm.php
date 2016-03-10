<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if(''!==$errmsg):?>

<div class="error"><?=$errmsg?></div>

<?php else:?>
<?php 
$page_head_pic = '/themes/mobiles/img/food001.png';
$category = 'food';

include T('inc/pref_popup_menu');?>

<div class="dress_title" style="margin-bottom:5px;">
	<ul>
		<li onclick="goods_switch('importfood','food_bg');"><p data-cat='importfood' data-type='food_bg' class="food_bg_r food_bg_b">进口食品</p></li>
		<li onclick="goods_switch('localfood','specialty_bg');"><p data-cat='localfood' data-type='specialty_bg' class="specialty_bg_b">地方特产</p></li>
		<li onclick="goods_switch('snackfood','sanck_bg');"><p data-cat='snackfood' data-type='sanck_bg' class="sanck_bg_b">休闲食品</p></li>
		<span class="lineone"></span>
		<span class="linetwo"></span>
		<div class="clear"></div>
	</ul>
</div>

<div class="teawinecomment" id="goodslist">
	<div class="tea_info_list">
		<ul>
		</ul>
	</div>
</div>

<div class="clear"></div>

<div class="click_more" onclick="pulldata(this);">
	点击加载更多......
</div>

<script>
$(document).ready(function(){
	var category = Cookies.get('food_category');
	if(category != 'importfood' && referIsFromItem()){
		var p = $(".dress_title").find("p[data-cat='"+category+"']");
		goods_switch(category, p.attr("data-type"));
	}else{
    	getGoodsByCat('importfood');
	}
	
	var _width = $(window).width() / 3;
	var _linetwo =_width * 2 ;
	$(".dress_title li").width(_width);
	$(".lineone").css({"position":"absolute","left":_width});
	$(".linetwo").css({"position":"absolute","left":_linetwo});
});

function getGoodsByCat(cat){
	$(".click_more").attr('data-cat', cat);
	getGoodsList(1, cat, true);
}

function contructGoodsHTML(ret, isinit, category){
	if(!ret || !ret.result || !ret.result.length){
		var emptyTR = "<li style=\"text-align:center;margin:0px;width:100%;line-height:35px;background:#fff;\">还没有商品！<br/>招商座机：0755-26418979</li>";
		handleGoodsListAppend($(".tea_info_list ul"), emptyTR, isinit);
		return;
	}
	var LI = buildGoodsListLI(ret);
	handleGoodsListAppend($(".tea_info_list ul"), LI, isinit);
}
</script>

<?php endif;?>