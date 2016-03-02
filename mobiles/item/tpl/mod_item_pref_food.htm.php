<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if(''!==$errmsg):?>

<div class="error"><?=$errmsg?></div>

<?php else:?>
<?php 
$page_head_pic = '/themes/mobiles/img/food001.png';
$category = 'food';

include T('inc/pref_popup_menu');?>

<div class="dress_title" style="margin-bottom:10px;">
	<ul>
		<li onclick="goods_switch('importfood','food_bg');"><p data-type='food_bg' class="food_bg_r food_bg_b">进口食品</p></li>
		<li onclick="goods_switch('localfood','specialty_bg');"><p data-type='specialty_bg' class="specialty_bg_b">地方特产</p></li>
		<li onclick="goods_switch('snackfood','sanck_bg');"><p data-type='sanck_bg' class="sanck_bg_b">休闲食品</p></li>
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
	getGoodsByCat('importfood');
	
	var _width = $(window).width() / 3;
	var _linetwo =_width * 2 ;
	$(".dress_title li").width(_width);
	$(".lineone").css({"position":"absolute","left":_width});
	$(".linetwo").css({"position":"absolute","left":_linetwo});
});

/* function food_switch(cat, type){
	var cur_cat = $(".click_more").attr('data-cat');
	if(cur_cat == cat){
		return;
	}
	$(".dress_title li").each(function(){
		var nav = $(this).find("p");
		var _curtype = nav.attr("data-type");
		if(_curtype == type){
			if(!nav.hasClass(type+"_r")){
				nav.addClass(type+"_r");
			}
		}else{
			nav.removeClass(_curtype+"_r");
		}
	});
	$(".tea_info_list ul").empty();
	getGoodsByCat(cat);
} */

function getGoodsByCat(cat){
	$(".click_more").attr('data-cat', cat);
	getGoodsList(1, cat, true);
}

function contructGoodsHTML(ret, isinit, category){
	if(!ret || !ret.result || !ret.result.length){
		var emptyTR = "<li style=\"text-align:center;margin:0px;width:100%;line-height:50px;\">还没有商品！</li>";
		handleAppend(emptyTR);
		return;
	}
	var LI = buildGoodsListLI(ret);
	handleAppend(LI, category);
	/* setTimeout(function(){
		var _width =( $(window).width() - 30 ) / 2;
		$(".tea_info_list img,.tea_info_list li").width(_width);
	}, 0); */
}
function handleAppend(LI, cat){
	var _list = $("#goodslist");
	_list.find(".tea_info_list ul").append(LI);
	F.set_scroller(false, 100);
}
</script>

<?php endif;?>