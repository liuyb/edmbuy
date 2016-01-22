<?php defined('IN_SIMPHP') or die('Access Denied');?>

<script id="forTopnav" type="text/html">
<div class="header">
	购物车
<?php if($backurl):?><a href="<?=$backurl?>" class="back"></a><?php endif;?>
	<a href="javascript:;" class="h_btn" id="cart_edit">编辑</a>
</div>
</script>
<script>show_topnav($('#forTopnav').html())</script>
<script id="for_nav_cart_tool" type="text/html">
<!-- 编辑工具栏 -->
<div class="cart_tool">
<b class="cart_select"></b>
<span class="fl cart_t_select">全选</span>
<span class="fr cart_t_btn" id="cart_t_btn">去结算</span>
<span class="fr cart_t_money">合计:<i class="cart_t_price">￥<small id="all_price">0.00</small></i></span>
</div>
</script>
<script>$('#nav_cart_tool').html($('#for_nav_cart_tool').html())</script>

<div class="cart_body">

	<!-- 空购物 -->
	<div class="cart_empty hide">
		<img src="/themes/mobiles/img/kgu.png" class="cart_empty_img" alt=""/>
		<p><button type="button" class="cart_empty_back">返回</button><p>
	</div>
	
	<!-- 购物车列表 -->
	<div class="cart_list">
	
		<div class="cart_shop">
			<div class="cart_shop_name">
				<b class="cart_select"></b>
				<a href="#" class="cart_shop_link">美骆世界旗舰店</a>
			</div>
			<div class="cart_product">
				<div class="cart_product_item">
					<b class="cart_select"></b>
					<a href="<?php echo U('item/1004')?>"><div class="cart_p_img"><img src="/themes/mobiles/img/pro.jpg"></div></a>
					<div class="cart_p_main">
						<div class="cart_p_desc">
							<a href="<?php echo U('item/1004')?>"><h1>美骆世界皮鞋美骆世界皮鞋</h1></a>
							<div class="cart_p_info">颜色:蓝色;尺码:45</div>
						</div>
						<div class="cart_p_price">
							<i>￥<small>1.1</small></i>
							<p>
								<span class="cart_btn btn_minus"></span>
								<span><input type="text" class="cart_inp" value="1"></span>
								<span class="cart_btn btn_plus"></span>
							</p>
						</div>
					</div>
				</div>
				
				<div class="cart_product_item">
					<b class="cart_select"></b>
					<a href="<?php echo U('item/1004')?>"><div class="cart_p_img"><img src="/themes/mobiles/img/pro.jpg"></div></a>
					<div class="cart_p_main">
						<div class="cart_p_desc">
							<a href="<?php echo U('item/1004')?>"><h1>美骆世界皮鞋</h1></a>
							<div class="cart_p_info">颜色:蓝色;尺码:45</div>
						</div>
						<div class="cart_p_price">
							<i>￥<small>1.1</small></i>
							<p>
								<span class="cart_btn btn_minus"></span>
								<span><input type="text" class="cart_inp" value="1"></span>
								<span class="cart_btn btn_plus"></span>
							</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	
		<div class="cart_shop">
			<div class="cart_shop_name">
				<b class="cart_select"></b>
				<a href="#" class="cart_shop_link">美骆世界旗舰店美骆世界旗舰店美骆世界旗舰店</a>
			</div>
			<div class="cart_product">
				<div class="cart_product_item">
					<b class="cart_select"></b>
					<a href="<?php echo U('item/1004')?>"><div class="cart_p_img"><img src="/themes/mobiles/img/pro.jpg"></div></a>
					<div class="cart_p_main">
						<div class="cart_p_desc">
							<a href="<?php echo U('item/1004')?>"><h1>美骆世界皮鞋美骆世界皮鞋美骆世界皮鞋美骆世界皮鞋美骆世界皮鞋美骆世界皮鞋</h1></a>
							<div class="cart_p_info">颜色:蓝色;尺码:45</div>
						</div>
						<div class="cart_p_price">
							<i>￥<small>1</small></i>
							<p>
								<span class="cart_btn btn_minus"></span>
								<span><input type="text" class="cart_inp" value="1"></span>
								<span class="cart_btn btn_plus"></span>
							</p>
						</div>
					</div>
				</div>
				
				<div class="cart_product_item">
					<b class="cart_select"></b>
					<a href="<?php echo U('item/1004')?>"><div class="cart_p_img"><img src="/themes/mobiles/img/pro.jpg"></div></a>
					<div class="cart_p_main">
						<div class="cart_p_desc">
							<a href="<?php echo U('item/1004')?>"><h1>美骆世界皮鞋</h1></a>
							<div class="cart_p_info">颜色:蓝色;尺码:45</div>
						</div>
						<div class="cart_p_price">
							<i>￥<small>1</small></i>
							<p>
								<span class="cart_btn btn_minus"></span>
								<span><input type="text" class="cart_inp" value="1"></span>
								<span class="cart_btn btn_plus"></span>
							</p>
						</div>
					</div>
				</div>
			</div>
		</div>
		
	</div>
	
</div>

<script type="text/javascript">
$(function(){
	
	//空购物车返回
	$(".cart_empty_back").on("click", function(){
		history.back();
	});

	//进入编辑模式
	$("#cart_edit").on("click", function(){
		var t = $(this);
		//is_edit标识正在编辑
		if(t.hasClass("is_edit")){
			$(".cart_t_money").show();
			$("#cart_edit").text("编辑").removeClass("is_edit");
			$("#cart_t_btn").text("去结算").removeClass("is_edit");
		}else{
			$(".cart_t_money").hide();
			$("#cart_edit").text("完成").addClass("is_edit");
			$("#cart_t_btn").text("删除").addClass("is_edit");
		}
	});

	//店铺-选择产品
	$(".cart_shop_name .cart_select").on("click", function(){
		var t = $(this);
		if(t.hasClass("on")){
			t.parent().parent().find(".cart_select").removeClass("on");
		}else{
			t.parent().parent().find(".cart_select").addClass("on");
			cartSelectAll();
		}
		cartSelectAll();
		calculatePrice();
	});

	//单个产品-选择产品
	$(".cart_product_item .cart_select").on("click", function(){
		var t = $(this);
		if(t.hasClass("on")){
			t.parent().parent().parent().find(".cart_shop_name .cart_select").removeClass("on");
			t.removeClass("on");
		}else{
			t.addClass("on");
			var item_num = t.parent().parent().find(".cart_product_item").length;
			var select_num = t.parent().parent().find(".cart_select.on").length;
			if(item_num == select_num){
				t.parent().parent().parent().find(".cart_shop_name .cart_select").addClass("on");
			}
		}
		cartSelectAll();
		calculatePrice();
	});

	//全选-选择产品
	$(".cart_tool .cart_select,.cart_tool .cart_t_select").on("click", function(){
		var t = $(".cart_tool .cart_select");
		if(t.hasClass("on")){
			$(".cart_select").removeClass("on");
		}else{
			$(".cart_select").addClass("on");
		}
		calculatePrice();
	});

	//是否符合全选
	function cartSelectAll(){
		var item_num = $(".cart_product_item").length;
		var select_num = $(".cart_product_item .cart_select.on").length;
		if(item_num == select_num){
			$(".cart_tool .cart_select").addClass("on");
		}else{
			$(".cart_tool .cart_select").removeClass("on");
		}
	}

	//产品数量填写
	$(".cart_inp").on("input propertychange", function(){
		var t = $(this);
		var num = t.val();
		if(!isZZ(num)){
			t.val('1');
		    boxalert("请输入大于零的整数!");
		    return false;
		}
		calculatePrice();
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
		calculatePrice();
	});

	//产品数量增加
	$(".btn_plus").on("click", function(){
		var t = $(this);
		var inp = t.parent().find(".cart_inp");
		var num = inp.val()*1+1;
		inp.val(num);
		calculatePrice();
	});

	//计算实时价格
	function calculatePrice(){
		var all_price = 0*1;
		$(".cart_product_item").each(function(){
			var t = $(this);
			if(t.find(".cart_select").hasClass("on")){
				var mon = t.find("small").text()*1;
				var num = t.find(".cart_inp").val()*1;
				all_price += mon*num*1;
			}
		});
		$("#all_price").text(all_price.toFixed(2));
	}

	//去结算or删除
	$("#cart_t_btn").on("click", function(){
		var t = $(this);
		var delete_number = 0;
		if(t.hasClass("is_edit")){  //删除
			$(".cart_product_item").each(function(){
				var t = $(this);
				if(t.find(".cart_select").hasClass("on")){
					++delete_number;
					t.remove();
				}
			});
			$(".cart_shop").each(function(){
				var t = $(this);
				if(t.find(".cart_product_item").length == 0){
					t.remove();
				}
			});
			if($(".cart_list").find(".cart_product_item").length == 0){
				$(".cart_tool .cart_select").removeClass("on");
				$("#cart_edit,.cart_tool").remove();
				$(".cart_empty").show();
			}
			var cart_number = $("#cart_number").text()*1;
			var new_cart_number = cart_number - delete_number*1;
			$("#cart_number").text(new_cart_number);
			if(new_cart_number == 0){
				$("#cart_number").hide();
			}
		}else{  //去结算
			if($(".cart_product_item .cart_select.on").length > 0){
				location.href="settle_accounts.html";
			}else{
				boxalert("你还没选择要结算的产品哦");
			}
		}
	});
	
});
</script>

