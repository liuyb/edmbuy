<?php defined('IN_SIMPHP') or die('Access Denied');?>
<script id="popqr-html" type="text/html">
<div class="cursor gicon_btn gicon_btn_qr" id="global-popqrbtn"></div>
<div class="mask no-bounce hide" id="global-popqrmask"></div>
<div class="cart_wx no-bounce" id="global-popqr">
	<div class="c_wx_tit">益多米</div>
	<div class="c_wx_img"><img src="/themes/mobiles/img/ydm.png"></div>
	<div class="c_wx_ewm">长按识别二维码，关注益多米</div>
	<div class="w_wx_colse"><img src="/themes/mobiles/img/gub.png"></div>
</div>
</script>
<script type="text/javascript">
append_to_body($('#popqr-html').html());
$(function(){
	var $mask  = $("#global-popqrmask");
	var $popqr = $('#global-popqr');
	$("#global-popqrbtn").click(function(){
		$mask.fadeIn(300);
		$popqr.fadeIn(300);
	});
	$popqr.on("click",".w_wx_colse",function(){
		$mask.hide();
		$popqr.addClass("is_confirmwx");
		setTimeout(function(){
			$popqr.hide().removeClass("is_confirmwx");
		},500);
	});
});
</script>