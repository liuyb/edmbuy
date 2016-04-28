<?php defined('IN_SIMPHP') or die('Access Denied');?>
<div id="sus_flow">
    <ul>
        <li class="li_co">商家资料</li>
        <li class="li_cs"><img src="/themes/mobiles/img/back.png"></li>
        <li>邀请码</li>
        <li class="li_cs"><img src="/themes/mobiles/img/back.png"></li>
        <li>支付</li>
        <li class="li_cs"><img src="/themes/mobiles/img/back.png"></li>
        <li>完成开通</li>
    </ul>
    <div class="clear"></div>
</div>

<div id="sus_info">
    <ul>
        <li>
            <input id="phone_nums_p" class="ps_common" type="text" value="" placeholder="请填写正确的号码">
            <span class="common_red_x">*</span>
        </li>
        <li>
            <input id="auth_code" class="ps_common" type="text" value="" placeholder="请输入验证码">
            <button id="ps_btn">获取验证码</button>
        </li>
        <li>
            <input id="ps_img" class="ps_common" type="text" value="" placeholder="请输入图形验证码">
            <span class="common_red_x"><img id="verifyimg" src="/vc.php" onclick="this.src='/vc.php?'+Math.random()" alt="验证码" title="点击刷新验证码"></span>
        </li>
        <li>
            <input id="ps_email" class="ps_common" type="text" value="" placeholder="请输入店铺名称(可不填)">
            <span class="common_red_x"></span>
        </li>
    </ul>
</div>
<div id="wx_success_pay"  style="margin-top:30px;">
    <span id="red_deal">我已阅读，同意接受《益多米商家入驻协议》</span>
    <button id="next_step">下一步</button>
</div>

<script>
    var num = 60;
    var count = num;
    var time;

    $("#ps_btn").click(function () {
        _this = $(this);

        //仍在倒数中
        if(_this.hasClass("sending")){
            return false;
        }

        var url = "/user/merchant/getcode";
        var mobile = $("#phone_nums_p").val();
        if(mobile == ""){
            myAlert("手机号不能为空！");
        };
        isphone(mobile);
        var data = {"mobile": mobile};
        $.post(url, data,function (ret) {
            myAlert(ret.retmsg);
            if(ret.status==1){
                refresh();
                $("#ps_btn").css("display","block");
                time = setInterval("refresh();", 1000);
                $("#ps_btn").attr("disabled","disabled").css("background","grey");
            }
        })
    });
    $("#next_step").click(function(){
        var mobile=$("#phone_nums_p").val();
        var auth_code = $("#auth_code").val();
        var shop_face = $("#ps_email").val();
        var verifycode = $("#ps_img").val();

        var url ="/user/merchant/doonestep";
        if(mobile == ""){
            myAlert("手机号不能为空！");return;
        };
        if(auth_code == ""){
            myAlert("验证码不能为空！");return;
        };
        isphone(mobile);
        var data ={'mobile':mobile,'mobile_code':auth_code,'verifycode':verifycode,'shop_face':shop_face};
        F.post(url,data,function(ret){
            if(ret.status==1){
               window.location.href="/user/merchant/twostep";
            }else{
                myAlert(ret.retmsg);
            }
        })
    });
    $(function () {
        $("#sus_flow").parent().css("background", "#fff");
        $("input").css("border","none");
    });

    //电话号码验证
    function isphone(phone) {
        var reg = /^1[0-9]{10}/;
        if (!reg.test(phone)) {
            myAlert("手机号码格式不正确！");
            return;
        }
    }
    function refresh(){

        num = num - 1;
        var msg = num + "秒";

        $("#ps_btn").html(msg);

        if(num == 0){
            num = count;
            $("#ps_btn").removeAttr("disabled").css("background","#f69267");
            $("#ps_btn").removeClass("sending").html("重新获取");
            clearInterval(time);
            return false;
        }
    }
</script>

