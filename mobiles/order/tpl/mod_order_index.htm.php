<?php defined('IN_SIMPHP') or die('Access Denied');?>

<script id="forTopnav" type="text/html">
<div class="header h_order" style="padding:0 0 0 20px;">
	<ul class="header_order">
		<li class="header_order_on">全部</li>
		<li>待付款</li>
		<li>待发货</li>
		<li>待收货</li>
		<a href="<?php echo U('user')?>" class="back">&nbsp;</a>
	</ul>
</div>
</script>
<script>show_topnav($('#forTopnav').html())</script>

<div class="order_list">
	<div class="order_title">
		<span class="tit_logo"><img src="/themes/mobiles/img/shop_logo.png"></span>
		<span class="title_name">彭大师茶叶店</span>
		<span class="tit_type">代付款</span>
	</div>
	<a href="order_info.html">
		<div class="order_info">
			<table cellspacing="0" cellpadding="0" class="order_info_tab">
				<tr>
					<td class="info_td1" >
						<img src="/themes/mobiles/img/pro.jpg">
					</td>
					<td class="info_td2">
						<p class="info_name">鼎折覆餗士大夫地方士大夫士大夫放到地方</p>
						<p class="ifno_etalon">颜色：蓝色；尺码：45</p>
					</td>
					<td class="info_td3">
						<p class="info_price">￥138</p>
						<p class="info_num">x1</p>	
					</td>
				</tr>
			</table>
		</div>
	</a>
	<div class="order_price">
		<p>
			<span style="padding-right:12px;">共<span>1</span>件商品 </span>
			合计：￥ <span class="price_p">138</span> （含运费：￥0.00）
		</p>
	</div>
	<div class="order_serve order_right">
		<button class="order_but_l del_success_order">删除订单</button>
		<button class="order_but_r buyer_money">付款</button>
	</div>
</div>

<div class="order_list">
	<div class="order_title">
		<span class="tit_logo"><img src="/themes/mobiles/img/shop_logo.png"></span>
		<span class="title_name">彭大师茶叶店</span>
		<span class="tit_type">待发货</span>
	</div>
	<a href="order_info.html">
		<div class="order_info">
			<table cellspacing="0" cellpadding="0" class="order_info_tab">
				<tr>
					<td class="info_td1" >
						<img src="/themes/mobiles/img/pro.jpg">
					</td>
					<td class="info_td2">
						<p class="info_name">鼎折覆餗士大夫地方士大夫士大夫放到地方</p>
						<p class="ifno_etalon">颜色：蓝色；尺码：45</p>
					</td>
					<td class="info_td3">
						<p class="info_price">￥138</p>
						<p class="info_num">x1</p>	
					</td>
				</tr>
			</table>
		</div>
	</a>
	<div class="order_price">
		<p>
			<span style="padding-right:12px;">共<span>1</span>件商品 </span>
			合计：￥ <span class="price_p">138</span> （含运费：￥0.00）
		</p>
	</div>
	<div class="order_serve order_right">
		<button class="order_but_r ferund_money">退款</button>
	</div>
</div>

<div class="order_list">
	<div class="order_title">
		<span class="tit_logo"><img src="/themes/mobiles/img/shop_logo.png"></span>
		<span class="title_name">彭大师茶叶店</span>
		<span class="tit_type">代收货</span>
	</div>
	<a href="order_info.html">
		<div class="order_info">
			<table cellspacing="0" cellpadding="0" class="order_info_tab">
				<tr>
					<td class="info_td1" >
						<img src="/themes/mobiles/img/pro.jpg">
					</td>
					<td class="info_td2">
						<p class="info_name">鼎折覆餗士大夫地方士大夫士大夫放到地方</p>
						<p class="ifno_etalon">颜色：蓝色；尺码：45</p>
					</td>
					<td class="info_td3">
						<p class="info_price">￥138</p>
						<p class="info_num">x1</p>	
					</td>
				</tr>
			</table>
		</div>
	</a>
	<div class="order_price">
		<p>
			<span style="padding-right:12px;">共<span>1</span>件商品 </span>
			合计：￥ <span class="price_p">138</span> （含运费：￥0.00）
		</p>
	</div>
	<div class="order_serve order_right">
		<button class="order_but_l log_info">物流信息</button>
		<button class="order_but_r ok_goods">确认收货</button>
	</div>
</div>

<div class="order_list">
	<div class="order_title">
		<span class="tit_logo"><img src="/themes/mobiles/img/shop_logo.png"></span>
		<span class="title_name">彭大师茶叶店</span>
		<span class="tit_success">交易成功</span>
	</div>
	<a href="order_info.html">
		<div class="order_info">
			<table cellspacing="0" cellpadding="0" class="order_info_tab">
				<tr>
					<td class="info_td1" >
						<img src="/themes/mobiles/img/pro.jpg">
					</td>
					<td class="info_td2">
						<p class="info_name">鼎折覆餗士大夫地方士大夫士大夫放到地方</p>
						<p class="ifno_etalon">颜色：蓝色；尺码：45</p>
					</td>
					<td class="info_td3">
						<p class="info_price">￥138</p>
						<p class="info_num">x1</p>	
					</td>
				</tr>
			</table>
		</div>
	</a>
	<div class="order_price">
		<p>
			<span style="padding-right:12px;">共<span>1</span>件商品 </span>
			合计：￥ <span class="price_p">138</span> （含运费：￥0.00）
		</p>
	</div>
	<div class="order_serve order_right">
		<button class="order_but_l del_success_order">删除订单</button>
		<button class="order_but_l return_order">退货</button>
		<button class="order_but_r again_buy">再次购买</button>
	</div>
</div>

<div class="order_list">
	<div class="order_title">
		<span class="tit_logo"><img src="/themes/mobiles/img/shop_logo.png"></span>
		<span class="title_name">彭大师茶叶店</span>
		<span class="tit_close">交易关闭</span>
	</div>
	<a href="order_info.html">
		<div class="order_info">
			<table cellspacing="0" cellpadding="0" class="order_info_tab">
				<tr>
					<td class="info_td1" >
						<img src="/themes/mobiles/img/pro.jpg">
					</td>
					<td class="info_td2">
						<p class="info_name">鼎折覆餗士大夫地方士大夫士大夫放到地方</p>
						<p class="ifno_etalon">颜色：蓝色；尺码：45</p>
					</td>
					<td class="info_td3">
						<p class="info_price">￥138</p>
						<p class="info_num">x1</p>	
					</td>
				</tr>
			</table>
		</div>
	</a>
	<div class="order_price">
		<p>
			<span style="padding-right:12px;">共<span>1</span>件商品 </span>
			合计：￥ <span class="price_p">138</span> （含运费：￥0.00）
		</p>
	</div>
	<div class="order_serve order_right">
		<button class="order_but_l del_success_order">删除订单</button>
		<button class="order_but_r goon_buy">继续购买</button>
	</div>
</div>

<!-- 遮罩层 -->
<a href="javascript:;"><div class="mask"></div></a>

<!-- 确认收货弹出框 -->
<div class="order_ok_goods goods_order">
	<div class="goods_f">是否确认收货？</div>
	<div class="goods_btn">
		<button type="button" class="order_comm orderYes">确认收货</button>
		<button type="button" class="order_comm orderNo">暂不确认</button>
	</div>
</div>

<!-- 客服弹出框 -->
<div class="order_ok_goods kf_order">
	<div class="goods_f" style="margin:10px 5px;">
		<p style="margin-top:20px">客服电话:13522341625</p>
		<p style="font-size:14px;color:#333;margin-top:15px;">与商家客服沟通后，为您安排退款。</p>
	</div>
	<div class="goods_btn">
		<button type="button" class="order_comm ferYes"><a href="tel:400-0755-888" style="color:#ff6d14">拨打电话</a></button>
		<button type="button" class="order_comm ferNo">暂不退款</button>
	</div>
</div>

<!-- 删除订单弹出框 -->
<div class="order_ok_goods del_order">
	<div class="goods_f">是否确认删除订单？</div>
	<div class="goods_btn">
		<button type="button" class="order_comm delYes">确认删除</button>
		<button type="button" class="order_comm delNo">暂不删除</button>
	</div>
</div>

