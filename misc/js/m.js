/*!
 * mobile common js
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
(function( $, F, w, UNDEF ) {
	
	F.isTouch = "createTouch" in document;
	//gData.downpull_display = true; //允许下拉显示标志位，默认允许，页面可改变此变量以改变其默认行为 (不能在这个位置设置该值，放这里是提醒有这么一个全局变量)
	//gData.page_render_mode = 2; //1: general一般请求页面；2: hash请求页面 (不能在这个位置设置该值，放这里是提醒有这么一个全局变量)
	//gData.page_use_iscroll = 0; //是否使用iScroll插件
	
	// Set dom constants
	F.doms = {wrapper:"#Mbody",nav:"#Mnav",topnav:"#topnav",activepage:"#activePage",loading:"#loadingCover",scroller:".scrollArea"};
	
	// Cache doms
	F.activePage = $(F.doms.activepage);
	F.scrollArea = $('>'+F.doms.scroller,F.activePage);
	F.pageBg     = $('>.pageBg',F.activePage);
	
	// Scroll cookie initialization
	F.scroll2old = false;
	F.scrollYold = 0; // 记录上一个滚动位置
	F.scrollDirection = 0; // <0:向上滚动(滚动条下移); >0:向下滚动(滚动条上移); 0: 位置不变
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
			F.activePage.hide();
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
			F.activePage.show();
			break;
		case 'overlay':
			break;
		case 'pure':
			break;
		}
	};
	F.loadingCount = 0;
	F.loadingOn = function(){
		F.loadingCount++;
		$("#loading_mask").show();
		$("#ajax_loadding").show();
	};
	F.loadingOff = function(){
		F.loadingCount--;
		if(F.loadingCount <= 0){
			$("#loading_mask").hide();
			$("#ajax_loadding").hide();
		}
	};
	// set content minimal height
	F.set_content_minheight = function(){
		if (typeof(gData.page_use_iscroll)!='undefined' && !gData.page_use_iscroll) return false;
		if (F.scrollArea.size()>0) {
			var $topnav  = $(F.doms.topnav);
			var $nav     = $(F.doms.nav);
			var topnav_h = $topnav.css('display')=='none' ? 0 : $topnav.height();
			var nav_h    = $nav.css('display')=='none' ? 0 : $nav.height();
			if (topnav_h>0 || nav_h>0) {
				var ch = $(document).height()-topnav_h-nav_h;
				F.scrollArea.css({minHeight:ch+'px'});
			}
		}
	};
	// set iScroll object
	F.set_scroller = function(toY, runTimeout){
		if (typeof(gData.page_use_iscroll)!='undefined' && !gData.page_use_iscroll) return false;
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
		F.event.flag.downpull = (0==this.y ? true : false);
		if (typeof(F.event.flag.showbg)=='undefined') {
			F.event.flag.showbg = false;
		}
		F.event.execEvent('scrollStart',this);
	};
	//outcall: F.onScrolling
	F._scrolling = function() {
		F.scrollDirection = this.y - F.scrollYold;
		F.scrollYold = this.y;
		
		var dp_type = 'downPull';
		var can_dpshow = (typeof(gData.downpull_display)=='undefined' || gData.downpull_display) ? true : false;
		if (this.y > 20) {
			
			if (can_dpshow && !F.event.flag.showbg) {
				F.event.flag.showbg = true;
				F.pageBg.show();
			}
			
			if(this.y > 50 && F.event.flag.downpull) {
				F.event.flag.downpull = false; //保证仅触发一次
				if ((typeof(F.event._events[dp_type])=='object') && F.event._events[dp_type].length>0) {
					F.event.execEvent(dp_type,this);
				}
			}
		}
		else {
			if (F.event.flag.showbg) {
				F.event.flag.showbg = false;
				F.pageBg.hide();
			}
		}
		F.event.execEvent('scrolling',this);
	};
	//outcall: F.onScrollEnd
	F._scrollEnd = function() {
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
		if (typeof(wrap)=='undefined') wrap = F.scrollArea;
		$('img',wrap).attr('data-loaded',0).load(function(){ $(this).attr('data-loaded',1); });
		F.set_scroller(false,100);
	};
	//weixin on ready
	F.onWxReady = function(fn) {
		F.event.on('wxReady',fn);
	};
	F._onWxReady= function() {
		F.event.execEvent('wxReady',this);
	};
	
	//获取内容包裹元素
	F.getContainerEle = function() {
		var $_c = F.scrollArea;
		if ($_c.size()==0) $_c = F.activePage;
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
			var _ct = F.scrollArea;
			var toPreClass = 'ui-page-pre-in';
			var toClass = 'slide in';
			if (_effect=='slide_in_right') {
				_ct.addClass(toPreClass);
				_ct.animationComplete(function(){
					_ct.removeClass(toClass);
				});
			}
			F.loadingStop('switch');
			if (_effect=='slide_in_right') {
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
		
		// Set content min height
		F.set_content_minheight();
		
		// Bind window.resize event
		setTimeout(function(){
			FastClick.attach(w.document.body);
		},10);

		// Prevent default scroll action
		$(w.document.body).on('touchmove','.no-bounce',function(e){
			e.preventDefault();
		});
		
		// Bind window.onhashchange event
		//$(w).hashchange(function(){w.go_hashreq();});
		
		// Hash trigger
		//var init_hash = F.getHash();
		//if (!init_hash) {w.go_hashreq(null,null,{changeHash:false});}
		//else {$(w).hashchange();}
		
		// Req page ajax
		if (2==gData.page_render_mode) {
			w.go_ajaxreq(gData.currURI);
		}
		
		$("#loading_mask").on('click', function(){
			F.loadingOff();
		});
		
	});
	
	/**
	 * 覆写F.get 方法，增加loading方法 默认2秒才显示滚动条
	 */
	F.get = function(url, data, callback){
		F.loading = true;
		setTimeout(function(){
			if(F.loading){
				F.loadingOn();
			}
		}, 2000);
		var promise = $.get(url, data, function(){
			if(callback){
				F.loading =false;
				callback.apply(this,arguments);
				F.loadingOff();
			}
		});
		promise.always = function(){
			F.loading =false;
			F.loadingOff();
		}
		promise.fail = function(){
			F.loading =false;
			F.loadingOff();
			alert('数据加载异常，请刷新重试!');
		}
	};
	
	/**
	 * 增加post 方法，增加loading方法 
	 */
	F.postWithLoading = function(url, data, callback, ajax_start_cb, ajax_complete_cb){
		F.loadingOn();
		F.post(url, data, callback, ajax_start_cb, function(param){
			F.loadingOff();
			if(ajax_complete_cb){
				ajax_complete_cb(param);
			}
		});
	};
	
	/**
	 * 下拉加载更多事件
	 * @param showMore 当前下拉加载DOM
	 */
	$.fn.pullMoreDataEvent = function(showMore){
		var _this = $(this);
		_this.on({
			'touchend' : function(){
				if(!showMore.is(":visible")){
					//console.log('没有更多了..');
					return;
				}
				//增加setTimeout延时是因为下拉太快时获取到的滚动条位置不准确。
				setTimeout(function(){
					var scrollTop = _this.scrollTop();
					var thisHeight = _this.height();
					var scrollHeight = _this[0].scrollHeight;
					//提前20px开始加载
					var bufferH = 20; 
					if((scrollTop + thisHeight +  bufferH) >= scrollHeight){
						//触发加载事件
						showMore.find("span").text("玩命加载中...");
						_this.trigger('pullMore');
					}
					//console.log(scrollTop+"///"+thisHeight+"///"+scrollHeight);
				},250);
			}
		});
	}
	/**
	 * 当还有下一页时处理下拉
	 * @param showMore 下拉加载对应的DOM
	 * @param data 后台PagePull 对象
	 */
	F.handleWhenHasNextPage = function(showMore, data){
		var hasnex = data.hasnexpage;
		if(hasnex){
			showMore.show();
			showMore.attr('data-curpage',data.curpage);
		}else{
			showMore.hide();
		}
	}
	/**
	 * 构建数据行之后处理Append HTML事件
	 * @param isInit 是不是初始化事件
	 * @param resultList 当前存放数据DIV DOM
	 * @param HTML 当前构建的数据行
	 * @param showMore 下拉加载更多DOM
	 */
	F.afterConstructRow = function(isInit,resultList,HTML,showMore){
		if(isInit){
			$("#Mbody").scrollTop(0)
			resultList.html($(HTML));
		}else{
			resultList.append($(HTML));
		}
		showMore.find("span").text("下拉加载更多");
	}
	
	F.displayNoData = function(){
		return "<div class='no_more_data'><img src='/themes/mobiles/img/no_data_show.png' /></div>";
	}
	
})(jQuery, FUI, this);

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
//回退的处理方式 增加直接进入页面回退失效的处理
;function goBack(){
	if(history.length > 1){
		history.back();
	}else{
		window.location.href = '/';
	}
};
/**
 * 用户等级对应的图标
 * @param level
 * @returns {String}
 */
function getLevelIcon(level){
	level = level ? parseInt(level) : 0;
	icon = "";
	switch (level){
        case 1 :
            icon = '/themes/mobiles/img/sha.png';
        break;
        case 2 :
            icon = '/themes/mobiles/img/he.png';
        break;
        case 3 :
            icon = '/themes/mobiles/img/jinpai1.png';
        break;
        case 5 :
            icon = '/themes/mobiles/img/jinpai1.png';
        break;
        case 4 :
            icon = '/themes/mobiles/img/yinpai2.png';
        break;
        default :
            icon = '/themes/mobiles/img/ke.png';
	}
	return icon;
}
