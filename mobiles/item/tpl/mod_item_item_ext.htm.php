<div class="pro_comment pro_lsit_tab" id="list2" style="display:none">
	<table cellspacing="0" cellpadding="0" class="pro_tab_evaluate">
		<tr>
			<td><div class="evaluate_on" data-cat='0' data-text='全部'>全部</div></td>
			<td><div data-cat='1' data-text='好评'>好评</div></td>
			<td><div data-cat='2' data-text='中评'>中评</div></td>
		</tr>
		<tr>
			<td><div data-cat='3' data-text='差评'>差评</div></td>
			<td><div data-cat='-1' data-text='有图'>有图</div></td>
		</tr>
	</table>
	<div class="e_info_d" id="commentlist" style="margin-bottom:10px;">
	</div>
	<div class="remm_more" id="pullMore" style="display: none;"><span>下拉加载更多</span></div>
</div>
<div class="pro_comment" id="list3" style="display:none">
	<div class="shop_remm">
		<ul>
		</ul>
		<div class="clear"></div>
	</div>
</div>

<?php include T('inc/meiqia');?>

<script>
var $mbody;
var $pullMore;
$(function(){
	$mbody = $("#Mbody");
	$pullMore = $("#pullMore");
	$mbody.pullMoreDataEvent($pullMore);
	$mbody.on('pullMore', function(){
		loadGoodsComment($pullMore.attr("curpage"), false, $pullMore.attr("category"));
	});
	//好评中评差评切换
	$(".pro_tab_evaluate").find("div").on('click', function(){
		var OBJ = $(this);
		if(OBJ.hasClass("evaluate_on")){
			return;
		}
		$(".pro_tab_evaluate").find("div").removeClass("evaluate_on");
		OBJ.addClass("evaluate_on");
		loadGoodsComment(1, true, OBJ.attr("data-cat"));
	});
	//监听美恰客服加载
	$(EventBus).on('meiqiaSet', function(event, online){
		if(online){
			$("#zxkf").removeClass("item_kefu_off").addClass("item_kefu_on");
		}else{
			$("#zxkf").removeClass("item_kefu_on").addClass("item_kefu_off");
		}
	});
});
//详情、评论TAB页切换
function detailTabSwitch(a){
	var _li = "#li" + a;
	var _list = "#list" + a;
	$(".pro_tab_check li").removeClass("check_on");
	$(".pro_comment").hide();
	
	$(_li).addClass("check_on");
	$(_list).show();
	if($(_li).attr('data-loaded') != 'Y'){
		$(_li).attr('data-loaded','Y');
		if(a == 2){
			loadGoodsComment(1, true);
		}else if(a == 3){
			getMerchantRecommend(1, true);
		}
	}
	//F.set_scroller(false, 100);
}
//加载商品评论列表
function loadGoodsComment(curpage, isinit, category){
	F.get('<?php echo U('item/comment/list')?>',{curpage : curpage, goods_id : <?=$item->item_id ?>, category : category},function(ret){
		var commentDom = $("#commentlist");
		if(!ret || !ret.result || ret.result.length == 0){
			var _html = "<div style='margin:15px;text-align:center;'>还没有人评论哦~</div>";
			commentDom.html(_html);
			handleWhenHasNextPage(ret, category);
			return;
		}
		renderCommentNum(ret);
		var _html = "";
		for(var i = 0,len=ret.result.length; i < len; i++){
			var comment = ret.result[i];
			var cls = ((i+1) < len) ? 'comment_container' : '';
 			_html += "<div class='"+cls+"'><table cellspacing='0' cellpadding='0' class='evaluate_info'><tr>";
			_html += "<td width=\"45px;\"><img src=\"/themes/mobiles/img/mt.png\" data-loaded=\"0\" onload=\"imgLazyLoad(this,'"+comment.user_logo+"')\"></td>";
			_html += "<td><p class=\"eval_name\">"+comment.user_name+"</p>";
			if(comment.obj_attr){
				_html += "<p class=\"eval_type\">"+comment.obj_attr+"</p>";
			}
			_html += "<p class=\"eval_time\">"+comment.add_time+"</p></td></tr></table>";
			_html += "<p class=\"eval_idea\">"+comment.content+"</p>";
			if(comment.comment_img){
				var cimg = comment.comment_img.split(","); 
				var cimg_thumb = comment.comment_thumb.split(","); 
				_html += "<div class=\"idea_img\"><ul>";
				for(var ig = 0,iglen = cimg.length; ig < iglen; ig++){
    				_html += "<li><img src=\"<?php echo ploadingimg()?>\" data-loaded=\"0\" data-orisrc=\""+cimg[ig]+"\" onload=\"imgLazyLoad(this,'"+cimg_thumb[ig]+"')\"></li>";
				}
				_html += "</ul></div>";
			}
			if(comment.is_reply && comment.is_reply > 0){
				_html += "<div class=\"eval_reply\">掌柜回复："+comment.comment_reply+"</div>";
			}
			_html += "</div>";
		}
		if(isinit){
			commentDom.html(_html);
		}else{
			commentDom.append(_html);
		}
		commentLoadedCallback();
		handleWhenHasNextPage(ret, category);
	});
}

//渲染评论数量
function renderCommentNum(ret){
	var render = $(".pro_tab_evaluate");
	var gather = ret['gather'];
	var all = 0;
	for(var i = 0,len=gather.length; i < len; i++){
		var level = gather[i];
		var total = level.total;
		if(total && total > 0){
			var curLevel = render.find("div[data-cat='"+level.comment_level+"']");
			curLevel.html(curLevel.attr('data-text')+"（"+total+"）");
		}
		if(level.comment_level != "-1"){
			all += parseInt(total);
		}
	}
	if(all > 0){
		var allLevel = render.find("div[data-cat='0']");
		allLevel.html(allLevel.attr('data-text')+"（"+all+"）");
	}
}
//评论加载后回调
function commentLoadedCallback(){
	//F.set_scroller(false, 100);
    //weixin img click
    $('.idea_img img').on('click',function(){
    	var comment_currpic = '';
        var comment_picset = new Array();
        $(this).closest("ul").find("img").each(function(){
        	comment_picset.push($(this).attr('data-orisrc'));
        	if (''==comment_currpic) comment_currpic = $(this).attr('src');
        });	
    	wx.previewImage({
            current: comment_currpic,
            urls: comment_picset
    	});
    });
}
//当还有下一页时处理下拉
function handleWhenHasNextPage(data, category){
	$pullMore.html("下拉加载更多");
	var hasnex = data.hasnexpage;
	if(hasnex){
		$pullMore.show();
		$pullMore.attr('curpage',data.curpage);
		$pullMore.attr('category',category);
	}else{
		$pullMore.hide();
	}
}

function getMerchantRecommend(curpage, isinit){
	F.get('<?php echo U('item/merchant/recommend')?>',{curpage : curpage, merchant_id : "<?=($item->merchant_id ? $item->merchant_id : '-1') ?>"},function(ret){
		if(!ret || !ret.result || !ret.result.length){
			$(".shop_remm").find("ul").html($("<li><p style='line-height:50px;margin-left:10px;'>还没有可显示的商品！</p></li>"));
			return;
		}
		var LI = "";
		var result = ret.result;
		for(var i = 0,len=result.length; i < len; i++){
			var good = result[i];
			LI += "<li><a href='/item/"+good.goods_id+"'><img src=\"<?php echo ploadingimg()?>\" data-loaded=\"0\" onload=\"imgLazyLoad(this,'"+good.goods_img+"')\">";
			LI += "<p class=\"remm_font\">"+good.goods_name+"</p>";
			LI += "<p class=\"tea_info_price remm_p\"><span>￥"+good.shop_price+"</span><b>￥"+good.market_price+"</b></p></a></li>";
		}
		$(".shop_remm").find("ul").html($(LI));
		//F.set_scroller(false, 100);
	});
}

//获取商品收藏数量
function getGoodsCollectNum(){
	$.get('/item/goods/collect/num?goods_id='+goods_id, null, function(ret){
		var total = ret.total;
		var my = ret.my;
		if(total && total > 0){
    		//$("#collect_number").text(total);
    		//$("#collect_number").show();
		}
		if(my && my > 0){
			$("#collect_status").addClass("t2_on");
		}
	});
}
//点击、取消 商品收藏
function changeGoodsCollect(obj){
	var $this = $(obj);
	var action = 0;
	if($this.hasClass("t2_on")){
		action = -1;
		$this.removeClass("t2_on");
		//setCollectNumber(-1);
	}else{
		action = 1;
		$this.addClass("t2_on");
		//setCollectNumber(1);
	}
	F.post('/item/goods/collect', {goods_id : goods_id, action : action});
}
//重置收藏数量
function setCollectNumber(num){
	var old = $("#collect_number").text();
	old = old ? parseInt(old) : 0;
	var now = old + num;
	now = now < 0 ? 0 : now;
	$("#collect_number").text(now);
	if(now > 0){
		$("#collect_number").show();
	}else{
		$("#collect_number").hide();
	}
}



//菜单开启
/* $(document.body).on("click", ".p_detail_more", function(){
	if($(this).attr('data-show')=='0') {
		$(".p_detail_menulist").show();
		$(this).attr('data-show','1');
	}
	else {
		$(".p_detail_menulist").hide();
		$(this).attr('data-show','0');
	}
});
//菜单关闭
$(document.body).on("click",function(e){
	var firecls = 'p_detail_more';
	var canfire = true;
	if ($(e.target).hasClass(firecls)) {
		canfire = false;
	}
	else {
		var $pn = $(e.target).parent();
		while($pn.size()>0 && $pn.get(0).tagName!='body') {
			if ($pn.hasClass(firecls)) {
				canfire = false;
				break;
			}
			$pn = $pn.parent();
		}
	}
	if (canfire) {
		$(".p_detail_menulist").hide();
		$("."+firecls).attr('data-show','0');
	}
}); */
</script>