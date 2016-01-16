<?php ?>
  
<script type="text/javascript">
function Pager(pageContainer, pageOp, callback){
	this.pageContainer = pageContainer;
	this.curpage = parseInt((pageOp.curpage || 1), 10);
	this.totalnum = parseInt((pageOp.totalnum || 0), 10);
	this.pagesize = parseInt((pageOp.pagesize || 15), 10);
	this.maxpage = parseInt(pageOp.maxpage, 10);
	this.callback = callback;
	this.p = 1;
}

Pager.prototype.showPager = function(){
	//清空原pager对象
	//this.pageContainer.empty();
	if(this.totalnum <= this.pagesize){
		return;
	}
	var _THIS = this;
	var pageDiv = "<div class=\"paging\" >";
	pageDiv +="<a href=\"javascript:;\" rel=\"begin\" class=\"pgbtn\" >首页</a>";
	pageDiv +="<a href=\"javascript:;\" rel=\"last\" class=\"pgbtn\" >上一页</a>";
	pageDiv +="<a href=\"javascript:;\" rel=\"next\" class=\"pgbtn\" >下一页</a>";
	pageDiv +="<a href=\"javascript:;\" rel=\"end\" class=\"pgbtn\" >末页</a>";
	pageDiv +="</div>";
	pageDiv = $(pageDiv);
	this.pageContainer.append(pageDiv);
	pageDiv.find("a").bind('click', function(){
		_THIS.gopage(this);
	});
	this.refreshPagerStatus();
};

Pager.prototype.refreshPagerStatus = function(){
	var pager = this.pageContainer.find(".paging");
	var begin = pager.find("a[rel = 'begin']");
	var last = pager.find("a[rel = 'last']");
	var next = pager.find("a[rel = 'next']");
	var end = pager.find("a[rel = 'end']");
	if(this.curpage == 1){
		begin.addClass("disable");
		last.addClass("disable");
		next.removeClass("disable");
		end.removeClass("disable");
	}else if(this.curpage == this.maxpage){
		begin.removeClass("disable");
		last.removeClass("disable");
		next.addClass("disable");
		end.addClass("disable");
	}else{
		begin.removeClass("disable");
		last.removeClass("disable");
		next.removeClass("disable");
		end.removeClass("disable");
	}
};

Pager.prototype.gopage = function(obj){
    if ($(obj).hasClass('disable')) {
    	return;
    }
    var curpage = this.curpage;
    var maxpage = this.maxpage;
    var p = 1, rel = $(obj).attr('rel');
    
    switch(rel) {
    default:
    	case 'begin': p = 1;break;
    	case 'end':   p = maxpage;break;
    	case 'last':  p = curpage-1;p = p > 0 ? p : 1;break;
    	case 'next':  p = curpage+1;p = p > maxpage ? maxpage : p;break;
    }
    
    if (maxpage<=1) {
    	myAlert('当前仅有一页');
    	return;
    }
    else if (p==curpage) {
    	if (p==1) {
    		myAlert('已经第一页');
    	}
    	else if (p==maxpage) {
    		myAlert('已经最后一页');
    	}
    	return;
    }
    this.curpage = p;
    this.callback();
    this.refreshPagerStatus();
}

</script>
