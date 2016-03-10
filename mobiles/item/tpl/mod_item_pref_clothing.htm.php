<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if(''!==$errmsg):?>

<div class="error"><?=$errmsg?></div>

<?php else:?>
<?php 
$page_head_pic = '/themes/mobiles/img/dress.png';
$category = 'clothing';

include T('inc/pref_popup_menu');?>
<!-- 
<div class="dress_title" style="margin-bottom:10px;">
	<ul>
		<li onclick="goods_switch('clothing','dress_bg');"><p data-cat='clothing' data-type='dress_bg' class="dress_bg_r dress_bg_b">服装</p></li>
		<li onclick="goods_switch('shoe','shoes_bg');"><p data-cat='shoe' data-type='shoes_bg' class="shoes_bg_b">鞋子</p></li>
		<li onclick="goods_switch('bag','cases_bg');"><p data-cat='bag' data-type='cases_bg' class="cases_bg_b">箱包</p></li>
		<span class="lineone"></span>
		<span class="linetwo"></span>
		<div class="clear"></div>
	</ul>
</div>
 -->
<div class="teawinecomment" id="goodslist">
	<div class="tea_info_list" style="margin-top:5px;">
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
	/* var category = Cookies.get('clothing_category');
	if(category != 'clothing' && referIsFromItem()){
		var p = $(".dress_title").find("p[data-cat='"+category+"']");
		goods_switch(category, p.attr("data-type"));
	}else{
    	getGoodsByCat('clothing');
	} */
	getGoodsByCat('clothing');
	
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