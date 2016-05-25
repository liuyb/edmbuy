(function($) {
	// form表单数据序列化成json
	$.fn.serializeJson = function() {
		var serializeObj = {};
		var array = this.serializeArray();
		var str = this.serialize();
		$(array).each(
				function() {
					if (serializeObj[this.name]) {
						if ($.isArray(serializeObj[this.name])) {
							serializeObj[this.name].push(this.value);
						} else {
							serializeObj[this.name] = [
									serializeObj[this.name], this.value ];
						}
					} else {
						serializeObj[this.name] = this.value;
					}
				});
		return serializeObj;
	};
	// 监听对象回车事件
	$.fn.enterEvent = function(callback) {
		$(this).bind('keyup', function(e) {
			if (e.keyCode == 13) {
				callback.apply(this, arguments);
			}
		});
	};

	// 增对古老IE浏览器 不支持forEach 作覆写
	if (typeof Array.prototype.forEach != "function") {
		Array.prototype.forEach = function(fn, context) {
			for (var k = 0, length = this.length; k < length; k++) {
				if (typeof fn === "function"
						&& Object.prototype.hasOwnProperty.call(this, k)) {
					fn.call(context, this[k], k, this);
				}
			}
		};
	}
})(jQuery);

// 复选框勾选封装
(function($) {
	$(document).on({
		mouseenter : function() {
			$(this).css('background-color', '#fff6f2');
		},
		mouseleave : function() {
			$(this).css('background-color', '#fff');
		},
		click : function(e) {
			var $target = $(e.target);
			var checkCol = $(this).find(".common_check");
			if (!checkCol || !checkCol.length) {
				return;
			}
			if (!$target.hasClass("undocheck")) {
				if (checkCol.hasClass("common_check_on")) {
					checkCol.removeClass("common_check_on");
				} else {
					checkCol.addClass("common_check_on");
				}
				handleSelectAll();
			}
		}
	}, '.body-data tr');
})(jQuery);
$(function() {
	$("#all_check").on('click', function() {
		var THIS = $(this);
		if (THIS.hasClass("all_check_on")) {
			THIS.removeClass("all_check_on");
			$(".common_check").removeClass("common_check_on");
		} else {
			THIS.addClass("all_check_on");
			$(".common_check").addClass("common_check_on");
		}
	});
	// 监听页面input元素
	$(document).on('blur', 'input', function() {
		var input = $(this);
		var val = input.val();
		var type = input.data("type");
		if (val && type) {
			if (type == 'money') {
				checkInputIsMoney(input);
			} else if (type == 'positive') {
				if (!isPositiveNum(val)) {
					input.val("");
					showMsg("请输入正整数！");
					input.focus();
				}
			}
		}
	});
});
function isURL(str_url) {
	var strRegex = /^((https|http)?:\/\/)[a-zA-Z0-9]*(\.)([a-zA-Z]{3})/;
	var re = new RegExp(strRegex);
	// re.test()
	if (re.test(str_url)) {
		return true;
	} else {
		return false;
	}
}
function checkInputIsMoney(input) {
	var val = input.val();
	if (!isMoney(val) || val < 0) {
		input.val("");
		showMsg("请输入正确的金额！");
		input.focus();
	} else {
		if (val) {
			input.val(parseFloat(val).toFixed(2));
		}
	}
}
// 复选框全选事件
function handleSelectAll() {
	var item_num = $(".common_check").length;
	var select_num = $(".common_check_on").length;
	if (item_num == select_num) {
		$("#all_check").addClass("all_check_on");
	} else {
		$("#all_check").removeClass("all_check_on");
	}
}
// 根据选中状态拿到当前ID
function getSelectIds(obj) {
	var ids = [];
	if (obj) {
		ids.push(obj.attr("data-id"));
	} else {
		$(".common_check").each(function() {
			if ($(this).hasClass("common_check_on")) {
				ids.push($(this).closest("tr").first().attr("data-id"));
			}
		});
	}
	return ids;
}

/**
 * 通用dialog
 */
function showDialog(width, height, content, title) {
	var dialog = $("#pop_dialog");
	dialog.css({
		'width' : width,
		'height' : height
	});
	centerDOM(dialog);
	var $title = dialog.find(".title");
	var $content = dialog.find(".content");
	$content.height(height - (40 + 90));// title+button区
	$title.html(title);
	$content.html(content);
	$('.dialog_mask').show();
	dialog.show();
}

function showSucc(text) {
	showBootstrapAlert('alert-success', text);
}
function showInfo(text) {
	showBootstrapAlert('alert-info', text);
}
function showWarn(text) {
	showBootstrapAlert('alert-warning', text);
}
function showError(text) {
	showBootstrapAlert('alert-danger', text);
}
// 显示一个bootstrap弹出层
function showBootstrapAlert(cls, text) {
	if (!cls || !text) {
		return;
	}
	if ($("." + cls).is(":visible")) {
		$("." + cls).hide();
	}
	var info = $("." + cls);
	topCenterDOM(info);
	info.find("span").last().text(text).fadeIn();
	var st = setTimeout(function() {
		info.fadeOut();
		if (st) {
			clearTimeout(st);
		}
	}, 3000);
}
//DOM居中显示
function centerDOM(obj) {
	var screenWidth = $(window).width(), screenHeight = $(window).height(); // 当前浏览器窗口的
	// 宽高
	var scrolltop = $(document).scrollTop();// 获取当前窗口距离页面顶部高度
	var objLeft = (screenWidth - obj.width()) / 2;
	var objTop = (screenHeight - obj.height()) / 2 + scrolltop;
	obj.css({
		left : objLeft + 'px',
		top : objTop + 'px',
		'display' : 'block'
	});
}

//中上部显示一个层
function topCenterDOM(obj) {
	var screenWidth = $(window).width(), screenHeight = $(window).height(); // 当前浏览器窗口的
	var scrolltop = $(document).scrollTop();// 获取当前窗口距离页面顶部高度
	var objLeft = (screenWidth - obj.width()) / 2;
	var objTop = obj.height() + scrolltop;
	obj.css({
		left : objLeft + 'px',
		top : objTop + 'px',
		'display' : 'block'
	});
}
// 表单验证
(function($) {
	$.fn.formValid = function() {
		var valid = true;
		$(this).find(":text").each(function() {
			var OBJ = $(this);
			var val = OBJ.val();
			if (OBJ.attr("required")) {
				if (!val) {
					layer.msg('必填项不能为空！');
					OBJ.focus();
					valid = false;
					return false;
				}
			}
			var type = OBJ.attr("data-type");
			if (type && "money" == type) {
				if (!isMoney(val) || val < 0) {
					layer.msg('请输入正确的金额！');
					OBJ.focus();
					valid = false;
					return false;
				}
			}
			if (type && "positive" == type) {
				if (!isPositiveNum(val)) {
					layer.msg('请输入正整数！');
					OBJ.focus();
					valid = false;
					return false;
				}
			}
			if (type && "url" == type) {
				if (!isURL(val)) {
					layer.msg('请输入正确的网址！');
					OBJ.focus();
					valid = false;
					return false;
				}
			}
			if (type && "pwd" == type) {
				if (!checkPwd(val)) {
					OBJ.focus();
					valid = false;
					return false;
				}
			}
			if (type && "card_no" == type) {
				if (!isCardNo(val)) {
					OBJ.focus();
					valid = false;
					return false;
				}
			}
			if (type && "mobile" == type) {
				if (!isMobile(val)) {
					OBJ.focus();
					layer.msg('请输入正确的手机号！');
					valid = false;
					return false;
				}
			}
			//
		});
		return valid;
	};
})(jQuery);
// 图片上传
function image_upload(url, e, obj, callback) {
	var files = e.target.files;
	var file = files[0];
	var valid = false;
	if (file && file.type && file.type) {
		var reg = /^image/i;
		valid = reg.test(file.type);
	}
	if (!valid) {
		layer.msg('请选择正确的图片格式上传，如：JPG/JPEG/PNG/GIF ');
		return;
	}
	var fr = new FileReader();
	fr.onload = function(ev) {
		var img = ev.target.result;
		F.postWithLoading(url, {
			img : img
		}, function(ret) {
			if (ret.flag == 'SUC') {
				callback(ret, obj);
			} else {
				layer.msg(ret.errMsg);
			}
		});
	};
	fr.readAsDataURL(file);
}

function isCardNo(card) {
	// 身份证号码为15位或者18位，15位时全为数字，18位前17位为数字，最后一位是校验位，可能为数字或字符X
	var reg = /(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/;
	if (reg.test(card) === false) {
		showMsg("身份证输入不合法");
		return false;
	}
	return true;
}

function checkPwd(pwd) {
	var pwd = $.trim(pwd);
	if (pwd.length < 6) {
		showMsg("密码长度不能少于6位!");
		return false;
	} else if (pwd.length > 12) {
		showMsg("密码长度不能大于12位!");
		return false;
	}
	return true;
}
function isMoney(s) {
	if (!s || s == 0) {// 不验证空
		return true;
	}
	if (isNaN(s)) {
		return false;
	}
	return true;
}
function isPositiveNum(s) {
	if (!s || s == 0) {// 不验证空
		return true;
	}
	var type = /^[0-9]*[1-9][0-9]*$/;
	var re = new RegExp(type);
	if (re.test(s)) {
		return true;
	}
	return false;
}
function isMobile(s) {
	var reg = /^1\d{10}$/; // 11数字
	if (!s || !reg.test(s)) {
		return false;
	}
	return true;
}
/**
 * 校验kahao
 * 
 * @param bankno
 * @returns {boolean}
 */
function cardNoCheck(bankno) {
	if (bankno == "" || bankno == null) {
		showMsg("卡号输入不正确，请检查");
		return false;
	}
	var lastNum = bankno.substr(bankno.length - 1, 1);

	var first15Num = bankno.substr(0, bankno.length - 1);
	var newArr = new Array();
	for (var i = first15Num.length - 1; i > -1; i--) {
		newArr.push(first15Num.substr(i, 1));
	}
	var arrJiShu = new Array();
	var arrJiShu2 = new Array();

	var arrOuShu = new Array();
	for (var j = 0; j < newArr.length; j++) {
		if ((j + 1) % 2 == 1) {
			if (parseInt(newArr[j]) * 2 < 9)
				arrJiShu.push(parseInt(newArr[j]) * 2);
			else
				arrJiShu2.push(parseInt(newArr[j]) * 2);
		} else
			arrOuShu.push(newArr[j]);
	}

	var jishu_child1 = new Array();
	var jishu_child2 = new Array();
	for (var h = 0; h < arrJiShu2.length; h++) {
		jishu_child1.push(parseInt(arrJiShu2[h]) % 10);
		jishu_child2.push(parseInt(arrJiShu2[h]) / 10);
	}

	var sumJiShu = 0;
	var sumOuShu = 0;
	var sumJiShuChild1 = 0;
	var sumJiShuChild2 = 0;
	var sumTotal = 0;
	for (var m = 0; m < arrJiShu.length; m++) {
		sumJiShu = sumJiShu + parseInt(arrJiShu[m]);
	}

	for (var n = 0; n < arrOuShu.length; n++) {
		sumOuShu = sumOuShu + parseInt(arrOuShu[n]);
	}

	for (var p = 0; p < jishu_child1.length; p++) {
		sumJiShuChild1 = sumJiShuChild1 + parseInt(jishu_child1[p]);
		sumJiShuChild2 = sumJiShuChild2 + parseInt(jishu_child2[p]);
	}
	sumTotal = parseInt(sumJiShu) + parseInt(sumOuShu)
			+ parseInt(sumJiShuChild1) + parseInt(sumJiShuChild2);

	var k = parseInt(sumTotal) % 10 == 0 ? 10 : parseInt(sumTotal) % 10;
	var luhm = 10 - k;

	if (parseInt(sumJiShu) + parseInt(sumOuShu) == 0) {
		showMsg("卡号输入不正确，请检查");
		return false;
	}

	if (lastNum == luhm) {
		return true;
	} else {
		showMsg("卡号输入不正确，请检查");
		return false;
	}
}

/**
 * 根据传入的多个数组返回笛卡尔数组
 * 
 * @param arrIndex
 * @param aresult
 * @param oriArr
 *            [[1,2],[3,4]]
 * @param result
 */
function dke_Array(arrIndex, aresult, oriArr, result) {
	if (arrIndex >= oriArr.length) {
		result.push(aresult);
		return;
	}
	;
	var aArr = oriArr[arrIndex];
	if (!aresult)
		aresult = new Array();
	for (var i = 0; i < aArr.length; i++) {
		var theResult = aresult.slice(0, aresult.length);
		theResult.push(aArr[i]);
		dke_Array(arrIndex + 1, theResult, oriArr, result);
	}
}

function showMsg(msg) {
	layer.msg(msg);
}
/**
 * confirm提示
 * 
 * @param msg
 * @param handler
 */
function showConfirm(msg, handler) {
	layer.confirm(msg, {
		btn : [ '确定', '取消' ]
	// 按钮
	}, function() {
		layer.closeAll();
		handler.apply(this, arguments);
	}, function() {
		layer.closeAll();
	});
}

/**
 * 省市区关联公共函数
 * 
 * @param obj
 * @param type
 * @param selDom
 */
function regionChange(obj, type, selDom, url) {
	if (!url) {
		url = '/order/region';
	}
	var selectVal = $(obj).val();
	F.get(url, {
		type : type,
		region : selectVal
	}, function(ret) {
		var selectObj = $("#" + selDom);
		console.log(selectObj.length);
		var html = "<option value=\"\">请选择...</option>";
		var selectedVal = "";
		if (ret && ret.length) {
			for (var i = 0, len = ret.length; i < len; i++) {
				var op = ret[i];
				if (i == 0) {
					selectedVal = op.region_id;
				}
				html += "<option value=\"" + op.region_id + "\">"
						+ op.region_name + "</option>";
			}
		}
		selectObj.html($(html));
		if (selectedVal) {
			selectObj.val(selectedVal);
			selectObj.trigger('change');
		} else {
			var childId = $(obj).data("child");
			$("#" + childId).html($("<option value=\"\">请选择...</option>"));
		}
	});
}
/**
 * 页面数据table处理
 * 
 * @param curpage
 * @param isinit
 * @param options
 */
function loadPageDataTable(curpage, isinit, options) {
	var id = options.id;
	var url = options.url;
	var colspan = options.colspan;
	var container = $("#" + id);
	var data = [];
	if (typeof (pageQueryCondtion) != 'undefined'
			&& typeof (pageQueryCondtion) == 'function') {
		data = pageQueryCondtion.call(this);
	}
	data.curpage = curpage ? curpage : 1;
	F.get(url, data, function(ret) {
		var TR = "";
		if (!ret || !ret.result || !ret.result.length) {
			TR = "<tr><td colspan='" + colspan
					+ "' style='text-align:center;'>没有符合条件的数据!</td></tr>";
		} else {
			var result = ret.result;
			result.forEach(function(item) {
				TR += costructRowData.call(this, item);
			});
		}
		container.find("tbody.body-data").html($(TR));
		if (isinit) {
			generatePager(ret.curpage, ret.maxpage, ret.totalnum, options);
		}
		// 全选框选中时去除全选框
		$('#all_check').removeClass("all_check_on");
		if (typeof (afterLoadRender) != 'undefined'
				&& typeof (afterLoadRender) == 'function') {
			afterLoadRender.call(this, ret, data);
		}
	});
}

// 生成分页
function generatePager(pageNo, totalPage, totalRecords, options) {
	// 生成分页
	kkpager.generPageHtml({
		pno : pageNo,
		// 总页码
		total : totalPage,
		// 总数据条数
		totalRecords : totalRecords,
		isGoPage : false,
		mode : 'click',
		click : function(n) {
			this.selectPage(n);
			loadPageDataTable(n, false, options);
		}
	}, true);
};
