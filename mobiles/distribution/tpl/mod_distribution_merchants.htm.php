<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if(''!==$errmsg):?>

<div class="error"><?=$errmsg?></div>

<?php else:?>

<script id="forTopnav" type="text/html">
<div class="header alliance">
	<ul id="order_bar">	
		<li onclick="merchants_order(this, 'oc')" class="allliance_on">销量</li><li onclick="merchants_order(this, 'cc')">粉丝</li>
	</ul>
</div>
</script>
<script>
show_topnav($('#forTopnav').html());
</script>

<div class="alliance_list_common" id="resultList">
</div>

<div id="showMore" style="text-align:center;height:40px;line-height:40px;margin-bottom:10px;display:none;"><span class="allicen_more">下拉加载更多</span></div>

<script>
function merchants_order(obj, orderby){
	var $obj = $(obj);
	if(!$obj.hasClass("allliance_on")){
		$obj.siblings("li").removeClass("allliance_on");
		$obj.addClass("allliance_on");
	}
	getMerchantsList(orderby);
}

$(function(){
	getMerchantsList('oc');

	$("#resultList").on('click', '.gotoshop', function(){
		var mid = $(this).data("mid");
		window.location.href='/shop/'+mid;
	});

	$("#resultList").on('click', '.iance_list_bottom li', function(){
		var gid = $(this).data("gid");
		window.location.href='/item/'+gid;
	});

	//$(document).on();
});
//获取商家列表
function getMerchantsList(orderby){
	F.get('/distribution/merchants/list', {orderby : orderby}, function(ret){
		constructRows(ret);
		handleWhenHasNextPage(ret);
	});
}
//当还有下一页时处理下拉
function handleWhenHasNextPage(data){
	var hasnex = data.hasnexpage;
	if(hasnex){
		$("#showMore").show();
		$("#showMore").attr('curpage',data.curpage);
	}else{
		$("#showMore").hide();
	}
}
//构建HTML输出
function constructRows(ret){
	var HTML = "";
	if(!ret || !ret.result || !ret.result.length){
		HTML = "<div style='text-align:center;'>还没有相关数据.</div>";
	}else{
		var result = ret.result;
		for(var i = 0,len=result.length; i < len; i++){
			var obj = result[i];
			HTML += "<div class=\"iance_list_infos\"><div class=\"iance_list_top\">";
			HTML += "<img src=\"<?php echo ploadingimg()?>\" class=\"l_top_img\" data-loaded=\"0\" onload=\"imgLazyLoad(this,'"+obj.logo+"')\" />";
			HTML += "<div class=\"l_top_infos\"><p class=\"t_infos_nmae\">"+obj.facename+"</p><p class=\"t_infos_num\"><i style=\"margin-right:10px;\">"+obj.cc+"人收藏</i><i>"+obj.oc+"个订单</i></p></div>";
			HTML += "<button class=\"l_top_btn gotoshop\" data-mid=\""+obj.merchant_id+"\">进店</button></div>";

			if(obj.recommend && obj.recommend.length == 3){
				HTML += "<div class=\"iance_list_bottom\"><ul>";
				for(var r = 0; r < 3; r++){
					var recomm = obj.recommend[r];
					var style = "";
					if(r == 1){
						style = "style='margin:0 2%;'";
					}
					HTML += "<li "+style+" data-gid=\""+recomm.goods_id+"\"><img src=\"<?php echo ploadingimg()?>\" data-loaded=\"0\" onload=\"imgLazyLoad(this,'"+recomm.goods_img+"')\" /></li>";
				}
				HTML += "</ul></div>";
			}

			HTML += "</div>";
		}
	}
	$("#resultList").html($(HTML));
}
</script>

<?php endif;?>