<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if(''!==$errmsg):?>

<div class="error"><?=$errmsg?></div>

<?php else:?>

<script id="forTopnav" type="text/html">
<div class="header alliance">
<div style="padding:0 20px;">
	<ul id="order_bar">	
		<li onclick="merchants_order(this, 'oc')" class="allliance_on" data-type='oc'>销量</li><li onclick="merchants_order(this, 'cc')" data-type='cc'>粉丝</li>
	</ul>
</div>
</div>
</script>
<script>
show_topnav($('#forTopnav').html());
</script>

<div class="alliance_list_common" id="resultList">
</div>

<div id="showMore" style="text-align:center;height:40px;line-height:40px;display:none;"><span class="allicen_more">下拉加载更多</span></div>

<script>
var $showMore;
var $resultList;
var $mbody;
$(function(){
	$showMore = $("#showMore");
	$resultList = $("#resultList");
	$mbody = $("#Mbody");
	
	getMerchantsList('oc', 1, true);

	$resultList.on('click', '.gotoshop', function(){
		var mid = $(this).data("mid");
		window.location.href='/shop/'+mid+'&spm=<?=isset($_GET['spm']) ? $_GET['spm'] : '' ?>';
	});

	$resultList.on('click', '.iance_list_bottom li', function(){
		var gid = $(this).data("gid");
		window.location.href='/item/'+gid+'&spm=<?=isset($_GET['spm']) ? $_GET['spm'] : '' ?>';
	});

	$mbody.on('pullMore', function(){
		var orderby = $("#order_bar").find("li[class='allliance_on']").data('type');
		var curpage = $showMore.attr('data-curpage');
		getMerchantsList(orderby ? orderby : 'oc', (curpage ? curpage : 1), false);
	});
	$mbody.pullMoreDataEvent($showMore);
});
//获取商家列表
function getMerchantsList(orderby, curpage, isInit){
	F.get('/distribution/merchants/list', {orderby : orderby, curpage : curpage}, function(ret){
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
			var facename = obj.facename ? obj.facename.substr(0,10) : obj.facename;
			HTML += "<div class=\"iance_list_infos\"><div class=\"iance_list_top\">";
			HTML += "<img src=\"<?php echo ploadingimg()?>\" class=\"l_top_img\" data-loaded=\"0\" onload=\"imgQueueLoad(this,'"+obj.logo+"')\" />";
			HTML += "<div class=\"l_top_infos\"><p class=\"t_infos_nmae\">"+facename+"</p><p class=\"t_infos_num\"><i style=\"margin-right:10px;\">"+obj.cc+"人收藏</i><i>"+obj.oc+"个订单</i></p></div>";
			HTML += "<button class=\"l_top_btn gotoshop\" data-mid=\""+obj.merchant_id+"\">进店</button></div>";
			if(obj.recommend && obj.recommend.length){
				HTML += "<div class=\"iance_list_bottom\"><ul>";
				for(var r = 0,rlen = obj.recommend.length; r < rlen; r++){
					var recomm = obj.recommend[r];
					HTML += "<li data-gid=\""+recomm.goods_id+"\"><img src=\"<?php echo ploadingimg()?>\" data-loaded=\"0\" onload=\"imgLazyLoad(this,'"+recomm.goods_img+"')\" /></li>";
				}
				HTML += "</ul></div>";
			}

			HTML += "</div>";
		}
	}
	F.afterConstructRow(isInit, $resultList, HTML, $showMore);
}

function merchants_order(obj, orderby){
	var $obj = $(obj);
	if(!$obj.hasClass("allliance_on")){
		$obj.siblings("li").removeClass("allliance_on");
		$obj.addClass("allliance_on");
	}
	getMerchantsList(orderby, 1, true);
}

</script>

<?php endif;?>