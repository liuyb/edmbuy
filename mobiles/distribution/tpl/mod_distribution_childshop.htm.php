<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if(''!==$errmsg):?>

<div class="error"><?=$errmsg?></div>

<?php else:?>
<script id="forTopnav" type="text/html">
<div class="header">
	我的店铺
	<a href="javascript:history.back();" class="back"></a>
</div>
</script>
<script>
show_topnav($('#forTopnav').html());
</script>

<div class="store_three_list">
	<ul>
		<li class="navig_on" onclick="shopChange(this, 1)">一级店铺</li>
		<li class="" onclick="shopChange(this, 2)">二级店铺</li>
		<li class="" onclick="shopChange(this, 3)">三级店铺</li>
	</ul>
</div>

<div class="m_store_common" id="resultList"></div>
<script>
function shopChange(obj, level){
	var $obj = $(obj);
	if(!$obj.hasClass("navig_on")){
		$obj.siblings("li").removeClass("navig_on");
		$obj.addClass("navig_on");
	}
	getShopList(level);
}

$(function(){
	getShopList(1);

	$("#resultList").on('click', 'button', function(){
		var $this = $(this);
		var mid = $this.data("mid");
		window.location.href='/shop/'+mid;
	});

});
//获取商家列表
function getShopList(level){
	F.get('/distribution/my/child/shop/list', {level : level}, function(ret){
		console.log(ret);
		constructRows(ret);
	});
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
			HTML += "<div class=\"mstore_list_common\"><div class=\"mstore_list_infos\"><div class=\"mstore_time\">"+obj.created+"</div><div class=\"mstore_list_top\">";
			HTML += "<img src=\"<?php echo ploadingimg()?>\" class=\"l_top_img\" data-loaded=\"0\" onload=\"imgLazyLoad(this,'"+obj.logo+"')\" class=\"mstore_top_imt\"/><div class=\"mstore_top_infos\"><p class=\"m_infos_nmae\">"+obj.facename+"</p>";
			HTML += "<p class=\"ms_infos_num\"><i style=\"margin-right:10px;\">"+obj.cc+"人收藏</i><i>"+obj.oc+"个订单</i></p><p class=\"ms_infos_rs\">益多米Edmbuy</p></div>";
			HTML += "<button class=\"l_top_btn\" data-mid=\""+obj.merchant_id+"\">进店</button></div></div></div>";
		}
	}
	$("#resultList").html($(HTML));
}
</script>
<?php endif;?>