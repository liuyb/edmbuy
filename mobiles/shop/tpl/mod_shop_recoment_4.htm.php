<?php defined('IN_SIMPHP') or die('Access Denied');?>

<div class="title_common_top">
	<div class="all_goods_title">
		<div class="tit_left"><img src="/themes/mobiles/img/fanhui.png"></div>
		<div class="tit_center"><button class="search" id="search"></button><input type="text" value="" placeholder="搜索" id="goods_name"></div>
		<div class="tit_right"><img src="/themes/mobiles/img/fenlei.png"></div>
	</div>

	<div class="all_goods_type">
		<ul>
			<li id="li1" class="li_all_on" onclick="f(1)" data-type="new_asc">最新</li>
			<li id="li2" onclick="f(2)" >销量</li>
			<li id="li3" onclick="f(3)" >售价</li>
			<div class="clear"></div>
		</ul>
	</div>
</div>

<div class="all_goods_common" style="padding: 0" id="list1">
	<div class="newest_goods" style="padding: 0">
		<ul id="recoment_list">
		</ul>
		<div class="clear"></div>
	</div>
</div>

<div class="click_more_all"  onclick="pulldata(this);"><span class="more_all_things">点击加载更多</span></div>
	<script>
		//切换
		var category ="<?=$category?>";
		$(function(){
			if(!category){
				getGoodsList(1,false);
			}else{
				getGoodsList(1,false,category);
			}

		})
		function f(a){
			var _li = "#li" + a;

			if($(_li).hasClass("li_all_on")){
				$(_li).removeClass("li_all_on");
				$(_li).addClass("li_all_on_x");
				if(a==1){
					$(_li).attr("data-type","new_desc");
				}else if(a==2){
					$(_li).attr("data-type","sale_desc");
				}else if(a==3){
					$(_li).attr("data-type","price_desc");
				}
			}else{
				$(".all_goods_type li").removeClass("li_all_on");
				$(".all_goods_type li").removeClass("li_all_on_x");
				$(_li).addClass("li_all_on");
				$(_li).removeClass("li_all_on_x");
				if(a==1){
					$(_li).attr("data-type","new_asc");
				}else if(a==2){
					$(_li).attr("data-type","sale_asc");
				}else if(a==3){
					$(_li).attr("data-type","price_asc");
				}
			}
			getGoodsList(1,false)
		}
		function pulldata(obj){
			var curpage = $(".click_more_all").attr('curpage');
			getGoodsList(curpage, false);
		}

		function handleWhenHasNextPage(data){
				var hasnex = data.hasnexpage;
			if(hasnex){
				$(".click_more_all").show();
				$(".click_more_all").attr('curpage',data.curpage);
			}else{
				$(".click_more_all").hide();
			}
		}

		function getGoodsList(curpage,init){
			var type ="";
			$(".all_goods_type li").each(function(){
				var $_li =$(this);
				if($_li.hasClass("li_all_on")){
					 type = $(this).attr("data-type");
				}else if($_li.hasClass("li_all_on_x")){
					 type = $(this).attr("data-type");
				}
			});
			category = category?category:"";
			var goods_name = init?init:"";
			var merchant_id = "<?=$merchant_id?>";
			var tpl_id =<?=$tpl_id?>;
			if(!category){
				var	url ="<?php echo U('shop/goods/recoment');?>";
				var data ={'curpage' : curpage, 'type':type,'merchant_id':merchant_id,'tpl_id':tpl_id,'goods_name':goods_name};
			}else{
				var	url ="<?php echo U('shop/goods/category');?>";
				var data = {'curpage' : curpage, 'type':type,'merchant_id':merchant_id,'tpl_id':tpl_id,'goods_name':goods_name,'category':category}
			}
			F.get(url,data , function(ret){
				getHtml(curpage,ret.result);
				handleWhenHasNextPage(ret);
			});
		}
		function getHtml(curpage,result){
			if(curpage==1){
				$("#recoment_list").html("");
			}
			var li ="";
			for(var i=0;i<result.length;i++){
				 li += "<li class='goods_li'>";
				li +="<div class='next_goods'>";
				li +="<img src='"+result[i]['goods_img']+"' style='float:right'/>";
				li +="<p class='all_goods_tit'>"+"</p>";
				li +="<p class='all_goods_price'><span>"
					+"￥"+result[i]['shop_price']+"</span><b>"
				+"￥"+result[i]['market_price']+"</b></p>";
				li +="</div></li>"
			}
			$("#recoment_list").append(li);
		}
		$(document).on('click','#search',function(){
				var goods_name = $("#goods_name").val();
				getGoodsList(1,goods_name);
		})

	</script>
<?php include T('inc/popqr');?>