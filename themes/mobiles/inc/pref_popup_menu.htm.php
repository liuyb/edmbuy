<div class="bg_prefe">
<img src="<?=$page_head_pic ?>">
</div>
<script id="forMtop" type="text/html">
<div class="bg_prefe">
<span class="pre_h"><a href='/'><img src="/themes/mobiles/img/home.png" style="width:30px;"></a></span>
<span class="pre_s"><a href='/trade/cart/list'><img src="/themes/mobiles/img/shopping00.png" style="width:30px;"></a></span>
<span class="pre_m"><img src="/themes/mobiles/img/more.png" style="width:30px;"></span>
<?php if($cartnum): ?>
<span class="ydm_com"><?=$cartnum ?></span>
<?php endif;?>
</div>
<div class="p_detail_menulist cursor" style="display:none;">
<span class="p_detail_jian"></span>
<ul class="p_detail_menu pre_li">
<?php if(isset($category) && 'teawine' != $category): ?>
<li><a href="/item/pref/show?type=teawine">茶/酒</a></li>
<?php endif; ?>
<?php if(isset($category) && 'fruit' != $category): ?>
<li><a href="/item/pref/show?type=fruit">水果</a></li>
<?php endif; ?>
<?php if(isset($category) && 'food' != $category): ?>
<li><a href="/item/pref/show?type=food">食品</a></li>
<?php endif; ?>
<?php if(isset($category) && 'clothing' != $category): ?>
<li><a href="/item/pref/show?type=clothing">服装</a></li>
<?php endif; ?>
<?php if(isset($category) && 'rice' != $category): ?>
<li><a href="/item/pref/show?type=rice">精选好米</a></li>
<?php endif; ?>
<?php if(isset($category) && '98' != $category): ?>
<li><a href="/item/pref/show?type=98">98专区</a></li>
<?php endif; ?>
</ul>
</div>
</script>
<div class="mask_menu cursor"></div>

<script>
show_mtop($('#forMtop').html());

//菜单开启
$(".pre_m").on("click",function(){
	$(".p_detail_menulist,.mask_menu").show();
})

//菜单关闭
$(".mask_menu").on("click", function(){
	$(".p_detail_menulist,.mask_menu").hide();
});

function getGoodsList(curpage, category, isinit){
	F.get('/item/pref/goods', {curpage : curpage, category : category}, function(ret){
		contructGoodsHTML(ret, isinit, category);
		handleWhenHasNextPage(ret, category);
	});
}

//当还有下一页时处理下拉
function handleWhenHasNextPage(data, category){
	var hasnex = data.hasnexpage;
	var more = $(".click_more[data-cat='"+category+"']");
	more.attr("data-hasnext", hasnex);
	if(hasnex){
		more.show();
		more.attr('curpage',data.curpage);
	}else{
		more.hide();
	}
}

function handleGoodsListAppend(obj, LI, isinit){
	if(isinit){
		obj.html(LI);
	}else{
    	obj.append(LI);//加载更多用append
	}
	F.set_scroller(false, 100);
}

function pulldata(obj){
	getGoodsList($(obj).attr("curpage"), $(obj).attr("data-cat"), false);
}

function gotoItem(goodId){
	window.location = '/item/'+goodId;
}

//有些专区可以共用的分类切换
function goods_switch(cat, type){
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
	//$(".tea_info_list ul").empty();
	
	$(".click_more").attr('data-cat', cat);
	getGoodsList(1, cat, true);
}

function buildGoodsListLI(ret){
	var LI = "";
	var result = ret.result;
	for(var i = 0,len=result.length; i < len; i++){
		var good = result[i];
		var spread = good.market_price - good.shop_price;
		spread = spread ? spread.toFixed(2) :0.00;
		var goodimg = good.goods_img;
		LI += "<li>";
		LI += "<a href='javascript:gotoItem(\""+good.goods_id+"\");' class='blka'>";
		LI += "<img src=\"<?php echo ploadingimg()?>\" data-loaded=\"0\" onload=\"imgLazyLoad(this,'"+goodimg+"')\">";
		LI += "<p class=\"tea_info_title\">"+good.goods_name+"</p>";
		LI += "<p class=\"tea_info_price\"><span>￥"+good.shop_price+"</span><b>￥"+good.market_price+"</b></p>";
		LI += "</a></li>";
	}
	return LI;	
}
</script>