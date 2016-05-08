<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if(''!==$errmsg):?>

<div class="error"><?=$errmsg?></div>

<?php else:?>
<?php if(Users::isAgent($curuser->level) && $agent->pid && !$agent->premium_id):?>
<div class="member_cen_common" style="margin:0;">
	<div class="in_common_agency del_bottom" style="background:#fdea9f;margin:0;">
		<span style="color:#994222">您还有<?=AgentPayment::getAgentPaidMoney($curuser->level) ?>元套餐未领取!</span>
		<a href="<?=U('distribution/agent/package') ?>"><i style="color:#f65d00;">立即领取</i></a>
	</div>
</div>
<?php endif;?>
<a href="/user/setting">
<div class="agency_become">
	<table cellspacing="0" cellpadding="0" class="become_a_tab">	
		<tr>
			<td width="70px;">
			<?php if($logo): ?>
    			<img src="<?=$logo?>?_=<?=$curuser->randver?>" alt="logo">
    		<?php else:?>
    			<img src="/themes/mobiles/img/mt.png">
    		<?php endif;?>
    		</td>
			<td>
				<p class="become_name"><?=$nickname ?><img src="<?=AgentPayment::getAgentIconByLevel($curuser->level) ?>" class="jin_z"></p>
				<p class="become_name_id">多米号：<?=$uid ?></p>
			</td>
			<td class="agency_index_img"><img src="/themes/mobiles/img/shezhi.png"></td>
		</tr>
	</table>
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
	<a href='<?=U('partner/my/child') ?>'>
	<div class="in_common_agency">
		<img src="/themes/mobiles/img/wdrm.png"><span>我的人脉</span><i>全部人脉</i>
	</div>
	</a>
	<a href='<?=U('distribution/my/child/agent') ?>'>
	<div class="in_common_agency" style="margin: 0 10px 0 33px;">
		<span>我发展的代理</span>
	</div>
	</a>
	<a href='<?=U('distribution/my/child/shop') ?>'>
	<div class="in_common_agency" style="margin: 0 10px 0 33px;">
		<span>我发展的店铺</span>
	</div>
	</a>
</div>
<?php if(!Users::isAgent($curuser->level)):?>
<div class="member_cen_common">
	<a href='<?=U('distribution/agent') ?>'>
	<div class="in_common_agency">
		<img src="/themes/mobiles/img/wydl.png"><span>我要代理</span>
	</div>
	</a>
</div>
<?php endif;?>
<div class="member_cen_common">
	<a href='<?=U('distribution/shop') ?>'>
	<div class="in_common_agency">
		<img src="/themes/mobiles/img/wddp.png"><span>我的店铺</span>
	</div>
	</a>
</div>

<div class="member_cen_common">
	<div class="in_common_agency" onclick="window.location.href='/user/my/wallet'">
		<img src="/themes/mobiles/img/wdqb.png"><span>我的钱包</span><i class="my_price_c"></i>
	</div>
</div>

<div class="member_cen_common" style="margin-bottom:60px;">
	<div class="in_common_agency">
		<img src="/themes/mobiles/img/yqx.png"><span>一起享</span>
	</div>
</div>

<!-- 
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
 -->
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