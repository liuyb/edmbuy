<?php defined('IN_SIMPHP') or die('Access Denied'); ?>
<style>
    * {
        margin: 0;
        padding: 0;
        color: #333;
        border: none;
        list-style: none;
        outline: none;
        font-family: "Microsoft Yahei" !important;
        -webkit-tap-highlight-color: rgba(255, 255, 255, 0);
    }
</style>

<div id="sus_money">
    <img src="/themes/mobiles/img/sss1.png">

    <p id="sus_tit">支付成功</p>

    <p style="padding-bottom:15px;">您还需要填写如下注册信息，完成注册</p>
</div>

<div id="sus_flow">
    <ul>
        <li class="li_cs">付款</li>
        <li class="li_cs li_co">注册</li>
        <li>激活完成</li>
    </ul>
</div>

<div id="sus_info">
    <ul>
        <li>
            <span>手机号码</span>
            <input id="phone_nums_p" class="ps_common" type="text" value="" placeholder="请填写正确的号码">
            <span class="common_red_x">*</span>
        </li>
        <li>
            <span>验<i class="i_common"></i>证<i class="i_common"></i>码</span>
            <input id="auth_code" class="ps_common" type="text" value="" placeholder="请输入验证码">
            <button id="ps_btn">获取验证码</button>
        </li>
        <li>
            <span>邮<i style="padding:0 15px"></i>箱</span>
            <input id="ps_email" class="ps_common" type="text" value="" placeholder="请填写正确的邮箱">
            <span class="common_red_x">*</span>
        </li>
        <li>
            <span>邀<i class="i_common"></i>请<i class="i_common"></i>码</span>
            <input id="inviter" class="ps_common" type="text" value="" placeholder="(选填)邀请人的多米号">
        </li>
    </ul>
</div>
<div id="wx_success_pay" style="margin-top:10px;">
    <span id="red_deal">我已阅读，同意接受《益多米商家入驻协议》</span>
    <button id="next_step">下一步</button>
</div>
<script>

    var num = 60;
    var count = num;
    var time;

    $("#ps_email").focus(function(){
        $("#sus_money").fadeOut("slow");
    });
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
        var email =$("#ps_email").val();
        var inviter =$("#inviter").val();
        var url ="/user/merchant/savereg";
        if(mobile == ""){
            myAlert("手机号不能为空！");return;
        };
        if(auth_code == ""){
            myAlert("验证码不能为空！");return;
        };
        if(email == ""){
            myAlert("邮箱不能为空！");return;
        };
        checkEmail(email);
        isphone(mobile);

        var data ={"mobile":mobile,"mobile_code":auth_code,"email":email,"invite_code":inviter};
        $.post(url,data,function(ret){
            myAlert(ret.retmsg);
            if(ret.status==1){
                var password = ret.pwd;
//                window.location.href="/user/merchant/regsuc?mobile="+mobile+"& pwd="+password;
            }
        })
    });
    $(function () {
        $("#sus_money").parent().css("background", "#fff");
    });
    /**
     * 校验邮箱
     * @param str
     */
    function checkEmail(str){
        var re = /[a-zA-Z0-9]{1,10}@[a-zA-Z0-9]{1,5}\.[a-zA-Z0-9]{1,5}/;
        if(!re.test(str)){
            myAlert("邮箱格式不正确！");return;
        }
    }

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
//            $(".code_ts").css("display","none");//
            clearInterval(time);
            return false;
        }
    }

</script>

