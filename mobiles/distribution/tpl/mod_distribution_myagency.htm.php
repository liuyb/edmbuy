<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if(''!==$errmsg):?>

<div class="error"><?=$errmsg?></div>

<?php else:?>
<script id="forTopnav" type="text/html">
<div class="header">
	我的代理
	<a href="javascript:history.back();" class="back"></a>
</div>
</script>
<script>
show_topnav($('#forTopnav').html());
</script>

<div class="store_three_list">
	<ul>
		<li class="navig_on" onclick="agencyChange(this, 1)">一级代理</li>
		<li class="" onclick="agencyChange(this, 2)">二级代理</li>
		<li class="" onclick="agencyChange(this, 3)">三级代理</li>
	</ul>
</div>

<div class="m_store_common" id="resultList">
</div>
<?php include T('inc/add_as_friend');?>
<script>
function agencyChange(obj, level){
	var $obj = $(obj);
	if(!$obj.hasClass("navig_on")){
		$obj.siblings("li").removeClass("navig_on");
		$obj.addClass("navig_on");
	}
	getAgencyList(level);
}

$(function(){
	getAgencyList(1);

	$("#resultList").on('click', 'button', function(){
		var $this = $(this);
		var uid = $this.data("u");
		var wxqr = $this.data("w");
		var mobilephone = $this.data("m");
		getAddFriendInstance().showFriend(uid,wxqr,mobilephone);
	});

});
//获取商家列表
function getAgencyList(level){
	F.get('/distribution/my/child/agent/list', {level : level}, function(ret){
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
			var icon = obj.level == 3 ? '/themes/mobiles/img/jinpai1.png' : '/themes/mobiles/img/yinpai2.png';
			HTML += "<div class=\"mstore_list_common\"><div class=\"mstore_list_infos\"><div class=\"mstore_time\">"+obj.reg_time+"</div><div class=\"mstore_list_top\">";
			HTML += "<img src=\"<?php echo ploadingimg()?>\" class=\"l_top_img\" data-loaded=\"0\" onload=\"imgLazyLoad(this,'"+obj.logo+"')\" class=\"mstore_top_imt\"/><div class=\"mstore_top_infos\"><p class=\"m_infos_nmae\">"+obj.nickname+"<img src=\""+icon+"\"></p>";
			HTML += "<p class=\"m_infos_num\">多米号："+obj.uid+"</p></div><button class=\"l_top_btn\" data-uid=\""+obj.uid+"\" data-u=\""+obj.uid+"\" data-w=\""+obj.wxqr+"\" data-m=\""+obj.mobilephone+"\">加好友</button></div></div></div>";
		}
	}
	$("#resultList").html($(HTML));
}
</script>
<?php endif;?>