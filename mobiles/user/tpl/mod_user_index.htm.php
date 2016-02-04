<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if(''!==$errmsg):?>

<div class="error"><?=$errmsg?></div>

<?php else:?>
<?php 
function displayNickImg($level){
	$bg = ($level == 1) ? "sha.png" : (($level == 2) ? "he.png" : "ke.png");
	return '/themes/mobiles/img/'.$bg;
}
?>
<a href="/user/setting">
<div class="mem_bg">
	<div class="mem_l">
		<?php if($logo): ?>
			<img src="<?=$logo?>">
		<?php else:?>
			<img src="/themes/mobiles/img/mt.png">
		<?php endif;?>
	</div>
	
	<div class="mem_c">
		<div class="c_name">
			<span class="c_n_tit" style="color:#fff;"><?=$nickname ?></span>
			<img class="refer_img_ad" src="<?=displayNickImg($level)?>"/>
		</div>
		<div class="c_id">多米号：<?=$uid ?></div>
	</div>
	
	<div class="mem_r">
		<img src="/themes/mobiles/img/set.png">
	</div>
	<div class="clear"></div>
</div>
</a>

<?php if(isset($parentUid)):?>
<div class="mem_refer">
	<div class="refer_tit order_bg1">我的推荐人</div>
	<div class="refer_info">
		<span class="refer_name"><?=$parentNickName ?></span>
		<img class="refer_img_ad1" style="margin-bottom: 4px;" src=" <?=displayNickImg($parentLevel)?>"/>
		<button class="refer_but">加好友</button>
	</div>
</div>
<?php endif;?>

<div style="height:10px;background:#eee"></div>

<div class="mem_my_order">
	<div class="my_order_l  order_bg2">我的订单</div>
	<a href="/trade/order/record"><div class="my_order_r">全部订单</div></a>
</div>
<div class="my_order_state">
	<ul>
		<a href="/trade/order/record?status=wait_pay">
    		<li>
    			<img src="/themes/mobiles/img/fk1.png">
    			<span class="waitPayCount"></span>
    		</li>
		</a>
		<a href="/trade/order/record?status=wait_ship">
			<li>
				<img src="/themes/mobiles/img/fh2.png">
    			<span class="unShipCount"></span>
			</li>
		</a>
		<a href="/trade/order/record?status=wait_recv">
			<li class="dsh">
				<img src="/themes/mobiles/img/sh3.png">
    			<span class="shipedCount"></span>
			</li>
		</a>
		<a href="/trade/order/record?status=finished"><li><img src="/themes/mobiles/img/sh4.png"></li></a>
	</ul>
</div>

<div style="height:10px;background:#eee"></div>

<div style="display: none;">
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
</div>
<?php include T('inc/add_as_friend');?>

<script>
$().ready(function(){
	loadAjaxData();
});
function loadAjaxData(){
	var url = "/user/index/ajax";
	F.get(url, null, function(data){
		if(data){
			var stateDOM = $(".my_order_state");
			if(data.status1 && data.status1 > 0){
				stateDOM.find(".waitPayCount").addClass("d_nums").text(data.status1);
			}
			if(data.status2 && data.status2 > 0){
				stateDOM.find(".unShipCount").addClass("d_nums").text(data.status2);
			}
			if(data.status3 && data.status3 > 0){
				stateDOM.find(".shipedCount").addClass("d_nums").text(data.status3);
			}
		}
	});
}

//好友弹框
$(".refer_but").on("click",function(){
	getAddFriendInstance().showFriend("","<?=isset($ParentWxqr)?$ParentWxqr:"" ?>","<?=isset($ParentMobile )?$ParentMobile :""?>");
});

$(function(){	
	var length = $(".c_n_tit").text().length;
	var _length = $(".refer_name").text().length;
	if(length >6){
		 var str = $(".c_n_tit").text();
		 var name = str.substr(0,6);
		 $(".c_n_tit").attr('title',str);
		 $(".c_n_tit").text(name + '...');
	}
	if(_length >5){
		 var str = $(".refer_name").text();
		 var name = str.substr(0,5);
		 $(".refer_name").attr('title',str);
		 $(".refer_name").text(name + '...');
	}
});
</script>

<?php endif;?>