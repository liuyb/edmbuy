//监听对象回车事件
(function ($) {
    $.fn.enterEvent = function (callback) {
        $(this).bind('keyup', function (e) {
            if (e.keyCode == 13) {
                callback.apply(this, arguments);
            }
        });
    };
})(jQuery);

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
 
 function getStartAndEndDate(period){
	var date = new Date();
	var sdate = "";
	var edate = "";
	var year = date.getFullYear();
	var month = date.getMonth();
	var d = date.getDate();
	var nowDayOfWeek = date.getDay(); //今天本周的第几天 
	if("today" == period){
		sdate = formatDate(date);
		edate = sdate;
	}else if("week" == period){
		sdate = getWeekStartDate(year, month, d, nowDayOfWeek);
		edate = formatDate(date);
	}else if("month" == period){
		sdate = getMonthStartDate(year, month);
		edate = formatDate(date);
	}
	return [sdate,edate];
}


//获得本周的开端日期 
function getWeekStartDate(nowYear, nowMonth, nowDay, nowDayOfWeek) { 
	var weekStartDate = new Date(nowYear, nowMonth, (nowDay - nowDayOfWeek) + 1); 
	return formatDate(weekStartDate); 
} 

//获得本月的开端日期 
function getMonthStartDate(nowYear, nowMonth){ 
	var monthStartDate = new Date(nowYear, nowMonth, 1); 
	return formatDate(monthStartDate); 
} 

//格局化日期：yyyy-MM-dd 
function formatDate(date) { 
	var myyear = date.getFullYear(); 
	var mymonth = date.getMonth()+1; 
	var myweekday = date.getDate(); 
	
	if(mymonth < 10){ 
		mymonth = "0" + mymonth; 
	} 
	if(myweekday < 10){ 
		myweekday = "0" + myweekday; 
	} 
	return (myyear+"-"+mymonth + "-" + myweekday); 
} 
