<?php defined('IN_SIMPHP') or die('Access Denied');?>

<a href="ydm_personInfo.html">
<div class="mem_bg">
	<div class="mem_l">
		<img src="/themes/mobiles/img/mt.png">
	</div>
	
	<div class="mem_c">
		<div class="c_name">彭向成向向成</div>
		<div class="c_id">多米号： 235623</div>
	</div>
	
	<div class="mem_r">
		<img src="/themes/mobiles/img/set.png">
	</div>
	<div class="clear"></div>
</div>
</a>

<div class="mem_refer">
	<div class="refer_tit order_bg1">我的推荐人</div>
	<div class="refer_info">
		<span class="refer_name">彭彭先诚先诚</span>
		<button class="refer_but">加好友</button>
	</div>
</div>

<div style="height:10px;background:#eee"></div>

<div class="mem_my_order">
	<div class="my_order_l  order_bg2">我的订单</div>
	<a href="javascript:;"><div class="my_order_r">全部订单</div></a>
</div>
<div class="my_order_state">
	<ul>
		<a href="my_order.html"><li><img src="/themes/mobiles/img/fk1.png"></li></a>
		<a href="my_order.html"><li><img src="/themes/mobiles/img/fh2.png"></li></a>
		<a href="my_order.html">
			<li class="dsh">
				<img src="/themes/mobiles/img/sh3.png">
				<span class="d_nums">12</span>
			</li>
		</a>
		<a href="my_order.html"><li><img src="/themes/mobiles/img/sh4.png"></li></a>
	</ul>
</div>

<div style="height:10px;background:#eee"></div>

<div class="mem_my_apply order_bg3">
	我的应用
</div>
<div class="mem_my_applylist">
	<ul>
		<li><img src="/themes/mobiles/img/tym.png"></li>
		<li><img src="/themes/mobiles/img/sxy.png"></li>
		<li> </li>
		<li style="border-right:0;"> </li>
	</ul>
</div>

<!-- 遮罩层 -->
<a href="javascript:;"><div class="mask"></div></a>

<!-- 加好友弹出框1 -->
<div class="add_friend"> 
	<div class="add_f_img"><img src="/themes/mobiles/img/ydm.png"></div>
	<div class="add_f_tit">长按加好友，请注明来自：益多米</div>
	<div class="add_f_phone">电话：<a href="tel:400-0755-888" style="color:#111">12345678912</a></div>
	<div class="w_wx_colse close_f"><img src="/themes/mobiles/img/gub.png"></div>
</div>

<!-- 加好友弹出框2 -->
<div class="add_friend1">
	<div class="friend_ts">温馨提示</div>
	<div class="friend_nr">
		推荐人未完善个人信息</br>
		提醒TA完善信息
	</div>
	<div class="goods_btn">
		<button type="button" class="order_comm feiend_yes">马上提醒</button>
		<button type="button" class="order_comm friend_no" style="color:#999;font-weight:normal;">暂不提醒</button>
	</div>
</div>

<!-- 加好友弹出框3 -->
<div class="add_friend2">
	<div style="font-size:18px;color:#111;text-align:center;line-height:64px;">电话：12345678941</div>
	<div class="w_wx_colse"><img src="/themes/mobiles/img/gub.png"></div>
</div>

<script>

//好友弹框
$(".refer_but").on("click",function(){
	$(".add_friend1").fadeIn(300);
	$(".mask").fadeIn(300);
});
$(".close_f,.mask,.friend_no,.w_wx_colse").on("click",function(){
	$(".add_friend1").fadeOut(300);
	$(".mask").fadeOut(300);
});

$(function(){	
	var length = $(".c_name").text().length;
	var _length = $(".refer_name").text().length;
	if(length >4){
		 var str = $(".c_name").text();
		 var name = str.substr(1,4);
		 $(".c_name").text(name + '...');
	}
	if(_length >3){
		 var str = $(".refer_name").text();
		 var name = str.substr(1,3);
		 $(".refer_name").text(name + '...');
	}
});
</script>