<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if(''!==$errmsg):?>

<div class="error"><?=$errmsg?></div>

<?php else:?>
<?php 
$page_head_pic = '/themes/mobiles/img/tea_wine.png';
$category = 'teawine';

include T('inc/pref_popup_menu');?>

<div class="tea_and_wine">
	<ul>
		<li class="tw_on" id="tea" onclick="tea_wine_switch('tea');">茶中臻品</li>
		<li id="wine" onclick="tea_wine_switch('wine');">酒香四溢</li>
		<div class="clear"></div>
	</ul>
</div>
<div class="teawinecomment" id="teawinelist">
<!-- 
	<div class="tea_pro_list" data-cat='tea'>
	 <a href="<?php echo U('zt/newtea')?>"><img src="http://fdn.oss-cn-hangzhou.aliyuncs.com/images/2016newtea.png"/></a>
	</div>
	<div class="tea_pro_list" data-cat='wine' style="display: none;">
		<a href="javascript:gotoItem(1047);"><img src="/themes/mobiles/img/wine01.png"></a>
		<a href="javascript:gotoItem(1032);"><img src="/themes/mobiles/img/wine02.png"></a>
	</div>
-->
	<div class="tea_info_list" style="margin-top: 5px;">
		<ul>
		</ul>
	</div>
</div>

<!-- 
<div class="teawinecomment" id="winelist" style="display:none;">
	<div class="tea_pro_list">
		<a href="javescript:;"><img src="/themes/mobiles/img/wine01.png"></a>
		<a href="javescript:;"><img src="/themes/mobiles/img/wine02.png"></a>
		<a href="javescript:;"><img src="/themes/mobiles/img/wine03.png"></a>
		<span class="wine_prices1">￥268.00</span>
		<span class="wine_prices2">￥238.00</span>
		<span class="wine_prices3">￥248.00</span>
	</div>
	<div class="tea_info_list">
		<ul>
		</ul>
	</div>
</div>
 -->
<div class="clear"></div>

<div class="click_more" onclick="pulldata(this);">
	点击加载更多......
</div>

<script>
$(document).ready(function(){
	var category = Cookies.get('teawine_category');
	if(category != 'tea' && referIsFromItem()){
		tea_wine_switch(category);
		return;
	}else{
		getGoodsByCat('tea');
	}
});

function tea_wine_switch(cat){
	var cur_cat = $(".click_more").attr('data-cat');
	if(cur_cat == cat){
		return;
	}
	var tab = $("#"+cat);
	$(".tea_and_wine li").removeClass("tw_on");
	tab.addClass("tw_on");
	$(".tea_pro_list").hide();
	$(".tea_pro_list[data-cat='"+cat+"']").show();
	$(".tea_info_list ul").empty();
	Cookies.set('teawine_category',cat,{path:'/'});
	getGoodsByCat(cat);
	/* var _list = $("#"+cat+"list");
	var tab = $("#"+cat);
	$(".tea_and_wine li").removeClass("tw_on");
	tab.addClass("tw_on");
	$(".teawinecomment").hide('slow');
	_list.show('slow');
	//click more 切换显示
	$(".click_more").hide();
	$(".click_more").each(function(){
		var more = $(this);
		if(more.attr("data-cat") == cat && more.attr("data-hasnext")){
			more.show();
		}
	});
	if(_list.attr("data-load") != 1){
    	getGoodsByCat(cat);
	}
	F.set_scroller(false, 500); */
}

function getGoodsByCat(cat){
	$(".click_more").attr('data-cat', cat);
	//var _list = $("#"+cat+"list");
	//_list.attr("data-load", 1);
	getGoodsList(1, cat, true);
}

function contructGoodsHTML(ret, isinit, category){
	if(!ret || !ret.result || !ret.result.length){
		//var emptyTR = "<li style=\"text-align:center;margin-top:20px;\">还没有商品！</li>";
		//handleAppend(emptyTR);
		return;
	}
	var LI = buildGoodsListLI(ret);
	handleGoodsListAppend($(".tea_info_list ul"), LI, isinit);
}
</script>

<?php endif;?>