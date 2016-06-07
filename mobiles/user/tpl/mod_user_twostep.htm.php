<?php defined('IN_SIMPHP') or die('Access Denied');?>
<div id="sus_flow">
    <ul>
        <li class="bottom_f">商家注册</li>
        <li class="li_cs bottom_f"><img src="/themes/mobiles/img/back.png"></li>
        <li class="li_co bottom_f">设置密码</li>
        <li class="li_cs"><img src="/themes/mobiles/img/back.png"><img class="bottom_dw" src="/themes/mobiles/img/liz.png"></li>
        <li>支付</li>
        <li class="li_cs"><img src="/themes/mobiles/img/back.png"></li>
        <li>完成开通</li>
    </ul>
    <div class="clear"></div>
</div>

<div id="sus_info">
    <ul>
		<li>
			<input id="shopPass" class="ps_common" type="password" value="" placeholder="设置店铺登录密码" style='width:100%;'>
		</li>
		<li>
			<input id="confirmShopPass" class="ps_common" type="password" value="" placeholder="请再次输入密码" style='width:100%;'>
		</li>
		<?php if(!$user->parentid): ?>
		<li>
			<input id="invite_code" class="ps_common" type="text" placeholder="请输入推荐人多米号">
		</li>
		<?php else:?>
		<li>
		您的推荐人：<?=$parent->nickname ?>（<?=$user->parentid ?>）
		<input id="invite_code" class="ps_common" type="hidden" value="<?=$user->parentid ?>" >
		</li>
		<?php endif;?>
	</ul>
</div>
<?php if(!$user->parentid): ?>
<div id="sus_info" style="font-size:14px;color:#999;margin-top:5px">
	注：为了保证益多米全体用户的利益不受损失，根据平台规则，入驻商家建议填写推荐人多米号；如果不填，默认推荐人为益多米中的上级；如果益多米中也没上级，则暂不允许开通。
</div>
<?php endif;?>
<div id="wx_success_pay" style="margin-top:30px;">
    <button id="next_step">去支付</button>
</div>

<script>
$(function () {
	$("#Mbody").css("background","#fff");
	$("input").css("border", "none");

    $("#next_step").click(function () {
        var shopPass = $("#shopPass").val();
        var confirmShopPass = $("#confirmShopPass").val();
        if(!ispw(shopPass) || shopPass.length > 12){
			myAlert('店铺登录密码长度为6到12位，请重新输入！');
			return;
        }
        if(shopPass != confirmShopPass){
        	myAlert('两次输入的密码不一致，请重新输入！');
			return;
        }
        var invite_code = $("#invite_code").val();
        var data ={"invite_code":invite_code, shopPass : shopPass, confirmShopPass : confirmShopPass};
        F.post('/user/merchant/dotwostep', data, function (ret) {
            if(ret.status==1){
                window.location.href = "<?php echo U("trade/order/confirm_sysbuy");?>"
            }else{
                myAlert(ret.retmsg);
            }
        });
    });
});
</script>

