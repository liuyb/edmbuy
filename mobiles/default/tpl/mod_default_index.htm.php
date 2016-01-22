<?php defined('IN_SIMPHP') or die('Access Denied');?>
<div class="mainb">
  <div class="swipe">
    <ul id="slider" class="slider">
    <li style="display:block"><a href="http://m.edmbuy.com/item/1041"><img src="/themes/mobiles/img/banner.png"></a></li>
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

  F.onScrollDownPull(function(){
window.location.reload();
	});
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
					<p class="jx_table_price"><span>￥38.00</span><span style="font-size:12px;color:#999;margin-left:5px;">(10斤/箱)</span></p>
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
					<img src="http://merchant.edmbuy.com/images/201601/goods_img/1041_P_1453009153869.jpg" style="float:right">
					<p class="list_table_tit">天山雪水养育  新疆阿克苏极品灰枣</p>
					<p class="list_table_price"><span>￥118.00</span><b>￥135.00</b></p>
				</a>
			</li>
			<li>
				<a href="http://m.edmbuy.com/item/1008" class="product_info_pc">
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
				<a href="http://m.edmbuy.com/item/1019">
					<div class="product_info_pc">
						<img src="http://merchant.edmbuy.com/images/201601/goods_img/1019_P_1452107600918.jpg" style="float:right">
						<p class="list_table_tit">佐佐果冻萌卡通包装水果味奶优果冻休闲食品690g粉猪棕熊</p>
						<p class="list_table_price"><span>￥42.00</span><b>￥53.00</b></p>
					</div>
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
		<table cellspacing="0" cellpadding="0" class="list_jx_tab" id="goods_list">	
		</table>
	</div>
	<div id="pageContainer" onclick="pulldata(this);" style="line-height: 30px;" class="pull_more_data">点击加载更多...</div>
</div>
<script>
	$().ready(function(){
		getGoodsList(1, true);
	});

	function getGoodsList(curpage, isinit){
		F.get('/default/goods', {curpage : curpage}, function(ret){
			contructGoodsHTML(ret, isinit);
			handleWhenHasNextPage(ret);
		});
	}

	function contructGoodsHTML(ret, isinit){
		if(!ret || !ret.result || !ret.result.length){
			return;
		}
		var TR = "";
		var result = ret.result;
		for(var i = 0,len=result.length; i < len; i++){
			var good = result[i];
			var mark_price = good.market_price ? good.market_price : "";
			TR += "<tr onclick=\"gotoItem('"+good.goods_id+"');\" style='cursor:pointer;'><td><img src=\""+good.goods_thumb+"\"></td><td>";
			TR += "<p class=\"jx_year_tit\">"+good.goods_name+"</p><p class=\"jx_table_price\">";
			TR += "<span>￥"+good.shop_price+"</span><b>"+mark_price+"</b></p>";
			TR += "<p class=\"jx_mail_type\">邮递 ：包邮</p></td></tr>";
		}
		$("#goods_list").append($(TR));
		F.set_scroller(false, 100);
	}

	//当还有下一页时处理下拉
	function handleWhenHasNextPage(data){
		var hasnex = data.hasnexpage;
		if(hasnex){
			$("#pageContainer").show();
			$("#pageContainer").attr('curpage',data.curpage);
		}else{
			$("#pageContainer").hide();
		}
	}
	
	function pulldata(obj){
		getGoodsList($(obj).attr("curpage"), false);
	}

	function gotoItem(goodId){
		window.location = '/item/'+goodId;
	}
</script>
