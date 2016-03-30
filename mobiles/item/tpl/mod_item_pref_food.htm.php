<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if(''!==$errmsg):?>

<div class="error"><?=$errmsg?></div>

<?php else:?>
<?php 
$page_head_pic = '/themes/mobiles/img/food001.png';
$category = 'food';

include T('inc/pref_popup_menu');?>
<!--[HEAD_CSS]-->
<style>
.dress_title {margin-bottom:5px;}
.dress_title li {border-right: 1px solid #eee;}
.dress_title li.last {border-right: none;}
</style>
<!--[/HEAD_CSS]-->
<div class="dress_title">
	<ul class="clearfix">
		<li onclick="goods_switch('snackfood','sanck_bg');" class="bbsizing c-3-1"><p data-cat='snackfood' data-type='sanck_bg' class="sanck_bg_b sanck_bg_r">休闲食品</p></li>
		<li onclick="goods_switch('localfood','specialty_bg');" class="bbsizing c-3-1"><p data-cat='localfood' data-type='specialty_bg' class="specialty_bg_b">地方特产</p></li>
		<li onclick="goods_switch('importfood','food_bg');" class="bbsizing c-3-1 last"><p data-cat='importfood' data-type='food_bg' class="food_bg_b">进口食品</p></li>
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
	if(category != 'snackfood' && referIsFromItem()){
		var p = $(".dress_title").find("p[data-cat='"+category+"']");
		goods_switch(category, p.attr("data-type"));
	}else{
    	getGoodsByCat('snackfood');
	}
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