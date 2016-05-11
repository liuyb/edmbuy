<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if(''!==$errmsg):?>

<div class="error"><?=$errmsg?></div>

<?php else:?>

<script id="forTopnav" type="text/html">
<div class="header">
	个人信息
<a href="<?=U('user') ?>" class="back"></a>
</div>
</script>
<script>show_topnav($('#forTopnav').html())</script>

<div style="background: #fff;">
	 <div class="person_head">
		<span class="head_l">我的头像</span>
		<span class="head_r" id="userHeaderImg">
			<?php if($logo): ?>
				<img src="<?=$logo?>?_=<?=$user->randver?>" alt="logo"/>
			<?php else:?>
				<img src="/themes/mobiles/img/mt.png">
			<?php endif;?>
		</span>
    	<input type="file" name="file" onchange="fileupload(event)" class="dia_file">
	</div>
	<a href="/user/mobile/show?nickname=<?=$nickname ?>">
		<div class="person_head_comm">
			<span class="head_l_com">昵称</span>
			<span class="head_r_com"><?=$nickname ?></span>
		</div>
	</a>
	<a href="/user/mobile/show?mobile=<?php if(empty($mobile)){echo "1";}{echo $mobile;} ?>">
		<div class="person_head_comm">
			<span class="head_l_com">手机号</span>
			<span class="head_r_com"><?=$mobile ?></span>
		</div>
	</a>
	<a href="/user/wxqr/show"> 
		<div class="person_head_comm">
			<span class="head_l_com">微信二维码</span>
			<span class="head_r_com">
			<img src="/themes/mobiles/img/e.png" style="margin-left: 5px">
			</span>
		</div>
	</a>
</div> 
<!-- 
<div style="height:20px;"></div>

<div style="background: #fff;">
	<a href="javascript:;"> 
		<div class="person_head_comm"  style="border-top:0;">
			<span class="head_l_com">收货地址</span>
		</div>
	</a>
</div>
 -->
<div style="height:20px;"></div>

<div style="background: #fff;">
	<div class="person_head_comment" style="border-top:0;">
		<span class="head_l_com">我的米号</span>
		<span class="head_r_com" style="margin-right:12px;"><?=$uid ?></span>
	</div>
	<div class="person_head_comment">
		<span class="head_l_com">我的角色</span>
		<span class="head_r_com" style="margin-right:12px;">
			<span style="margin-right: 5px;font-size:16px;">
			<?=Users::displayUserLevel($level) ?>
			</span>
		</span>
	</div>
	<?php if(isset($parentUid)):?>
	<div class="person_head_comment">
		<span class="head_l_com">推荐人</span>
		<span class="head_r_com" style="margin-right:12px;">
			<span class="fre" style="margin-right: 5px;font-size:16px;"><?=$parentNickName?></span>
			<button class="personInfo_but per_add_friend" id="add-friend" style="font-size:14px;">加好友</button>
		</span>
	</div>
	<?php endif;?>
</div>

<div style="height:20px;"></div>
<div style="text-align: center">
<button onclick="location.href='<?php echo U('/user/logout_account')?>'" class="btn_common_bg" style="width:90%;margin:0 auto;">退出登录</button>
</div>
<div style="height:30px;"></div>

<?php include T('inc/add_as_friend');?>

<script>

$(function(){	
	var length = $(".fre").text().length;
	if(length > 5){
		 var str = $(".fre").text();
		 var name = str.substr(0,5);
		 $(".fre").text(name + '...');
	}
});

$(function(){
	$('#be-partner').bind('click',function(){
		window.location.href = '/riceplan';
	});
	//好友弹框
	$("#add-friend").bind("click",function(){
		getAddFriendInstance().showFriend("","<?=isset($ParentWxqr)?$ParentWxqr:"" ?>","<?=isset($ParentMobile )?$ParentMobile :""?>");
	});
});

function fileupload(e){
    var files = e.target.files;
    var file = files[0];
    var valid = false;
    if (file && file.type && file.type) {
    	var reg = /^image/i;
    	valid = reg.test(file.type);
    }
    if(!valid){
    	myAlert('请选择正确的图片格式上传，如：JPG/JPEG/PNG/GIF ');
		return;
    }
    var fr = new FileReader();
    fr.onload = function(ev) {
    	var img = ev.target.result;
    	F.postWithLoading('/user/logo/upload', {img:img}, function(ret){
    		if(ret.flag=='SUC'){
    			$("#userHeaderImg").find("img").attr("src",ret.result+'?r='+Math.random());
    		}else{
    			myAlert(ret.errMsg);
    		}
    	});
    };
  	fr.readAsDataURL(file);
}
</script>
<?php endif;?>