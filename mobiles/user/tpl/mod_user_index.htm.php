<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if(''!==$errmsg):?>

<div class="error"><?=$errmsg?></div>

<?php else:?>

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
		<div class="c_name <?php if(2 == $level): ?>c_name_bg <?php endif;?>"><?=$nickname ?></div>
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
		<span class="refer_name <?php if(2 == $parentLevel): ?> refer_name_bg <?php endif;?>"><?=$parentNickName ?></span>
		<button class="refer_but">加好友</button>
	</div>
</div>
<?php endif;?>
<div style="height:10px;background:#eee"></div>

<div class="mem_my_order">
	<div class="my_order_l  order_bg2">我的订单</div>
	<a href="/order"><div class="my_order_r">全部订单</div></a>
</div>
<div class="my_order_state">
	<ul>
		<a href="/order">
    		<li>
    			<img src="/themes/mobiles/img/fk1.png">
    			<?php if($waitPayCount && $waitPayCount > 0): ?>
    			<span class="d_nums"><?=$waitPayCount ?></span>
    			<?php endif;?>
    		</li>
		</a>
		<a href="/order">
			<li>
				<img src="/themes/mobiles/img/fh2.png">
				<?php if($unShipCount && $unShipCount > 0): ?>
    			<span class="d_nums"><?=$unShipCount ?></span>
    			<?php endif;?>
			</li>
		</a>
		<a href="/order">
			<li class="dsh">
				<img src="/themes/mobiles/img/sh3.png">
				<?php if($shipedCount && $shipedCount > 0): ?>
    			<span class="d_nums"><?=$shipedCount ?></span>
    			<?php endif;?>
			</li>
		</a>
		<a href="/order"><li><img src="/themes/mobiles/img/sh4.png"></li></a>
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

<?php include T('inc/add_as_friend');?>

<script>


//好友弹框
$(".refer_but").on("click",function(){
	getAddFriendInstance().showFriend("<?=isset($ParentWxqr)?$ParentWxqr:"" ?>","<?=isset($ParentMobile )?$ParentMobile :""?>");
});

$(function(){	
	var length = $(".c_name").text().length;
	var _length = $(".refer_name").text().length;
	if(length >4){
		 var str = $(".c_name").text();
		 var name = str.substr(1,4);
		 $(".c_name").attr('title',str);
		 $(".c_name").text(name + '...');
	}
	if(_length >3){
		 var str = $(".refer_name").text();
		 var name = str.substr(1,3);
		 $(".refer_name").attr('title',str);
		 $(".refer_name").text(name + '...');
	}
});
</script>

<?php endif;?>