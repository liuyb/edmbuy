<?php defined('IN_SIMPHP') or die('Access Denied');?>
<script id="popqr-html" type="text/html">
<?php if(!isset($_GET['spm']) || !preg_match('/^user\.(\d+)\.merchant$/i', $_GET['spm'])):?>
<div class="gicon_btn gicon_btn_qr" id="global-popqrbtn"></div>
<?php endif;?>
<div class="gicon_btn gicon_btn_gotop" id="global-gotopbtn"></div>
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
	$("#global-gotopbtn").click(function(){
		if ($("body").hasClass('iOS')) {
			$("#Mbody").animate({scrollTop:0},600);
		}
		else {
			$("body,html").animate({scrollTop:0},600);
		}
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