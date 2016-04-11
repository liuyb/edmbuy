//form表单数据序列化成json
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
//监听对象回车事件
(function($){  
    $.fn.enterEvent=function(callback){  
        $(this).bind('keyup', function(e){
        	if(e.keyCode == 13){
        		callback.apply(this, arguments);
        	}
        });  
    };  
})(jQuery);
//复选框勾选封装
(function($){  
    $(document).on({
    	mouseenter: function () {
	    	//$(this).addClass('rb-hover');
	    },
	    mouseleave: function () {
	    	//$(this).removeClass('rb-hover');
	    },
	    click: function (e) {
	    	var $target = $(e.target);
	    	var checkCol = $(this).find(".common_check");
	    	if(!$target.hasClass("undocheck")){
	    		if(checkCol.hasClass("common_check_on")){
	    			checkCol.removeClass("common_check_on");
	    		}else{
	    			checkCol.addClass("common_check_on");
	    		}
	    		handleSelectAll();
	    	}
	    }
    }, '.body-data tr');
    
    $("#all_check").on('click',function(){
		var THIS = $(this);
		if(THIS.hasClass("all_check_on")){
			THIS.removeClass("all_check_on");
			$(".common_check").removeClass("common_check_on");
		}else{
			THIS.addClass("all_check_on");
			$(".common_check").addClass("common_check_on");
		}
	});
})(jQuery);

//复选框全选事件
function handleSelectAll(){
	var item_num = $(".common_check").length;
	var select_num = $(".common_check_on").length;
	if(item_num == select_num){
		$("#all_check").addClass("all_check_on");
	}else{
		$("#all_check").removeClass("all_check_on");
	}
}
//根据选中状态拿到当前ID
function getSelectIds(obj){
	var ids = [];
	if(obj){
		ids.push(obj.attr("data-id"));
	}else{
		$(".common_check").each(function(){
			if($(this).hasClass("common_check_on")){
				ids.push($(this).closest("tr").first().attr("data-id"));
			}
		});
	}
	return ids;
}
/*function showSucc(text){
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
}*/
//表单验证
(function($){  
    $.fn.formValid=function(){  
    	var valid = true;
        $(this).find(":text").each(function(){
        	var OBJ = $(this);
        	var val = OBJ.val();
        	if(OBJ.attr("required")){
        		if(!val){
        			layer.msg('必填项不能为空！');
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
function isMobile(s){
	var reg = /^1\d{10}$/;  //11数字
    if(!s || !reg.test(s)){
		return false;
    }
    return true;
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
/**
 * confirm提示
 * @param msg
 * @param handler
 */
function showConfirm(msg, handler){
	layer.confirm(msg, {
	  btn: ['确定','取消'] //按钮
	}, function(){
		layer.closeAll();
		handler.apply(this,arguments);
	}, function(){
	   layer.closeAll();
	});
}
/**
 * 页面数据table处理
 * @param curpage
 * @param isinit
 * @param options
 */
function loadPageDataTable(curpage, isinit, options){
	var id = options.id;
	var url = options.url;
	var colspan = options.colspan;
	var container = $("#"+id);
	var data = [];
	if(typeof(pageQueryCondtion) != 'undefined' && typeof(pageQueryCondtion) == 'function'){
		data = pageQueryCondtion.call(this);
	}
	data.curpage = curpage ? curpage : 1;
	F.get(url, data, function(ret){
		var TR = "";
		if(!ret || !ret.result || !ret.result.length){
			TR = "<tr><td colspan='"+colspan+"' style='text-align:center;'>没有符合条件的数据!</td></tr>";
		}else{
			var result = ret.result;
			result.forEach(function(item){
				TR += costructRowData.call(this, item);
			});
		}
		container.find("tbody.body-data").html($(TR));
		if(isinit){
			generatePager(ret.curpage, ret.maxpage, ret.totalnum, options);
		}
		//全选框选中时去除全选框
		if($('#all_check') && $('#all_check').hasClass("common_check_on")){
			$('#all_check').removeClass("common_check_on");
		}
		if(typeof(afterLoadRender) != 'undefined' && typeof(afterLoadRender) == 'function'){
    		afterLoadRender.call(this, ret);
    	}
	});
}

//生成分页
function generatePager(pageNo, totalPage, totalRecords, options){
	//生成分页
	kkpager.generPageHtml({
		pno : pageNo,
		//总页码
		total : totalPage,
		//总数据条数
		totalRecords : totalRecords,
		isGoPage : false,
		mode : 'click',
		click : function(n){
			this.selectPage(n);
			loadPageDataTable(n, false, options);
		}
	}, true);
};
