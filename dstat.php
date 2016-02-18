<?php
/**
 * 数据统计
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
//~ require init.php
require (__DIR__.'/core/init.php');

define('ONE_DAY_TIME', 86400);
define('DAY_SEP', ' ');
define('DAY_BEGIN', DAY_SEP.'00:00:00');
define('DAY_END',   DAY_SEP.'23:59:59');

$request  = new Request();
$response = new Response();

SimPHP::I()->boot();

//统计类型
$type = $request->get('type', 1);
if (!in_array($type, [1,2,3])) {
	$type = 1;
}

@header("Content-Type: text/html;charset=UTF-8");

//HTML标题
$title = '有效产品销售统计';
if (2==$type) {
	$title = '退款订单统计';
}
elseif (3==$type) {
	$title = '用户统计';
}

$html  =<<<HEREDOC
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8"/>
<title>{$title}</title>
<style type="text/css">
html,body,h1,table,tr,td,th,div,p{ margin: 0;padding: 0; }
h1 { text-align: center;margin-bottom: 15px;font-size: 18px;border-bottom: 2px solid #ccc;padding: 10px 0; }
table { width: 100%;border-spacing: 0; }
th,td { border-style: solid;border-color: #ddd;border-width: 0 0 1px;padding:5px 2px; }
td { text-align: center; }
th { font-weight: bold; }
</style>
</head>
<body>
HEREDOC;

if (1==$type) { //有效产品销售统计
	//打印表格标题
	$html .= "<h1>有效产品销售统计</h1>";
	$html .= "<table>";
	$html .= '<tr>';
	$html .= '<th>产品ID</th>';
	$html .= '<th>产品标题</th>';
	$html .= '<th>所属商家</th>';
	$html .= '<th>供货价</th>';
	$html .= '<th>销售价</th>';
	$html .= '<th>总订单数</th>';
	$html .= '<th>销售总额</th>';
	$html .= '<th>总佣金</th>';
	$html .= '<th>总纯收入</th>';
	$html .= '<th>总分配佣金</th>';
	$html .= '</tr>';
	
	$list = ThisFn::goods_sell_list();
	foreach ($list AS $it) {
		$html.= '<tr>';
		$html.= '<td>'.$it['goods_id'].'</td>';
		$html.= '<td>'.$it['goods_name'].'</td>';
		$html.= '<td>'.$it['facename'].'</td>';
		$html.= '<td>'.$it['income_price'].'</td>';
		$html.= '<td>'.$it['shop_price'].'</td>';
		$html.= '<td>'.$it['paid_order_count'].'</td>';
		$html.= '<td>'.Fn::money_yuan($it['shop_price']*$it['order_goods_num']).'</td>';
		$html.= '<td>'.Fn::money_yuan($it['commision']*$it['order_goods_num']).'</td>';
		$html.= '<td>'.Fn::money_yuan($it['commision']*$it['order_goods_num']*PLATFORM_COMMISION).'</td>';
		$html.= '<td>'.Fn::money_yuan($it['commision']*$it['order_goods_num']*(1-PLATFORM_COMMISION)).'</td>';
		$html.= '</tr>';
	}
}
elseif (2==$type) { //退款订单统计
	//打印表格标题
	$html .= "<h1>退款订单统计</h1>";
	$html .= "<table>";
	$html .= '<tr>';
	$html .= '<th>订单编号</th>';
	$html .= '<th>产品ID</th>';
	$html .= '<th>产品标题</th>';
	$html .= '<th>所属商家</th>';
	$html .= '<th>供货价</th>';
	$html .= '<th>销售价</th>';
	$html .= '</tr>';
	
	$list = ThisFn::refund_list();
	foreach ($list AS $it) {
		$html.= '<tr>';
		$html.= '<td>'.$it['order_sn'].'</td>';
		$html.= '<td>'.$it['goods_id'].'</td>';
		$html.= '<td>'.$it['goods_name'].'</td>';
		$html.= '<td>'.$it['facename'].'</td>';
		$html.= '<td>'.$it['income_price'].'</td>';
		$html.= '<td>'.$it['shop_price'].'</td>';
		$html.= '</tr>';
	}
}
elseif (3==$type) { //用户统计
	//打印表格标题
	$html .= "<h1>用户统计</h1>";
	$html .= "<table>";
	$html .= '<tr>';
	$html .= '<th>日期时间</th>';
	$html .= '<th>益多米用户总数</th>';
	$html .= '<th>公众号关注总数</th>';
	$html .= '<th>甜玉米平移用户总数</th>';
	$html .= '<th>益多米推广用户总数</th>';
	$html .= '<th>甜玉米赠送米商</th>';
	$html .= '<th>益多米推广米商</th>';
	$html .= '</tr>';
	
	$from = $request->get('from', '');
	$to   = $request->get('to',   '');
	if (strlen($from)==8) {
		$from = substr($from, 0, 4).'-'.substr($from, 4, 2).'-'.substr($from, 6, 2).DAY_BEGIN;
		$from = strtotime($from);
	}
	else {
		$from = 0;
	}
	if (strlen($to)==8) {
		$to   = substr($to,   0, 4).'-'.substr($to,   4, 2).'-'.substr($to,   6, 2).DAY_END;
		$to   = strtotime($to);
	}
	else {
		$to = 0;
	}
	
	$begin_daytime = strtotime('2016-02-15'.DAY_BEGIN);
	$now_daytime   = strtotime(date('Y-m-d').DAY_END);
	for ($tbegin = $from>$begin_daytime ? $from : $begin_daytime;$tbegin < ($to && $to<$now_daytime ? $to : $now_daytime); $tbegin += ONE_DAY_TIME) {
		$begin_t = $tbegin == $begin_daytime ? '' : $tbegin;
		$end_t   = $tbegin + ONE_DAY_TIME - 1;
		$total_user_cnt = ThisFn::calcTotalUserCnt($begin_t, $end_t);
		$tym_user_cnt   = ThisFn::regFromTymUserCnt($begin_t, $end_t);
		$mscnt_by_tym   = ThisFn::mishangCnt($begin_t, $end_t, true);
		$mscnt_platform = ThisFn::mishangCnt($begin_t, $end_t, false);
		
		$html.= '<tr>';
		$html.= '<td>'.date('Y-m-d',$tbegin).'</td>';
		$html.= '<td>'.$total_user_cnt.'</td>';
		$html.= '<td>'.'--'.'</td>';
		$html.= '<td>'.$tym_user_cnt.'</td>';
		$html.= '<td>'.($total_user_cnt-$tym_user_cnt).'</td>';
		$html.= '<td>'.$mscnt_by_tym.'</td>';
		$html.= '<td>'.$mscnt_platform.'</td>';
		$html.= '</tr>';
	}
}

$html .= "</table>\n</body>\n</html>";
echo $html;

class ThisFn {
	
	/**
	 * 统计总用户数
	 * @param string $to_time
	 * @param string $begin_time
	 */
	static function calcTotalUserCnt($begin_time, $to_time = '')
	{
		$where = '';
		if ($begin_time) {
			$where .= " AND `reg_time`>=".$begin_time;
		}
		if ($to_time) {
			$where .= " AND `reg_time`<=".$to_time;
		}
		$sql = "SELECT COUNT(user_id) AS tnum FROM `shp_users` WHERE 1 {$where}";
		$tnum = D()->query($sql)->result();
		//Fn::header_debug(D()->getSqlFinal());
		return $tnum;
	}
	
	/**
	 * 统计甜玉米平移用户总数
	 * @param string $to_time
	 * @param string $begin_time
	 */
	static function regFromTymUserCnt($begin_time, $to_time = '')
	{
		$where = "AND `from`='app567528488ae7e'";
		if ($begin_time) {
			$where .= " AND `reg_time`>=".$begin_time;
		}
		if ($to_time) {
			$where .= " AND `reg_time`<=".$to_time;
		}
		$sql = "SELECT COUNT(user_id) AS tnum FROM `shp_users` WHERE 1 {$where}";
		$tnum = D()->query($sql)->result();
		//Fn::header_debug(D()->getSqlFinal());
		return $tnum;
	}
	
	/**
	 * 统计米商数
	 * @param string $to_time
	 * @param string $begin_time
	 */
	static function mishangCnt($begin_time, $to_time = '',$by_tym = false)
	{
		$where = "AND `level`=1";
		if ($by_tym) {
			$where .= " AND `from`='app567528488ae7e'";
		}
		else {
			$where .= " AND `from`<>'app567528488ae7e'";
		}
		
		if ($begin_time) {
			$where .= " AND `reg_time`>=".$begin_time;
		}
		if ($to_time) {
			$where .= " AND `reg_time`<=".$to_time;
		}
		$sql = "SELECT COUNT(user_id) AS tnum FROM `shp_users` WHERE 1 {$where}";
		$tnum = D()->query($sql)->result();
		//Fn::header_debug(D()->getSqlFinal());
		return $tnum;
	}
	
	/**
	 * 获取退款列表
	 * @return array
	 */
	static function refund_list()
	{
		$sql = "SELECT o.order_id,orf.order_sn,g.goods_id,g.goods_name,g.income_price,g.shop_price,m.facename,orf.refund_time,orf.succ_time
			FROM `shp_order_refund` orf
      INNER JOIN `shp_order_info` o ON orf.order_sn=o.order_sn
      INNER JOIN `shp_order_goods` og ON o.order_id=og.order_id
      INNER JOIN `shp_goods` g ON og.goods_id=g.goods_id
      INNER JOIN `shp_merchant` m ON g.merchant_uid=m.admin_uid";
		$list = D()->query($sql)->fetch_array_all();
		return $list;
	}
	
	/**
	 * 商品销售列表
	 */
	static function goods_sell_list()
	{
		$sql = "SELECT g.goods_id,g.goods_name,g.income_price,g.shop_price,g.paid_order_count,g.commision,m.facename,SUM(og.goods_number) AS order_goods_num
			FROM `shp_goods` g INNER JOIN `shp_merchant` m ON g.merchant_uid=m.admin_uid
				INNER JOIN `shp_order_goods` og ON g.goods_id=og.goods_id
			WHERE 1
			GROUP BY g.goods_id
			ORDER BY paid_order_count DESC";
		$list = D()->query($sql)->fetch_array_all();
		return $list;
	}
	
}




/*----- END FILE: dstat.php -----*/