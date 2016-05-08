<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if(1==$step):?>
<div class="login_index">
	<div class="login_wirte_phone"><input type="text" value="" placeholder="请输入手机号码"></div>
	<div class="login_not_code del_bottom"><input type="text" value="" placeholder="请输入短信染验证码"><button class="get_note_code">获取验证码</button></div>
</div>

<div class="prompt_code"><p>验证码已发送至手机，请注意查收！</p></div>

<div class="bottom_common" style="margin:0;">
	<div class="btn_common_bg" id="login_btn">下一步</div>
</div>
<div class="use_protocol prot_on_ok">我已阅读并同意《益多米用户注册协议》</div>

<script>
var num = 60;
var count = num;
var time;

function refresh(){
	
	num = num - 1;
	var msg = num + "秒";
	 
	$(".get_note_code").html(msg);
		
    if(num == 0){
    	num = count;
    	$(".get_note_code").removeClass("sending").html("重新获取");
    	clearInterval(time);
    	return false;
    }
}

$(".get_note_code").on("click",function(){
	refresh();
	time = setInterval("refresh();", 1000);
	$(".prompt_code p").show();
})

$(".use_protocol").on("click",function(){
	if($(this).hasClass("prot_on_ok")){
		$(this).removeClass("prot_on_ok").addClass("prot_on_no");
	}else{
		$(this).removeClass("prot_on_no").addClass("prot_on_ok");
	}
})
</script>

<?php else:?>

<div class="login_index">
	<div class="login_password"><input type="text" value="" placeholder="请设置登录密码"></div>
	<div class="login_password del_bottom"><input type="text" value="" placeholder="请再次输入密码"></div>
</div>

<div class="bottom_common">
	<div class="btn_common_bg" id="login_btn">完成注册</div>
</div>

<script>
var num = 60;
var count = num;
var time;

//发送验证码
$("#getCode").bind("click", function(){
	
	_this = $(this);
	
	//仍在倒数中
	if(_this.hasClass("sending")){
		return false;
	}
	
	//获取验证码
	$.ajax({
		url:"",
		type:"post",
		success:function(dat){
			if(dat.msg=='success'){
				boxalert("验证码已发送");
			}else{
				boxalert("验证码发送失败");
			}
		}
	})
	
	
	_this.addClass("sending");
	refresh();
	time = setInterval("refresh();", 1000);
	
});

function refresh(){
	
	num = num - 1;
	var msg = num + "秒";
	 
	$("#getCode").html(msg);
		
    if(num == 0){
    	num = count;
    	$("#getCode").removeClass("sending").html("重新获取");
    	clearInterval(time);
    	return false;
    }
}

</script>

<?php endif;?>