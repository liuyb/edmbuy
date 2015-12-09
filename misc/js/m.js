/*!
 * mobile common js
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
(function( $, F, w, UNDEF ) {
	
	F.isTouch = "createTouch" in document;
	F.isDownpullDisplay = true;
	
	// Set dom constants
	F.doms = {wrapper:"#rtWrap",activepage:"#activePage",nav:"#nav-1",scroller:".scrollArea",loading:"#loadingCanvas"};
	
	// Cache doms
	F.pageactive = $(F.doms.activepage);
	F.scrollarea = $('>'+F.doms.scroller,F.pageactive);
	F.pagebg     = $('>.pageBg',F.pageactive);
	
	// Scroll cookie initialization
	F.scroll2old = false;
	F.scroll_cookie_key = function(){
		return 'LS'+gData.currURI.replace(/(\/|\?|\&|\=|\%|\-)/g,'_');
	};
	
	// Loading effect
	F.loading_icons  = {};
	F.loadingStart = function(effect) {
		if (typeof(effect)=='undefined') effect = 'overlay'; //optional effect value: 'switch','overlay','pure'
		if (typeof(F.loading_canvas)=='undefined') F.loading_canvas = $(F.doms.loading);
		var opacity = 1;
		switch (effect) {
		case 'switch':
			F.pageactive.hide();
			break;
		case 'overlay':
			opacity = .75;
			break;
		case 'pure':
			opacity = 0;
			break;
		}
		F.loading_canvas.css('opacity',opacity).show();
		if (this.loading_icons[effect] == UNDEF) {
			var opts = {
					lines: 12, // The number of lines to draw
					length: 6, // The length of each line
					width: 2, // The line thickness
					radius: 6, // The radius of the inner circle
					corners: 1, // Corner roundness (0..1)
					rotate: 0, // The rotation offset
					direction: 1, // 1: clockwise, -1: counterclockwise
					color: '#000', // #rgb or #rrggbb or array of colors
					speed: 1, // Rounds per second
					trail: 60, // Afterglow percentage
					shadow: false, // Whether to render a shadow
					hwaccel: false, // Whether to use hardware acceleration
					className: 'spinner', // The CSS class to assign to the spinner
					zIndex: 2e9, // The z-index (defaults to 2000000000)
					top: '50%', // Top position relative to parent in px
					left: '50%' // Left position relative to parent in px
				};
			F.loading_icons[effect] = new Spinner(opts);
			F.loading_icons[effect].spin(F.loading_canvas.get(0));
		}
		else {
			F.loading_icons[effect].spin(F.loading_canvas.get(0));
		}
	};
	F.loadingStop = function(effect) {
		if (typeof(effect)=='undefined') effect = 'overlay';
		F.loading_icons[effect].stop();
		F.loading_canvas.hide();
		switch (effect) {
		case 'switch':
			F.pageactive.show();
			break;
		case 'overlay':
			break;
		case 'pure':
			break;
		}
	};
	// set content minimal height
	F.set_content_minheight = function(){
		if (typeof F.pagenav_height == 'undefined' || !F.pagenav_height) {
			F.pagenav_height = $(F.doms.nav).height();
		}
		if (typeof F.pageactive == 'undefined') {
			F.pageactive = $(F.doms.activepage);
		}
		var _bh=$(document).height()-F.pagenav_height;
		if (F.scrollarea.size()>0) {
			F.scrollarea.css({minHeight:_bh+'px'});
		}
	};
	// set iScroll object
	F.set_scroller = function(toY, runTimeout){
		if (typeof(toY)=='undefined') toY = false;
		if (typeof(runTimeout)=='undefined') runTimeout = 0;
		
		if (typeof(F.set_scroller.timer)=='number') {//避免连续的set_scroller被多次执行
			clearTimeout(F.set_scroller.timer);
			F.set_scroller.timer = UNDEF;
		}
		
		F.set_scroller.timer = setTimeout(function(){
			if(typeof(F.oIScroll)!='object') {
				F.oIScroll = new IScroll(F.doms.activepage,{probeType:2,mouseWheel:true,scrollbars:true,fadeScrollbars:true,momentum:true});
				F.oIScroll.on('beforeScrollStart',F._beforeScrollStart);
				F.oIScroll.on('scrollCancel',F._scrollCancel);
				F.oIScroll.on('scrollStart',F._scrollStart);
				F.oIScroll.on('scroll',F._scrolling);
				F.oIScroll.on('scrollEnd',F._scrollEnd);
				F.oIScroll.on('flick',F._flick);
			}else{
				F.oIScroll.refresh();
			}
			if (typeof(toY)=='boolean') {
				if(true===toY) { // is scroll to top
					F.oIScroll.scrollTo(0,0,1000);
				}
			}
			else {
				toY = parseInt(toY);
				F.oIScroll.scrollTo(0,toY);
			}
			F.set_scroller.timer = UNDEF;
		},runTimeout);
	};
	//outcall: F.onBeforeScrollStart
	F._beforeScrollStart = function() {
		F.event.execEvent('beforeScrollStart',this);
	};
	//outcall: F.onScrollCancel
	F._scrollCancel = function() {
		F.event.execEvent('scrollCancel',this);
	};
	//outcall: F.onScrollStart
	F._scrollStart = function() {
		F.event.flag.downpull = (0===this.y ? true :false);
		F.event.execEvent('scrollStart',this);
	};
	//outcall: F.onScrolling
	F._scrolling = function() {
		var dp_type = 'downPull';
		if (this.y > 20) {
			
			if(F.isDownpullDisplay) F.pagebg.show();
			else F.pagebg.hide();
			
			if(F.event.flag.downpull
		       && (typeof(F.event._events[dp_type])=='object')
		       && F.event._events[dp_type].length>0)
			{
				if (this.y > 50) {
					F.event.flag.downpull = false;
					F.event.execEvent(dp_type,this);
				}
			}
		}
		else { //avoiding too many calling
			F.pagebg.hide();
		}
		F.event.execEvent('scrolling',this);
	};
	//outcall: F.onScrollEnd
	F._scrollEnd = function() {
		F.event.flag.downpull = false;
		F.event.execEvent('scrollEnd',this);
	};
	F._flick = function() {
		F.event.execEvent('flick',this);
	};
	//事件挂载
	F.onBeforeScrollStart = function(fn) {
		F.event.on('beforeScrollStart',fn);
	};
	F.onScrollCancel = function(fn) {
		F.event.on('scrollCancel',fn);
	};
	F.onScrollStart = function(fn) {
		F.event.on('scrollStart',fn);
	};
	F.onScrolling = function(fn) {
		F.event.on('scrolling',fn);
	};
	F.onScrollEnd = function(fn) {
		F.event.on('scrollEnd',fn);
	};
	F.onFlick = function(fn) {
		F.event.on('flick',fn);
	};
	F.onScrollDownPull = function(fn) {
		F.event.on('downPull',fn);
	};
	
	//img等dom onload 事件
	F.onDocLoad = function(fn) {
		F.event.on('docLoad',fn);
	};
	F._onDocLoad= function(wrap) {
		if (typeof(wrap)=='undefined') wrap = 'body';
		if (typeof(wrap)=='string') wrap = $(wrap);
		var oThis = this;
		(function(){
			var s = $('img[data-loaded=0]',wrap).size();
			if (0===s) {
				F.event.execEvent('docLoad',oThis);
			}else{
				setTimeout(arguments.callee,100);
			}
		})();
	};
	//ajax document ready
	F._onAjaxDocReady= function(wrap) {
		if (typeof(wrap)=='undefined') wrap = F.scrollarea;
		$('img',wrap).attr('data-loaded',0).load(function(){ $(this).attr('data-loaded',1); });
		F.set_scroller(false,100);
	};
	
	//获取内容包裹元素
	F.getContainerEle = function() {
		var $_c = F.scrollarea;
		if ($_c.size()==0) $_c = F.pageactive;
		return $_c;
	};
	
	//附加到末尾的script
	F.renderAppend = function() {
		return '<script type="text/javascript">F.onDocLoad(function(){F.set_scroller(!F.scroll2old?false:Cookies.get(F.scroll_cookie_key()),100)});$(function(){var c = F.getContainerEle();F._onAjaxDocReady(c);F._onDocLoad(c)});</script>';
	};
	
	// Page functions
	// hash请求
	w.go_hashreq = function(hash, maxage, options) {
		F.loadingStart('switch');
		
		var data = {};
		if (maxage) {
			data.maxage = parseInt(maxage);
		}
		if (typeof options == 'undefined') {
			options = {};
		}
		
		options = $.extend({
			container: F.getContainerEle(),
			renderPrepend: '<script type="text/javascript">F.event.reset();</script>'
		},options);
		
		var _effect = 'none';
		if (typeof (options.effect)!='undefined') {
			_effect = options.effect;
		}
		
		F.hashLoad(hash,data,function(ret){
			var _ct = F.scrollarea;
			var toPreClass = 'ui-page-pre-in';
			var toClass = 'slide in';
			if (_effect=='slide_right_in') {
				_ct.addClass(toPreClass);
				_ct.animationComplete(function(){
					_ct.removeClass(toClass);
				});
			}
			F.loadingStop('switch');
			if (_effect=='slide_right_in') {
				_ct.removeClass( toPreClass ).addClass( toClass );
			}
			F.set_scroller(true,500);
			
		},options);
		
		return false;
	};
	
	// 一般ajax请求
	w.go_ajaxreq = function(gouri) {
		if (''==gouri) gouri = '/';
		F.loadingStart('switch');
		F.getJSON(gouri, {maxage:0,_hr:1}, function(ret){
			if (ret.flag=='SUC') {
				ret.body += F.renderAppend();
				F.getContainerEle().html(ret.body);
			}
			F.loadingStop('switch');
		});
	};
	
	// On document ready
	$(function(){
		
		// Bind window.resize event
		setTimeout(function(){
			//F.set_content_minheight();
			FastClick.attach(w.document.body);
		},100);

		// Prevent default scroll action
		w.document.addEventListener('touchmove', function (e) {
			//e.preventDefault();
		}, false);
		
		// Bind window.onhashchange event
		//$(w).hashchange(function(){w.go_hashreq();});
		
		// Hash trigger
		//var init_hash = F.getHash();
		//if (!init_hash) {w.go_hashreq(null,null,{changeHash:false});}
		//else {$(w).hashchange();}
		
		// Req page ajax
		w.go_ajaxreq(gData.currURI);
		
	});
	
})(jQuery, FUI, this);

/***** Util Functions *****/
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

//显示和隐藏弹出框
;function show_popdlg(title,content) {
  var me = show_popdlg;
  if (typeof me._wrap == 'undefined') {
    me._wrap = $('#popdlg');
  }
  me._wrap.find('.poptit .txt').html(title);
  me._wrap.find('.popcont').html(content);
	
  var inPreClass = 'ui-page-pre-in',
	     inClass = 'slideup in';
  me._wrap.addClass(inPreClass).show();
  me._wrap.animationComplete(function(){
    me._wrap.removeClass(inClass);
  });
  me._wrap.removeClass(inPreClass).addClass(inClass);
}
;function hide_popdlg(callback) {
  var outClass = 'slideup out reverse';
  show_popdlg._wrap.animationComplete(function(){
    show_popdlg._wrap.removeClass(outClass).hide();
    var to = typeof callback;
    if (to!='undefined') {
    	if (to=='function') {
    		callback();
    	}
    	else { //是一个dom元素
    		if ($('#topnav-btn-filter').size()>0) {
    			$('#topnav-btn-filter').attr('rel',1).find('.triangle').removeClass('triangle-up');
    		}
    	}
    }
  });
  show_popdlg._wrap.addClass(outClass);
}

//主导航显示和隐藏
;function nav_show(nav_no, nav, nav_second) {
	if (nav_no===undefined) nav_no = 1;
	
	var $thenav = $('#nav-'+nav_no);
	$('a', $thenav).removeClass('cur');
	$('a[rel='+nav+']', $thenav).addClass('cur');
	$('.nav').hide();
	$thenav.show();

	nav_no = parseInt(nav_no);
	F.pagenav_height = $thenav.height();
	
	switch (nav_no) {
	case 1:
		break;
	case 2:
		break;
	case 3:
		break;
	case 4:
		break;
	}
	
	return false;
}
;function nav_hide(nav_no) {
	if (nav_no===undefined) no = 0;
	nav_no = parseInt(nav_no);
	if (0===nav_no) $('.nav').hide();
	else $('#nav-'+nav_no).hide();
	return false;
}

//去文本输入框末尾
;function toEditText(id) {
	var ele = id;
	if (typeof(id)=='string') ele = document.getElementById(id);
	F.placeCaretAtEnd(ele);
	return false;
}

//查看更多内容
;function see_more(_self, callback) {
    var page = $(_self).attr('data-next-page');
    var total_page = $(_self).attr('data-total-page');
    page = parseInt(page);
    total_page = parseInt(total_page);
    if(page>total_page){
      return false;
    }
    var hash = location.hash;
    var connector = hash.indexOf(',')!=-1 ? '&':',';
    hash += connector+'p='+page;
    F.loadingStart();
    F.hashReq(hash,{},function(data){
    	
      F.loadingStop();
      callback(data.body+F.renderAppend());
      $(_self).attr('data-next-page', ++page);
      if(page>total_page){
        $(_self).hide();
      }
      
    },{changeHash:false});
}

//喜欢、收藏操作
;function action(nid, act){
  var _this = action;
  var ev = window.event || action.caller.arguments[0];
  var src = ev.target || ev.srcElement;
  if(src.nodeName!='A'){
    src = src.parentNode;
  }
  if(typeof _this.running =='undefined' || _this.running == 0){
    _this.running = 1;
    F.loadingStart();
  }else{
    return;
  }

  $.post('/node/action/'+act, {nid:nid}, function(data){
    F.loadingStop();
    _this.running = 0;
    if(data.flag=='SUC'){
      F.clearCacheAll();
      if(act=='love'){
        $(src).children('span').text(data.data.cnt);
        if(data.data.acted==1){
          $(src).children('i').addClass('active_num');
        }else{
          $(src).children('i').removeClass('active_num');
        }  
      }else if(act=='collect'){
        if(data.data.acted==1){
          $(src).children('span').text('已收藏');  
          $(src).children('i').addClass('active_s');
        }else{
          $(src).children('span').text('收藏');
          $(src).children('i').removeClass('active_s');
        }
      }
    }else{
    	alert(data.msg);
    }
  }, 'json');
}

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

//设置购物车页面操作动作
;function set_cart_action(cart_num, record_num) {
	cart_num = parseInt(cart_num);
	record_num = parseInt(record_num);
	
	var cartact = {editmode: 0,cart_num: cart_num,record_num: record_num,ajaxing: 0};
	cartact._chkall       = $('#cart-checkall');
	cartact._totalwrap    = $('#cart-totalwrap');
	cartact._totalprice   = $('#cart-totalprice');
	cartact._btncheckout  = $('#cart-btncheckout');
	cartact._btndelete    = $('#cart-btndelete');
	cartact._cartlistbody = $('#cart-list-body');
	cartact._cartedit     = $('#cart-edit');
	cartact._cartgnum     = $('.cart-gnum',cartact._cartlistbody);
	cartact._totalcartnum = cartact._btncheckout.find('span');
	if (cartact._cartedit.attr('data-editmode')=='1'){
		cartact.editmode = 1;
	}
	
	cartact.calCheckedPrice = function(){
		var total = 0;
		cartact._cartlistbody.find('.checked').each(function(){
			total += parseFloat($(this).attr('data-gprice')) * parseInt($(this).attr('data-gnum'));
		});
		return total;
	};
	cartact.calCheckedCartNum = function() {
		var total = 0;
		cartact._cartlistbody.find('.checked').each(function(){
			total += parseInt($(this).attr('data-gnum'));
		});
		return total;
	};
	cartact.calCheckedItemNum = function(className) {
		if (typeof(className)==undefined) className = 'checked';
		return cartact._cartlistbody.find('.'+className).size();
	};
	cartact.__rmChkClsCb = function(index, clsNames) {
		var t = new Array();
		if (clsNames.lastIndexOf('checked')>=0) {
			t.push('checked');
		}
		if (clsNames.lastIndexOf('delete')>=0) {
			t.push('delete');
		}
		return t.join(' ');
	};
	cartact.cancelCheckAll = function(ele){
		$(ele).removeClass(cartact.__rmChkClsCb).find('.check').removeClass(cartact.__rmChkClsCb);
		cartact._cartlistbody.find('.check').removeClass(cartact.__rmChkClsCb);
		if(!cartact.editmode) { //非编辑模式
			cartact._totalprice.text('0');
			cartact._totalcartnum.hide();			
			cartact._btndelete.hide();
			cartact._btncheckout.attr('disabled','true').show();
		}
		else { //编辑模式
			cartact._btncheckout.hide();
			cartact._btndelete.attr('disabled','true').show();
		}
		
	};
	cartact.toCheckAll = function(ele){
		if(!cartact.editmode) { //非编辑模式
			$(ele).removeClass('delete').addClass('checked').find('.check').removeClass('delete').addClass('checked');
			cartact._cartlistbody.find('.check').removeClass('delete').addClass('checked');
			cartact._totalprice.text(cartact.calCheckedPrice());
			cartact.cart_num = cartact.calCheckedCartNum(); //需更新cartact.cart_num
			cartact._totalcartnum.text('('+cartact.cart_num+')').show();
			cartact._btndelete.hide();
			cartact._btncheckout.removeAttr('disabled').show();
		}
		else { //编辑模式
			$(ele).removeClass('checked').addClass('delete').find('.check').removeClass('checked').addClass('delete');
			cartact._cartlistbody.find('.check').removeClass('checked').addClass('delete');
			cartact._btncheckout.hide();
			cartact._btndelete.removeAttr('disabled').show();
		}
	};
	
	cartact._chkall.click(function(){
		if (!cartact.editmode && $(this).hasClass('checked') || cartact.editmode && $(this).hasClass('delete')) { //将要取消全选
			cartact.cancelCheckAll(this);
		}
		else { //将要全选
			cartact.toCheckAll(this);
		}
		return false;
	});
	cartact._cartlistbody.find('.check').click(function(){
		if (!cartact.editmode) { //非编辑模式
			if ($(this).hasClass('checked')) { //将要取消选中
				$(this).removeClass('checked');
			}
			else { //将要选中
				$(this).addClass('checked');
			}
			
			var t = cartact.calCheckedPrice(); //重新计算
			cartact._totalprice.text(t);
			
			var n = cartact.calCheckedCartNum();
			cartact._totalcartnum.text('('+n+')');
			
			if (n < cartact.cart_num) { //不是“全选”了
				cartact._chkall.find('.check').removeClass('checked');
			}else{
				cartact.toCheckAll(cartact._chkall.get(0));
			}
			if (!n) { //等于取消“全选”
				cartact.cancelCheckAll(cartact._chkall.get(0));
			}
			else {
				cartact._totalcartnum.show();
				cartact._btncheckout.removeAttr('disabled');
				if (!cartact._chkall.hasClass('checked')) {
					cartact._chkall.addClass('checked');
				}
			}
		}
		else { //编辑模式
			if ($(this).hasClass('delete')) { //将要取消选中
				$(this).removeClass('delete');
			}
			else { //将要选中
				$(this).addClass('delete');
			}
			
			var dn = cartact.calCheckedItemNum('delete');
			if (dn < cartact.record_num) { //不是“全选”了
				cartact._chkall.find('.check').removeClass('delete');
			}else{
				cartact.toCheckAll(cartact._chkall.get(0));
			}
			if (!dn) { //等于取消“全选”
				cartact.cancelCheckAll(cartact._chkall.get(0));
			}
			else {
				cartact._btndelete.removeAttr('disabled');
				if (!cartact._chkall.hasClass('delete')) {
					cartact._chkall.addClass('delete');
				}
			}
		}
		return false;
	});
	cartact._cartedit.click(function(){
		if($(this).attr('data-editmode')=='1') { // "完成" -> "编辑"，退出"编辑"模式
			$(this).attr('data-editmode',0).text('编辑');
			cartact.editmode = 0;
			cartact._totalwrap.css('visibility','visible');
			cartact._cartgnum.find('>.gnum-change').css('display','none');
			cartact._cartgnum.find('>.gnum-show').show();
			cartact.toCheckAll(cartact._chkall.get(0)); //默认回来后选中所有 TODO: 可以改成回到之前选中状态
			cartact.change_gnum(); //更新数据库端的状态
		}
		else { // "编辑" -> "完成"，进入"编辑"模式
			$(this).attr('data-editmode',1).text('完成');
			cartact.editmode = 1;
			cartact.cancelCheckAll(cartact._chkall.get(0));
			cartact._totalwrap.css('visibility','hidden');
			cartact._cartgnum.find('>.gnum-show').hide();
			cartact._cartgnum.find('>.gnum-change').css('display','inline-block');
		}
		return false;
	});
	cartact._cartgnum.find('>.gnum-change .response-area').click(function(){
		var _inp= $(this).parent().find('>input.txt');
		var v = _inp.val();
		v = parseInt(v);
		if ($(this).hasClass('response-area-minus')) { //减按钮
			if(v>1) {
				--v;
				_inp.val(v);
				$(this).parents('.cart-goods-it').find('.check').attr('data-gnum',v); //更新用于统计的数量
				$(this).parents('.cart-gnum').find('.gnum-show').text('x'+v);
				if (1==v) { //不能少于1
					$(this).parent().find('button.minus').addClass('disabled').attr('disabled','true');
				}
			}
		}
		else { //加按钮
			++v;
			if(v<=2) {
				v = 2;
			}
			_inp.val(v);
			$(this).parents('.cart-goods-it').find('.check').attr('data-gnum',v); //更新用于统计的数量
			$(this).parents('.cart-gnum').find('.gnum-show').text('x'+v);
			if(2==v) {
				$(this).parent().find('button.minus').removeClass('disabled').removeAttr('disabled');
			}
			//TODO 是否要检查是否超出库存？
		}
		return false;
	});
	cartact._btndelete.click(function(){
		if (confirm('确定要删除么？')) {
			if (cartact.ajaxing) return false;
			var rec_ids = [];
			cartact._cartlistbody.find('.delete').each(function(){
				rec_ids.push(parseInt($(this).attr('data-rid')));
			});
			cartact.ajaxing = true;
			F.post('/trade/cart/delete',{'rec_id[]':rec_ids},function(ret){
				cartact.ajaxing = false;
				if (ret.flag=='SUC') {
					window.location.reload();
				}
				else{
					alert(ret.msg);
				}
			});
		}
		
		return false;
	});
	cartact.change_gnum = function() {
		if (cartact.ajaxing) return false;
		var rec_ids = [], gnums = [];
		cartact._cartlistbody.find('.check').each(function(){
			rec_ids.push(parseInt($(this).attr('data-rid')));
			gnums.push(parseInt($(this).attr('data-gnum')));
		});
		cartact.ajaxing = true;
		F.post(gData.contextpath+'trade/cart/chgnum',{'rec_id[]':rec_ids,'gnum[]':gnums},function(ret){
			cartact.ajaxing = false;
			if (ret.flag=='SUC') {
				//window.location.reload();
			}
			else{
				//alert(ret.msg);
			}
		});
		return false;
	};
	cartact._btncheckout.click(function(){
		var rec_ids = [];
		cartact._cartlistbody.find('.checked').each(function(){
			rec_ids.push(parseInt($(this).attr('data-rid')));
		});
		if (0===rec_ids.length) {
			alert('请选择要结账的商品');
			return false;
		}
		var ids_str = rec_ids.join(',');
		window.location.href = gData.contextpath+"trade/order/confirm?cart_rids="+ids_str+"&t="+F.time()+(wxData.isReady ? "&showwxpaytitle=1" : "");
		return false;
	});
	
	//初始化
	cartact._totalprice.text(cartact.calCheckedPrice());
	cartact._totalcartnum.text(cart_num?'('+cart_num+')':'');
	
	//全局化cartact
	window.cartact = cartact;
}






