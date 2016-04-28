<?php defined('IN_SIMPHP') or die('Access Denied');?>
<!-- for alert dialog -->
<div class="no-bounce" id="popalert-bg"></div>
<div class="no-bounce" id="popalert">
	<div class="top">
		<h1 class="alert-tit">提示</h1>
		<div class="alert-cont" id="alertcont"></div>
	</div>
	<div class="line"></div>
	<div class="btm">
		<button id="alertok">好的</button>
	</div>
</div>
<script>
function myAlert(msg, okcall, title, oktext, contentcss) {
	if (typeof (myAlert._popbg)=='undefined')  myAlert._popbg  = $('#popalert-bg');
	if (typeof (myAlert._popdlg)=='undefined') myAlert._popdlg = $('#popalert');
	if (typeof (myAlert._cont)=='undefined')   myAlert._cont   = $('#alertcont');
	myAlert._okcall = okcall;
	myAlert._okargs = new Array();
	if (arguments.length > 5) {
		for (var i = 5; i < arguments.length; i++) {
			myAlert._okargs.push(arguments[i]);
	  }
	}
	var _tit = myAlert._popdlg.find('.alert-tit');
	_tit.html('提示').show();
	if (typeof(title)!='undefined') {
		if (title===false) {
			_tit.hide();
		}
		else if(title!='') {
			_tit.html(title);
		}
	}
	var _ok = $('#alertok');
	_ok.text('好的');
	if (typeof(oktext)!='undefined' && oktext!='') {
		_ok.text(oktext);
	}
	myAlert._cont.attr('style','').html(msg);
	if (typeof(contentcss)=='object') {
		myAlert._cont.css(contentcss);
	}
	var _h = myAlert._popdlg.height();
	var _t = parseInt(($(document).height()-_h)/2) - 30;
	myAlert._popdlg.css('top',_t+'px');
	myAlert._popbg.show();
	myAlert._popdlg.show();
	return;
}
function _hideAlert() {
	myAlert._popdlg.hide();
	myAlert._popbg.hide();
}
$(function(){
	$('#popalert-bg,#popalert').bind('touchmove',function(e){
		e.preventDefault();
	});
	$('#alertok').bind('click',function(){
		_hideAlert();
		if (typeof(myAlert._okcall)=='function') {
			myAlert._okcall.apply(myAlert,myAlert._okargs);
		}
	});
});
</script>