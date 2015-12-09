/*!
 * admin js
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
(function( $, F, w, UNDEF ) {
	
	// resize ifr-body height
	F.resize_bodyheight = function(){
		if (typeof F.ifrhead_height == 'undefined') {
			F.ifrhead_height = $('#ifr-head').height();
		}
		if (typeof F.ifrbody == 'undefined') {
			F.ifrbody = $('#ifr-body');
		}
		var _bh=$(document).height()-F.ifrhead_height;
		F.ifrbody.css({height:_bh+'px'});
		var $ifrmain = $('#ifr-main');
		if ($ifrmain.length>0) {
			var $ifrfoot = $('#ifr-foot');
			var _fh = 0;
			if ($ifrfoot.length>0) {
				_fh = $ifrfoot.outerHeight();
			}
			$ifrmain.css({height:(_bh-_fh-20)+'px'}); //20 = paddingTop(10) + paddingBottom(10)
		}
	};
	
	F.checkListAll = function(obj) {
		$targets = $(obj).parents('.listTable').find('.rb .chkrid');
		if ($(obj).is(':checked')) {
			$targets.prop('checked',true);
			$targets.parents('.rb').addClass('rb-on');
		}
		else {
			$targets.prop('checked',false);
			$targets.parents('.rb').removeClass('rb-on');
		}
	};
	F.getListChkIds = function(selector) {
		selector = selector==undefined?'#ifr-main .chkrid:checked':selector;
		$ridobj = $(selector);
		rids = [];
		if ($ridobj.length) {
			$ridobj.each(function(){
				rids.push($(this).val());
			});
		}
		return rids;
	};
	F.confirmListDelete = function(obj, q, pretit){
		if (q==undefined || q=='') return false;
		if (pretit==undefined || pretit=='') pretit = '';
		else pretit = pretit+"\n\n";
		
		var tit = pretit+'确定删除该记录？';
		var rel = $(obj).attr('data-rid');
		var isbatch = rel=='batch' ? 1 : 0;
		var rids = [rel];
		var $ridobj = null;
		if (isbatch) {
			rids = this.getListChkIds();
			if (!rids.length) {
				alert('请先选择记录');
				return false;			
			}
			else if (rids.length>0) {
				tit = pretit+'确定删除这些记录？';
			}
		}
		if (confirm(tit)){
			F.post(genurl(q),{'rids[]':rids}, function(data){
				rids = typeof(data.rids)=='object' ? data.rids : rids;
				if (!isbatch) {
					if (rids.length) {
						$(obj).parent().parent().fadeOut('slow', function(){$(this).remove();});
						showTopPrompt('已删除');
					}
					else {
						showTopPrompt('删除失败', 'error');
					}	
				}
				else {
					var $listtb = $('#ifr-main .listTable');
					for(var i=rids.length-1; i>=0; i--) {
						$('input.chkrid[value='+rids[i]+']', $listtb).parent().parent().fadeOut('slow', function(){$(this).remove();});
					}
					showTopPrompt('已删除');
				}
			});
		}
		return false;
	};
	F.confirmListRecommend = function(obj, q, act){
		if (q==undefined || q=='') return false;
		act = !!act ? 1 : 0; //1:do;0:undo
		
		var tit = '确定推荐该内容？';
		var rel = $(obj).attr('data-rid');
		var isbatch = rel=='batch' ? 1 : 0;
		var rids = [rel];
		var $ridobj = null;
		if (isbatch) {
			rids = this.getListChkIds();
			if (!rids.length) {
				alert('请先选择内容记录');
				return false;			
			}
			else if (rids.length>0) {
				if (act) tit = '确定推荐这些内容记录？';
				else tit = '确定解除推荐这些内容记录？';
			}
		}
		else {
			if (act) tit = '确定推荐该内容记录？';
			else tit = '确定解除推荐该内容记录？';	
		}
		if (confirm(tit)){
			F.post(genurl(q),{'rids[]':rids,'act':act}, function(data){
				if (act) showTopPrompt('推荐成功！');
				else showTopPrompt('解除推荐成功！');
				F.hashReload();
			});
		}
		return false;
	};
	F.confirmListSuspend = function(obj, q, act){
		if (q==undefined || q=='') return false;
		act = !!act ? 1 : 0; //1:挂起;0:激活
		
		var tit = '确定挂起该记录？';
		var rel = $(obj).attr('data-rid');
		var isbatch = rel=='batch' ? 1 : 0;
		var rids = [rel];
		var $ridobj = null;
		if (isbatch) {
			rids = this.getListChkIds();
			if (!rids.length) {
				alert('请先选择记录');
				return false;			
			}
			else if (rids.length>0) {
			  if (act) tit = '确定挂起这些记录？';
			  else tit = '确定激活这些记录？';
			}
		}
		else {
		  if (act) tit = '确定挂起该记录？';
		  else tit = '确定激活该记录？';	
		}
		if (confirm(tit)){
			F.post(genurl(q),{'rids[]':rids,'act':act}, function(data){
		    if (act) showTopPrompt('已挂起');
		    else showTopPrompt('已激活');
				F.hashReload();
			});
		}
		return false;
	};
	F.confirmListRelated = function(obj, q, act){
		if (q==undefined || q=='') return false;
		act = !!act ? 1 : 0; //1:关联;0:取消关联
		
		var tit = '';
		var rel = $(obj).attr('data-rid');
		var isbatch = rel=='batch' ? 1 : 0;
		var rids = [rel];
		var $ridobj = null;
		if (isbatch) {
			rids = this.getListChkIds();
			if (!rids.length) {
				alert('请先选择记录');
				return false;			
			}
			else if (rids.length>0) {
			  if (act) tit = '确定关联这些记录？';
			  else tit = '确定取消关联这些记录？';
			}
		}
		else {
		  if (act) tit = '确定关联这些记录？';
		  else tit = '确定取消关联这些记录？';	
		}
		if (confirm(tit)){
			F.post(genurl(q),{'rids[]':rids,'act':act}, function(data){
		    if (act) showTopPrompt('已关联');
		    else showTopPrompt('已取消关联');
				F.hashReload();
			});
		}
		return false;
	};
	F.confirmListAction = function(obj, q, tit){
		if (q==undefined || q=='') return false;
		
		var rel = $(obj).attr('data-rid');
		var isbatch = rel=='batch' ? 1 : 0;
		var ids = [rel];
		var $ridobj = null;
		if (isbatch) {
			ids = this.getListChkIds();
			if (!ids.length) {
				alert('请先选择记录');
				return false;			
			}
		}
		if (confirm(tit)){
			F.post(genurl(q),{'ids[]':ids}, function(data){
				if (data.flag=='OK') {
					showTopPrompt('操作成功');
				}
				else {
					showTopPrompt('数据库处理出错', 'error');
				}
				F.hashReload();
			});
		}
		return false;
	};
	F.confirmChangeStatus = function(obj, q, statusTo, cfmTit){
		if (q==undefined || q=='') return false;
		if (statusTo==undefined || statusTo=='') return false;
		
		var tit = '确定更改该记录的状态？';
		var rel = $(obj).attr('data-rid');
		var isbatch = rel=='batch' ? 1 : 0;
		var rids = [rel];
		var $ridobj = null;
		if (isbatch) {
			rids = this.getListChkIds();
			if (!rids.length) {
				alert('请先选择记录');
				return false;			
			}
			else if (rids.length>0) {
				tit = '确定更改这些记录的状态？';
			}
		}
		if (cfmTit==undefined || cfmTit=='') {
			cfmTit = tit;
		}
		
		if (confirm(cfmTit)){
			F.post(genurl(q),{'rids[]':rids, 'statusto':statusTo}, function(data){
				if (data.flag=='OK') {
					showTopPrompt('操作成功','ok',5);
				}
				else {
					showTopPrompt('数据库处理出错', 'error',3);
				}
				F.hashReload();
			});
		}
		return false;
	};
	
	w.inputBlur = function(obj){
		$(obj).removeClass('inptxt-focus');
	};
	w.inputFocus = function(obj){
		$(obj).addClass('inptxt-focus');
	};
	w.set_curnav = function(curcls) {
		if (w._nav == UNDEF) w._nav = $('#htabs-bar');
		$('.htabs-list',w._nav).removeClass('htabs-list-on');
		if (!curcls) curcls = 'sy';
		$('.'+curcls,w._nav).addClass('htabs-list-on');
		return false;
	};
	w.go_hashreq = function(hash, maxage) {
		var data = {};
		if (maxage) data.maxage = parseInt(maxage);
		F.hashLoad(hash,data,function(data){
			
		},{container: '#ifr-body'});
		return false;
	};
	
	// On document ready
	$(w.document).ready(function(){
		
		$(w).resize(function(){F.resize_bodyheight();});
		//$(w).resize();
		
		var init_hash = F.getHash();
		if (!init_hash) init_hash = '#'+w.location.pathname;
		w.go_hashreq(init_hash);
		
		// Bind an event to window.onhashchange
		$(w).hashchange( function(){
			w.go_hashreq();
		});
		
		var $ifrbody = $('#ifr-body');
		
		// Bind hashreq link click event
		$ifrbody.on('a.hashreq', 'click', function(){ 
			var hash = $(this).attr('href');
			var maxage = $(this).attr('data-maxage');
			return w.go_hashreq(hash, maxage);
		});
		
		// listbtn mo effect
		$ifrbody.on({
		    mouseenter: function () {
				$(this).addClass('listbtn-active');
		    },
		    mouseleave: function () {
				$(this).removeClass('listbtn-active');
		    }
		},'.listbtn');
		
		// list mouse effect
		$ifrbody.on({
		    mouseenter: function () {
		    	$(this).addClass('rb-hover');
		    },
		    mouseleave: function () {
		    	$(this).removeClass('rb-hover');
		    },
		    click: function (e) {
		    	var $target = $(e.target);
		    	var $o;
		    	if (!$target.hasClass('chkrid')) {
				    $target = $(this);
				    $o = $(this).find('.chkrid');
				    if ($o.prop('checked')) $o.prop('checked',false);
				    else $o.prop('checked', true);
				}
		    	else {
		    		$o = $target;
		    	}
		    	
		    	// check all
		    	var $ltb = $target.parents('.listTable');
		    	var totnum = $ltb.find('input.chkrid').length;
		    	var chknum = $ltb.find('input.chkrid:checked').length;
		    	if (chknum===totnum) $('#chkListAll').prop('checked',true);
		    	else if (chknum===0) $('#chkListAll').prop('checked',false);
		    	
		    	// highlight
		    	if ($o.is(':checked')) $(this).addClass('rb-on');
		    	else $(this).removeClass('rb-on');
		    }
		},'.listTable .rb');
		
		//list head click event
		/*
		$('.listTable .sortfield', $ifrbody)
		.attr('title', '点击排序')
		.on('click', function(){
		  var hashurl = $(this).attr('data-hashurl');
		  var ow = $(this).find('b.icon').hasClass('icon-list-down') ? 'asc' : 'desc';
		  if(hashurl.indexOf(',')<0) {
		    hashurl += ',order='+ow;
		  }
		  else {
		    hashurl += '&order='+ow;
		  }   
		  //return F.triggerHashLoad(hashurl);
		});
		*/
		
		//form post
		$ifrbody.on('submit', '#formPost', function(e){
			if (typeof F.formPost == 'function') {
				F.formPost();
			}
			return false;
		});
	});
	
})(jQuery, FUI, this);

/*-------------------------Page Message Prompt-------------------------BEGIN*/
//: parameter:
//: object      : DOM id, used as in getElementById(), no matter whether having prefix: '#', required
//: msg         : 'Prompting Message', required
//: type        : 'msg', 'ok', 'error', 'warning', 'loading' OR '', required
//: waitsec     : waiting how many seconds for next action, optional
//: nextaction  : what is the next action: 'reload' or go url, optional
function showPagePrompt(object, msg, type, waitsec, nextaction, urlprefix, disapear) {
	
	//~ show message
	object = object.indexOf('#')==0 ? object : '#' + object;
	$obj   = $(object);
	type   = (typeof(type)=='undefined' || type=='')?'msg':type;
	urlprefix = (typeof(urlprefix)=='undefined' || urlprefix=='') ? STATIC_SITEURL : urlprefix;
	var prompt_css = 'prompt-' + type;
	msg = '<span>' + msg + '</span>';
	$obj.removeClass().addClass(prompt_css).html(msg).show();
	disapear = typeof(disapear)=='undefined' ? true : disapear;
	
	//~ if show loading, no need closed the prompting
	if(type=='loading' || !disapear) return;
	
	//~ hide message prompt area
	var sleepsec = _gWaitWarn*1000; //option: _gWaitSucc(1s), _gWaitWarn(2s), _gWaitFail(3s)
	if ( typeof(waitsec) != 'undefined' && waitsec>0 ) {
		sleepsec = waitsec*1000;
	}
	sleep(this,sleepsec);
	this.nextStep=function() {
	  $obj.hide();
	  if ( typeof(nextaction) != 'undefined' && nextaction != '' ) {
	    if ( nextaction=='reload' ) {
				window.location.reload();
	    } else if (nextaction=='parent.reload') {
				window.parent.location.reload();
	    } else {
				window.location.href = nextaction;
	    }
	  }
	}
	return false;
};
//: parameter:
//: msg         : 'Prompting Message', required
//: type        : 'msg', 'ok', 'warning', 'error', 'loading' OR '', required
//: waitsec     : waiting how many seconds for next action, optional
//: nextaction  : what is the next action: 'reload' or go url, or a callback function, optional
function showTopPrompt(msg, type, waitsec, nextaction) {
	
	//~ show message
	$obj = $('#mainMsgBox');
	type = (typeof(type)=='undefined' || type=='') ? '' : type;
	var csscls = 'msg' + ((type=='ok'||type=='msg'||type=='')?'':' msg-'+type);
	$obj.find('span').text(msg).attr('class', csscls);
	$obj.show();
	
	//~ if show loading, no need closed the prompting
	if(type=='loading') return;
	
	//~ hide message prompt area
	var sleepsec = _gWaitWarn*1000; //option: _gWaitSucc(1s), _gWaitWarn(2s), _gWaitFail(3s)
	if ( typeof(waitsec) != 'undefined' && waitsec>0 ) {
		sleepsec = waitsec*1000;
	}
	
	//~ do next action immediately
	setTimeout(function(){
	  var _t = typeof(nextaction);
	  if (_t=='function') {
	  	nextaction();
	  }
	  else if (_t=='string') {
	    if ( nextaction=='reload' ) {
				window.location.reload();
	    } else if (nextaction=='parent.reload') {
				window.parent.location.reload();
	    } else {
				window.location.href = nextaction;
	    }
	  }
	}, 500);
	setTimeout(function(){$obj.fadeOut('slow');}, sleepsec);
	return false;
};

//~ gen q url 
function genurl(q) {
  var url = '/';
  if (typeof(q)=='string' && q!='') {
	  url += q;
  }
  return  url;
};
//~ url connecter
function urlconnecter() {
	return '?';
};
//~ gen page part of url
function urlpagepart(page) {
	if(typeof(page)=='undefined' || page < 1) page = 1;
	return 'p=' + page;
};
/*-------------------------Page Message Prompt---------------------------END*/

//---------------弹出对话框的配置信息------------------ 
//~ dialogType=2: iframe way
//reqtype: 0: pure ifram; 1: iframe ajax; 2: general ajax, 3: pure html, default to 1 
//
//config_ex including: 
//isHaveBGCover: true or false，是否有背景蒙布(背景是否可点击)
//isHaveTitle: true or false，是否有标题栏
//isHaveTitleBG: true or false，标题栏是否有背景色
//isClose: true or false，是否需要关闭按钮
//isReloadOnClose: true or false，关闭对话框时是否刷新页面 
//titleCssType: 标题栏样式类型，1：默认蓝色；2：红色
//onCloseCallback: callback function on closing，点击关闭时调用的外部函数
//afterCloseCallback: callback function after closing，点击关闭时调用的外部函数
//onLoadCallback: callback function on load，对话框加载完毕时调用的函数
function showAjaxDlg(gourl, title, width, height, reqtype, config_ex) {
	title = title==undefined?'':title;
	width = width==undefined?615:width;
	height = height==undefined?375:height;
	reqtype= reqtype==undefined?0:reqtype;
	
	config_ex = config_ex==undefined?{}:config_ex;
	config_ex.isHaveBGCover   = config_ex.isHaveBGCover==undefined ? true : config_ex.isHaveBGCover;
	config_ex.isHaveTitle     = config_ex.isHaveTitle==undefined ? true : config_ex.isHaveTitle;
	config_ex.isHaveTitleBG   = config_ex.isHaveTitleBG==undefined ? true : config_ex.isHaveTitleBG;
	config_ex.isClose         = config_ex.isClose==undefined ? true : config_ex.isClose;
	config_ex.isReloadOnClose = config_ex.isReloadOnClose==undefined ? false : config_ex.isReloadOnClose;
	config_ex.titleCssType    = config_ex.titleCssType==undefined ? 1 : config_ex.titleCssType;
	config_ex.onCloseCallback = typeof config_ex.onCloseCallback!='function' ? null : config_ex.onCloseCallback;
	config_ex.afterCloseCallback = typeof config_ex.afterCloseCallback!='function' ? null : config_ex.afterCloseCallback;
	config_ex.onLoadCallback  = typeof config_ex.onLoadCallback!='function' ? null : config_ex.onLoadCallback;

	var contentType = 1;
	if (reqtype>1) contentType=2;
	var config = { dialogType:2,contentType:contentType,width:width,height:height,scrollType:'no',
	               isBackgroundCanClick: !config_ex.isHaveBGCover,
	               isHaveTitle: config_ex.isHaveTitle,
	               isHaveTitleBG: config_ex.isHaveTitleBG,
	               isClose: config_ex.isClose,
	               isReloadOnClose: config_ex.isReloadOnClose,
	               titleCssType: config_ex.titleCssType,
	               onCloseCallback: config_ex.onCloseCallback,
	               afterCloseCallback: config_ex.afterCloseCallback,
	               onLoadCallback: config_ex.onLoadCallback};
	var pop=new Popup(config); _gPop = pop;
	if (reqtype>0 && contentType==1) {
		gourl = CONTEXT_PATH+'a/ajaxdlg.htm?w='+width+'&h='+height+'&url='+gourl;
	}
	pop.setContent("title", title);
	if (reqtype==3) {
		pop.setContent("contentHtml",gourl);
	}else{
		pop.setContent("contentUrl",gourl);
	}
	
	pop.build();
	pop.show();
};
//~ close popup modal dialog
function closePopup(isRefresh, callback, params){
	params = params || {};
	if (callback) callback;
	if (isRefresh) {
		window.parent._gPop.setConfig("isReloadOnClose", true);
		window.parent._gPop.reset();
	} else  {
		window.parent._gPop.close();	// must use 'parent' because of IFrame
	}
};
//~ reset popup modal dialog size
function resizePopup(iWidth, iHeight) {
	var pop = parent._gPop;
	pop.setConfig("width", iWidth);
	pop.setConfig("height", iHeight);
	pop.reSize();
	pop.show();
};
//~ change popup height
function changePopupHeight(iHeight) {
	var pop = parent._gPop;
	pop.changeHeight(iHeight);
};
//~ Eliminate the reduplication value, input format: "word1;word2;word3"
function tickDuplicate(str, sep) {
	sep = sep==undefined ? ',' : sep;
	str = str.trim();
	str	= str.replace(/(\s{1,})/g,sep);		// eliminate spacing char
	str	= str.replace(/\；/g	,sep);	// make sure separator is sep
	str	= str.replace(/\，/g	,sep);	// make sure separator is sep
	
	var arr = str.split(sep);
	var i = 0, j = 0;
	
	//~ set reduplicate value to ''
	for (i=0; i < arr.length; ++i){
		if (arr[i] == '') continue;
		for (j=i+1; j < arr.length; ++j){
			if ( arr[i] == arr[j] ) {
				arr[j] = '';
			}
		}
	}
	
	//~ re-gen the string
	var retstr = '';
	for (i=0; i < arr.length; ++i){
		if (arr[i] == '') continue;
		retstr += arr[i] + sep;
	}
	
	//~ return it
	return retstr.substring(0, retstr.length-1);  
}