<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if(''!==$errmsg):?>

<div class="error"><?=$errmsg?></div>

<?php else:?>

<script id="forGTop" type="text/html">
<div class="header p_header_bar">
	<div class="p_header_bar_main">
		<a href="javascript:history.back();" class="back"></a>
		<a href="javascript:;" class="ph_btn"></a>
	</div>
</div>

<div class="p_header_btn">
	<span class="p_detail_back cursor"></span>
	<span class="p_detail_more cursor"></span>
</div>

<div class="p_detail_menulist cursor">
	<span class="p_detail_jian"></span>
	<ul class="p_detail_menu">
		<li class="p_menu_sy"><a href="#">首页</a></li>
		<li class="p_menu_hhr"><a href="#">合伙人</a></li>
		<li class="p_menu_hy"><a href="#">会员</a></li>
	</ul>
</div>
</script>
<script>show_gtop($('#forGTop').html())</script>
<script id="forNav" type="text/html">
<div class="p_detail_tool">
	<div class="fl cursor p_d_t1"><p></p></div>
	<div class="fl cursor p_d_t2"><span class="f_num" id="cart_number" style="display: none;">0</span></div>
	<div class="fl cursor p_d_t3">加入购物车</div>
	<div class="fl cursor p_d_t4">立即购买</div>
</div>
</script>
<script>show_nav($('#forNav').html())</script>
<script id="forBody" type="text/html">
<div class="mask_menu cursor"></div>

<div class="cursor p_detail_button p_detail_follow"></div>
<div class="cursor p_detail_button p_detail_gotop"></div>

<div class="mask" style="display: ;"></div>
<div class="p_cart_main" style="display: ;">
	<div class="p_cart_content">
		<div class="p_cart_hd clearfix">
			<div class="p_cart_hl fl">
				<img src="/themes/mobiles/img/pro.jpg">
			</div>
			<div class="p_cart_hr fl">
				<div class="p_hr_price">￥138</div>
				<div class="p_hr_stock">库存30件</div>
				<div class="p_hr_select">已选：<span id="product_select">"蓝色","45"</span></div>
			</div>
		</div>
		<div class="p_cart_bd">
			<div class="p_cart_item">
				<div class="p_cart_btit">颜色</div>
				<ul class="p_cart_ul clearfix">
					<li data-style="" class="on">蓝色</li>
					<li data-style="">红色</li>
					<li data-style="">酒红色</li>
					<li data-style="">卡其色</li>
					<li data-style="">黑色</li>
					<li data-style="">深蓝色</li>
					<li data-style="">米黄色</li>
					<input type="hidden">
				</ul>
			</div>
			<div class="p_cart_item">
				<div class="p_cart_btit">尺码</div>
				<ul class="p_cart_ul clearfix">
					<li data-style="" class="on">36</li>
					<li data-style="">37</li>
					<li data-style="">38</li>
					<li data-style="">39</li>
					<li data-style="">40</li>
					<li data-style="">41</li>
					<input type="hidden">
				</ul>
			</div>
			<div class="cart_p_price p_cart_number">
				<b>购买数量</b>
				<p>
					<span class="cart_btn btn_minus"></span>
					<span><input type="text" class="cart_inp" value="1"></span>
					<span class="cart_btn btn_plus"></span>
				</p>
			</div>
		</div>
		<span class="cursor p_cart_close"></span>
		<div class="cursor p_cart_btn">确定</div>
	</div>
</div>

<!-- 遮罩层 -->
<a href="javascript:;"><div class="mask"></div></a>

<!-- 二维码 -->
<div class="cart_wx">
	<div class="c_wx_tit">益多米</div>
	<div class="c_wx_img"><img src="/themes/mobiles/img/ydm.png"></div>
	<div class="c_wx_ewm">长按识别二维码，关注益多米</div>
	<div class="w_wx_colse"><img src="/themes/mobiles/img/gub.png"></div>
</div>
</script>
<script>append_to_body($('#forBody').html())</script>

<div class="mainb">
  <div class="swipe">
    <ul id="slider" class="slider">
    <?php foreach ($galleries AS $g):?>
    <li><a href=""><img src="<?=$g->img_url?>" alt="" /></a></li>
    <?php endforeach;?>
    </ul>
    <div id="pagenavi" class="pagenavi clearfix">
    <?php for ($i=0; $i<$gallerynum; $i++):?>
    <a href="javascript:void(0);" class="<?php if(0==$i):?>active<?php endif;?>">1</a>
    <?php endfor;?>
     </div>
  </div>
</div>
<script type="text/javascript">
var t1;
$(function(){
	var _active = 0, $_ap = $('#pagenavi a');
  
  t1 = new TouchSlider({
     id:'slider',
     speed:600,
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
});
</script>
<!-- 
<div class="p_detail_banner">
	<div class="swiper-container" id="product_banner">
		<div class="swiper-wrapper">
			<div class="swiper-slide">
				<img src="/themes/mobile/img/pro.jpg">
			</div>
			<div class="swiper-slide">
				<img src="/themes/mobile/img/share_01.jpg">
			</div>
		</div>
		<div class="pagination" id="banner_pagination"></div>
	</div>
</div>
-->
<div class="p_detail_msg">
	<div class="p_d_title"><?=$item->item_name?></div>
	<div class="p_d_intro"><?=$item->item_brief?></div>
	<div class="p_d_price"><span>￥<?=$item->shop_price?></span><b>￥<?=$item->market_price?></b></div>
	<div class="p_d_sale"><span class="fr">销量：<?=$item->paid_order_count?>笔</span><span class="fl">快递：免运费</span></div>
</div>

<div class="p_detail_friend">
	<div class="fl p_friend_left"><img src="/themes/mobiles/img/mt.png"></div>
	<div class="fl p_friend_right">你的好友“<b>彭先灿</b>”向你推荐！</div>
</div>

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
	<div class="product_detail"><?=$item->item_desc?></div>
</div>

<script>
//弹出二维码
$(".p_detail_follow").on("click",function(){
	$(".cart_wx").fadeIn(300);
	$(".mask").fadeIn(300);
})
//goto顶部
$(".p_detail_gotop").on("click", function(){
	F.set_scroller(true);
});
//加入购物车
var main_h = $(".p_cart_main").height() - 176;
$(".p_cart_bd").height(main_h);

//拖动字样
var p_length = ($(".p_detail_pull p").text().length + 1) * 13;
$(".p_detail_pull p").css("width",p_length);

//菜单开启
$(".p_detail_more,.ph_btn").on("click", function(){
	$(".p_detail_menulist,.mask_menu").show();
});

//菜单关闭
$(".mask_menu").on("click", function(){
	$(".p_detail_menulist,.mask_menu").hide();
});

//加入购物车
$(".p_d_t3").on("click", function(){
	$(".mask").show();
	$(".p_cart_main").show().addClass("is_show");
	showSelect();
});

//购物车关闭
$(".p_cart_close,.mask,.w_wx_colse").on("click", function(){
	$(".cart_wx").fadeOut(300);
	$(".mask").hide(300);
	$(".p_cart_main").addClass("is_hide");
	setTimeout(function(){
		$(".p_cart_main").hide().attr("class","p_cart_main");
	},500);
});

//加入购物车选项
$(".p_cart_ul li").on("click", function(){
	var t = $(this);
	var style = t.data("stype");
	t.parent().find("li").removeClass("on");
	t.addClass("on");
	t.parent().find("input").val(style);
	showSelect();
});

//产品数量填写
$(".cart_inp").on("input propertychange", function(){
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
$(".btn_minus").on("click", function(){
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
$(".btn_plus").on("click", function(){
	var t = $(this);
	var inp = t.parent().find(".cart_inp");
	var num = inp.val()*1+1;
	inp.val(num);
	showSelect();
});

//已选择的所有属性
function showSelect(){
	var select = '';
	$(".p_cart_ul").each(function(){
		var li = $(this).find("li.on").text();
		select += '"' + li + '",';
	});
	var length = select.length;
	select = select.substr(0,length-1);
	$("#product_select").text(select);
}

//确定加入
$(".p_cart_btn").on("click", function(){
	$(".mask").hide();
	$(".p_cart_main").addClass("is_confirm");
	setTimeout(function(){
		$(".p_cart_main").hide().attr("class","p_cart_main");
		var cart_number = $("#cart_number").text()*1;
		var select_number = $(".cart_inp").val()*1;
		$("#cart_number").text(cart_number + select_number).show();
	},500);
});
</script>

<script>
/* 详情样式强制转换 */
$(".product_detail *").css({"max-width":"100%"});
$(".product_detail table").css({"width":"100%"});
$(".product_detail img").css({"height":"auto"});
$(".product_detail img").parent().css({"text-indent":"0"});
$(".product_detail iframe").css({"width":"100%","border":"0"});
</script>

<?php endif;?>