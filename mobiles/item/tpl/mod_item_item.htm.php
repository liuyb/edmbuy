<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if(''!==$errmsg):?>

<div class="error"><?=$errmsg?></div>

<?php else:?>

<script id="forMtop" type="text/html">
<div class="p_header_btn">
	<span class="p_detail_back cursor"></span>
	<span class="p_detail_more cursor"></span>
</div>
<div class="p_detail_menulist cursor">
	<span class="p_detail_jian"></span>
	<ul class="p_detail_menu">
		<li class="p_menu_sy"><a href="#">首页</a></li>
		<li class="p_menu_hhr"><a href="#">米商</a></li>
		<li class="p_menu_hy"><a href="#">会员</a></li>
	</ul>
</div>
</script>
<script id="forMnav" type="text/html">
<div class="p_detail_tool">
	<div class="fl cursor p_d_t1"><a href="<?=$kefu_link?>" <?php if(''!=$kefu_link&&'javascript:;'!=$kefu_link):?>target="_blank"<?php endif;?> >&nbsp;</a></div>
	<a href="<?php echo U('trade/cart/list')?>">
		<div class="fl cursor p_d_t2"><span class="f_num" id="cart_number" style="display:none;">0</span></div>
	</a>
	<div class="fl cursor p_d_t3" id="Mnav-add-cart">加入购物车</div>
	<div class="fl cursor p_d_t4" id="Mnav-buy">立即购买</div>
</div>
</script>
<script id="forBody" type="text/html">
<div class="mask_menu cursor"></div>

<div class="cursor p_detail_button p_detail_follow"></div>
<div class="cursor p_detail_button p_detail_gotop" id="Mgotop"></div>

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
					<li <?php if(0===$i): ?>class="on"<?php endif;?> data-style="" data-attr_id="<?=$attr['attr_id']?>" data-attr_price="<?=$attr['attr_price']?>"><?=$attr['attr_value']?></li>
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
		<div class="cursor no-bounce p_cart_btn">确定</div>
	</div>
</div>

<!-- 二维码 -->
<div class="cart_wx no-bounce" id="cart_wx">
	<div class="c_wx_tit">益多米</div>
	<div class="c_wx_img"><img src="/themes/mobiles/img/ydm.png"></div>
	<div class="c_wx_ewm">长按识别二维码，关注益多米</div>
	<div class="w_wx_colse"><img src="/themes/mobiles/img/gub.png"></div>
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
<script>show_mtop($('#forMtop').html());
show_mnav($('#forMnav').html());
append_to_body($('#forBody').html());
</script>

<div class="mainb">
  <div class="swipe">
    <ul id="slider" class="slider">
    <?php foreach ($galleries AS $g):?>
    <li><a href="javascript:;"><img src="<?=$g->img_url?>" alt="" /></a></li>
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
	  picset.push($(this).attr('src'));
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
	<div class="p_d_price"><span>￥<?=$item->shop_price?></span><b>￥<?=$item->market_price?></b><?php if($user->uid && $user->level>0):?><span class="product_zyj" id="product_zyj">总佣金：￥<?=$item->commision_show?></span><?php endif;?></div>
	<div class="p_d_sale"><span class="fr">销量：<?=$item->paid_order_count?>笔</span><span class="fl">快递：免运费</span></div>
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
	<div class="p_i_title"><span>图文详情</span></div>
	<div class="product_detail" id="product_detail"><?=$item->item_desc?></div>
</div>

<script>
$(document).ready(function(){

	//缓存对象
	var $mask     = $("#bodymask");
	var $cartmain = $('#p_cart_main');
	var $cartwx   = $('#cart_wx');
	
	$('#Mgotop').on("click",function(){
		F.set_scroller(true);
	});
	
	$('#product_zyj').on("click",function(){
		$mask.fadeIn(255);
		$("#cart_yj_f").fadeIn(255);
	});
	
	$(document.body).on("click",".w_wx_colse",function(){
		$mask.fadeOut(255);
		$("#cart_yj_f").fadeOut(255);
	});
	
	//弹出二维码
	$(document.body).on("click",".p_detail_follow",function(){
		$mask.fadeIn(300);
		$cartwx.fadeIn(300);
	});
	$cartwx.on("click",".w_wx_colse",function(){
		$mask.hide();
		$cartwx.addClass("is_confirmwx");
		setTimeout(function(){
			$cartwx.hide().removeClass("is_confirmwx");
		},500);
	});
	
	//菜单开启
	$(document.body).on("click", ".p_detail_more", function(){
		$(".p_detail_menulist,.mask_menu").show();
	});
	//菜单关闭
	$(document.body).on("click", ".mask_menu", function(){
		$(".p_detail_menulist,.mask_menu").hide();
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
	$cartmain.on("input propertychange", ".cart_inp", function(){
		var t = $(this);
		var num = t.val();
		if(!isZZ(num)){
			t.val('1');
			boxalert("请输入大于零的整数!");
			return false;
		}
		showSelect();
	});
	
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
	$cartmain.on("click", ".p_cart_btn", function(){
		var buy_type = $('#frm_buy_type').val();
		var item_id  = $('#frm_item_id').val();
		var item_num = $('#frm_item_num').val();
		if ('buy'==buy_type) {
			immediate_buy(item_id, item_num);
		}
		else {
			$mask.hide();
			$cartmain.addClass("is_confirm");
			setTimeout(function(){
				$cartmain.hide().attr("class","p_cart_main");
				var cart_number = $("#cart_number").text()*1;
				var select_number = $(".cart_inp").val()*1;
				$("#cart_number").text(cart_number + select_number).show();
			},500);
		}
	});

});

//已选择的所有属性
function showSelect(){
	if (typeof(showSelect.base_price)=='undefined') {
		showSelect.base_price = parseFloat($('#cart_shop_price').attr('data-base_price'));
	}
	var select = '', price = showSelect.base_price;
	$("#p_cart_main .p_cart_ul").each(function(){
		var li = $(this).find("li.on");
		select += '"' + li.text() + '",';
		price += parseFloat(li.attr('data-attr_price'));
	});
	var length = select.length;
	select = select.substr(0,length-1);
	$("#product_select").text(select);
	$('#cart_shop_price').text(price.toFixed(2));
}

//立即购买
function immediate_buy(item_id, item_num) {
	if (gUser.uid != 104 && gUser.uid != 112 && gUser.uid != 2667) return;
	if (typeof (immediate_buy.ajaxing)=='undefined') {
		immediate_buy.ajaxing = 0;
	}
	if (immediate_buy.ajaxing) return;
	immediate_buy.ajaxing = 1;
	F.post('<?php echo U('trade/buy')?>',{item_id:item_id,item_num:item_num},function(ret){
		immediate_buy.ajaxing = 0;
		var gourl = '<?php echo U('trade/order/confirm')?>';
		if (gourl.lastIndexOf('?') < 0) gourl += '?';
		else gourl += '&';
		gourl += 'cart_rids='+ret.code+'&t='+ret.ts;
		window.location.href = gourl;
	});
}
//加入购物车
function add_to_cart(item_id, item_num) {
	var _self = add_to_cart;
	if (gUser.uid != 104 && gUser.uid != 112 && gUser.uid != 2667) return;
	if (typeof (_self.ajaxing)=='undefined') {
		_self.ajaxing = 0;
	}
	if (_self.ajaxing) return;
	_self.ajaxing = 1;
	F.post('<?php echo U('trade/cart/add')?>',{item_id:item_id,item_num:item_num},function(ret){
		_self.ajaxing = 0;
		if (ret.code > 0) {
			var old_stock = parseInt($('#stock-num').text());
			old_stock -= ret.added_num;
			$('#stock-num').text(old_stock);
			myAlert(ret.msg);
		}else{
			myAlert(ret.msg);
		}
	});
}

$(function(){
	$("#activePage > .scrollArea").css('background','#fff')
})
</script>

<?php endif;?>