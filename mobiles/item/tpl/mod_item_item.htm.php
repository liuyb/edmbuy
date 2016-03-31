<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if(''!==$errmsg):?>

<div class="error"><?=$errmsg?></div>

<?php else:?>

<script id="forMtop" type="text/html">
<div class="p_header_btn">
	<span class="p_detail_back" onclick="<?=$back ?>"></span>
	<span class="p_detail_more" data-show="0"></span>
</div>
<div class="p_detail_menulist">
	<span class="p_detail_jian"></span>
	<ul class="p_detail_menu">
		<li class="p_menu_sy"><a href="/">首页</a></li>
		<li class="p_menu_hhr"><a href="/partner">米商</a></li>
		<li class="p_menu_hy"><a href="/user">会员</a></li>
	</ul>
</div>
</script>
<script id="forMnav" type="text/html">
<div class="p_detail_tool">
	<div class="fl cursor p_d_t1"><a href='<?=$kefu_link?>' <?php if(''!=$kefu_link&&'javascript:;'!=$kefu_link):?>target="_blank"<?php endif;?> >&nbsp;</a></div>
	<a href="<?php echo U('trade/cart/list')?>">
		<div class="fl cursor p_d_t2"><span class="f_num" id="cart_number" <?php if(!$cartnum):?>style="display:none;"<?php endif;?>><?=$cartnum?></span></div>
	</a>
<?php if (!$item->is_on_sale):?>
<div style="background: #c0c0c0;width: 60%;font-size:16px;color:#fff;font-weight:bold;">产品已下架</div>
<?php else:?>
	<div class="fl cursor p_d_t3" id="Mnav-add-cart">加入购物车</div>
	<div class="fl cursor p_d_t4" id="Mnav-buy">立即购买</div>
<?php endif;?>
</div>
</script>
<?php include T('inc/popqr');?>
<script id="forBody" type="text/html">
<div class="mask no-bounce" id="bodymask" style="display:none;"></div>
<div class="p_cart_main no-bounce" id="p_cart_main" style="display:none;">
	<input type="hidden" id="frm_buy_type" value="cart"/>
	<input type="hidden" id="frm_item_id" value="<?=$item->item_id?>"/>
	<div class="p_cart_content">
		<div class="p_cart_hd no-bounce clearfix">
			<div class="p_cart_hl fl">
				<img src="<?=$item->item_thumb?>" alt="" class="img_item_thumb">
			</div>
			<div class="p_cart_hr fl">
				<div class="p_hr_price">￥<span id="cart_shop_price" data-base_price="<?=$item->shop_price?>"><?=$item->shop_price?></span></div>
				<div class="p_hr_stock">库存<span id="stock-num"><?=$item->item_number?></span>件</div>
				<div class="p_hr_select <?php if(empty($attr_grp)):?>hide<?php endif;?>">已选：<span id="product_select">"蓝色","45"</span></div>
			</div>
		</div>
		<div class="p_cart_bd no-bounce">
<?php foreach ($attr_grp AS $attrgrp): ?>
			<div class="p_cart_item">
				<div class="p_cart_btit"><?=$attrgrp['attr_name']?></div>
				<ul class="p_cart_ul clearfix">
	<?php $i=0; foreach ($attrgrp['attrs'] AS $attr): ?>
					<li <?php if(0===$i): ?>class="on"<?php endif;?> data-style="" data-goods_attr_id="<?=$attr['goods_attr_id']?>" data-attr_id="<?=$attr['attr_id']?>" data-attr_price="<?=$attr['attr_price']?>"><?=$attr['attr_value']?></li>
	<?php $i++; endforeach;?>
					<input type="hidden"/>
				</ul>
			</div>
<?php endforeach;?>
			<div class="cart_p_price p_cart_number">
				<b>购买数量</b>
				<p>
					<span class="cart_btn btn_minus"></span>
					<span><input type="text" class="cart_inp" value="1" id="frm_item_num"></span>
					<span class="cart_btn btn_plus"></span>
				</p>
			</div>
		</div>
		<span class="cursor p_cart_close"></span>
		<div class="no-bounce p_cart_btn"><a href="javascript:;">确定</a></div>
	</div>
</div>

<!-- 佣金分配  -->
<div class="cart_yj_f no-bounce" id="cart_yj_f">
	<div class="yi_artio_tit">佣金分配比例</div>
	<div class="yi_artio_1">一级佣金：35%</div>
	<div class="yi_artio_2">二级佣金：35%</div>
	<div class="yi_artio_3">三级佣金：30%</div>
	<div class="yi_artio_ts">提示：此佣金只有米商可见</div>
	<div class="w_wx_colse"><img src="/themes/mobiles/img/gub.png"></div>
</div>
</script>
<script>
<?php if(!isset($_GET['spm']) || !preg_match('/^user\.(\d+)\.merchant$/i', $_GET['spm'])):?>
show_mtop($('#forMtop').html());
<?php endif;?>
$(function(){
	show_mnav($('#forMnav').html());
	append_to_body($('#forBody').html());
});
</script>

<div class="mainb">
  <div class="swipe">
    <ul id="slider" class="slider">
    <?php $i=0; foreach ($galleries AS $g):?>
    <li <?php if($i++==0):?>style="display:block"<?php endif;?>><a href="javascript:;"><img src="<?php echo ploadingimg()?>" data-loaded="0" data-orisrc="<?=$g->img_url?>" onload="imgLazyLoad(this,'<?=$g->img_url?>')" alt="" /></a></li>
    <?php endforeach;?>
    </ul>
    <div id="slinav" class="slinav clearfix">
    <?php for ($i=0; $i<$gallerynum; $i++):?>
    <a href="javascript:void(0);" class="hwspeed <?php if(0==$i):?>active<?php endif;?>">1</a>
    <?php endfor;?>
     </div>
  </div>
</div>
<script type="text/javascript">
var t1;
$(function(){
	var _active = 0, $_ap = $('#slinav a');
  
  t1 = new TouchSlider({
     id:'slider',
     auto: false,
     speed:300,
     timeout:6000,
     before:function(newIndex, oldSlide){
       $_ap.get(_active).className = '';
       _active = newIndex;
       $_ap.get(_active).className = 'active';
     }
  });
  
  $_ap.each(function(index,ele){
    $(ele).click(function(){
      t1.slide(index);
      return false;     
    });
  });
  
  setTimeout(function(){t1.resize();},500);

  //weixin img click
  var currpic = '';
  var picset = new Array();
  $('#slider img').each(function(){
	  picset.push($(this).attr('data-orisrc'));
	  if (''==currpic) currpic = $(this).attr('src');
	})
	.click(function(){
    wx.previewImage({
      current: currpic,
      urls: picset
    });
  });
});
</script>

<div class="p_detail_msg">
	<div class="p_d_title"><?=$item->item_name?></div>
	<div class="p_d_intro"><?=$item->item_brief?></div>
	<div class="p_d_price"><span>￥<?=$item->shop_price?></span><b>￥<?=$item->market_price?></b><?php if(0&&$user->uid && $user->level>0):?><span class="product_zyj" id="product_zyj">总佣金：￥<?=$item->commision_show?></span><?php endif;?></div>
	<div class="p_d_sale"><span class="fr">销量：<?=$item->paid_goods_number?>件</span><span class="fl">快递：免运费</span></div>
</div>

<?php if($referee):?>
<div class="p_detail_friend">
	<div class="fl p_friend_left"><img src="<?php echo !empty($referee->logo) ? $referee->logo : '/themes/mobiles/img/mt.png' ?>" alt=""/></div>
	<div class="fl p_friend_right">你的好友“<b><?=$referee->nickname?></b>”向你推荐！</div>
</div>
<?php endif;?>

<div class="p_detail_serv clearfix">
	<ul>
		<li>七天退货</li>
		<li>正品保证</li>
		<li>厂家直供</li>
		<li>担保交易</li>
		<li>48小时内发货</li>
	</ul>
</div>

<div class="p_detail_pull">
	<!-- <h1><p>向上拖动查看图文详情</p></h1> -->
</div>

<div class="p_detail_info">
	<div class="pro_tab_check">
		<ul>
		<li class="check_on" id="li1" data-loaded='Y' style="width:100%;">图文详情</li>
		<!-- 
			<li class="check_on" id="li1" onclick="detailTabSwitch(1)" data-loaded='Y'>图文详情</li>	
			<li class="" id="li2" onclick="detailTabSwitch(2)">宝贝评价</li>
			<li class="" id="li3" onclick="detailTabSwitch(3)">店铺推荐</li>
		 -->
		</ul><!-- 
		<span class="pro_line1"></span>
		<span class="pro_line2"></span>-->
		<div class="clear"></div> 
	</div>
	<div class="pro_comment" id="list1">
		<div class="product_detail">
		</div>
	</div>
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
		<div class="remm_more" style="display: none;">下拉加载更多...</div>
	</div>
	<div class="pro_comment" id="list3" style="display:none">
		<div class="shop_remm">
			<ul>
			</ul>
			<div class="clear"></div>
		</div>
	</div>
</div>

<script>
$(document).ready(function(){

	//缓存对象
	var $mask     = $("#bodymask");
	var $cartmain = $('#p_cart_main');
	
	$('#product_zyj').on("click",function(){
		$mask.fadeIn(255);
		$("#cart_yj_f").fadeIn(255);
	});
	
	$(document.body).on("click",".w_wx_colse",function(){
		$mask.fadeOut(255);
		$("#cart_yj_f").fadeOut(255);
	});
	
	//菜单开启
	$(document.body).on("click", ".p_detail_more", function(){
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
	});
	
	//加入购物车
	$('#Mnav-add-cart,#Mnav-buy').bind("click", function(){
		if ($(this).attr('id')=='Mnav-buy') {
			$('#frm_buy_type').val('buy');
		}else{
			$('#frm_buy_type').val('cart');
		}
		$('#Mtop').hide();
		$mask.show();
		$cartmain.find('img').show();
		$cartmain.show().addClass("is_show");
		showSelect();
	});
	//关闭购物车
	$cartmain.on("click", ".p_cart_close", function(){
		$mask.hide();
		$('#Mtop').show();
		$cartmain.find('img').hide();
		$cartmain.removeClass("is_show").addClass("is_hide");
		setTimeout(function(){
			$cartmain.hide().removeClass("is_hide");
		},300);
	});
	
	//加入购物车选项
	$cartmain.on("click", ".p_cart_ul li", function(){
		var t = $(this);
		var style = t.data("stype");
		t.parent().find("li").removeClass("on");
		t.addClass("on");
		t.parent().find("input").val(style);
		showSelect();
	});
	
	//产品数量填写
	/* $cartmain.on("input propertychange", ".cart_inp", function(){
		var t = $(this);
		var num = t.val();
		if(!isZZ(num)){
			t.val('1');
			boxalert("请输入大于零的整数!");
			return false;
		}
		showSelect();
	}); */
	
	//产品数量减少
	$cartmain.on("click", ".btn_minus", function(){
		var t = $(this);
		var inp = t.parent().find(".cart_inp");
		var num = inp.val()*1-1;
		if(isZZ(num)){
			inp.val(num);
		}else{
			boxalert("产品数量不能再少啦!");
			return false;
		}
		showSelect();
	});
	
	//产品数量增加
	$cartmain.on("click", ".btn_plus", function(){
		var t = $(this);
		var inp = t.parent().find(".cart_inp");
		var num = inp.val()*1+1;
		inp.val(num);
		showSelect();
	});
	
	//确定加入
	$cartmain.on("click", ".p_cart_btn a", function(){
		var buy_type = $('#frm_buy_type').val();
		var item_id  = $('#frm_item_id').val();
		var item_num = $('#frm_item_num').val();
		if(!isZZ(item_num)){
			boxalert("产品数量不能再少啦!");
			return;
		}
		if ('buy'==buy_type) {
			immediate_buy(item_id, item_num);
		}
		else {
			add_to_cart(item_id, item_num, function(){
				$mask.hide();
				$cartmain.addClass("is_confirm");
				setTimeout(function(){
					$cartmain.hide().attr("class","p_cart_main");
					var cart_number = $("#cart_number").text()*1;
					var select_number = $(".cart_inp").val()*1;
					$("#cart_number").text(cart_number + select_number).show();
				},500);
			});
		}
	});

	 F.onScrollEnd(function(){
		if(!$("#li2").hasClass("check_on")){
			return;
		}
		if(!$(".remm_more").is(":visible")){
			return;
		}
		var more = $(".remm_more");
		var scrollHeight = $(".scrollArea").height();
		var sh = Math.abs(this.y);
		var windowH = $(window).height();
		var btnNav = $("#Mnav").height();
		windowH = windowH - (btnNav ? btnNav :0);
		//提前20px开始加载
		var bufferH = 20; 
		if((sh + windowH + bufferH) > scrollHeight){
			pulldata(more);
		}
	});
});

//已选择的所有属性
var spec_ids = '';
function showSelect(){
	if (typeof(showSelect.base_price)=='undefined') {
		showSelect.base_price = parseFloat($('#cart_shop_price').attr('data-base_price'));
	}
	spec_ids = '';
	var select = '', price = showSelect.base_price;
	$("#p_cart_main .p_cart_ul").each(function(){
		var li = $(this).find("li.on");
		select += '"' + li.text() + '",';
		spec_ids += li.attr('data-goods_attr_id') + ',';
		price += parseFloat(li.attr('data-attr_price')?li.attr('data-attr_price'):0);
	});
	select = select.substr(0,select.length-1);
	spec_ids = spec_ids.substr(0,spec_ids.length-1);
	$("#product_select").text(select);
	$('#cart_shop_price').text(price.toFixed(2));
}
//购买白名单
function can_buy() {
	return true;
	if (!gUser.uid) return false;
	var white_uids = [104,112,2667,7217,75497,79554,1737];
	for (var i=0; i<white_uids.length; i++) {
		if (gUser.uid==white_uids[i]) return true;
	}
	return false;
}
//立即购买
function immediate_buy(item_id, item_num) {
	if (!can_buy()) return;
	var _self = immediate_buy;
	if (typeof (_self.ajaxing)=='undefined') {
		_self.ajaxing = 0;
	}
	if (_self.ajaxing) return;
	_self.ajaxing = 1;
	F.post('<?php echo U('trade/buy')?>',{item_id:item_id,item_num:item_num,spec:spec_ids},function(ret){
		_self.ajaxing = 0;
		var gourl = '<?php echo U('trade/order/confirm')?>';
		if (gourl.lastIndexOf('?') < 0) gourl += '?';
		else gourl += '&';
		gourl += 'cart_rids='+ret.code+'&t='+ret.ts;
		window.location.href = gourl;
	});
}
//加入购物车
function add_to_cart(item_id, item_num, callback) {
	if (!can_buy()) return;
	var _self = add_to_cart;
	if (typeof (_self.ajaxing)=='undefined') {
		_self.ajaxing = 0;
	}
	if (_self.ajaxing) return;
	_self.ajaxing = 1;
	F.post('<?php echo U('trade/cart/add')?>',{item_id:item_id,item_num:item_num,spec:spec_ids},function(ret){
		_self.ajaxing = 0;
		if (ret.code > 0) {
			var old_stock = parseInt($('#stock-num').text());
			old_stock -= ret.added_num;
			$('#stock-num').text(old_stock);
			if (typeof(callback)=='function') {
				callback();
			}
		}else{
			myAlert(ret.msg);
		}
	});
}
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
	F.set_scroller(false, 100);
}
//加载商品评论列表
function loadGoodsComment(curpage, isinit, category){
	F.get('<?php echo U('item/comment/list')?>',{curpage : curpage, goods_id : <?=$item->item_id ?>, category : category},function(ret){
		var commentDom = $("#commentlist");
		if(!ret || !ret.result || ret.result.length == 0){
			$(".remm_more").html("下拉加载更多...");
			$(".remm_more").attr("curpage",1).hide();
			var _html = "<div style='margin:15px;text-align:center;'>还没有人评论哦~</div>";
			commentDom.html(_html);
			F.set_scroller(false, 100);
			return;
		}
		renderCommentNum(ret);
		var _html = "";
		for(var i = 0,len=ret.result.length; i < len; i++){
			var comment = ret.result[i];
 			_html += "<table cellspacing='0' cellpadding='0' class='evaluate_info'><tr>";
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
			if(comment.comment_reply){
				_html += "<div class=\"eval_reply\">掌柜回复："+comment.comment_reply+"</div>";
			}
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

$(function(){
	$("#activePage > .scrollArea").css('background','#fff');
	setTimeout(function(){
		$(".product_detail").html(<?=$item_desc ?>);
		F.set_scroller(false, 100);
	},0);

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
});
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
	F.set_scroller(false, 100);
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
	$(".remm_more").html("下拉加载更多...");
	var more = $(".remm_more");
	var hasnex = data.hasnexpage;
	if(hasnex){
		more.show();
		more.attr('curpage',data.curpage);
		more.attr('category',category);
	}else{
		more.hide();
	}
}

function pulldata(obj){
	$(".remm_more").html("加载中...");
	loadGoodsComment($(obj).attr("curpage"), false, $(obj).attr("category"));
}

function getMerchantRecommend(curpage, isinit){
	F.get('<?php echo U('item/merchant/recommend')?>',{curpage : curpage, merchant_uid : <?=$item->merchant_uid ?>},function(ret){
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
		F.set_scroller(false, 100);
	});
}

</script>

<?php endif;?>