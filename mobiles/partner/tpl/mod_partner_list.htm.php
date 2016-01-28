<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if(''!==$errmsg):?>

<div class="error"><?=$errmsg?></div>

<?php else:?>
<script id="forTopnav" type="text/html">
<div class="header">
	<?=$title ?>
<a href="/partner" class="back"></a>
</div>
</script>
<script>show_topnav($('#forTopnav').html())</script>

<style>
.animation_tr{
	display : none;
}
</style>
<div class="copartner_list">
	<table cellspacing="10" cellpadding="10" class="copartner_list_table">
	</table>
</div>
<div id="pageContainer" onclick="pulldata(this);" class="pull_more_data">点击加载更多...</div>

<?php include T('inc/add_as_friend');?>

<script>
	$().ready(function(){
		getPageData(1, true);
	});
	
	function getPageData(curpage, isinit){
		var level = "<?=$level ?>";
		var url = "/partner/list/ajax?level="+level+"&curpage="+curpage;
		F.get(url, null, function(data){
			buildListHtml(data, isinit);
			handleWhenHasNextPage(data, level);
		});
	}
	
	function buildListHtml(data, isinit){
		var table = $("table.copartner_list_table");
		if(isinit && (!data || !data.result || !data.result.length)){
			var EMPTY_TR = $("<tr><td class=\"list_td1\" style='font-size:16px;text-align:center;'>暂无数据...</td></tr>");
			table.append(EMPTY_TR);
			return;
		}
		if(isinit){
			if(table.html().length){
				table.empty();
			}
		}
		var html = "";
		var result = data.result;
		for(var i = 0,len=result.length; i < len; i++){
			var item = result[i];
			var logo = item.logo ? item.logo : "/themes/mobiles/img/mt.png";
			var address = item.province+" "+item.city;
			address = (item.province || item.city) ? address : "&nbsp;";
			var bg = (item.level == 1) ? "user_bg_sha" : ((item.level == 2) ? "user_bg_he" : "user_bg_ke");
    		html += "<tr class=\"animation_tr\"><td class=\"list_td1\"><img src=\""+logo+"\" class=\"list_img1\"></td>";
    		html += "<td class=\"list_td2\">";
    		html += "<span class=\"list_nickname "+bg+"\" >"+item.nickname+"</span>";
    		html += "<span class=\"list_address\">"+address+"</span>";
    		html += "<span class=\"list_number\">";
    		html += "推荐人数：<span style=\"color:#ff6d14;margin-right:5px;\">"+item.childnum1+"</span>";
    		html += "上级：<span style=\"color:#ff6d14;\">"+item.parentnick+"</span>";
    		html += "</span></td>";
    		html += "<td class=\"list_td3\">";
    		html += "<button class=\"td3_btn\" onclick=\"getAddFriendInstance().showFriend('"+item.wxqr+"','"+item.mobilephone+"');\">加好友</button>";
    		html += "</td></tr>";
		}
		if(!html.length){
			html += "<tr><td></td></tr>";
		}
		//html += "$(function(){ F.set_scroller(false, 100);} )";
		table.append($(html));
		table.find("tr.animation_tr").each(function(){
			$(this).show('slow');
			$(this).removeClass("animation_tr");
		});
		F.set_scroller(false, 100);
	}

	//当还有下一页时处理下拉
	function handleWhenHasNextPage(data, level){
		var hasnex = data.hasnexpage;
		if(hasnex){
			$("#pageContainer").show();
			$("#pageContainer").attr('curpage',data.curpage).attr('level',level);
		}else{
			$("#pageContainer").hide();
		}
	}
	
	function pulldata(obj){
		getPageData($(obj).attr("curpage"), false);
	}
</script>

<?php endif;?>
