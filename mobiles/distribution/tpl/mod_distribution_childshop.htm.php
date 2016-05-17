<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if(''!==$errmsg):?>

<div class="error"><?=$errmsg?></div>

<?php else:?>
<script id="forTopnav" type="text/html">
<div class="header">
	我发展的店铺
	<a href="javascript:history.back();" class="back"></a>
</div>
</script>
<script>
show_topnav($('#forTopnav').html());
</script>

<div class="store_three_list">
	<ul id="levelBar">
		<li class="navig_on" onclick="shopChange(this, 1)" data-type=1><?=$level1 ?><br/>一级店铺</li>
		<li onclick="shopChange(this, 2)" data-type=2><?=$level2 ?><br/>二级店铺</li>
		<li onclick="shopChange(this, 3)" data-type=3><?=$level3 ?><br/>三级店铺</li>
	</ul>
</div>

<div class="m_store_common" id="resultList"></div>

<div id="showMore" style="text-align:center;height:40px;line-height:40px;display:none;"><span class="allicen_more">下拉加载更多</span></div>
<script>
var $mbody;
var $resultList;
var $showMore;
$(function(){
	$showMore = $("#showMore");
	$resultList = $("#resultList");
	$mbody = $("#Mbody");
	
	getShopList(1, 1, true);

	$resultList.on('click', 'button', function(){
		var $this = $(this);
		var mid = $this.data("mid");
		window.location.href='/shop/'+mid;
	});

	$mbody.on('pullMore', function(){
		var level = $("#levelBar").find("li[class='navig_on']").data('type');
		var curpage = $showMore.attr('data-curpage');
		getShopList(level ? level : 1, (curpage ? curpage : 1), false);
	});
	$mbody.pullMoreDataEvent($showMore);

});
//获取商家列表
function getShopList(level, curpage, isInit){
	F.get('/distribution/my/child/shop/list', {level : level, curpage : curpage}, function(ret){
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
			var facename = obj.facename ? obj.facename.substr(0,8) : obj.facename;
			HTML += "<div class=\"mstore_list_common\"><div class=\"mstore_list_infos\"><div class=\"mstore_time\">"+obj.created+"</div><div class=\"mstore_list_top\">";
			HTML += "<img src=\"<?php echo ploadingimg()?>\" class=\"l_top_img\" data-loaded=\"0\" onload=\"imgLazyLoad(this,'"+obj.logo+"')\" class=\"mstore_top_imt\"/><div class=\"mstore_top_infos\"><p class=\"m_infos_nmae\">"+facename+"</p>";
			HTML += "<p class=\"ms_infos_num\"><i style=\"margin-right:10px;\">"+obj.cc+"人收藏</i><i>"+obj.oc+"个订单</i></p><p class=\"ms_infos_rs\"><img src=\"/themes/mobiles/img/yiduomi-inc.png\"></p></div>";
			HTML += "<button class=\"l_top_btn\" data-mid=\""+obj.merchant_id+"\">进店</button></div></div></div>";
		}
	}
	F.afterConstructRow(isInit, $resultList, HTML, $showMore);
}
function shopChange(obj, level){
	var $obj = $(obj);
	if(!$obj.hasClass("navig_on")){
		$obj.siblings("li").removeClass("navig_on");
		$obj.addClass("navig_on");
	}
	getShopList(level, 1, true);
}
</script>
<?php endif;?>