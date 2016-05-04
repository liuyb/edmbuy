<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if(''!==$errmsg):?>

<div class="error"><?=$errmsg?></div>

<?php else:?>
<script id="forTopnav" type="text/html">
<div class="header">
	收入明细
<a href="javascript:history.back();" class="back"></a>
</div>
</script>
<script>show_topnav($('#forTopnav').html())</script>
<div class="sx_broke_list">
	<table cellspacing="0" cellpadding="0" class="sx_table_list">
		<tr>
			<th>昵称</th>
			<th>付款时间</th>
			<th>订单金额</th>
			<th>佣金</th>
			<th></th>
		</tr>
		<tbody id="resultList"></tbody>		
	</table>
</div>
<div id="showMore" style="text-align:center;height:40px;line-height:40px;display:none;"><span class="allicen_more">下拉加载更多</span></div>
<script>
var type = <?=$type ?>;
var state = <?=$state ?>;
var rebate = <?=$rebate ?>;

var $mbody;
var $resultList;
var $showMore;
$(function(){
	$showMore = $("#showMore");
	$resultList = $("#resultList");
	$mbody = $("#Mbody");
	
	getIncomeDetail(1, '', true);

	$resultList.on('click', '.commisionRow', function(){
		var $this = $(this);
		var uid = $this.data("uid");
		var oid = $this.data("oid");
		var level = $this.data("level");
		window.location.href='/user/commission?user_id='+uid+'&order_id='+oid+'&level='+level;
	});

	$mbody.on('pullMore', function(){
		var curpage = $showMore.attr('data-curpage');
		getIncomeDetail((curpage ? curpage : 1), false);
	});
	$mbody.pullMoreDataEvent($showMore);

});
//获取商家列表
function getIncomeDetail(curpage, isInit){
	F.get('/user/income/detail/list', {state : state, type : type, rebate : rebate, curpage : curpage}, function(ret){
		constructRows(ret, isInit);
		F.handleWhenHasNextPage($showMore, ret);
	});
}
//构建HTML输出
function constructRows(ret, isInit){
	var HTML = "";
	if(!ret || !ret.result || !ret.result.length){
		HTML = "<tr><td colspan='5' style='text-align:center;'>还没有相关数据.<td></tr>";
	}else{
		var result = ret.result;
		for(var i = 0,len=result.length; i < len; i++){
			var obj = result[i];
			HTML += "<tr class='commisionRow' data-uid="+obj.user_id+" data-oid="+obj.order_id+" data-level="+obj.parent_level+"><td>"+obj.order_unick+"</td><td class=\"sx_table_list_time\">"+obj.paid_time+"</td><td>"+obj.order_amount+"元</td><td>"+obj.commision+"元</td><td>></td></tr>";
		}
	}
	F.afterConstructRow(isInit, $resultList, HTML, $showMore);
}
</script>
<?php endif;?>