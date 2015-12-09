$(document).ready(function(){
	$("body").append("<div id='carlendar_div'></div>");
	$(document).click(function(event){
		//alert($(event.target).attr("class"));
		var element=event.target;
		var emeCls=$(element).attr("bj");
		if(emeCls!="cBj"){
			$("#carlendar_div").css("display","none");
		}
 	 });						   
});
$.fn.cld=function(options){
	var defaults={
		ipt:this,
		evt:"click"
	};
	if(options){
	  $.extend(defaults,options);
	}
	var e=defaults.evt;
	var startDate=defaults.sd;
	var endDate=defaults.ed;
	var obj=defaults.ipt;
	if(e=="click") $(this).click(showCar);
	if(e=="mouseover") $(this).mouseover(showCar);
	//在页面上显示日期选择界面
	function showCar(){
		var iptId=$(obj).attr("id");
		var thisDate=$(obj).val();
		var dateReg=/^[12]\d{3}-(0?[1-9]|1[0-2])-((0?[1-9])|((1|2)[0-9])|30|31)$/;
		var dateRst=dateReg.test(thisDate);
		if(dateRst) var d=new Date(Date.parse(thisDate.replace(/-/g, '/')));
		else var d=new Date();
		var tLeft=$(obj).offset().left;
		var tTop=$(obj).offset().top+20;
		var syear=d.getFullYear();
		var smonth=d.getMonth()+1;
		if(smonth<10) smonth="0"+smonth;
		var sdate=d.getDate();
		carHtml(syear,smonth,sdate,iptId);
		$("#carlendar_div").css({display:"block",left:tLeft,top:tTop});
	}
	//生成日历html代码
	function carHtml(vYear,vMonth,vDay,vipt){
		var vDate=vYear+"-"+vMonth+"-1";
		//if(vMonth<10) vMonth="0"+vMonth;
		var vWeek=getWeek(vDate);		
		var vDayNum=getDayNum(vYear,vMonth);
		var tmpNum=Number(vDayNum)-7+Number(vWeek);
		if((tmpNum % 7)==0) var line=tmpNum/7;
		else var line=parseInt(tmpNum/7)+1;
		var carArr=new Array();
		carArr.push("<table border='0' cellspacing='0' cellpadding='0' width='280'>");
		carArr.push("<tr><td colspan='7'><table border='0' cellspacing='0' cellpadding='0'>");
		carArr.push("<tr><td width='13'>&nbsp;</td><td width='20'><span id='ylow' bj='cBj'>&lt;&lt;</span></td><td width='44'>");
		carArr.push("<input name='inYear' id='inYear' type='text' value='"+vYear+"' maxlength='4' bj='cBj' /></td>");
		carArr.push("<td width='25'>年</td><td width='25' height='30'>");
		carArr.push("<input name='inMonth' type='text' id='inMonth' value='"+vMonth+"' maxlength='2' bj='cBj' /></td>");
		carArr.push("<td width='25'>月</td><td width='20'><span id='yadd' bj='cBj'>&gt;&gt;</span></td><td width='13'>&nbsp;</td>");
		carArr.push("</tr></table></td></tr>");
		carArr.push("<tr id='cssWeek'><td>一</td><td>二</td>");
		carArr.push("<td>三</td><td>四</td>");
		carArr.push("<td>五</td><td>六</td>");
		carArr.push("<td>日</td></tr>");
		for(var i=0;i<=line;i++){
			carArr.push("<tr>");
			for(var j=1;j<8;j++){				
				var tDay=Number(i*7)+Number(j)-Number(vWeek)+1;
				if(tDay<1||tDay>vDayNum) carArr.push("<td width='40'>&nbsp;</td>");
				else if(tDay==vDay) carArr.push("<td width='40'><a href='#this' id='cur' ipt='"+vipt+"'>"+tDay+"</a></td>");
				else carArr.push("<td width='40'><a href='#this' ipt='"+vipt+"'>"+tDay+"</a></td>");
			}
			carArr.push("</tr>");
		}
		carArr.push("</table>")
		var htmlStr=carArr.join("");
		$("#carlendar_div").html(htmlStr);
		$("#carlendar_div #inYear").bind("click",showipt);
		$("#carlendar_div #inMonth").bind("click",showipt);
		$("#carlendar_div #inYear").bind("blur",hideipt);
		$("#carlendar_div #inMonth").bind("blur",hideipt);
		$("#carlendar_div #inYear").bind("keyup",changeipt);
		$("#carlendar_div #inMonth").bind("keyup",changeipt);
		$("#carlendar_div #ylow").bind("click",lowMonth);
		$("#carlendar_div #yadd").bind("click",addMonth);
		$("#carlendar_div a").bind("click",setDateValue);
	}
	
	//返回星期
	function getWeek(dayValue){
		var day = new Date(Date.parse(dayValue.replace(/-/g, '/'))); //将日期值格式化
		var week=day.getDay();
		if(week==0){
			week=7;	
		}		
		return week;
	}
	//返回某个月天数
	function getDayNum(vYear,vMonth){
		var Num=new Date(vYear,vMonth,0).getDate();
		return Num;
	}
	//改变input样式
	function showipt(){
		$(this).css("border","1px solid #fff")
	}
	function hideipt(){
		$(this).css("border","1px solid #55682A");
	}
	//日期改变时重新生成日历html代码
	function changeipt(event){
		 if($.browser.msie){
		 var keyStr=event.keyCode;
		 }
		 else var keyStr=event.which;
		 if((keyStr>=48&&keyStr<=57)||(keyStr>=96&&keyStr<=105)){
			var vYear=$("#inYear").val();
			var vMonth=$("#inMonth").val();
			var vDate=$("#carlendar_div #cur").text();
			if(vYear.length==4&&vMonth.length==2){
				var cipt=$("#carlendar_div a").eq(0).attr("ipt");
				if(vYear>=1900&&Number(vMonth)>=1&&Number(vMonth)<=12) carHtml(vYear,vMonth,vDate,cipt);
				else alert("年份要大于1900\n月份要在01和12之间");
			}
		 }
	}
	//点击向左向右箭头
	function lowMonth(){
		var yVal=$("#inYear").val();
		var mVal=$("#inMonth").val()-1;
		var dVal=$("#carlendar_div#cur").text();
		var lipt=$("#carlendar_div a").eq(0).attr("ipt");
		if(mVal<=0){
			mVal="12";
			yVal=yVal-1;
		}
		if(mVal<10) mVal="0"+mVal;
		carHtml(yVal,mVal,dVal,lipt);
	}
	function addMonth(){
		var yVal=$("#inYear").val();
		var mVal=Number($("#inMonth").val())+1;
		var dVal=$("#carlendar_div #cur").text();
		var aipt=$("#carlendar_div a").eq(0).attr("ipt");
		if(mVal>12){
			mVal="1";
			yVal=Number(yVal)+1;
		}
		if(mVal<10) mVal="0"+mVal;
		carHtml(yVal,mVal,dVal,aipt);
	}
	//设置选择的日期
	function setDateValue(){
		var vipt=$(this).attr("ipt");
		var yVal=$("#inYear").val();
		var mVal=$("#inMonth").val();
		//var dVal=$(this).text();
		if(yVal.length==4&&yVal>1900&&mVal.length==2&&Number(mVal)>=1&&Number(mVal)<=12){
			var dVal="0"+$(this).text();
			mVal="0"+mVal;
			var currDate=yVal+"-"+mVal.slice(mVal.length-2)+"-"+dVal.slice(dVal.length-2);
			var cp1=true;
			var cp2=true;
			//if(startDate!=undefined) cp1=(currDate>startDate)?true:false;
			//if(endDate!=undefined) cp2=(currDate<endDate)?true:false;
			if(startDate!=undefined) cp1=(new Date(Date.parse(currDate.replace(/-/g, '/')))>=new Date(Date.parse(startDate.replace(/-/g, '/'))))?true:false;
			if(endDate!=undefined) cp2=(new Date(Date.parse(currDate.replace(/-/g, '/')))<=new Date(Date.parse(endDate.replace(/-/g, '/'))))?true:false;
			
			//alert(currDate+"|"+cp1+"|"+cp2+"|"+endDate);
			if(cp1&&cp2){
				$("#"+vipt).val(currDate);
				$("#carlendar_div").css("display","none");
			}
			else alert("您选择的时间不在允许的范围内");
			
		}
		else alert("年份要大于1900\n月份要在01和12之间");
	}
}