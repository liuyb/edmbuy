<?php defined('IN_SIMPHP') or die('Access Denied');?>
<div class="store_index_tit">
	<div class="swipe">
		<ul id="slider" class="slider">
				<?php foreach ($shop_carousel as $carousel): ?>
			<li style="display:block"><a href="<?=$carousel['link_url']?>"><img src="<?=$carousel['carousel_img']?>"></a></li>
				<?php endforeach;?>
		</ul>
		<div id="slinav" class="slinav clearfix">
			<?php for($i=1;$i<=count($shop_carousel);$i++){ ?>
			<a href="javascript:void(0);" <?php if($i==1);?> class='active'><?=$i?></a>
			<?php }?>
		</div>
	</div>
	<img class="tit_d" src="<?=$shop_info['logo']?>">
</div>
<script type="text/javascript">
	var t1;
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
		/*
		 F.onScrollDownPull(function(){
		 window.location.reload();
		 });*/
	});
</script>

<div class="store_name_info">
	<div class="name_s_line"><span class="line_inf"><?=$shop_info['facename']?></span></div>
	<div class="store_n">
		<div class="store_ensure">
			<ul>
				<li class="flag_img">旗舰店</li><li class="ydm_img">益多米担保</li><li class="auto_img">实名认证</li><li class="direct_img">厂家直供</li>
			</ul>
		</div>
		<div class="store_sell_things"><?=$shop_info['shop_desc']?></div>
	</div>
</div>

<div class="store_index_navi">
	<ul>
		<li id="tit_o"><a href="javascript:;"><img src="/themes/mobiles/img/qbsp.png"><br>全部商品</a></li>
		<li id="tit_t"><a href="javascript:;" ><img src="/themes/mobiles/img/ddzx.png"><br>订单中心</a></li>
		<li id="tit_th"><a href="javascript:;"><img src="/themes/mobiles/img/hyzx.png"><br>会员中心</a></li>
		<span class="line_o" style="position: absolute; left:33%;"></span>
		<span class="line_t" style="position: absolute; left:66%;"></span>
		<div class="clear"></div>
	</ul>
</div>

<div class="store_list_co">
	<div style="padding:10px;"><p class="co_title">店长推荐</p><p class="co_see_more"><a href="javascript:;">查看更多</a></p></div>
	<div class="buy_store_list">
		<?php if(!empty($recommend_info)): ?>
		<ul>
			<?php foreach ($recommend_info as $good): ?>
			<a href="javascript:;" onclick="gotoItem(<?=$good['goods_id'] ?>">
				<li>
					<div class="store_info_pc">
						<img src="<?=$good['goods_img']?>" style="float:right">
						<p class="store_table_tit"><?=$good['goods_name']?></p>
						<p class="store_table_price"><span>￥<?=$good['shop_price']?></span><b>￥<?=$good['market_price']?></b></p>
					</div>
				</li>
			</a>
			<?php endforeach;?>
		</ul>
		<?php else: ?>
			<div style="text-align: center;margin-top: 20px;padding-bottom: 20px;">
				还没有商品!
			</div>
		<?php endif;?>
	</div>
	<div class="clear"></div>
</div>

<div class="store_list_co" style="margin-top:15px;">
	<div style="padding:10px;"><p class="co_title"><?=$goods_category['cat_list']?></p><p class="co_see_more"><a href="javascript:;">查看更多</a></p></div>
	<div class="buy_store_list">
		<?php if(!empty($goods_category['category'])): ?>
		<ul>
			<?php foreach ($goods_category['category'] as $category): ?>
			<a href="javascript:;" onclick="gotoItem(<?=$good['goods_id'] ?>">
				<li>
					<div class="store_info_pc">
						<img src="<?=$category['goods_img']?>" style="float:right">
						<p class="store_table_tit"><?=$category['goods_name']?></p>
						<p class="store_table_price"><span>￥<?=$category['shop_price']?></span><b>￥<?=$category['market_price']?></b></p>
					</div>
				</li>
			</a>
			<?php endforeach;?>
		</ul>
		<?php else: ?>
				<div style="text-align: center;margin-top: 20px;padding-bottom: 20px;">
					还没有商品!
				</div>
		<?php endif;?>
	</div>
	<div class="clear"></div>
</div>

<div class="tech_m">
	<img src="/themes/mobiles/img/ydm_logo.png">益多米技术支持<span style="padding-left:10px;">益多米联盟商家</span>
</div>


<a href="javascript:;"><div class="mask"></div></a>

<!-- 二维码 -->
<div class="store_wx">
	<div class="store_wx_tit">更多优惠，请关注店铺公众号</div>
	<div class="store_wx_img"><img src="<?=$shop_info['wxqr']?>"></div>
	<div class="store_ts">长按二维码可以保存</div>
	<div class="store_wx_colse"><img src="/themes/mobiles/img/guanbi.png"></div>
</div>

<!-- 收藏 -->
<div class="store_up_t">
	<div class="up_wx_tit"><span>收藏成功</span></div>
	<div class="store_wx_img"><img src="<?=$shop_info['shop_qcode']?>"></div>
	<div class="store_ts">关注益多米，超值产品任你选</div>
	<div class="store_wx_colse"><img src="/themes/mobiles/img/guanbi.png"></div>
</div>

<script>
	$(document).on("click",'.store_up',function(){
		var $store_up =$(this);
		if(!$(this).hasClass("store_up_on")){
			$(this).addClass("store_up_on");
			$(".two_code").removeClass("two_code_on");
			$(".shop_cart_store").removeClass("shop_cart_store_on");
			var url ="/shop/collect";
			var data ={};
			F.post(url,data,function(){
				var val =$store_up.text();
				var reg =/[0-9]+/;
				var res=val.match(reg);
				$store_up.text("收藏("+res+")");
			});
		}
	});

	function gotoItem(goodId){
		window.location = '/item/'+goodId+'?back=index';
	}
	$(document).on("click",'.two_code',function(){
		if(!$(this).hasClass("two_code_on")){
			$(this).addClass("two_code_on");
			$(".shop_cart_store").removeClass("shop_cart_store_on");
		}
		$(".mask").show();
		$(".store_wx").show();
	})
	$(document).on("click",'.shop_cart_store',function(){
		if(!$(this).hasClass("shop_cart_store_on")){
			$(this).addClass("shop_cart_store_on");
			$(".two_code").removeClass("two_code_on");
		}
	})

	//关闭
	$(".mask,.store_wx_colse").on("click",function(){
		$(".mask").hide();
		$(".store_wx").hide();
		$(".store_up_t").hide();
	})
</script>
<?php include T('inc/popqr');?>
