<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if(''!==$errmsg):?>

<div class="error"><?=$errmsg?></div>

<?php else:?>
<script id="forTopnav" type="text/html">
<div class="header">
	我收藏的宝贝
<a href="javascript:history.back();" class="back"></a>
</div>
</script>
<script>show_topnav($('#forTopnav').html())</script>

<div class="newest_goods" style="margin-top:2%;">
	<ul id="resultList"></ul>
	<div class="clear"></div>
</div>
<div id="showMore" style="text-align:center;height:40px;line-height:40px;display:none;"><span class="allicen_more">下拉加载更多</span></div>
<script>
var $mbody;
var $resultList;
var $showMore;
$(function(){
	$showMore = $("#showMore");
	$resultList = $("#resultList");
	$mbody = $("#Mbody");
	
	getGoodsList(1, true);

	$resultList.on('click', 'img', function(){
		var $this = $(this);
		var gid = $this.data("gid");
		window.location.href='/item/'+gid;
	});

	$mbody.on('pullMore', function(){
		var curpage = $showMore.attr('data-curpage');
		getGoodsList((curpage ? curpage : 1), false);
	});
	$mbody.pullMoreDataEvent($showMore);

});
//获取商家列表
function getGoodsList(curpage, isInit){
	F.get('/user/favorite/goods/list', {curpage : curpage}, function(ret){
		constructRows(ret, isInit);
		F.handleWhenHasNextPage($showMore, ret);
	});
}
//构建HTML输出
function constructRows(ret, isInit){
	var HTML = "";
	if(!ret || !ret.result || !ret.result.length){
		HTML = "<div class='no_more_data'>还没有相关数据.</div>";
	}else{
		var result = ret.result;
		for(var i = 0,len=result.length; i < len; i++){
			var obj = result[i];
			HTML += "<li class=\"goods_li\"><div class=\"next_goods\">";
			HTML += "<img src=\"<?php echo ploadingimg()?>\" data-gid="+obj.goods_id+" style='float:right;' data-loaded=\"0\" onload=\"imgLazyLoad(this,'"+obj.goods_img+"')\"/>";
			HTML += "<p class=\"all_goods_tit\">"+obj.goods_name+"</p><p class=\"all_goods_price\"><span>￥"+obj.shop_price+"</span><b>￥"+obj.market_price+"</b></p></div></li>";
		}
	}
	F.afterConstructRow(isInit, $resultList, HTML, $showMore);
}
</script>
<?php endif;?>