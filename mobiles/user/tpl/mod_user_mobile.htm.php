<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if(''!==$errmsg):?>

<div class="error"><?=$errmsg?></div>

<?php else:?>

<script id="forTopnav" type="text/html">
<div class="header">
	个人信息
<a href="javascript:history.back();" class="back"></a>
<span style='float:right;'>
    <input type='button' value='保存' onclick='saveUserMobile();'/>
</span>
</div>
</script>
<script>show_topnav($('#forTopnav').html())</script>

<div class="update_p">
	<input type="text" maxlength="20"  placeholder="请输入您的手机号码" value="<?=$mobile ?>" class="phone_text">
</div>
<div style="font-size:14px;color:#ff0000;margin:12px;">
	请输入正确的手机号码，方便客户与您联系
</div>
<script>
	function saveUserMobile(){
		var phone = $('.phone_text').val();
		if(!phone){
			alert('请输入手机号码！');
			return;
		}
		var reg = /^1\d{10}$/;  //11数字
	    if(!reg.test(phone)){
			alert('你输入的手机号码格式有误，请重新输入！');
			return;
	    }
	    F.post('/user/mobile/update', {mobile : phone}, function(ret){
    		window.location.href = '/user/setting';
    	});
	}
</script>
<?php endif;?>