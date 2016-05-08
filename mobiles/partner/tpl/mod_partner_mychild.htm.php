<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if(''!==$errmsg):?>

<div class="error"><?=$errmsg?></div>

<?php else:?>
<script id="forTopnav" type="text/html">
<div class="header">
	我的人脉
	<a href="javascript:history.back();" class="back"></a>
</div>
</script>
<script>
show_topnav($('#forTopnav').html());
</script>

<div class="store_three_list">
	<ul id="levelBar">
		<li class="navig_on" onclick="agencyChange(this, 1)" data-type=1><?=$level1 ?><br/>一级代理</li>
		<li onclick="agencyChange(this, 2)" data-type=2><?=$level2 ?><br/>二级代理</li>
		<li onclick="agencyChange(this, 3)" data-type=3><?=$level3 ?><br/>三级代理</li>
	</ul>
</div>

<div class="m_store_common" id="resultList"></div>

<div id="showMore" style="text-align:center;height:40px;line-height:40px;display:none;"><span class="allicen_more">下拉加载更多</span></div>

<?php include T('inc/add_as_friend');?>
<script>
var $mbody;
var $resultList;
var $showMore;
$(function(){
	$showMore = $("#showMore");
	$resultList = $("#resultList");
	$mbody = $("#Mbody");
	
	getChildList(1, 1, true);

	$resultList.on('click', 'button', function(){
		var $this = $(this);
		var uid = $this.data("u");
		var wxqr = $this.data("w");
		var mobilephone = $this.data("m");
		getAddFriendInstance().showFriend(uid,wxqr,mobilephone);
	});

	$mbody.on('pullMore', function(){
		var level = $("#levelBar").find("li[class='navig_on']").data('type');
		var curpage = $showMore.attr('data-curpage');
		getChildList(level ? level : 1, (curpage ? curpage : 1), false);
	});
	$mbody.pullMoreDataEvent($showMore);
});
//获取商家列表
function getChildList(level, curpage, isInit){
	F.get('/partner/list/ajax', {level : level, curpage : curpage}, function(ret){
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
			var address = obj.province+" "+obj.city;
			address = (obj.province || obj.city) ? address : "&nbsp;";
			var icon = obj.level == 3 ? '/themes/mobiles/img/jinpai1.png' : '/themes/mobiles/img/yinpai2.png';
			HTML += "<div class=\"mstore_list_common\"><div class=\"mstore_list_infos\"><div class=\"mstore_time\">"+obj.reg_time+"</div><div class=\"mstore_list_top\">";
			HTML += "<img src=\"<?php echo ploadingimg()?>\" class=\"l_top_img\" data-loaded=\"0\" onload=\"imgLazyLoad(this,'"+obj.logo+"')\" class=\"mstore_top_imt\"/><div class=\"mstore_top_infos\"><p class=\"m_infos_nmae\">"+obj.nickname+"<img src=\""+icon+"\"></p>";
			HTML += "<p>"+address+"</p>";
			HTML += "<p class=\"m_infos_num\">推荐人数："+obj.childnum1	+"</p><p class=\"m_infos_num\">上级："+obj.parentnick	+"</p></div><button class=\"l_top_btn\" data-uid=\""+obj.uid+"\" data-u=\""+obj.uid+"\" data-w=\""+obj.wxqr+"\" data-m=\""+obj.mobilephone+"\">加好友</button></div></div></div>";
		}
	}
	F.afterConstructRow(isInit, $resultList, HTML, $showMore);
}

function agencyChange(obj, level){
	var $obj = $(obj);
	if(!$obj.hasClass("navig_on")){
		$obj.siblings("li").removeClass("navig_on");
		$obj.addClass("navig_on");
	}
	getChildList(level, 1, true);
}
</script>
<?php endif;?>