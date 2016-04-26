<?php defined('IN_SIMPHP') or die('Access Denied');?>
<div id="sus_flow">
    <ul>
        <li>商家资料</li>
        <li class="li_cs"><img src="/themes/mobiles/img/back.png"></li>
        <li class="li_co">邀请码</li>
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
            <span style="font-size: 16px;padding-right: 20px;">邀请码(选填)</span><input id="phone_nums_p" class="ps_common"
                                                                                    type="text" value=""
                                                                                    placeholder="请输入邀请码">
            <span class="common_red_x"></span>
        </li>
    </ul>
</div>
<div id="wx_success_pay" style="margin-top:30px;">
    <button id="next_step">去支付</button>
</div>

<script>
    $(function () {
        $("#sus_flow").parent().css("background", "#fff");
        $("input").css("border", "none");
    });
    $("#next_step").click(function () {
        var invite_code = $("#phone_nums_p").val();
            var data ={"invite_code":invite_code};
        F.post('/user/merchant/dotwostep', data, function (ret) {
            if(ret.status==1){
                window.location.href = "<?php echo U("trade/order/confirm_sysbuy");?>"
            }else{
                myAlert("推荐人不存在!");
            }
        });
    });
</script>

