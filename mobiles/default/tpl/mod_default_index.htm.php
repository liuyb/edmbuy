<?php defined('IN_SIMPHP') or die('Access Denied');?>
<?php if(!Users::is_logined()):?>
<span class="pre_m">
<?php echo icon_html('nologin');?>
</span>
<?php endif;?>
<div class="mainb">
  <div class="swipe">
    <ul id="slider" class="slider">
    <li><a href="<?php echo U('item/2013')?>"><img src="http://fdn.oss-cn-hangzhou.aliyuncs.com/images/tea.jpg"></a></li>
    <li><a href='<?php echo U('item/1978')?>'><img src="http://fdn.oss-cn-hangzhou.aliyuncs.com/images/cup.jpg"></a></li>
    <li><a href="<?php echo U('item/1282')?>"><img src="http://fdn.oss-cn-hangzhou.aliyuncs.com/images/hongzao.jpg"></a></li>
    </ul>
    <div id="slinav" class="slinav clearfix">
    <a href="javascript:void(0);" class="active">1</a>
    <a href="javascript:void(0);" class="">2</a>
    <a href="javascript:void(0);" class="">3</a>
     </div>
  </div>
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

<div class="sp_info">
	<div class="sp_t">	
		<p><a href='<?php echo U('item/pref/show',['type'=>'best'])?>'><img src="/themes/mobiles/img/zq/index_pref_best.png"></a></p><p><a href='<?php echo U('item/pref/show',['type'=>'moml'])?>'><img src="/themes/mobiles/img/zq/moml.png"></a></p>
	</div>

	<div class="sp_ls">
		<p><a href='<?php echo U('item/pref/show',['type'=>'teawine'])?>'><img src="/themes/mobiles/img/zq/teawine.png"></a></p><p><a href='/item/pref/show?type=fruit'><img src="/themes/mobiles/img/zq/fruit.png"></a></p><p><a href='<?php echo U('item/pref/show',['type'=>'food'])?>'><img src="/themes/mobiles/img/zq/food.png"></a></p><p><a href='<?php echo U('item/pref/show',['type'=>'clothing'])?>'><img src="/themes/mobiles/img/zq/cloth.png"></a></p>
	</div>
</div>


<div class="clear"></div>

<?php if(isset($limit_goods) && count($limit_goods) > 0):?>
<div class="time_buy">
	<div class="time_b_tit">
	<!-- 
		<div class="b_tit_l">限时抢购</div>
		<div class="b_tit_r">
			<span class="r_f">距离结束</span><span id="t_h"></span> : <span id="t_m"></span> : <span id="t_s"></span>
		</div>
	 -->
	 <img src="/themes/mobiles/img/home/homep_xsqg.png" />
	 <p><span id="t_h"></span> : <span id="t_m"></span> : <span id="t_s"></span></p>
	</div>
	<div class="time_b_info">
		<table cellspacing="0" cellpadding="0" class="time_b_tab">	
			<?php foreach ($limit_goods as $good): ?>
			<tr onclick="gotoItem(<?=$good['goods_id'] ?>);">
				<td style="width:110px;"><img src="<?php echo ploadingimg()?>" data-loaded="0" onload="imgLazyLoad(this,'<?=$good['goods_img']?>')"/></td>
				<td>
					<p class="b_tab_tit"><?=$good['goods_name'] ?></p>
					<p class="b_tab_price"><span class="p_color">￥<?=$good['shop_price'] ?></span><span class="p_num">仅剩<span class="p_ln"><?=$good['goods_number'] ?></span>份</span></p>
				</td>
			</tr>
			<?php endforeach;?>
		</table>
	</div>
</div>
<?php endif;?>

<div class="fk_buy">
	<div class="fk_b_tit">
		<img src="/themes/mobiles/img/home/homep_ttfq.png">
	</div>
	<div class="fk_b_list">
		<ul>
			<li><button class="b_on" data-cat='eat'>吃的</button></li>
			<li><button data-cat='drink'>喝的</button></li>
			<li><button data-cat='wear'>穿的</button></li>
			<li><button data-cat='use'>用的</button></li>
		</ul>
		<div class="clear"></div>
	</div>
	<div class="time_b_info">
		<table id="goods_list" cellspacing="0" cellpadding="0" class="time_b_tab time_b_tab_list">	
		</table>
	</div>
</div>

<div class="click_more" onclick="pulldata(this);">点击加载更多...</div>
<script>
	var promote_endtime;
	var $glist;
	$(function(){
		$glist = $('#goods_list');
		//图片大小
		//ajust_div_size();
		
		//$(".scrollArea").css("overflow","hidden");
		//显示吃喝穿商品列表
		getGoodsList(1, true);

		//监听点击继续购买
		$glist.on('click','tr', function(){
			var goodid = $(this).attr("data-goodid");
			if(!goodid){
				return;
			}
			gotoItem(goodid);
		});

		//吃喝穿切换
		$(".fk_b_list button").on("click",function(){
			if($(this).hasClass("b_on")){
				return;
			}
			$(".fk_b_list button").removeClass("b_on");
			$(this).addClass("b_on");
			getGoodsList(1, false);
			return false;
		});
		//倒计时处理
		<?php if(isset($package)):?>
			promote_endtime = '<?=date('Y-m-d H:i:s', $package['end_time']) ?>';
			setInterval(GetRTime,0);
		<?php endif?>
	});

	function ajust_div_size(){
		var line_height = $(".sp_t").height();
		var line_heightl = $(".sp_ls").height();
		$(".line1").height(line_height);
		$(".line2,.line3,.line4").height(line_heightl);
	}

	function getGoodsList(curpage, isinit){
		//F.loadingStart();
		var category = "";
		if(isinit && referIsFromItem()){
			category = Cookies.get('index-category');
			if(!category){
				category = 'eat';
			}else{
				$(".fk_b_list button").removeClass("b_on");
				$(".fk_b_list").find("button[data-cat='"+category+"']").first().addClass("b_on");
			}
		}else{
    		$(".fk_b_list button").each(function(){
    			var BTN = $(this);
    			if(BTN.hasClass("b_on")){
    				category = BTN.attr('data-cat');
    			}
    		});
		}
		if(!category){
			return;
		}
		F.get('/default/goods', {curpage : curpage, category : category}, function(ret){
			$glist.empty();
			contructGoodsHTML(ret, isinit);
			handleWhenHasNextPage(ret);
			//设置
			Cookies.set('index-category',category,{path:'/'});
			//F.loadingStop();
		});
	}

	function contructGoodsHTML(ret, isinit){
		if(!ret || !ret.result || !ret.result.length){
			var emptyTR = "<tr><td style=\"width:95px;text-align:center;line-height:30px;\">还没有商品！<br/>招商座机：0755-26418979</td></tr>";
			handleGoodsListAppend(emptyTR);
			return;
		}
		var TR = "";
		var result = ret.result;
		for(var i = 0,len=result.length; i < len; i++){
			var good = result[i];
			var spread = good.market_price - good.shop_price;
			spread = spread ? spread.toFixed(2) :0.00;
			var goodimg = good.goods_img;
			TR += "<tr data-goodid='"+good.goods_id+"'><td style=\"width:95px;\"><img src=\"<?php echo ploadingimg()?>\" data-loaded=\"0\" onload=\"imgLazyLoad(this,'"+goodimg+"')\"></td><td>";
			TR += "<p class=\"tab_t_f\">"+good.goods_name+"</p><p class=\"tab_t_i\">"+good.goods_brief+"</p><p class=\"tab_t_price\">";
			TR += "<span>特价：￥"+good.shop_price+"</span></p>";
			TR += "<p class=\"tab_t_type\"><span class=\"type_l_css\">比原价省</span><span class=\"type_r_css\">"+spread+"元</span></p>";
			TR += "<button class=\"jx_btn_new\">立即购买</button></td></tr>";
		}
		handleGoodsListAppend(TR);
	}

	function handleGoodsListAppend(TR){
		//$glist.append($(TR));
		$glist.append(TR);
		//$glist.html(TR);
		//F.set_scroller(false, 100);
		//scrollToHistoryPosition();
	}

	//当还有下一页时处理下拉
	function handleWhenHasNextPage(data){
		var hasnex = data.hasnexpage;
		if(hasnex){
			$(".click_more").show();
			$(".click_more").attr('curpage',data.curpage);
		}else{
			$(".click_more").hide();
		}
	}
	
	function pulldata(obj){
		getGoodsList($(obj).attr("curpage"), false);
	}

	function gotoItem(goodId){
		window.location = '/item/'+goodId+'?back=index&spm=<?=isset($_GET['spm']) ? $_GET['spm'] : '' ?>';
	}

	function scrollToHistoryPosition(){
		if(!referIsFromItem()){
			return;
		}
		var historyPos = Cookies.get(F.scroll_cookie_key());
		/* var c= F.scrollArea.height();
		if(Math.abs(historyPos) > c){
			historyPos = -c;
		} */
		F.set_scroller(!F.scroll2old?false:historyPos,100);
	}

	//来自商品页面的返回需要读取cookie
	function referIsFromItem(){
		var refer=document.referrer;
		var reg = /item\/\d+/;
		if(!reg.test(refer)){
			return false;
		}
		return true;
	}

	<!-- 倒计时 -->
	function GetRTime(){ 
		if(!promote_endtime){
			return;
		}
		//兼容IOS的时间处理方式
		var EndTime= new Date(promote_endtime.replace(/\s+/g, 'T'));
		var NowTime = new Date();
		var t =EndTime.getTime() - NowTime.getTime();
		var h=Math.floor((t/(1000*60*60*24))*24);
		var m=Math.floor(t/1000/60%60);
		var s=Math.floor(t/1000%60);
		
			if(h<10){
				$("#t_h").html("0"+ h);
			}
			else{
				$("#t_h").html(h);
			}
			
			if(m<10){
				$("#t_m").html("0"+ m );
			}
			else{
				$("#t_m").html(m);
			}
			if(s<10){
				$("#t_s").html("0"+s);
			}
			else{
				$("#t_s").html(s);
			}
		
	}
	setTimeout("ajust_div_size()",0);
</script>
<?php include T('inc/popqr');?>
