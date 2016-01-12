<?php defined('IN_SIMPHP') or die('Access Denied');?>
<!-- follow box -->
<section class="follow-box">
<div class="c c3"><button class="follow_btn">关注</button></div>
<div class="c c1"><img src="/themes/mobiles/img/ydm_logo.png" alt="" /></div>
<div class="c c2">
  <p class="emp">益多米</p>
  <p>品质生活从这里开始...</p>
</div>
</section>
<!-- 遮罩层 -->
<div class="mask"></div>
<!-- 二维码 -->
<div class="cart_wx" style="display:none;">
	<div class="c_wx_tit">益多米</div>
	<div class="c_wx_img"><img src="/misc/images/wx/qrcode_430.jpg"></div>
	<div class="c_wx_ewm">长按识别二维码，关注益多米</div>
	<div class="w_wx_colse"><img src="/themes/mobiles/img/gub.png"></div>
</div>
<script>
$(function(){
	$(".follow_btn").bind("click",function(){
		$(".mask").fadeIn(255);
		$(".cart_wx").fadeIn(255);
	})
	$(".w_wx_colse,.mask").bind("click",function(){
		$(".mask").fadeOut(255);
		$(".cart_wx").fadeOut(255);
	})
});
</script>