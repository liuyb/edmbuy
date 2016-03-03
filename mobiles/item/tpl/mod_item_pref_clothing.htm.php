<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if(''!==$errmsg):?>

<div class="error"><?=$errmsg?></div>

<?php else:?>
<?php 
$page_head_pic = '/themes/mobiles/img/dress.png';
$category = 'clothing';

include T('inc/pref_popup_menu');?>

<div class="dress_title" style="margin-bottom:10px;">
	<ul>
		<li onclick="goods_switch('clothing','dress_bg');"><p data-type='dress_bg' class="dress_bg_r dress_bg_b">服装</p></li>
		<li onclick="goods_switch('shoe','shoes_bg');"><p data-type='shoes_bg' class="shoes_bg_b">鞋子</p></li>
		<li onclick="goods_switch('bag','cases_bg');"><p data-type='cases_bg' class="cases_bg_b">箱包</p></li>
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
		var emptyTR = "<li style=\"text-align:center;margin:0px;width:100%;line-height:35px;\">还没有商品！<br/>招商座机：0755-26418979</li>";
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