/*!
 * global js, non-business util functions
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
//Core function wrapper
;function myParseInt(val){
	return (undefined==val || ''==val.trim()) ? 0 : parseInt(val);
}
;function myParseFloat(val){
	return (undefined==val || ''==val.trim()) ? 0 : parseInt(val);
}

//img lazyload
;function imgLoaded(img) {
	img.setAttribute('data-loaded','1');
}
;function imgLazyLoad(img, thesrc) {
	img.onerror = img.onload = null;
	var _img = new Image();
	_img.onload = function() {
		this.onload = null;
		img.src = thesrc;
		imgLoaded(img);
	};
	_img.src = thesrc;
	return;
}

//Form checking
;function checkUsername(username){
	var _self = checkUsername;
	if(typeof _self.error == 'undefined'){
		_self.error = '';
	}
	var msg = '用户名规则为5-15个字母数字或下划线(首字母不能为数字)';
	var re = /^[a-zA-Z_][a-zA-Z_\d]{4,14}$/;
	if(username.match(re)==null){
		_self.error = msg;
		return false;
	}
	return true;
}
;function checkPwd(pwd){
	var _self = checkPwd;
	if(typeof _self.error == 'undefined'){
		_self.error = '';
	}
	var msg = '密码应为6-20个字符';
	if(pwd.length<6||pwd.length>20){
		_self.error = msg;
		return false;
	}
	return true;
}
;function checkEmail(email){
	var _self = checkEmail;
	if(typeof _self.error == 'undefined'){
		_self.error = '';
	}
	var msg = '邮箱格式不正确';
	var re = /^[\w\-\.]+@[\w\-]+(\.\w+)+$/;
	if(email.length<6||email.match(re)==null){
		_self.error = msg;
		return false;
	}
	return true;
}
;function checkMobile(mobile){
	var _self = checkMobile;
	if(typeof _self.error == 'undefined'){
		_self.error = '';
	}
	var msg = '手机号格式不正确';
	var re = /^[0-9]{11}$/;
	if(mobile.match(re)==null){
		_self.error = msg;
		return false;
	}
	return true;
}

//去文本输入框末尾
;function toEditText(id) {
	var ele = id;
	if (typeof(id)=='string') ele = document.getElementById(id);
	F.placeCaretAtEnd(ele);
	return false;
}