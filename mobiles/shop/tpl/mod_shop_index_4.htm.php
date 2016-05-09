<?php defined('IN_SIMPHP') or die('Access Denied');?>
<?php if ('' !== $errmsg): ?>
	<div class="error"><?= $errmsg ?></div>
<?php else: ?>

<div class="store_index_tit">
	<?php if ($shop_info->slogan):?>
	<img src="<?=$shop_info->slogan?>" />
	<?php else:?>
	<img src="/themes/mobiles/img/shop_slogan_df.png" />
	<?php endif;?>
	<img class="tit_d" src="<?=$shop_info->logo?>">
</div>
<script type="text/javascript">
	/* var t1;
	$(function(){
		var _active = 0, $_ap = $('#slinav a');

		t1 = new TouchSlider({
			id:'slider',
			auto: true,
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
	}); */
</script>

<div class="store_name_info">
	<div class="name_s_line"><span class="line_inf"><?=$shop_info->facename ?></span></div>
	<div class="store_n">
		<div class="store_ensure">
			<ul>
				<!-- <li class="flag_img">旗舰店</li><li class="ydm_img">益多米担保</li><li class="auto_img">实名认证</li><li class="direct_img">厂家直供</li> -->
				<li><img src="/themes/mobiles/img/yiduomi-inc.png" style="width:79px;margin-left:166%;"></li>
			</ul>
		</div>
		<div class="store_sell_things"><?=$shop_info->shop_desc ?></div>
	</div>
</div>

<div class="store_index_navi">
	<ul>
		<a href="javascript:showGoodsList('all');"><li id="tit_o"><img src="/themes/mobiles/img/qbsp.png"><br>全部商品</li></a>
		<a href="<?=U('trade/order/record?status=all') ?>" ><li id="tit_t"><img src="/themes/mobiles/img/ddzx.png"><br>订单中心</li></a>
		<a href="<?=U('user') ?>"><li id="tit_th"><img src="/themes/mobiles/img/hyzx.png"><br>会员中心</li></a>
		<span class="line_o" style="position: absolute; left:33%;"></span>
		<span class="line_t" style="position: absolute; left:66%;"></span>
		<div class="clear"></div>
	</ul>
</div>

<div class="store_list_co">
	<div style="padding:10px;"><p class="co_title">店长推荐</p><p class="co_see_more"><a href="javascript:;" onclick="showGoodsList('shop_recommend', 1)">查看更多</a></p></div>
	<div class="buy_store_list">
		<?php if(!empty($recommend_info)): ?>
		<ul>
			<?php foreach ($recommend_info as $good): ?>
			<a href="<?=U('item/'.$good['goods_id']) ?>">
				<li>
					<div class="store_info_pc">
						<img src="<?php echo ploadingimg()?>" data-loaded="0" onload="imgLazyLoad(this,'<?=$good['goods_img']?>')" style="float:right"/>
						<p class="store_table_tit"><?=$good['goods_name']?></p>
						<p class="store_table_price"><span>￥<?=$good['shop_price']?></span><b>￥<?=$good['market_price']?></b></p>
					</div>
				</li>
			</a>
			<?php endforeach;?>
		</ul>
			<input type="hidden" id="merchant_id" value="<?=$merchant_id?>">
		<?php else: ?>
			<div style="text-align: center;margin-top: 20px;padding-bottom: 20px;">
				还没有商品!
			</div>
		<?php endif;?>
	</div>
	<div class="clear"></div>
</div>
<?php if(!empty($goods_category)): ?>
<?php foreach ($goods_category as $category): ?>
<div class="store_list_co" style="margin-top:15px;border-bottom:1px solid #ddd;">
	<div style="padding:10px;"><p class="co_title"><b><?=$category['cat_name']?></b></p></div>
	<div class="buy_store_list">
		<?php if($category['goods'] && count($category['goods']) > 0): ?>
		<ul>
			<?php foreach ($category['goods'] as $good):?>
			<a href="<?=U('item/'.$good['goods_id']) ?>">
				<li>
					<div class="store_info_pc">
						<img src="<?php echo ploadingimg()?>" data-loaded="0" onload="imgLazyLoad(this,'<?=$good['goods_img']?>')" style="float:right"/>
						<p class="store_table_tit"><?=$good['goods_name']?></p>
						<p class="store_table_price"><span>￥<?=$good['shop_price']?></span><b>￥<?=$good['market_price']?></b></p>
					</div>
				</li>
			</a>
			<?php endforeach;?>
		</ul>
		<?php endif; ?>
	</div>
	<div class="clear"></div>
</div>
<?php if($category['goods'] && count($category['goods']) > 3):?>
<div style="text-align:center;height:30px;line-height:40px;" onclick="showGoodsList('shop_cat_id', '<?=$category['cat_id']?>');"><p class="co_title">查看更多商品</p></div>
<?php endif;?>
<?php endforeach;?>
<?php endif; ?>


<div class="tech_m">
	<img src="/themes/mobiles/img/ydm_logo.png">益多米技术支持<span style="padding-left:10px;">益多米联盟商家</span>
</div>


<a href="javascript:;"><div class="mask"></div></a>

<!-- 二维码 -->
<div class="store_wx">
	<div class="store_wx_tit">更多优惠，请关注店铺公众号</div>
	<?php if($shop_info->wxqr):?>
	<div class="store_wx_img"><img src="<?=$shop_info->wxqr?>"></div>
	<div class="store_ts">长按二维码可以保存</div>
	<?php else:?>
	<div class="store_wx_img">还没有上传二维码</div>
	<?php endif;?>
	<div class="store_wx_colse"><img src="/themes/mobiles/img/guanbi.png"></div>
</div>

<!-- 收藏 -->
<div class="store_up_t">
	<div class="up_wx_tit"><span>收藏成功</span></div>
	<div class="store_wx_img"><img src="<?=$shop_info->shop_qcode?>"></div>
	<div class="store_ts">关注益多米，超值产品任你选</div>
	<div class="store_wx_colse"><img src="/themes/mobiles/img/guanbi.png"></div>
</div>
<script id="forMnav" type="text/html">
<div class="store_bottom_comm">
    <ul>
        <?php if(isset($isCollect) && $isCollect):?>
        <li id="collectShopLi"><p class="store_up store_up_on" id="collectShop">已收藏<?php if($num):?>(<span><?=$num?></span>)<?php endif;?></p></li>
        <?php else:?>
        <li id="collectShopLi"><p class="store_up" id="collectShop">收藏<?php if($num):?>(<span><?=$num?></span>)<?php endif;?></p></li>
        <?php endif;?>
    	<li><p class="two_code">二维码</p></li>
    	<li><p class="shop_cart_store" onclick="window.location.href='/trade/cart/list'">购物车</p></li>
    </ul>
</div>
</script>
<script>
	var merchant_id = "<?=$merchant_id ?>";

	$(function(){
		show_mnav($('#forMnav').html());

		$(document).on("click","#collectShopLi",function(){
			var $store_up =$(this).find("p.store_up");
			var action = 0;
			if(!$store_up.hasClass("store_up_on")){
				$store_up.addClass("store_up_on");
				action = 1;
			}else{
				$store_up.removeClass("store_up_on");
				action = -1;
			}
			var data ={merchant_id : merchant_id, action : action};
			F.post('/shop/collect',data,function(){
				var _span = $store_up.find("span");
				var _num = 0;
				var _collect_txt = action > 0 ? "已收藏" : "收藏";
				if(_span && _span.length){
					_num = _span.text();
					_num = _num ? parseInt(_num) + parseInt(action) : 0;
				}else{
					_num = action > 0 ? 1 : 0;
				}
				_num  = _num ? _num : 0;
				if(_num){
    				$store_up.html(_collect_txt+"(<span>"+_num+"</span>)");
				}else{
					$store_up.html(_collect_txt);
				}
			});
		});
	});

	function showGoodsList(type, val){
		window.location.href="/shop/goods?merchant_id="+merchant_id+"&"+type+"="+val;
	}

	$(document).on("click",'.two_code',function(){
		/* if(!$(this).hasClass("two_code_on")){
			$(this).addClass("two_code_on");
			$(".shop_cart_store").removeClass("shop_cart_store_on");
		} */
		$(".mask").show();
		$(".store_wx").show();
	});
	/* $(document).on("click",'.shop_cart_store',function(){
		if(!$(this).hasClass("shop_cart_store_on")){
			$(this).addClass("shop_cart_store_on");
			$(".two_code").removeClass("two_code_on");
		}
	}) */

	//关闭
	$(".mask,.store_wx_colse").on("click",function(){
		$(".mask").hide();
		$(".store_wx").hide();
		$(".store_up_t").hide();
	})
</script>
<?php include T('inc/popqr');?>
<?php endif;?>