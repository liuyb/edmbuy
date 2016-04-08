<!--{add_css file="user.css" scope="global"}-->
<style>
    .verify_phone{
        margin-left:10px;
        width:360px;
        border:1px solid #ddd;
        height:30px;
        padding-left:5px;
    }
</style>

<div id="forget_bg">
    <div id="find_word_tit">找回密码</div>
    <!--{if $step==1}-->
    <div id="find_word_one" class="find_one">
        <div class="one_password">
            <img src="/themes/merchants/img/yzsf2.png"><img src="/themes/merchants/img/szmm2.png"><img src="/themes/merchants/img/wc2.png">
        </div>
        <div class="one_flow">
            <ul>
                <li class="flow_on">验证身份</li>
                <li>设置新密码</li>
                <li>完成</li>
            </ul>
        </div>
        <div class="one_verify">
            <div class="phone_id">手机号码：<input type="text" value="" id="verify_phone" class="phone_nums" name="phone"></div>
            <div>
                验证码：
                <input type="text" value="" class="auth_code" name="phoneCode"/>
                <button class="gain_note" id="getCode">获取短信验证码</button>
            </div>
            <div style="margin-left:-100px;">
                图形验证码：
                <input type="text"  value="" class="auth_code" name="imgCode" />
                <img id="verifyimg" src="/vc_mch.php" onclick="this.src='/vc_mch.php?_='+Math.random()*100000;" alt="验证码" title="点击刷新验证码"/>
            </div>
            <div class="code_ts">验证码已发出，请注意查收短信，如果没有收到，你可以在60秒后要求系统重新发送</div>
        </div>

        <div id="next_one">
            <button id="next_step" class="one_submit">下一步</button>
        </div>
    </div>
    <!--{/if}-->

    <!--{if $step==2}-->
    <div id="find_word_one" class="find_two">
        <div class="one_password">
            <img src="/themes/merchants/img/yzsf1.png"><img src="/themes/merchants/img/szmm3.png"><img src="/themes/merchants/img/wc2.png">
        </div>
        <div class="one_flow">
            <ul>
                <li class="flow_on">验证身份</li>
                <li class="flow_on">设置新密码</li>
                <li>完成</li>
            </ul>
        </div>

        <div class="one_verify">
            <div class="phone_id">新登录密码：<input type="password" value="" class="verify_phone"  class="new_password" placeholder="请输入6-12位的新登录密码" name="pwd"></div>
            <div class="phone_id">确认新密码：<input type="password" value="" id="verify_phone" class="ok_new_password" placeholder="请再次输入密码" name="confirmpwd"></div>
            <input type="hidden" name="phone" value="<!--{$phone}-->"/>
        </div>
        <div id="next_one">
            <button id="next_step" class="two_submit">提交</button>
        </div>
    </div>
    <!--{/if}-->

    <!--{if $step==3}-->
    <div id="find_word_one" class="find_three">
        <div class="one_password">
            <img src="/themes/merchants/img/yzsf1.png"><img src="/themes/merchants/img/szmm1.png"><img src="/themes/merchants/img/wc1.png">
        </div>
        <div class="one_flow">
            <ul>
                <li class="flow_on">验证身份</li>
                <li class="flow_on">设置新密码</li>
                <li>完成</li>
            </ul>
        </div>

        <div class="three_infos">
            <div class="success_img">
                <img src="/themes/merchants/img/cg.png">
            </div>

            <div class="success_ts">
                <p class="ts_tit">新密码设置成功！</p>
                <p class="ts_password">请牢记您新设置的密码。<a href="/login">返回重新登录</a></p>
            </div>
        </div>
    </div>
    <!--{/if}-->
</div>

<script>
    //    $(".one_submit").on("click",function(){
    //        $(".find_one").hide();
    //        $(".find_two").show();
    //    })

    //    $(".two_submit").on("click",function(){
    //        $(".find_one").hide();
    //        $(".find_two").hide();
    //        $(".find_three").show();
    //    })



    var num = 60;
    var count = num;
    var time;

    //发送验证码
    $("#getCode").on("click", function(){

        _this = $(this);

        //仍在倒数中
        if(_this.hasClass("sending")){
            return false;
        }


        url="/user/getPhoneCodeAjax";
        var phone=$("input[name=phone]").val()
        var  data ={"phone":phone};
        F.postWithLoading(url,data,function(ret){
            showMsg(ret.retmsg)
            if(ret.status==1){
                refresh();
                $(".code_ts").css("display","block");
                time = setInterval("refresh();", 1000);
                $("#getCode").attr("disabled","disabled").css("background","grey");
            }
        });
//        if(dat=="timeLimit"){
//            boxalert("验证码发送失败，请稍后在试");
//        } else {
//            boxalert("验证码已发送");
//        }
    });

    function refresh(){

        num = num - 1;
        var msg = num + "秒";

        $("#getCode").html(msg);

        if(num == 0){
            num = count;
            $("#getCode").removeAttr("disabled").css("background","#f69267");
            $("#getCode").removeClass("sending").html("重新获取");
            $(".code_ts").css("display","none");
            clearInterval(time);
            return false;
        }
    }
    $(".one_submit").click(function(){
        var phone=$("input[name=phone]").val()
        var  data ={"phone":phone};
        if(phone==""||data==""){
            showMsg("参数不能为空！")
            return;
        }
        url="/checkSmsCode"
        var phoneCode=$("input[name=phoneCode]").val()
        var imgCode=$("input[name=imgCode]").val()
        var data ={'phone':phone,'imgCode':imgCode,"phoneCode":phoneCode};
        F.postWithLoading(url,data,function(ret){
            if(ret.status==0){
                showMsg(ret.retmsg);
            }else{
                window.location.href="/forgetPwd?step=2";
            }
        },"JSON")
    })
    var phone =<!--$phone-->;
    $(".two_submit").click(function(){
        var pwd=$("input[name=pwd]").val();
        var confirmpwd=$("input[name=confirmpwd]").val();
        if(pwd==""||confirmpwd==""||pwd!=confirmpwd){
            showMsg("参数错误!");
        }
        url="/user/forgotSavePwd";
        var phone =$("input[name=phone]").val()
        var data={"pwd":pwd,"confirmpwd":confirmpwd,"phone":phone};
        $.post(url,data,function(ret){
            if(ret.status==1){
                window.location.href="/forgetPwd?step=3";
            }else{
                showMsg(ret.retmsg)
            }
        })
    });
</script>