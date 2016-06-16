<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if(''!==$errmsg):?>

<div class="error"><?=$errmsg?></div>

<?php else:?>

<!--[HEAD_CSS]-->
<style>
.alliance li{
	display:inline-block;
	width:33.3%;
	height:43px;
}
.new_collects{
	background:url(../img/scdp.png) no-repeat left;
	background-size:14px 13px;
	padding-left:20px;
	margin-right:10px;
}
.new_order_bg{
	background:url(../img/ding.png) no-repeat left;
	background-size:13px 15px;
	padding-left:20px;
}
.new_mer_type{
	vertical-align: middle;
	margin-left:5px;
	width:17px;
	height:19px;
}
</style>
<!--[/HEAD_CSS]-->

<script id="forTopnav" type="text/html">
<div class="header alliance">
<div style="width:80%;float:left;">
	<ul>	
		<li id="li1" onclick="merchants_order(this, 'oc')" class="allliance_on">销量</li><li id="li2" onclick="merchants_order(this, 'cc')">粉丝</li><li id="li3" onclick="merchants_order(this, 'sc')">我的收藏</li>
	</ul>
</div>
<div style="float:right;"><img src="/themes/mobiles/img/ruz.png" style="height:45px;" onclick="location.href='<?php echo U('user/merchant/checkin') ?>'"></div>
</div>
</script>
<script>
show_topnav($('#forTopnav').html());
</script>

<div class="alliance_list_common" id="resultList">
</div>

<div id="showMore" class="moreblk"><span class="allicen_more">下拉加载更多</span></div>

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
		var curpage = $("#showMore").attr('data-curpage');
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
			var img = "";
			if(obj.verify == 2){
				img = "<img src='/themes/mobiles/img/zheng.png' class='new_mer_type'/>";
			}else{
				img = "<img src='/themes/mobiles/img/shang_22.png' class='new_mer_type'/>";
			}
			HTML += "<div class=\"iance_list_infos\"><div class=\"iance_list_top\">";
			HTML += "<img src=\"<?php echo ploadingimg()?>\" class=\"l_top_img\" data-loaded=\"0\" onload=\"imgQueueLoad(this,'"+obj.logo+"')\" />";
			HTML += "<div class=\"l_top_infos\"><p class=\"t_infos_nmae\">"+facename+""+img+"</p><p class=\"t_infos_num\"><i class='new_collects'>"+obj.cc+"人收藏</i><i class='new_order_bg'>"+obj.oc+"个订单</i></p></div>";
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