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

<div class="copartner_list">
	<table cellspacing="10" cellpadding="10" class="copartner_list_table">
	</table>
</div>
<div id="pageContainer"><a href="#" onclick="pulldata(this);">下拉显示...</a></div>

<?php include T('inc/add_as_friend');?>

<script>
	$().ready(function(){
		getPageData(1);
	});
	
	function getPageData(curpage){
		var level = "<?=$level ?>";
		var url = "/partner/list/ajax?level="+level+"&curpage="+curpage;
		F.get(url, null, function(data){
			buildListHtml(data);
			handleWhenHasNextPage(data, level);
		});
	}
	
	function buildListHtml(data){
		if(!data){
			return;
		}
		var table = $("table.copartner_list_table");
		var html = "";
		var result = data.result;
		for(var i = 0,len=result.length; i < len; i++){
			var item = result[i];
			var logo = item.logo ? item.logo : "/themes/mobiles/img/mt.png";
			var bg = (item.level == 1) ? "user_bg_sha" : ((item.level == 2) ? "user_bg_he" : "user_bg_ke");
    		html += "<tr><td class=\"list_td1\"><img src=\""+logo+"\" class=\"list_img1\"></td>";
    		html += "<td class=\"list_td2\">";
    		html += "<span class=\"list_nickname "+bg+"\" >"+item.nickname+"</span>";
    		html += "<span class=\"list_address\">"+item.province+" "+item.city+"</span>";
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
		html += "$(function(){ F.set_scroller(false, 100); })";
		table.append($(html));
	}

	//当还有下一页时处理下拉
	function handleWhenHasNextPage(data, level){
		var hasnex = data.hasnexpage;
		if(hasnex){
			$("#pageContainer").show();
			$("#pageContainer").find("a").attr('curpage',data.curpage).attr('level',level);
		}else{
			$("#pageContainer").hide();
		}
	}
	
	function pulldata(obj){
		getPageData($(obj).attr("curpage"), $(obj).attr("level"), true);
	}
</script>

<?php endif;?>
