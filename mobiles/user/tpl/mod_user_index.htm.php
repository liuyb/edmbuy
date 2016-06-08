<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if(''!==$errmsg):?>

<div class="error"><?=$errmsg?></div>

<?php else:?>

<?php if($is_account_logined && $is_agent && $agent->pid && !$agent->premium_id):?>
<div class="member_cen_common trans" style="margin:0;display:none;">
	<div class="in_common_agency del_bottom" style="background:#fdea9f !important;margin:0;">
		<span style="color:#994222">您还有<?php echo AgentPayment::getAgentPaidMoney($currentUser->level) ?>元套餐未领取!</span>
		<i style="color:#f65d00;" onclick="_link('<?php echo U('distribution/agent/package') ?>')">立即领取</i>
	</div>
</div>
<div class="no_get_s" id="no_get_s">
	<div style="text-align:center;margin-top:25px;font-size:16px;">你还有<i><?php echo AgentPayment::getAgentPaidMoney($currentUser->level) ?>元</i>套餐未领取哦!</div>
	<button class="later_get_btn" id="later_get_btn">稍后再说</button><button class="n_get_btn" onclick="_link('<?php echo U('distribution/agent/package') ?>')">立即领取</button>
</div>
<?php endif;?>
<div class="agency_become" onclick="_link('<?php echo U('user/setting') ?>');">
	<table cellspacing="0" cellpadding="0" class="become_a_tab">	
		<tr>
			<td width="70px;">
    			<img src="/themes/mobiles/img/mt.png" alt="logo" id="userLogo"/>
    		</td>
			<td>
			<?php if($is_account_logined):?>
				<p class="become_name"><?=$currentUser->nickname ?><img src="<?php echo AgentPayment::getAgentIconByLevel($currentUser->level) ?>" class="jin_z">
				<?php if(Users::isAgent($currentUser->level) && !Users::isGoldAgent($currentUser->level)):?>
				<img src="/themes/mobiles/img/wysj.png" onclick="_link('<?php echo U('trade/order/agent') ?>', event)" style="width:52px;height:19px;border-radius:0;margin-left:0px;"/>
				<?php endif;?>
				</p>
				<p class="become_name_id">多米号：<?=$currentUser->uid ?></p>
			<?php else:?>
				<p class="become_name">注册/登录</p>
				<p class="become_name_id">多米号：<?=$currentUser->uid ?></p>			
			<?php endif;?>
			</td>
			<td class="agency_index_img"><img src="/themes/mobiles/img/shezhi.png"></td>
		</tr>
	</table>
</div>
<?php if(isset($parentUser)):?>
<div class="member_cen_common">
	<div class="in_common_agency del_bottom" onclick="_link('<?php echo U('distribution/my/parent') ?>')">
		<img src="/themes/mobiles/img/tuijianr.png"><span>我的推荐人：<?=$parentUser->uid ?></span><i><?=$parentUser->nickname ?></i>
	</div>
</div>
<?php endif;?>

<div class="member_cen_common">
	<div class="in_common_agency">
		<span style="margin-left:0;">我的订单</span><i class="all_order_c" onclick="_link('<?php echo U('trade/order/record?status=all') ?>')">全部订单</i>
	</div>
	<div class="my_order_state">
	<ul>
		<li onclick="_link('<?php echo U('trade/order/record?status=wait_pay') ?>')">
			<img src="/themes/mobiles/img/fk1.png">
			<span class="waitPayCount"></span>
		</li>
		<li onclick="_link('<?php echo U('trade/order/record?status=wait_ship') ?>')">
			<img src="/themes/mobiles/img/fh2.png">
			<span class="unShipCount"></span>
		</li>
		<li class="dsh" onclick="_link('<?php echo U('trade/order/record?status=wait_recv') ?>')">
			<img src="/themes/mobiles/img/sh3.png">
			<span class="shipedCount"></span>
		</li>
		<li onclick="_link('<?php echo U('trade/order/record?status=all') ?>')"><img src="/themes/mobiles/img/sh4.png"><span class="returnCount"></span></li>
	</ul>
	</div>
</div>

<?php if(!$shopOpend):?>
<div class="member_cen_common">
	<div class="in_common_agency del_bottom" onclick="_link('<?php echo U('user/merchant/checkin') ?>')">
		<img src="/themes/mobiles/img/wddp.png"><span>我要开店</span>
	</div>
</div>
<?php endif;?>
<?php if(1 || !$is_agent):?>
<div class="member_cen_common">
	<div class="in_common_agency del_bottom" onclick="_link('<?php echo U('trade/order/agent') ?>')">
		<img src="/themes/mobiles/img/wydl.png"><span>我要代理</span>
	</div>
</div>
<?php endif;?>

<div class="member_cen_common">
	<div class="in_common_agency" onclick="_link('<?php echo U('user/favorite/shop') ?>')">
		<img src="/themes/mobiles/img/scddp.png"><span>我收藏的店铺</span><i><?=$shop_count ?>家</i>
	</div>
	<div class="in_common_agency del_bottom" onclick="_link('<?php echo U('user/favorite/goods') ?>')">
		<img src="/themes/mobiles/img/scdbb.png"><span>我收藏的宝贝</span><i><?=$goods_count ?>件</i>
	</div>
</div>

<div class="member_cen_common">
	<div class="in_common_agency <?php if(!$is_agent):?>del_bottom<?php endif;?>" onclick="_link('<?php echo U('partner/my/child') ?>')">
		<img src="/themes/mobiles/img/wdrm.png"><span>我的人脉</span><i>全部人脉</i>
	</div>
	<?php if($is_agent):?>
	<div class="in_common_agency" style="margin: 0 10px 0 33px;" onclick="_link('<?php echo U('distribution/my/child/agent') ?>')">
		<span>我发展的代理</span>
	</div>
	<div class="in_common_agency del_bottom" style="margin: 0 10px 0 33px;" onclick="_link('<?php echo U('distribution/my/child/shop') ?>')">
		<span>我发展的店铺</span>
	</div>
	<?php endif;?>
</div>

<?php if($shopOpend):?>
<div class="member_cen_common">
	<div class="in_common_agency del_bottom" onclick="_link('<?php echo U('distribution/shop') ?>')">
		<img src="/themes/mobiles/img/wddp.png"><span>我的店铺</span>
	</div>
</div>
<?php endif;?>

<div class="member_cen_common">
	<div class="in_common_agency del_bottom" onclick="_link('<?php echo U('user/my/wallet') ?>')">
		<img src="/themes/mobiles/img/wdqb.png"><span>我的钱包</span><i class="my_price_c"></i>
	</div>
</div>

<div class="member_cen_common" style="margin-bottom:60px;">
	<div class="in_common_agency del_bottom">
		<a class="blka" href="http://eqx.edmbuy.com/index.jsp"><img src="/themes/mobiles/img/yqx.png"><span>一起享</span></a>
	</div>
</div>

<?php include T('inc/add_as_friend');?>

<script>
function loadAjaxData(){
	var url = "<?php echo U('user/index/ajax')?>";
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
			if(data.status4 && data.status4 > 0){
				stateDOM.find(".returnCount").addClass("d_nums").text(data.status4);
			}
		}
	});
}

$(function(){

	<?php if($is_account_logined && $currentUser->logo): ?>
	$("#userLogo").attr("src","<?=$currentUser->logo?>?_=<?=$currentUser->randver?>");
    <?php endif;?>
	
	<?php if($is_account_logined && $is_agent && $agent->pid && !$agent->premium_id):?>
	remindUserGetPackage();
	<?php endif;?>
	
	loadAjaxData();
	
	//好友弹框
	/*
	$(".refer_but").on("click",function(){
		getAddFriendInstance().showFriend("","<?php echo isset($parentUser->wxqr)?$parentUser->wxqr:''?>","<?php echo isset($parentUser->mobile )?$parentUser->mobile:''?>");
	});
	*/
	/*
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
	*/
});

function _link(url, event){
	if (required_account_logined()) {
		return false;
	}
	if(event){
		event.stopPropagation();
	}
	if(!url){
		return;
	}
	window.location.href=url;
}

//提醒用户领取套餐
function remindUserGetPackage(){
	$(".mask").show();
	$("#later_get_btn").on("click",function(){
		$("#no_get_s").addClass("tran_on");
		$(".mask").fadeOut(2000);
		setTimeout(function(){$(".trans").addClass("tran_on_tit");},1000);
	});
}
</script>

<?php endif;?>