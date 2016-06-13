<div class="bg_prefe">
<img src="<?=$page_head_pic ?>">
</div>
<script id="forMtop" type="text/html">
<div class="bg_prefe">
<span class="pre_h"><?php echo icon_html('home');?></span>
<span class="pre_s">
<?php if(Users::is_logined()):?>
<?php echo icon_html('cart');?>
<?php else:?>
<?php echo icon_html('nologin');?>
<?php endif;?>
</span>
<span class="pre_m" data-show="0"><img src="/themes/mobiles/img/more.png" style="width:30px;"></span>
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
<?php if(isset($category) && 'moml' != $category): ?>
<li><a href="/item/pref/show?type=moml">清真专区</a></li>
<?php endif; ?>
<?php if(isset($category) && 'best' != $category): ?>
<li><a href="/item/pref/show?type=best">精选专区</a></li>
<?php endif; ?>
</ul>
</div>
</script>
<?php require_scroll2old();?>
<script>
show_mtop($('#forMtop').html());

var cur_category = "<?=$category ?>";

$(function(){
	//菜单开启
	$(".pre_m").on("click",function(){
		if ($(this).attr('data-show')=='0') {
			$(".p_detail_menulist").show();
			$(this).attr('data-show','1');
		}
		else {
			$(".p_detail_menulist").hide();
			$(this).attr('data-show','0');
		}
	});
	//菜单关闭
	$(document.body).on("click",function(e){
		var firecls = 'pre_m';
		var canfire = true;
		if ($(e.target).hasClass(firecls)) {
			canfire = false;
		}
		else {
			var $pn = $(e.target).parent();
			while($pn.size()>0 && $pn.get(0).tagName!='body') {
				if ($pn.hasClass(firecls)) {
					canfire = false;
					break;
				}
				$pn = $pn.parent();
			}
		}
		if (canfire) {
			$(".p_detail_menulist").hide();
			$("."+firecls).attr('data-show','0');
		}
	});
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
	//F.set_scroller(false, 100);
	//scrollToHistoryPosition();
}

function pulldata(obj){
	getGoodsList($(obj).attr("curpage"), $(obj).attr("data-cat"), false);
}

function gotoItem(goodId){
	window.location = '/item/'+goodId+'?back=pref&category='+cur_category;
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
	Cookies.set(cur_category+'_category',cat,{path:'/'});
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

function scrollToHistoryPosition(){
	if(!referIsFromItem()){
		return;
	}
	var historyPos = Cookies.get(F.scroll_cookie_key());
	F.set_scroller(!F.scroll2old?false:historyPos,100);
}

//来自商品页面的返回需要读取cookie
function referIsFromItem(){
	var refer=document.referrer;
	var reg = /item\/\d+/;
	if(!reg.test(refer)){
		return false;
	}
	return true;
}
</script>