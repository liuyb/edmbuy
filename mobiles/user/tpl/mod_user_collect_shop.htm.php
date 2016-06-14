<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if(''!==$errmsg):?>

<div class="error"><?=$errmsg?></div>

<?php else:?>
<script id="forTopnav" type="text/html">
<div class="header">
	我收藏的店铺
<a href="javascript:history.back();" class="back"></a>
</div>
</script>
<script>show_topnav($('#forTopnav').html())</script>

<div class="collect_dp">
	<div class="collect_dp_list">
		<ul id="orderBar">
			<li data-type="latest" onclick="shopChange(this,'latest')" class="coll_on">最近</li>
			<li data-type="oc" onclick="shopChange(this,'oc')" >销量</li>
			<li data-type="cc" onclick="shopChange(this,'cc')" >粉丝</li>
		</ul>
	</div>
</div>

<div class="collect_common" id="resultList">
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
	
	getShopList(1, '', true);

	$resultList.on('click', 'button', function(){
		var $this = $(this);
		var mid = $this.data("mid");
		window.location.href='/shop/'+mid+'&spm=<?=isset($_GET['spm']) ? $_GET['spm'] : '' ?>';
	});

	$mbody.on('pullMore', function(){
		var orderby = $("#orderBar").find("li[class='coll_on']").data('type');
		var curpage = $("#showMore").attr('data-curpage');
		getShopList(orderby, (curpage ? curpage : 1), false);
	});
	$mbody.pullMoreDataEvent($showMore);

});
//获取商家列表
function getShopList(orderby, curpage, isInit){
	F.get('/user/favorite/shop/list', {orderby : orderby, curpage : curpage}, function(ret){
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
			HTML += "<div class=\"iance_list_infos\"><div class=\"iance_list_top\"><img src=\"<?php echo ploadingimg()?>\" class=\"l_top_img\" data-loaded=\"0\" onload=\"imgLazyLoad(this,'"+obj.logo+"')\"/>";
			HTML += "<div class=\"l_top_infos\"><p class=\"t_infos_nmae\">"+facename+"</p><p class=\"t_infos_num\"><i style=\"margin-right:10px;\">"+obj.cc+"人收藏</i><i>"+obj.oc+"个订单</i></p></div>";
			HTML += "<button class=\"l_top_btn\" data-mid=\""+obj.merchant_id+"\">进店</button></div></div>";
		}
	}
	F.afterConstructRow(isInit, $resultList, HTML, $showMore);
}
function shopChange(obj, orderby){
	var $obj = $(obj);
	if(!$obj.hasClass("coll_on")){
		$obj.siblings("li").removeClass("coll_on");
		$obj.addClass("coll_on");
	}
	getShopList(orderby, 1, true);
}
</script>
<?php endif;?>