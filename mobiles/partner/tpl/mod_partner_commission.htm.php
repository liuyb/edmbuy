<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if(''!==$errmsg):?>

<div class="error"><?=$errmsg?></div>

<?php else:?>
<script id="forTopnav" type="text/html">
<div class="header broke">
	<ul>
		<li class="<?php if ($status == 1):?>broke_on<?php endif;?>" id="broke1" onclick="showByStatus(this, 1)">已生效</li>
		<li class="<?php if ($status == 0):?>broke_on<?php endif;?>" style="margin-left:20%;" id="broke2" onclick="showByStatus(this, 0)">未生效</li>
		<a href="/partner" class="back"></a>
		<span class="broke_line"></span>
	</ul>
</div>
</script>
<script>show_topnav($('#forTopnav').html())</script>
<div class="broke_list" id="list1">
	<div class="wsx_broke_ts" style="display:none;">
		<p class="wsx_ts_tit">怎样才能生效?</p>
	</div>
	
	<div class="broke_tab">
		<table cellspacing="0" cellpadding="0" class="broke_table" style="border-collapse:inherit">
			<tr>
				<td class="broke_tab_on" data-level="1" id="tab1" onclick="showByLevel(this, 1)">
					一层用户
				</td>
				<td class="" id="tab2" data-level="2" onclick="showByLevel(this, 2)">
					二层用户
				</td>
				<td class="" id="tab3" data-level="3" onclick="showByLevel(this, 3)" style="border-right:0;">
					三层用户
				</td>
			</tr>
		</table>
	</div>
	<div class="broke_list_info">
		<div class="broke_order">
			<span class="broke_order_l">订单金额合计(元)</span>
			<span class="broke_order_r totalAmount">0.00</span>
		</div>
		<div class="broke_hj">
			<span class="broke_order_l">佣金合计(元)</span>
			<span class="broke_order_r totalCommision">0.00</span>
		</div>
		<div class="broke_order_num">
			<span class="broke_order_l">订单个数(元)</span>
			<span class="broke_order_r totalNum">0</span>
		</div>
	</div>
</div>
<div class="broke_list" id="list2">
	<div class="sx_broke_list">
		<table cellspacing="0" cellpadding="0" class="sx_table_list">
			<tr style="display: none;">
				<th>昵称</th>
				<th>付款时间</th>
				<th>订单金额</th>
				<th>佣金</th>
			</tr>
		</table>
	</div>
	<div onclick="pulldata(this);" style="margin-top:10px;" id="pageContainer" class="pull_more_data">更多...</div>
</div>

<!-- 遮罩层 -->
<a href="javascript:;"><div class="mask"></div></a>

<!-- 加好友弹出框 -->
<div class="broke_wsx_t"> 
	<p class="wxt_t_title">佣金什么时候才能生效?</p>
	<p class="wxt_t_text">交易遵循T+7原则：客户付款，商家发货后客户确认收货（如果买家没有及时确认收货，那么从发货开始7天后系统自动确认收货），及交易完成。</br>交易完成后7天（国家规定7天无理由退货），分销商佣金都可以提现了。</p>
	<p class="wxt_t_close">关闭</p>
</div>


<script>
var variable = {};
variable.status = "<?=$status ?>";
//切换
if(variable.status == 0){
	$(".wsx_broke_ts").show();
}
function showByStatus(obj, status){
	if($(obj).hasClass("broke_on")){
		return;
	}
	$(".broke li").removeClass("broke_on");
	$(obj).addClass("broke_on");
	if(status == 0){
		$(".wsx_broke_ts").show();
	}else{
		$(".wsx_broke_ts").hide();
	}
	variable.status = status;
	var level = 1;
	var levelObj = $(".broke_table").find(".broke_tab_on");
	if(levelObj && levelObj.length){
		level = levelObj.attr("data-level");
	}
	getPageData(1, level, false);
}

function showByLevel(obj, level){
	if($(obj).hasClass("broke_tab_on")){
		return;
	}
	$(".broke_tab td").removeClass("broke_tab_on");
	$(obj).addClass("broke_tab_on");
	getPageData(1, level, false);
}
function resetPageData(isPullData){
	$(".sx_table_list").find("tr:gt(0)").remove();
	var countContainer = $(".broke_list_info");
	countContainer.find(".totalAmount").text("0.00");
	countContainer.find(".totalCommision").text("0.00");
	countContainer.find(".totalNum").text("0");
	$("#pageContainer").hide();
		
}
$().ready(function(){
	getPageData(1, 1, false);
    //未生效弹出框
    $(".wsx_broke_ts").bind("click",function(){
    	$(".mask").fadeIn(200);
    	$(".broke_wsx_t").fadeIn(200);
    });
    $(".mask,.broke_wsx_t").bind("click",function(){
    	$(".mask").fadeOut(200);
    	$(".broke_wsx_t").fadeOut(200);
    });
});

/**
 * curpage 当前页
 * level 当前层
 * status 当前佣金状态
 * isPullData 当前是否为下拉发起的请求，下拉的话不需要获取total数据
 */
function getPageData(curpage, level, isPullData){
	var needtotal = 0;
	if(!isPullData){
    	resetPageData(isPullData);
    	needtotal = 1;
	}
	var url = "/partner/commission/ajax?status="+variable.status+"&level="+level+"&curpage="+curpage+"&needtotal="+needtotal;
	F.get(url, null, function(data){
		renderDataRegion(data, isPullData);
    	handleWhenHasNextPage(data, level);
	});
};
function renderDataRegion(data, isPullData){
	if(!data){
		return;
	}
	if(!isPullData){
    	var countContainer = $(".broke_list_info");
    	var otherMap = data.otherMap;
    	countContainer.find(".totalAmount").text(otherMap ? otherMap['totalAmount'] : "0.00");
    	countContainer.find(".totalCommision").text(otherMap ? otherMap['totalCommision'] : "0.00");
    	countContainer.find(".totalNum").text(otherMap ? otherMap['totalNum'] : "0");
	}
	var tableContainer = $(".sx_table_list");
	var result = data.result;
	if(!result || !result.length){
		if(!isPullData){
	    	tableContainer.find("tr:eq(0)").hide();
		}
		return;
	}
	if(!isPullData){
    	tableContainer.find("tr:eq(0)").show();
	}
	var TR = "";
	for(var i = 0,len=result.length; i < len; i++){
		var row = result[i];
		TR += "<tr><td>"+row.nickname+"</td><td class=\"sx_table_list_time\">"+row.paytime+"</td><td>"+row.amount+"元</td><td>"+row.commision+"元</td></tr>";
	}
	//TR += "$(function(){ F.set_scroller(false, 100); })";
	tableContainer.append($(TR));
	F.set_scroller(false, 100);
};
//当还有下一页时处理下拉
function handleWhenHasNextPage(data, level){
	var hasnex = data.hasnexpage;
	if(hasnex){
		$("#pageContainer").show();
		$("#pageContainer").attr('curpage',data.curpage).attr('level',level);
	}else{
		$("#pageContainer").hide();
	}
}

function pulldata(obj){
	getPageData($(obj).attr("curpage"), $(obj).attr("level"), true);
}
</script>

<?php endif;?>