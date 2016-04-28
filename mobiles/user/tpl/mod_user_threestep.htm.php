<?php defined('IN_SIMPHP') or die('Access Denied');?>
<div id="sus_flow">
    <ul>
        <li>商家资料</li>
        <li class="li_cs"><img src="/themes/mobiles/img/back.png"></li>
        <li >邀请码</li>
        <li class="li_cs"><img src="/themes/mobiles/img/back.png"></li>
        <li class="li_co">支付</li>
        <li class="li_cs"><img src="/themes/mobiles/img/back.png"></li>
        <li>完成开通</li>
    </ul>
    <div class="clear"></div>
</div>

<div id="sus_info" style="height:150px;">
    <div class="pay_n" style="padding-top:30px"><img src="/themes/mobiles/img/sss1.png"></div>
    <div class="pay_n">订单提交成功</div>
</div>

<div class="pay_order_info">订单详情</div>

<div id="sus_info">
    <table cellspacing="0" cellpadding="0" class="buy_time_info">
        <tr>
            <th>名称</th><th>有效期</th><th>总金额</th>
        </tr>
        <tr>
            <td>多米分销系统</td><td>1年</td><td>￥699.00</td>
        </tr>
    </table>
</div>

<div id="sus_info">
    <textarea class="to_us_tell" placeholder="有话更益多米说..."></textarea>
</div>

<div id="wx_success_pay"  style="margin-top:20px;">
    <button id="sus_pay">确认支付</button>
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
                var password = ret.pwd;
               window.location.href="/user/merchant/twostep";
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

