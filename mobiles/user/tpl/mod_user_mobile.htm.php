<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if(''!==$errmsg):?>

<div class="error"><?=$errmsg?></div>

<?php else:?>

<script id="forTopnav" type="text/html">
<div class="header">
	个人信息
<a href="javascript:history.back();" class="back"></a>
	<?php if($ismoblie):?>
<a href="javascript:;" class="h_btn" id="cart_edit"  onclick='saveUserMobile();'>保存</a>
	<?php else:?>
	<a href="javascript:;" class="h_btn" id="cart_edit"  onclick='saveUserNickname();'>保存</a>
	<?php endif;?>
</div>
</script>
<script>show_topnav($('#forTopnav').html())</script>
	<?php if($ismoblie):?>
<div class="update_p">
	<input type="text" maxlength="20"  placeholder="请输入您的手机号码" value="<?=$mobile==1?"":$mobile ?>" class="bbsizing phone_text">
</div>
<div style="font-size:14px;color:#ff0000;margin:12px;">
	请输入正确的手机号码，方便客户与您联系
</div>
		<?php else:?>
		<div class="update_p">
			<input type="text" maxlength="20"  placeholder="请输入昵称" value="<?=$nickname ?>" class="bbsizing phone_text">
		</div>
		<?php endif;?>
<script>
	var mobile = '<?=$mobile ?>';
	function saveUserMobile(){
		var phone = $('.phone_text').val();
		if(!phone){
			weui_alert('请输入手机号码！');
			return;
		}
		var reg = /^1\d{10}$/;  //11数字
	    if(!reg.test(phone)){
			alert('你输入的手机号码格式有误，请重新输入！');
			return;
	    }
	    if(mobile == phone){
			return;
	    }
	    F.postWithLoading('/user/mobile/update', {mobile : phone}, function(ret){
		    if(ret['result'] == 'FAIL'){
		    	weui_alert(ret['msg']);
		    	return;
		    }
    		setTimeout(function(){
    			window.location.href = '/user/setting';
        	}, 500);
    	});
	}
	var nickname='<?=$nickname?>';
	function saveUserNickname(){
		var name=$('.phone_text').val();
		if(!name){
			weui_alert('请输入昵称！');
			return;
		}
		if(nickname==name){
			return;
		}
		F.postWithLoading('/user/nickname/update', {'nickname' : name}, function(ret){
			if(ret['result'] == 'FAIL'){
				weui_alert(ret['msg']);
				return;
			}
			setTimeout(function(){
				window.location.href = '/user/setting';
			}, 500);
		});
	}

</script>
<?php endif;?>