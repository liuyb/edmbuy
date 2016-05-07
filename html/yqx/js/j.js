$(function(){
	loadingFun();
});

function trim(str){ //删除左右两端的空格
    return str.replace(/(^\s*)|(\s*$)/g, "");
}
function ltrim(str){ //删除左边的空格
    return str.replace(/(^\s*)/g,"");
}
function rtrim(str){ //删除右边的空格
    return str.replace(/(\s*$)/g,"");
}
function trimAll(str){ //删除所有的空格   包括 nbsp;之类的
	return str.replace(/^\s*((?:[\S\s]*\S)?)\s*$/, "");
}
//检测手机号
function isphone(phone){
	
	var flag = 0;
	var phone = trim(phone);
	var length = phone.length;
	
	if(length == 11){
		flag = 1;
	}
	
	return flag;
}

//检测电话号码
function istelphone(phone){
	
	var flag = 0;
	var telphone = trim(phone);
	var checkPhone=/^((0\d{2,3})-)?(\d{7,8})(-(\d{3,}))?$/;
	
	if(checkPhone.test(telphone)){
		flag = 1;
	}
	
	return flag;
}

//检测密码
function ispw(pw){
	
	var flag = 0;
	var pw = trim(pw);
	var check = /^[\x21-\x7e]{6,}$/;
	
	if(check.test(pw)){
		flag = 1;
	}
	return flag;
}

//真实姓名判断
function ismyname(str){
	
	var flag = 0;
	var str = trim(str);
	var length = str.length;
    var check = /^([\u4E00-\u9FA5]|[·])+$/;
    
    if ((length > 1) && (check.test(str))) {  
    	flag = 1;
	}
    
	return flag; 
	
}

//检测email
function isemail(email){

	var flag = 0;
	var email = trim(email);
	var length = email.length;
	var check = /^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i;
	if(check.test(email)){
		flag = 1;
	}
	return flag;
}

//正整数
function isZZ(num){
	
	var flag = 0;
	var check = /^[0-9]*[1-9][0-9]*$/;
    
    if (check.test(num)) {  
    	flag = 1;
	}
    
	return flag; 
	
}

//错误提示层
function boxalert(error,time){
	
	if($(".reg_error").length<=0){
		$("body").append('<div class="reg_error"></div>');
	}
	
	if(!time){
		time = 2000;
	}
	
	$(".reg_error").html(error).show();
	
	setTimeout(function(){
		$(".reg_error").fadeOut(100);	
	},time);
	
}

//带确定按钮
function boxtip(tip, url){
	
	if(!tip) tip='';
	
	var havaUrl;
	if(url){
		havaUrl = '<button type="button" class="boxBtn" onclick="boxReload(\'' + url + '\')">确定</button>'
	} else {
		havaUrl = '<button type="button" class="boxBtn" onclick="boxReload()">确定</button>'
	}
	
	var html = '<div class="mask_pay" style="display:block;"></div><div class="boxTip">'
	 	     +	'<div class="boxtitle">温馨提醒</div>'
			 +	'<div class="boxTip1">'+tip+'</div>'
			 +	'<div class="boxTip2">'
			 +		havaUrl
			 +	'</div>'
			 + '</div>';
	
	$("body").append(html);  
}

//确定弹层
function boxReload(url){
	if(url){
		location.href=url;
	}else{
		$(".mask_pay").hide();
		$(".boxTip").remove();
	}
}

//判断是滚动到页面底部  
function uiIsPageBottom() {  
    var scrollTop = 0;  
    var clientHeight = 0;  
    var scrollHeight = 0;  
    if (document.documentElement && document.documentElement.scrollTop) {  
        scrollTop = document.documentElement.scrollTop;  
    } else if (document.body) {  
        scrollTop = document.body.scrollTop;  
    }  
    if (document.body.clientHeight && document.documentElement.clientHeight) {  
        clientHeight = (document.body.clientHeight < document.documentElement.clientHeight) ? document.body.clientHeight: document.documentElement.clientHeight;  
    } else {  
        clientHeight = (document.body.clientHeight > document.documentElement.clientHeight) ? document.body.clientHeight: document.documentElement.clientHeight;  
    }  
    // 比较大小，取最大值返回  
    scrollHeight = Math.max(document.body.scrollHeight, document.documentElement.scrollHeight); 
		
    if (scrollTop + clientHeight >= scrollHeight-1.0) {  
        return true;  
    } else {  
        return false;  
    }  
}

//获取url参数值
function getQueryString(name) {
	var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
	var r = window.location.search.substr(1).match(reg);
	if (r != null)
		return unescape(r[2]);
	return null;
}

//统一加载中
function loadingFun(text){
	if(!text){
		text = '加载中，请稍候…';
	}
	var loadHtml = '<div id="zxloading" style="display: none;">'
				 + '		<div id="loadtext">'
				 + '	<h1><img src="http://staticzxt.zxtom.com/images/eventImg/loading4.gif"></h1>'
				 + '	<p>'+text+'</p>'
				 + '</div>'
				 + '</div>';
	$("body").append(loadHtml);
}


//普通字符转换成转意符
function html2Escape(sHtml) {
	return sHtml.replace(/[<>&"]/g,function(c){return {'<':'&lt;','>':'&gt;','&':'&amp;','"':'&quot;'}[c];});
}

//转意符换成普通字符
function escape2Html(str) {
	var arrEntities={'lt':'<','gt':'>','nbsp':' ','amp':'&','quot':'"'};
	return str.replace(/&(lt|gt|nbsp|amp|quot);/ig,function(all,t){return arrEntities[t];});
}

//回顶部
$(".p_detail_gotop").live("click", function(){
	$('html,body').animate({scrollTop:0},300);
});