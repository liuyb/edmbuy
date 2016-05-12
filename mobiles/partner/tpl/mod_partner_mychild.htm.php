<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if(''!==$errmsg):?>

<div class="error"><?=$errmsg?></div>

<?php else:?>
<!--[HEAD_CSS]-->
<style>
.new_partner_list{
	height:70px !important;
}
.new_m_infos_address{
    color: #8c8c8c;
    height: 20px;
	line-height:20px;
    font-size: 12px;
    margin-left: 10px;	
}
.new_l_top_btn{
	margin-top:24px !important;
}
.new_l_top_img{
	width:70px;
	height:70px;
}
.new_m_infos_num{
	height: 20px !important;
	line-height:20px !important;
}
</style>
<!--[/HEAD_CSS]-->
<script id="forTopnav" type="text/html">
<div class="header">
	我的人脉
	<a href="<?php echo U('user')?>" class="back"></a>
</div>
</script>
<script>
show_topnav($('#forTopnav').html());
</script>

<div class="store_three_list">
	<ul id="levelBar">
		<li class="navig_on" onclick="agencyChange(this, 1)" data-type=1><?=$level1 ?><br/>一级人脉</li>
		<li onclick="agencyChange(this, 2)" data-type=2><?=$level2 ?><br/>二级人脉</li>
		<li onclick="agencyChange(this, 3)" data-type=3><?=$level3 ?><br/>三级人脉</li>
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
		HTML = F.displayNoData();
	}else{
		var result = ret.result;
		for(var i = 0,len=result.length; i < len; i++){
			var obj = result[i];
			var address = obj.province+" "+obj.city;
			address = (obj.province || obj.city) ? address : "&nbsp;";
			var icon = getLevelIcon(obj.level);
			HTML += "<div class=\"mstore_list_common\"><div class=\"mstore_list_infos\"><div class=\"mstore_time\">"+obj.reg_time+"</div><div class=\"mstore_list_top new_partner_list\">";
			HTML += "<img src=\"<?php echo ploadingimg()?>\" class=\"l_top_img new_l_top_img\" data-loaded=\"0\" onload=\"imgLazyLoad(this,'"+obj.logo+"')\" class=\"mstore_top_imt\"/><div class=\"mstore_top_infos\"><p class=\"m_infos_nmae\">"+obj.nickname+"<img src=\""+icon+"\"></p>";
			HTML += "<p class=\"new_m_infos_address\">"+address+"</p>";
			HTML += "<p class=\"m_infos_num new_m_infos_num\"><span>推荐人数："+obj.childnum1	+"</span><span>上级："+obj.parentnick	+"</p></span></p></div><button class=\"l_top_btn new_l_top_btn\" data-uid=\""+obj.uid+"\" data-u=\""+obj.uid+"\" data-w=\""+obj.wxqr+"\" data-m=\""+obj.mobilephone+"\">加好友</button></div></div></div>";
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