<?php defined('IN_SIMPHP') or die('Access Denied');?>

<style>
#Mbody {
	background-color : #fff;
}
.disabled_btn {
	background-color : #c0c0c0 !important;
}
</style>

<?php if(''!==$errmsg):?>

<div class="error"><?=$errmsg?></div>

<?php else:?>

<script id="forMtop" type="text/html">
<div class="p_header_btn">
	<span class="p_detail_back" onclick="<?=$back ?>"></span>
    <a href="<?php echo U('trade/cart/list')?>"><span class="p_detail_carts cursor"></span></a>
	<span class="carts_nums_n cursor" id="cart_number" <?php if(!$cartnum):?>style="display:none;"<?php endif;?>><?=$cartnum?></span>
</div>
</script>
<script id="forMnav" type="text/html">
<div class="p_detail_tool">
	<div class="fl cursor p_d_t1"><a href='<?=$kefu_link?>' <?php if(''!=$kefu_link&&'javascript:;'!=$kefu_link):?>target="_blank"<?php endif;?> >&nbsp;</a></div>
    <div class="fl cursor p_d_t5" onclick="window.location.href='/shop/<?=$merchant->uid ?>'"><p></p></div>
	<div class="fl cursor p_d_t2" id="collect_status" onclick="changeGoodsCollect(this);";><span class="f_num" style='display:none;' id="collect_number"></span></div>
<?php if (!$item->is_on_sale || $item->is_delete):?>
<div style="background: #c0c0c0;width: 52%;font-size:16px;color:#fff;font-weight:bold;">产品已下架</div>
<?php elseif(!$item->goods_flag):?>
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
                <div class="p_hr_select <?php if(count($attr_grp) == 0):?>hide<?php endif;?>">已选：<span id="product_select"></span></div>
			</div>
		</div>
		<div class="p_cart_bd no-bounce">
<?php foreach ($attr_grp AS $attrgrp): ?>
			<div class="p_cart_item">
				<div class="p_cart_btit"><?=$attrgrp['cat_name']?></div>
				<ul class="p_cart_ul clearfix">
	<?php $i=0; foreach ($attrgrp['attrs'] AS $attr): ?>
					<li data-id="<?=$attr['attr_id']?>"><?=$attr['attr_value']?></li>
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
		<div class="no-bounce p_cart_btn"><a href="javascript:;" id="p_cart_btn_a" <?php if(count($attr_grp) > 0):?>class='disabled_btn'<?php endif;?>>确定</a></div>
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
    <li <?php if($i++==0):?>style="display:block"<?php endif;?>><a href="javascript:;"><?php echo imgqueueload($g->img_url)?></a></li>
    <?php endforeach;?>
    </ul>
    <div id="slinav" class="slinav clearfix">
    <?php for ($i=0; $i<$gallerynum; $i++):?>
    <a href="javascript:void(0);" class="<?php if(0==$i):?>active<?php endif;?>">1</a>
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
	<div class="p_d_price"><span>￥<?=$item->shop_price?></span><b>市场价: ￥<?=$item->market_price?></b></div>
	<div class="p_d_sale"><span class="fr">销量：<?=$item->paid_goods_number?>件</span><span class="fl">快递：<?php echo ($item->shipFee > 0 ? $item->shipFee : '免运费' )?></span></div>
</div>

<?php if($referee):?>
<div class="p_detail_friend">
	<div class="fl p_friend_left"><img src="<?php echo !empty($referee->logo) ? $referee->logo : '/themes/mobiles/img/mt.png' ?>" alt=""/></div>
	<div class="fl p_friend_right">你的好友“<b><?=$referee->nickname?></b>”向你推荐！</div>
</div>
<?php endif;?>

<div class="bg_details">
	<div class="p_detail_serv clearfix">
		<ul>
			<li>七天退货</li>
			<li>担保交易</li>
			<li>48小时内发货</li>
		</ul>
	</div>
</div>

<div class="iance_list_top">
	<img src="<?=$merchant->logo ?>" class="l_top_img">
	<div class="l_top_infos">
		<p class="t_infos_nmae" style="margin-top:13px;"><?=$merchant->facename ?></p>
		<!-- <p class="t_infos_num"><i style="margin-right:10px;">3366人收藏</i><i>2255个订单</i></p> -->
	</div>
	<button class="join_top_btn" onclick="window.location.href='/shop/<?=$merchant->uid ?>'">进店逛逛  </button>
</div>

<div class="p_detail_pull">
	<!-- <h1><p>向上拖动查看图文详情</p></h1> -->
</div>

<div class="p_detail_info">
	<div class="pro_tab_check">
		<ul>
		<!--<li class="check_on" id="li1" data-loaded='Y' style="width:100%;">图文详情</li> -->
		 
			<li class="check_on" id="li1" onclick="detailTabSwitch(1)" data-loaded='Y'>图文详情</li>	
			<li class="" id="li2" onclick="detailTabSwitch(2)">宝贝评价</li>
			<li class="" id="li3" onclick="detailTabSwitch(3)">店铺推荐</li>
		
		</ul>
		<span class="pro_line1"></span>
		<span class="pro_line2"></span>
		<div class="clear"></div> 
	</div>
	<div class="pro_comment" id="list1">
		<div class="product_detail"><?=$item_desc ?></div>
	</div>
	<?php include T('mod_item_item_ext');?>
</div>

<script>
var goods_id = <?=$item->item_id ?>;
var attrmap = <?=$attrmap ?>;
$(function(){
	getGoodsCollectNum();
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
	
	//属性规格选择
	$cartmain.on("click", ".p_cart_ul li", function(){
		var t = $(this);
		var style = t.data("stype");
		t.parent().find("li").removeClass("on");
		t.addClass("on");
		t.parent().find("input").val(style);
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
	$cartmain.on("click", ".p_cart_btn a", function(){
		if($(this).hasClass("disabled_btn")){
			return;
		}
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
				$('#Mtop').show();
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
});

//已选择的所有属性
var spec_ids = '';
function showSelect(){
	if (typeof(showSelect.base_price)=='undefined') {
		showSelect.base_price = parseFloat($('#cart_shop_price').attr('data-base_price'));
	}
	spec_ids = '';
	var key = 'k_';
	var select = '', price = showSelect.base_price;
	$("#p_cart_main .p_cart_ul").each(function(){
		var li = $(this).find("li.on");
		select += '"' + li.text() + '",';
		key += li.data('id')+"_";
	});
	var attr = attrmap[key];
	if(attr){
		spec_ids = attr['goods_attr_id'];
		price = parseFloat(attr['shop_price']);
		var goods_number = attr['goods_number'];
		$("#stock-num").text(goods_number);
    	select = select.substr(0,select.length-1);
    	$("#product_select").text(select);
    	$('#cart_shop_price').text(price.toFixed(2));
    	if(goods_number && goods_number > 0){
        	$("#p_cart_btn_a").removeClass("disabled_btn");
        }else{
        	$("#p_cart_btn_a").addClass("disabled_btn");
        }
	}
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
			var attr = getAttrMapBySpecId(spec_ids);
			if(attr){
				var gn = parseInt(attr['goods_number']) - ret.added_num;
				gn = gn > 0 ? gn : 0; 
				attr['goods_number'] = gn;
			}
			if (typeof(callback)=='function') {
				callback();
			}
		}else{
			myAlert(ret.msg);
		}
	});
}

//根据规格ID反查attrmap
function getAttrMapBySpecId(spec_id){
	if(!spec_id){
		return null;
	}
	for(var key in attrmap){
		var attr = attrmap[key];
		if(attr['goods_attr_id'] == spec_id){
			return attr;
		}
	}
	return null;
}

</script>

<?php endif;?>