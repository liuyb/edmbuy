$(function()
	{
		"use strict";
		function a(){
			var a="BackCompat"===h.compatMode?i:h.documentElement;
			return{w:a.clientWidth,h:a.clientHeight}
		}
		function b(){
			var b=a();
			$("#wrapper").height(b.h)
		}
		function c(a,b){
			b>0&&j>0?($(i).unbind("mousewheel"),k.eq(j-1).click()):0>b&&2>j&&($(i).unbind("mousewheel"),k.eq(j+1).click())
		}
		function d(){
			var b=a();
			$("#wrapper").height(b.h),k.eq(j).click()
		}
		function e(a,b){
			return["toolbar=0,status=0,resizable=1,width="+a+",height="+b+",left=",(screen.width-a)/2,",top=",(screen.height-b)/2].join("")
		}
		// function f(a){
		// 	if("wechat"===a){
		// 		var b=$("#qr-share-area"),c=$("#wechat").offset();
		// 		b.css({top:c.top-b.height()-10,left:c.left}).show()
		// 	}
		// 	else{
		// 		var d="fb"===a,f=d?"http://mojime.wechat.com":"Create your own special sticker with 'MojiMe for WeChat'! #mojime #wechat http://mojime.wechat.com",g=encodeURIComponent(f),h="share",i=e(800,600),j=d?"https://www.facebook.com/sharer.php?u=":"https://twitter.com/intent/tweet?text=";j+=g,window.open(j,h,i,!1)
		// 	}
		// }
		function g(){
			b(),$("#nav").localScroll({target:"#wrapper",queue:!0,duration:500,hash:!1,onAfter:function(a){$(i).bind("mousewheel",c),j=parseInt($(a).attr("id").split("-")[1],10)-1}}),$(i).bind("mousewheel",c),$(window).resize(d)
		}
		var h=document,i=h.body,j=0,k=$("#nav").find("a").not(".back"),l=k.eq(0);k.on("click",function(a){a.preventDefault(),l&&l.removeClass(l.attr("class"));var b=k.index(this)+1;$(".page").css("display","block"),l=$(this),l.addClass("current"),l.addClass("item-"+b),$(i).unbind("mousewheel")}),$(".back").on("click",function(){k.eq(0).click()}),g()});