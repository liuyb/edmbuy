<?php defined('IN_SIMPHP') or die('Access Denied');?>
<div class="mainb">
  <div class="swipe">
    <ul id="slider" class="slider">
    <li><a href="http://m.edmbuy.com/item/1041"><img src="/themes/mobiles/img/banner.png"></a></li>
    <li><a href="http://m.edmbuy.com/item/1010"><img src="/themes/mobiles/img/banner04.jpg"></a></li>
    <li><a href="http://m.edmbuy.com/item/1045"><img src="/themes/mobiles/img/banner02.png"></a></li>
    <li><a href="http://m.edmbuy.com/item/1016"><img src="/themes/mobiles/img/banner03.png"></a></li>
    </ul>
    <div id="slinav" class="slinav clearfix">
    <a href="javascript:void(0);" class="hwspeed active">1</a>
    <a href="javascript:void(0);" class="hwspeed">2</a>
    <a href="javascript:void(0);" class="hwspeed">3</a>
    <a href="javascript:void(0);" class="hwspeed">4</a>
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
});
</script>

<div style="margin-top:10px;margin-bottom:10px;">
	<div class="por_list_title">
		<span class="tit_left"></span>传递爱心，帮果农
		<img src="/themes/mobiles/img/xin.png" class="tit_list_img">
	</div>
	<div class="list_jx">
		<table cellspacing="0" cellpadding="0" class="list_jx_tab">	
			<tr>
				<td style="width:110px;"><img src="/themes/mobiles/img/01_ba.png"></td>
				<td>
					<p class="jx_year_tit">万吨红富士“滞销”  帮果农寻出路   脆甜多汁38元10斤</p>
					<p class="jx_table_price"><span>￥38.00</span></p>
					<p class="jx_mail_type">快递：包邮</p>
					<button class="jx_btn_new">立即订购</button>
				</td>
			</tr>
		</table>
	</div>
</div>

<div class="por_list_title">
	<span class="tit_left"></span> 年货特价
	<span class="tit_right">益多米年货特供</span>
</div>

<div class="rice_list_tjproduct">
	<div class="list_product_info index_product_info">
		<ul>
			<li>
				<a href="http://m.edmbuy.com/item/1041" class="product_info_pc">
					<img src="http://oss.edmbuy.com/1/img/424de5c13c935311b6605579b75ae11a.jpg" style="float:right">
					<p class="list_table_tit">天山雪水养育  新疆阿克苏极品灰枣</p>
					<p class="list_table_price"><span>￥118.00</span><b>￥135.00</b></p>
				</a>
			</li>
			<li>
				<a href="http://m.edmbuy.com/item/1010" class="product_info_pc">
					<img src="http://merchant.edmbuy.com/images/201601/goods_img/1008_P_1452643503621.jpg" style="float:right">
				
					<p class="list_table_tit">浓桂园香金骏眉  高山种植纯绿色  四代工艺传承 （100g）</p>
					<p class="list_table_price"><span>￥268.00</span><b>￥500.00</b></p>
				</a>
			</li>
			<li>
				<a href="http://m.edmbuy.com/item/1014" class="product_info_pc">
					<img src="http://merchant.edmbuy.com/images/201601/goods_img/1014_P_1452906851188.jpg" style="float:right">
					<p class="list_table_tit">2016过年好礼健康养生天然硒米褚米</p>
					<p class="list_table_price"><span>￥98.00</span><b>￥128.00</b></p>
				</a>
			</li>
			<li>
				<a href="http://m.edmbuy.com/item/1012" class="product_info_pc">
					<img src="http://merchant.edmbuy.com/images/201601/goods_img/1012_P_1452796547251.jpg" style="float:right">
					<p class="list_table_tit">【抗癌之王】冷泥牌东北稻花香富硒绿色有机大米礼盒装</p>
					<p class="list_table_price"><span>￥288.00</span><b>￥328.00</b></p>
				</a>
			</li>
			<li>
				<a href="http://m.edmbuy.com/item/1042" class="product_info_pc">
					<img src="http://merchant.edmbuy.com/images/201601/goods_img/1042_P_1452906750984.jpg" style="float:right">
					<p class="list_table_tit">宁夏五谷合一粗粮杂粮米4斤装</p>
					<p class="list_table_price"><span>￥98.00</span><b>￥112.00</b></p>
				</a>
			</li>
			<li>
				<a href="http://m.edmbuy.com/item/1045" class="product_info_pc">
					<img src="http://merchant.edmbuy.com/images/201601/goods_img/1045_P_1452712649986.jpg" style="float:right">
					<p class="list_table_tit">【钟橙 绿色放心品牌】橙赣南脐橙 家庭实惠装10斤装</p>
					<p class="list_table_price"><span>￥88.00</span><b>￥108.00</b></p>
				</a>
			</li>
		</ul>
	</div><div class="clear"></div>
</div>

<div style="margin:10px 0">
<a href="http://m.edmbuy.com/item/1047"><img src="/themes/mobiles/img/y_jiu.png" style="max-width:100%;"></a>
</div>

<div style="margin:10px 0 0px 0;">
	<div class="por_list_title">
		<span class="tit_left"></span> 年货精选
	</div>
	<div class="list_jx">
		<table cellspacing="0" cellpadding="0" class="list_jx_tab">	
			<tr>
				<td><img src="/themes/mobiles/img/tea.png"></td>
				<td>
					<p class="jx_year_tit">年货特价年货特价年货特价年货特价年货货特价年货特价年货特价年货特价</p>
					<p class="jx_table_price"><span>￥88.00</span><b>￥182.00</b></p>
					<p class="jx_mail_type">邮递 ：包邮</p>
				</td>
			</tr>
			<tr>
				<td><img src="/themes/mobiles/img/tea.png"></td>
				<td>
					<p class="jx_year_tit">年货特价年货特价年货特价年货特价年货货特价年货特价年货特价年货特价</p>
					<p class="jx_table_price"><span>￥88.00</span><b>￥182.00</b></p>
					<p class="jx_mail_type">邮递 ：包邮</p>
				</td>
			</tr>
		</table>
	</div>
</div>