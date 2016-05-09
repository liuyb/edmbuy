<?php defined('IN_SIMPHP') or die('Access Denied');?>

<script id="forTopnav" type="text/html">
<div class="header">
	商品列表
	<a href="javascript:history.back();" class="back"></a>
</div>
</script>
<script>
show_topnav($('#forTopnav').html());
</script>

<div class="all_goods_common" style="padding: 0" id="list1">
	<div class="newest_goods" style="padding: 0">
		<ul id="resultList">
		</ul>
		<div class="clear"></div>
	</div>
</div>

<div id="showMore" style="text-align:center;height:40px;line-height:40px;display:none;"><span class="allicen_more">下拉加载更多</span></div>
<script>
var merchant_id = "<?=$merchant_id ?>";
var shop_recommend = "<?=$shop_recommend ?>";
var shop_cat_id = "<?=$shop_cat_id ?>";
var $mbody;
var $resultList;
var $showMore;
$(function(){
	$showMore = $("#showMore");
	$resultList = $("#resultList");
	$mbody = $("#Mbody");
	$mbody.css("background", "#f1f1f1");
	
	getGoodsList(1, true);

	$resultList.on('click', 'button', function(){
		var $this = $(this);
		var id = $this.data("id");
		window.location.href='/item/'+id;
	});

	$mbody.on('pullMore', function(){
		var curpage = $showMore.attr('data-curpage');
		getGoodsList((curpage ? curpage : 1), false);
	});
	$mbody.pullMoreDataEvent($showMore);
});
//获取商家列表
function getGoodsList(curpage, isInit){
	F.get('/shop/goods/list', {curpage:curpage, merchant_id : merchant_id, shop_recommend : shop_recommend,shop_cat_id:shop_cat_id}, function(ret){
		constructRows(ret, isInit);
		F.handleWhenHasNextPage($showMore, ret);
	});
}
//构建HTML输出
function constructRows(ret, isInit){
	var HTML = "";
	if(!ret || !ret.result || !ret.result.length){
		HTML = F.displayNoData();
	}else{
		var result = ret.result;
		for(var i = 0,len=result.length; i < len; i++){
			var obj = result[i];
			HTML += "<li class='goods_li' data-id='"+obj.goods_id+"'>";
			HTML +="<div class='next_goods'>";
			HTML +="<img src=\"<?php echo ploadingimg()?>\" data-loaded=\"0\" onload=\"imgLazyLoad(this,'"+obj.goods_img+"')\"  style='float:right'>";
			HTML +="<p class='all_goods_tit'>"+obj.goods_name+"</p>";
			HTML +="<p class='all_goods_price'><span>"
				 +"￥"+obj['shop_price']+"</span><b>"
				 +"￥"+obj['market_price']+"</b></p>";
			HTML +="</div></li>"
		}
	}
	F.afterConstructRow(isInit, $resultList, HTML, $showMore);
}

</script>
