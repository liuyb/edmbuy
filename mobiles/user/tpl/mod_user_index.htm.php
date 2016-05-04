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
			<img src="<?=$logo?>?_=<?=$user->randver?>" alt="logo">
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
<div class="member_cen_common">
	<div class="in_common_agency del_bottom">
		<img src="/themes/mobiles/img/tuijianr.png"><span>我的推荐人：<?=$parentUid ?></span><i><?=$parentNickName ?></i>
	</div>
</div>
<?php endif;?>

<div class="member_cen_common">
	<div class="in_common_agency">
		<span style="margin-left:0;">我的订单</span><a href="/trade/order/record?status=all"><i class="all_order_c">全部订单</i></a>
	</div>
	<!-- <div class="my_order_c">
		<ul>	
			<li><img src="/themes/mobiles/img/dfk.png"></li>
			<li><img src="/themes/mobiles/img/dfh.png"></li>
			<li><img src="/themes/mobiles/img/dsh.png"></li>
			<li><img src="/themes/mobiles/img/tksh.png"></li>
		</ul>
	</div> -->
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
</div>

<div class="member_cen_common">
	<div class="in_common_agency" onclick="window.location.href='/user/favorite/shop'">
		<img src="/themes/mobiles/img/scddp.png"><span>我收藏的店铺</span><i><?=$shop_count ?>家</i>
	</div>
	<div class="in_common_agency del_bottom" onclick="window.location.href='/user/favorite/goods'">
		<img src="/themes/mobiles/img/scdbb.png"><span>我收藏的宝贝</span><i><?=$goods_count ?>件</i>
	</div>
</div>

<div class="member_cen_common">
	<div class="in_common_agency" onclick="window.location.href='/user/my/wallet'">
		<img src="/themes/mobiles/img/wdqb.png"><span>我的钱包</span><i class="my_price_c"></i>
	</div>
	<!-- <div class="in_common_agency del_bottom">
		<img src="/themes/mobiles/img/yhk.png">
	</div> -->
</div>

<div class="agency_index_infos" style="margin-bottom:60px;">
	<div class="teamword_use">合作应用</div>
	<div class="mem_my_applylist">
		<ul>
			<li onclick="window.location.href='/distribution/merchants'"><img src="/themes/mobiles/img/dmfx.png"></li>
			<li><img src="/themes/mobiles/img/yiqix.png"></li>
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