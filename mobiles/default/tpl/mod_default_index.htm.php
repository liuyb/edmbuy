<?php defined('IN_SIMPHP') or die('Access Denied');?>
<div class="mainb">
  <div class="swipe">
    <ul id="slider" class="slider">
    <li style="display:block"><a href='/activity'><img src="/themes/mobiles/img/banner.png"></a></li>
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
/*
  F.onScrollDownPull(function(){
window.location.reload();
	});*/
});
</script>

<div class="index_promote">
	<div class="por_list_title">
		<span class="tit_left"></span>极品脐橙，多米推荐
		<img src="/themes/mobiles/img/xin.png" class="tit_list_img" alt=""/>
	</div>
	<div class="list_jx">
		<table cellspacing="0" cellpadding="0" class="list_jx_tab">	
			<tr>
				<td style="width:110px;"><a href="http://m.edmbuy.com/item/1045" ><img src="http://oss.edmbuy.com/1/img/b1658f5d702eb8807a0b9d483eede957.jpg" alt=""></a></td>
				<td>
					<a href="http://m.edmbuy.com/item/1045" ><p class="jx_year_tit">【过年正常发货 顺丰 六省包邮】 钟橙 绿色放心品牌橙 赣南脐橙 家庭实惠装10斤装</p></a>
					<p class="jx_table_price"><span>￥88.00</span><span style="font-size:12px;color:#999;margin-left:5px;">出售140万斤</span></p>
					<p class="jx_mail_type">快递：包邮</p>
					<a href="http://m.edmbuy.com/item/1045" ><button class="jx_btn_new">立即订购</button></a>
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
	<ul class="list_product_info index_product_info">
	<?php foreach ($result as $good): 
	      $mp = $good['market_price'] ? ('￥'.$good['market_price']) : "";?>
		<li>
			<a href="/item/<?=$good['goods_id'] ?>" class="product_info_pc">
				<img src="<?php echo ploadingimg()?>" data-loaded="0" onload="imgLazyLoad(this,'<?=$good['goods_img']?>')"/>
				<p class="list_table_tit"><?=$good['goods_name'] ?></p>
				<p class="list_table_price"><span>￥<?=$good['shop_price'] ?></span><b><?=$mp ?></b></p>
			</a>
		</li>
	<?php endforeach;?>	
	</ul>
</div>

<div style="margin:10px 0">
<a href="http://m.edmbuy.com/item/1047">
<img src="<?php echo ploadingimg()?>" data-loaded="0" onload="imgLazyLoad(this,'/themes/mobiles/img/y_jiu.png')" style="max-width:100%;"/>
</a>
</div>

<div style="margin:10px 0 0px 0;">
	<div class="por_list_title">
		<span class="tit_left"></span> 年货精选
	</div>
	<ul id="goods_list" class="list_product_info index_product_info"></ul>
	<div id="pageContainer" onclick="pulldata(this);" style="line-height: 40px;" class="pull_more_data">点击加载更多...</div>
</div>
<?php require_scroll2old();?>
<script>
	$().ready(function(){
		//$(".scrollArea").css("overflow","hidden");
		getGoodsList(1, true);
		$("#goods_list").on('click','li', function(){
			var goodid = $(this).attr("data-goodid");
			gotoItem(goodid);
		});
	});

	function getGoodsList(curpage, isinit){
		F.loadingStart();
		$.get('/default/goods', {curpage : curpage}, function(ret){
			contructGoodsHTML(ret, isinit);
			handleWhenHasNextPage(ret);
			F.loadingStop();
		});
	}

	function contructGoodsHTML(ret, isinit){
		if(!ret || !ret.result || !ret.result.length){
			scrollToHistoryPosition();
			return;
		}
		var LI = "";
		var result = ret.result;
		for(var i = 0,len=result.length; i < len; i++){
			var good = result[i];
			var mark_price = good.market_price ? ("￥"+good.market_price) : "";
			var thumb = good.goods_img;
			/* TR += "<tr onclick=\"gotoItem('"+good.goods_id+"');\" style='cursor:pointer;'><td><img src=\""+good.goods_thumb+"\"></td><td>";
			TR += "<p class=\"jx_year_tit\">"+good.goods_name+"</p><p class=\"jx_table_price\">";
			TR += "<span>￥"+good.shop_price+"</span><b>"+mark_price+"</b></p>";
			TR += "<p class=\"jx_mail_type\">邮递 ：包邮</p></td></tr>"; */
			var img = "<img src=\"<?php echo ploadingimg()?>\" data-loaded=\"0\" onload=\"imgLazyLoad(this,'"+thumb+"')\" style=\"float:right\"/>"
			LI += "<li data-goodid='"+good.goods_id+"'><div class=\"product_info_pc\">"+img+"<p class=\"list_table_tit\">"+good.goods_name+"</p><p class=\"list_table_price\"><span>￥"+good.shop_price+"</span><b>"+mark_price+"</b></p></div></li>";
		}
		$("#goods_list").append($(LI));

		F.set_scroller(false, 100);
		scrollToHistoryPosition();
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

	function scrollToHistoryPosition(){
		var refer=document.referrer;
		var reg = /item\/\d+/;
		if(!reg.test(refer)){
			return;
		}
		var historyPos = Cookies.get(F.scroll_cookie_key());
		/* var c= F.scrollArea.height();
		if(Math.abs(historyPos) > c){
			historyPos = -c;
		} */
		F.set_scroller(!F.scroll2old?false:historyPos,100);
	}
</script>
