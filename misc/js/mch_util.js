(function($){  
    $.fn.serializeJson=function(){  
        var serializeObj={};  
        var array=this.serializeArray();  
        var str=this.serialize();  
        $(array).each(function(){  
            if(serializeObj[this.name]){  
                if($.isArray(serializeObj[this.name])){  
                    serializeObj[this.name].push(this.value);  
                }else{  
                    serializeObj[this.name]=[serializeObj[this.name],this.value];  
                }  
            }else{  
                serializeObj[this.name]=this.value;   
            }  
        });  
        return serializeObj;  
    };  
})(jQuery); 
function showSucc(text){
	showBootstrapAlert('alert-success', text);
}
function showInfo(text){
	showBootstrapAlert('alert-info', text);
}
function showWarn(text){
	showBootstrapAlert('alert-warning', text);
}
function showError(text){
	showBootstrapAlert('alert-danger', text);
}
//显示一个弹出层
function showBootstrapAlert(cls, text){
	if(!cls || !text){
		return;
	}
	if($("."+cls).is(":visible")){
		$("."+cls).hide();
	}
	var info = $("."+cls);
	topCenterDOM(info);
	info.find("span").last().text(text).fadeIn();
	var st = setTimeout(function(){
		info.fadeOut();
		if(st){
			clearTimeout(st);
		}
	}, 3000);
} 
//居中显示一个层
function centerDOM(obj){
	var screenWidth = $(window).width(), screenHeight = $(window).height();  //当前浏览器窗口的 宽高
	var scrolltop = $(document).scrollTop();//获取当前窗口距离页面顶部高度
	var objLeft = (screenWidth - obj.width())/2 ;
	var objTop = (screenHeight - obj.height())/2 + scrolltop;
	obj.css({left: objLeft + 'px', top: objTop + 'px','display': 'block'});
}
//中上部显示一个层
function topCenterDOM(obj){
	var screenWidth = $(window).width(), screenHeight = $(window).height();  //当前浏览器窗口的 宽高
	var scrolltop = $(document).scrollTop();//获取当前窗口距离页面顶部高度
	var objLeft = (screenWidth - obj.width())/2 ;
	var objTop = obj.height() + scrolltop;
	obj.css({left: objLeft + 'px', top: objTop + 'px','display': 'block'});
}
//表单验证
(function($){  
    $.fn.formValid=function(){  
    	var valid = true;
        $(this).find(":text").each(function(){
        	var OBJ = $(this);
        	var val = OBJ.val();
        	if(OBJ.attr("required")){
        		if(!val){
        			layer.msg('必填不能为空！');
        			OBJ.focus();
        			valid = false;
        			return false;
        		}
        	}
        	var type = OBJ.attr("data-type");
        	if(type && "money" == type){
        		if(!isMoney(val)){
        			layer.msg('请输入正确的金额！');
        			OBJ.focus();
        			valid = false;
        			return false;
        		}
        	}
        	if(type && "positive" == type){
        		if(!isPositiveNum(val)){
        			layer.msg('请输入正整数！');
        			OBJ.focus();
        			valid = false;
        			return false;
        		}
        	}
        });
        return valid;
    };  
})(jQuery); 
//图片上传
function image_upload(url, e, obj, callback){
    var files = e.target.files;
    var file = files[0];
    var valid = false;
    if (file && file.type && file.type) {
    	var reg = /^image/i;
    	valid = reg.test(file.type);
    }
    if(!valid){
    	layer.msg('请选择正确的图片格式上传，如：JPG/JPEG/PNG/GIF ');
		return;
    }
    var fr = new FileReader();
    fr.onload = function(ev) {
    	var img = ev.target.result;
    	F.postWithLoading(url, {img:img}, function(ret){
    		if(ret.flag=='SUC'){
    			callback(ret, obj);
    		}else{
    			layer.msg(ret.errMsg);
    		}
    	});
    };
  	fr.readAsDataURL(file);
}
function isMoney(s){
	if(!s || s == 0){//不验证空
		return true;
	}
    if (isNaN(s)) {
        return false;
    }
    return true;
}
function isPositiveNum(s){
	if(!s || s == 0){//不验证空
		return true;
	}
	var type = /^[0-9]*[1-9][0-9]*$/;
	var re = new RegExp(type);
	if (re.test(s)) {
		return true;
	}
	return false;
}
/**
 * 根据传入的多个数组返回笛卡尔数组
 * @param arrIndex 
 * @param aresult
 * @param oriArr [[1,2],[3,4]]
 * @param result
 */
function dke_Array(arrIndex,aresult, oriArr, result)
{
  if(arrIndex>=oriArr.length) {result.push(aresult);return;};
  var aArr=oriArr[arrIndex];
  if(!aresult) aresult=new Array();
  for(var i=0;i<aArr.length;i++)
  {
    var theResult=aresult.slice(0,aresult.length);
    theResult.push(aArr[i]);
    dke_Array(arrIndex+1,theResult, oriArr, result);
  }
}

function showMsg(msg){
	layer.msg(msg);
}