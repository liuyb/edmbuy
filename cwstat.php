<?php
/**
 * 财务统计
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
//~ require init.php
require (__DIR__.'/core/init.php');

define('ONE_DAY_TIME', 86400);
define('DAY_SEP', ' ');
define('DAY_BEGIN', DAY_SEP.'00:00:00');
define('DAY_END',   DAY_SEP.'23:59:59');
define('CSV_SEP', ',');
define('CSV_LN', "\n");

$request  = new Request();
$response = new Response();

SimPHP::I()->boot();

@header("Content-Type: text/html;charset=UTF-8");

//统计类型
$type = $request->get('type', 0);
if (!in_array($type, [0,1,2,3,4,5,6,7])) {
	$type = 0;
}

//获取统计时间
$from = $request->get('from', '');
$to   = $request->get('to',   '');
$target = $request->get('target');
$settle = $request->get('settle');
$from_time = 0;
$to_time   = 0;
if (strlen($from)==10 && preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) {
	$from_time = strtotime($from.DAY_BEGIN);
}
if (strlen($to)==10 && preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
	$to_time   = strtotime($to.DAY_END);
}

$filename  = SIMPHP_ROOT . '/var/tmp/CWSTAT_TYPE'.$type.'_%s.csv';
$from_date = $from_time ? date('Y-m-d', $from_time) : '';
$to_date   = $to_time   ? date('Y-m-d', $to_time)   : '';
//$to_date   = '2016-01-31';
$stat_time_text = '';
if (!$from_time && !$to_time) {
	$stat_time_text = '全部记录';
	$filename  = sprintf($filename, 'ALL');
}
elseif ($from_time && !$to_time) {
	$stat_time_text = date('Y-m-d H:i:s', $from_time) . ' 起至今';
	$filename  = sprintf($filename, 'FROM'.$from);
}
elseif (!$from_time && $to_time) {
	$stat_time_text = '截止至 '.date('Y-m-d H:i:s', $to_time);
	$filename  = sprintf($filename, 'TO'.$to);
}
else {
	$stat_time_text = '从 '.date('Y-m-d H:i:s', $from_time) . ' 起至 '.date('Y-m-d H:i:s', $to_time);
	$filename  = sprintf($filename, 'FROM'.$from.'TO'.$to);
}

if (0==$type) { //显示链接
    $from_time = $from_time ? simphp_gmtime($from_time) : 0;
    $to_time   = $to_time   ? simphp_gmtime($to_time)   : 0;
    
	$html  =<<<HEREDOC
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8"/>
<title>益多米财务数据下载</title>
<style type="text/css">
html,body,h1,div,p,ul,li{ margin: 0;padding: 0; }
h1 { text-align: center;margin-bottom: 15px;font-size: 18px;border-bottom: 2px solid #ccc;padding: 10px 0; }
li { padding: 5px 10px; }
</style>
</head>
<body>
	<h1>益多米财务数据下载</h1>
	<ul>
	  <li><a href="?type=1&from={$from_date}&to={$to_date}">汇总统计(统计时间：{$stat_time_text})</a></li>
	  <li><a href="?type=2&from={$from_date}&to={$to_date}">结算给供应商统计(统计时间：{$stat_time_text})</a></li>
	  <li><a href="?type=1&from={$from_date}&to={$to_date}&target=platform">益多米商城汇总统计(统计时间：{$stat_time_text})</a></li>
	  <li><a href="?type=2&from={$from_date}&to={$to_date}&target=platform">益多米商城结算给统计(统计时间：{$stat_time_text})</a></li>
	  <li><a href="?type=3&from={$from_date}&to={$to_date}">代理相关数据(统计时间：{$stat_time_text})</a></li>
	  <li><a href="?type=4&from={$from_date}&to={$to_date}">可以结算代理订单数据(统计时间：{$stat_time_text})</a></li>
      <li><a href="?type=5&from={$from_date}&to={$to_date}">不可用结算代理订单数据(统计时间：{$stat_time_text})</a></li>
      <li><a href="?type=6&from={$from_date}&to={$to_date}&settle=y">可以结算供应商零售收入(统计时间：{$stat_time_text})</a></li>
      <li><a href="?type=6&from={$from_date}&to={$to_date}&settle=n">不可用结算供应商零售收入(统计时间：{$stat_time_text})</a></li>  
      <li><a href="?type=7&from={$from_date}&to={$to_date}&settle=n">供应商退款查询(统计时间：{$stat_time_text})</a></li>  
	</ul>
</body>
</html>
HEREDOC;
	echo $html;
	exit;
}

$csv = '';

if (1==$type) { //汇总统计
	
	$from_time = $from_time ? simphp_gmtime($from_time) : 0;
	$to_time   = $to_time   ? simphp_gmtime($to_time)   : 0;
	
	$isPlatform = ($target == "platform") ? true : false;
	
	$csv = "商家名称,商家ID,平台总收入,商家收入,产生总佣金,米商佣金收入,平台佣金收入,扣除套餐佣金收入".CSV_LN;
	$duplicate = 0;
	//获取商家总收入列表
	$list = ThisFn::getMerchantTotalIncome($from_time, $to_time, $isPlatform, $duplicate);
	if (!empty($list)) {
		$totalIncome        = 0.00;
		$totalMerchantIncome= 0.00;
		$totalCommision     = 0.00;
		$totalMsIncome      = 0.00;
		$totalPlatCommision = 0.00;
		$actotalPlatCommision = 0.00;
		foreach ($list AS $it) {
			$it['merchant_id'] = $it['merchant_id'] ? : '';
			$merchant_income  = 0.00;
			$total_commistion = 0.00;
			$row = ThisFn::getMerchantIncome($it['merchant_id'], $from_time, $to_time);
			if (!empty($row)) {
				$total_commistion = $row['total_commision'];
				//$merchant_income  = $row['total_income_amount'];
				$merchant_income  = $it['total_income']-$total_commistion; //销售额 - 佣金 = 商家收入
			}
			$ms_income = ThisFn::getMishangIncome($it['merchant_id'], $from_time, $to_time);
			$totalMsIncome += $ms_income;
			$plat_commision = Fn::money_yuan($total_commistion-$ms_income);
			$acplat_commision = $plat_commision - $duplicate;
			$csv .= '"'.$it['merchant_name'].'"'.CSV_SEP.$it['merchant_id'].CSV_SEP.$it['total_income'].CSV_SEP.$merchant_income.CSV_SEP.$total_commistion.CSV_SEP.$ms_income.CSV_SEP.$plat_commision.CSV_SEP.$acplat_commision.CSV_LN;
			$totalIncome += $it['total_income'];
			$totalMerchantIncome += $merchant_income;
			$totalCommision += $total_commistion;
			$totalPlatCommision += $plat_commision;
			$actotalPlatCommision += $acplat_commision;
		}
		$csv .= '合计'.CSV_SEP.'--'.CSV_SEP.$totalIncome.CSV_SEP.$totalMerchantIncome.CSV_SEP.$totalCommision.CSV_SEP.$totalMsIncome.CSV_SEP.$totalPlatCommision.CSV_SEP.$actotalPlatCommision.CSV_LN;
	}
	
}
elseif (2==$type) { //结算给供应商统计

	$from_time = $from_time ? simphp_gmtime($from_time) : 0;
	$to_time   = $to_time   ? simphp_gmtime($to_time)   : 0;
	
	$isPlatform = ($target == "platform") ? true : false;
	
	$csv = "商家名称,商家ID,用户ID,用户昵称,订单号,微信交易号,订单金额,进货价(商家收入),产生佣金,订单时间,快递单号,米商佣金收入,平台佣金收入".CSV_LN;
	
	//获取商家收入订单详情
	$list = ThisFn::getMerchantOrderDetail($from_time, $to_time, $isPlatform);
	if (!empty($list)) {
		$totalOrderAmount  = 0.00;
		$totalIncomePrice  = 0.00;
		$totalCommision    = 0.00;
		$totalOutComision = 0.00;
		$totalPlatIncome = 0.00;
		foreach ($list AS $it) {
		    $out_comision = ThisFn::getMishangIncomeByOrder($it['order_id'], $from_time, $to_time);
		    $plat_income_comision = $it['commision']-$out_comision;
			$it['merchant_id'] = $it['merchant_id'] ? : '';
			$csv .= '"'.$it['merchant_name'].'"'.CSV_SEP.$it['merchant_id'].CSV_SEP.$it['user_id'].CSV_SEP.'"'.$it['nick_name'].'"'.CSV_SEP.$it['order_sn'].CSV_SEP.$it['pay_trade_no'].CSV_SEP.$it['money_paid'].CSV_SEP.$it['income_price'].CSV_SEP.$it['commision'].CSV_SEP.'"'.simphp_dtime('std',simphp_gmtime2std($it['pay_time'])).'"'.CSV_SEP.'"'.$it['invoice_no'].'"'.CSV_SEP.$out_comision.CSV_SEP.$plat_income_comision.CSV_LN;
			$totalOrderAmount += $it['money_paid'];
			$totalIncomePrice += $it['income_price'];
			$totalCommision   += $it['commision'];
			$totalOutComision += $out_comision;
			$totalPlatIncome  += $plat_income_comision;
		}
		$csv .= '合计'.CSV_SEP.'--'.CSV_SEP.'--'.CSV_SEP.'--'.CSV_SEP.'--'.CSV_SEP.'--'.CSV_SEP.$totalOrderAmount.CSV_SEP.$totalIncomePrice.CSV_SEP.$totalCommision.CSV_SEP.'--'.CSV_SEP.'--'.CSV_SEP.$totalOutComision.CSV_SEP.$totalPlatIncome.CSV_LN;
	}
	
}
elseif (3==$type) { //代理相关数据

    $from_time = $from_time ? simphp_gmtime($from_time) : 0;
    $to_time   = $to_time   ? simphp_gmtime($to_time)   : 0;

    $csv = "用户ID,用户名称,用户当前级别,购买代理,支付金额,领取时间,订单编号,领取套餐名称,套餐价格,总进货价,套餐商品详情".CSV_LN;
    //获取商家收入订单详情
    $list = ThisFn::getAgentPackageDetail($from_time, $to_time);
    if (!empty($list)) {
        foreach ($list AS $it) {
            $csv .= '"'.$it['user_id'].'"'.CSV_SEP.'"'.$it['nick_name'].'"'.CSV_SEP.'"'.$it['clevel'].'"'.CSV_SEP.'"'.$it['blevel'].'"'.CSV_SEP.$it['money_paid'].CSV_SEP.'"'.$it['pay_time'].'"'.CSV_SEP.$it['order_id'].CSV_SEP.'"'.$it['package_name'].'"'.CSV_SEP.$it['actual_price'].CSV_SEP.$it['total_income'].CSV_SEP.'"'.$it['goods_desc'].'"'.CSV_LN;
        }
    }

}elseif (4 == $type){
$from_time = $from_time ? simphp_gmtime($from_time) : 0;
	$to_time   = $to_time   ? simphp_gmtime($to_time)   : 0;
	
	$csv = "商家名称,商家ID,订单号,父订单编号,微信交易号,订单金额,折扣后金额,进货价(商家收入),产生佣金,订单时间,快递单号".CSV_LN;
	
	//获取商家收入订单详情
	$list = ThisFn::getMerchantPackageOrderDetail($from_time, $to_time, true);
	if (!empty($list)) {
		$totalOrderAmount  = 0.00;
		$totalIncomePrice  = 0.00;
		$totalCommision    = 0.00;
		$totalDiscount = 0.00;
		foreach ($list AS $it) {
			$it['merchant_id'] = $it['merchant_id'] ? : '';
			$csv .= '"'.$it['merchant_name'].'"'.CSV_SEP.$it['merchant_id'].CSV_SEP.$it['order_sn'].CSV_SEP.$it['parent_id'].CSV_SEP.$it['pay_trade_no'].CSV_SEP.$it['money_paid'].CSV_SEP.$it['discount'].CSV_SEP.$it['income_price'].CSV_SEP.$it['commision'].CSV_SEP.'"'.simphp_dtime('std',simphp_gmtime2std($it['pay_time'])).'"'.CSV_SEP.'"'.$it['invoice_no'].'"'.CSV_LN;
			$totalOrderAmount += $it['money_paid'];
			$totalIncomePrice += $it['income_price'];
			$totalCommision   += $it['commision'];
			$totalDiscount += $it['discount'];
		}
		$csv .= '合计'.CSV_SEP.'--'.CSV_SEP.'--'.CSV_SEP.'--'.CSV_SEP.'--'.CSV_SEP.$totalOrderAmount.CSV_SEP.$totalDiscount.CSV_SEP.$totalIncomePrice.CSV_SEP.$totalCommision.CSV_SEP.'--'.'--'.CSV_LN;
	}
}elseif (5 == $type){
    $from_time = $from_time ? simphp_gmtime($from_time) : 0;
	$to_time   = $to_time   ? simphp_gmtime($to_time)   : 0;
	
	$csv = "商家名称,商家ID,订单号,父订单编号,微信交易号,订单金额,折扣后金额,进货价(商家收入),产生佣金,订单时间,快递单号".CSV_LN;
	
	//获取商家收入订单详情
	$list = ThisFn::getMerchantPackageOrderDetail($from_time, $to_time);
	if (!empty($list)) {
		$totalOrderAmount  = 0.00;
		$totalIncomePrice  = 0.00;
		$totalCommision    = 0.00;
		$totalDiscount = 0.00;
		foreach ($list AS $it) {
			$it['merchant_id'] = $it['merchant_id'] ? : '';
			$csv .= '"'.$it['merchant_name'].'"'.CSV_SEP.$it['merchant_id'].CSV_SEP.$it['order_sn'].CSV_SEP.$it['parent_id'].CSV_SEP.$it['pay_trade_no'].CSV_SEP.$it['money_paid'].CSV_SEP.$it['discount'].CSV_SEP.$it['income_price'].CSV_SEP.$it['commision'].CSV_SEP.'"'.simphp_dtime('std',simphp_gmtime2std($it['pay_time'])).'"'.CSV_SEP.'"'.$it['invoice_no'].'"'.CSV_LN;
			$totalOrderAmount += $it['money_paid'];
			$totalIncomePrice += $it['income_price'];
			$totalCommision   += $it['commision'];
			$totalDiscount += $it['discount'];
		}
		$csv .= '合计'.CSV_SEP.'--'.CSV_SEP.'--'.CSV_SEP.'--'.CSV_SEP.'--'.CSV_SEP.$totalOrderAmount.CSV_SEP.$totalDiscount.CSV_SEP.$totalIncomePrice.CSV_SEP.$totalCommision.CSV_SEP.'--'.'--'.CSV_LN;
	}
}elseif (6 == $type){
    $from_time = $from_time ? simphp_gmtime($from_time) : 0;
	$to_time   = $to_time   ? simphp_gmtime($to_time)   : 0;
	$canSettle = ($settle == "y") ? true : false;
	
	$csv = "商家名称,商家ID,订单号,微信交易号,订单金额,进货价(商家收入),产生佣金,订单时间,快递单号".CSV_LN;
	
	//获取商家收入订单详情
	$list = ThisFn::getMerchantNormalOrderDetail($from_time, $to_time, $canSettle);
	if (!empty($list)) {
		$totalOrderAmount  = 0.00;
		$totalIncomePrice  = 0.00;
		$totalCommision    = 0.00;
		foreach ($list AS $it) {
			$it['merchant_id'] = $it['merchant_id'] ? : '';
			$csv .= '"'.$it['merchant_name'].'"'.CSV_SEP.$it['merchant_id'].CSV_SEP.$it['order_sn'].CSV_SEP.$it['pay_trade_no'].CSV_SEP.$it['money_paid'].CSV_SEP.$it['income_price'].CSV_SEP.$it['commision'].CSV_SEP.'"'.simphp_dtime('std',simphp_gmtime2std($it['pay_time'])).'"'.CSV_SEP.'"'.$it['invoice_no'].'"'.CSV_LN;
			$totalOrderAmount += $it['money_paid'];
			$totalIncomePrice += $it['income_price'];
			$totalCommision   += $it['commision'];
		}
		$csv .= '合计'.CSV_SEP.'--'.CSV_SEP.'--'.CSV_SEP.'--'.CSV_SEP.$totalOrderAmount.CSV_SEP.$totalIncomePrice.CSV_SEP.$totalCommision.CSV_SEP.'--'.'--'.CSV_LN;
	}
}elseif (7 == $type){
    $from_time = $from_time ? simphp_gmtime($from_time) : 0;
	$to_time   = $to_time   ? simphp_gmtime($to_time)   : 0;
	
	$csv = "商家名称,订单号,退款单号,微信退款流水号,订单金额,退款金额,退款时间".CSV_LN;
	
	//获取商家收入订单详情
	$list = ThisFn::getRefundList($from_time, $to_time);
	if (!empty($list)) {
		$totalRefund    = 0.00;
		foreach ($list AS $it) {
			$it['merchant_id'] = $it['merchant_id'] ? : '';
			$csv .= '"'.$it['facename'].'"'.CSV_SEP.$it['order_sn'].CSV_SEP.$it['refund_sn'].CSV_SEP.$it['pay_refund_no'].CSV_SEP.$it['money_paid'].CSV_SEP.$it['refund_money'].CSV_SEP.'"'.$it['succ_time'].'"'.CSV_LN;
			$totalRefund += $it['refund_money'];
		}
		$csv .= '合计'.CSV_SEP.'--'.CSV_SEP.'--'.CSV_SEP.'--'.CSV_SEP.'--'.CSV_SEP.$totalRefund.CSV_LN;
	}
}

if (''!=$csv) {
	file_put_contents($filename, $csv);
	download($filename);
}

/**
 * 工具类
 */
class ThisFn {
	
	/**
	 * 将东八区时间字符串(如: '2016-01-31 23:59:59')转换成GMT时间戳
	 */
	static function dtime2gmtime($str_dtime = '') {
		return $str_dtime ? simphp_gmtime(strtotime($str_dtime)) : 0;
	}
	
	/**
	 * 获取商家总收入列表
	 * @param integer $from_time
	 * @param integer $to_time
	 * @return array
	 */
	static function getMerchantTotalIncome($from_time, $to_time, $isPlatform = false, &$duplicate) {
		$where = "";
		if ($from_time) {
			$where .= " AND o.`pay_time`>=".$from_time;
		}
		if ($to_time) {
			$where .= " AND o.`pay_time`<=".$to_time;
		}
		if($isPlatform){
		    $where .= " AND o.`merchant_ids` = 'mc_570b4e6b540ae' ";
		    $sqltmp = "SELECT o.`merchant_ids` AS merchant_id, o.`money_paid`  AS total_income,o.order_flag, IFNULL(m.facename,'【测试商家】') AS merchant_name
		    FROM  `shp_order_info` AS o LEFT JOIN `shp_merchant` AS m ON o.`merchant_ids` = m.merchant_id left join shp_agent_payment p on o.order_id = p.order_id
		    WHERE o.`pay_status`=2 AND o.`is_separate`=0 {$where}
		    ORDER BY total_income DESC";
		    $list = D()->query($sqltmp)->fetch_array_all();
		    foreach ($list as $it){
		        if($it['order_flag'] == Order::ORDER_FLAG_AGENT){
		            if($it['total_income'] == 698){
		                $duplicate += 198;
		            }else if($it['total_income'] == 398){
		                $duplicate += 98;
		            }
		        }
		    }
		}else{
		    $where .= " AND o.`merchant_ids` <> 'mc_570b4e6b540ae' ";
		}
		$sql = "SELECT o.`merchant_ids` AS merchant_id, SUM( o.`money_paid` ) AS total_income,o.order_flag, IFNULL(m.facename,'【测试商家】') AS merchant_name
FROM  `shp_order_info` AS o LEFT JOIN `shp_merchant` AS m ON o.`merchant_ids` = m.merchant_id left join shp_agent_payment p on o.order_id = p.order_id 
WHERE o.`pay_status`=2 AND o.`is_separate`=0 {$where}   
GROUP BY o.`merchant_ids`
ORDER BY total_income DESC";
		$list = D()->query($sql)->fetch_array_all();
		return $list;
	}
	
	/**
	 * 获取商家收入及商家销售订单产生的总结佣金
	 * @param string  $merchant_id
	 * @param integer $from_time
	 * @param integer $to_time
	 * @return array
	 */
	static function getMerchantIncome($merchant_id, $from_time, $to_time) {
		$where = "";
		if ($from_time) {
			$where .= " AND o.`pay_time`>=".$from_time;
		}
		if ($to_time) {
			$where .= " AND o.`pay_time`<=".$to_time;
		}
		$sql = "SELECT a.merchant_id, SUM(a.income_amount) AS total_income_amount, SUM(a.commision) AS total_commision
FROM (
SELECT o.`merchant_ids` AS merchant_id,o.`order_id`, o.`commision`,SUM(og.`goods_number` * og.`income_price`) AS income_amount
FROM  `shp_order_info` AS o INNER JOIN `shp_order_goods` AS og ON o.`order_id` = og.`order_id`
WHERE o.`merchant_ids`='%s' AND o.`pay_status`=2 AND o.`is_separate`=0 {$where} 
GROUP BY o.`order_id`
) AS a";
		$row = D()->query($sql,$merchant_id)->get_one();
		return $row;
	}
	
	/**
	 * 获取商家收入及商家销售订单产生的总结佣金
	 * @param integer $from_time
	 * @param integer $to_time
	 * @return array
	 */
	static function getMishangIncome($merchant_id, $from_time, $to_time) {
		$where = "";
		if ($from_time) {
			$where .= " AND o.`pay_time`>=".$from_time;
		}
		if ($to_time) {
			$where .= " AND o.`pay_time`<=".$to_time;
		}
		
		//先获取独立订单(非父子订单)产生的佣金$commision1
		$sql = "
SELECT SUM(uc.`commision`) AS ms_commision1
FROM  `shp_order_info` AS o INNER JOIN `shp_user_commision` AS uc ON o.`order_id` = uc.`order_id`
WHERE o.`pay_status`=2 AND o.`is_separate`=0 AND o.`parent_id`=0 AND o.`merchant_ids`='%s' {$where} AND uc.`state`>=0
";
		$ms_commision1 = D()->query($sql,$merchant_id)->result();
		
		//然后获取父子订单产生的佣金$commision2
		$plat_comm = PLATFORM_COMMISION;
		$sql = "
SELECT SUM(oa.commision*(1-{$plat_comm})*ob.total_ratio) AS ms_commision2
FROM `shp_order_info` AS oa INNER JOIN (
	SELECT o.order_id, SUM( uc.use_ratio ) AS total_ratio
	FROM  `shp_order_info` AS o INNER JOIN `shp_user_commision` AS uc ON o.`order_id` = uc.`order_id`
	WHERE o.`pay_status`=2 AND o.`is_separate`=1 AND o.`merchant_ids` like '%%%s%%' {$where} AND uc.`state`>=0
	GROUP BY o.order_id
) AS ob ON oa.parent_id=ob.order_id
WHERE oa.`merchant_ids`='%s' AND oa.`pay_status`=2 AND oa.`is_separate`=0
";
		$ms_commision2 = D()->query($sql,$merchant_id,$merchant_id)->result();
		return Fn::money_yuan($ms_commision1 + $ms_commision2);
	}
	
	/**
	 * 获取商家收入及商家销售订单产生的总结佣金
	 * @param integer $from_time
	 * @param integer $to_time
	 * @return array
	 */
	static function getMishangIncomeByOrder($order_id, $from_time, $to_time) {
	    $where = "";
	    if ($from_time) {
	        $where .= " AND o.`pay_time`>=".$from_time;
	    }
	    if ($to_time) {
	        $where .= " AND o.`pay_time`<=".$to_time;
	    }
	
	    //先获取独立订单(非父子订单)产生的佣金$commision1
	    $sql = "
	    SELECT SUM(uc.`commision`) AS ms_commision1
	    FROM  `shp_order_info` AS o INNER JOIN `shp_user_commision` AS uc ON o.`order_id` = uc.`order_id`
	    WHERE o.`pay_status`=2 AND o.`is_separate`=0 AND o.`parent_id`=0 AND o.`order_id`='%s' {$where} AND uc.`state`>=0
	    ";
	    $ms_commision1 = D()->query($sql,$order_id)->result();
	
	    //然后获取父子订单产生的佣金$commision2
	    $plat_comm = PLATFORM_COMMISION;
	    $sql = "
	    SELECT SUM(oa.commision*(1-{$plat_comm})*ob.total_ratio) AS ms_commision2
	    FROM `shp_order_info` AS oa INNER JOIN (
	    SELECT o.order_id, SUM( uc.use_ratio ) AS total_ratio
	    FROM  `shp_order_info` AS o INNER JOIN `shp_user_commision` AS uc ON o.`order_id` = uc.`order_id`
	    WHERE o.`pay_status`=2 AND o.`is_separate`=1 AND o.`order_id` = '%s' {$where} AND uc.`state`>=0
	    GROUP BY o.order_id
	    ) AS ob ON oa.parent_id=ob.order_id
	    WHERE oa.`order_id`='%s' AND oa.`pay_status`=2 AND oa.`is_separate`=0
	    ";
	    $ms_commision2 = D()->query($sql,$order_id,$order_id)->result();
	    return Fn::money_yuan($ms_commision1 + $ms_commision2);
	}
	

	/**
	 * 获取商家总收入列表
	 * @param integer $from_time
	 * @param integer $to_time
	 * @return array
	 */
	static function getMerchantOrderDetail($from_time, $to_time, $isPlatform = false) {
		$where = "";
		if ($from_time) {
			$where .= " AND o.`pay_time`>=".$from_time;
		}
		if ($to_time) {
			$where .= " AND o.`pay_time`<=".$to_time;
		}
		if($isPlatform){
		    $where .= " AND o.`merchant_ids` = 'mc_570b4e6b540ae' ";
		}else{
		    $where .= " AND o.`merchant_ids` <> 'mc_570b4e6b540ae' ";
		}
		$sql = "SELECT o.`merchant_ids` AS merchant_id, IFNULL(m.facename,'【测试商家】') AS merchant_name,u.user_id,u.nick_name, o.`order_id`, o.`order_sn`, o.`pay_trade_no`, o.`money_paid`, (o.`money_paid`-o.`commision`) AS income_price, o.`commision`, o.`pay_time`, o.`invoice_no` 
FROM  `shp_order_info` AS o LEFT JOIN `shp_merchant` AS m ON o.`merchant_ids` = m.merchant_id left join shp_users u on o.user_id = u.user_id 
WHERE o.`pay_status`=2 AND o.`is_separate`=0 {$where} 
ORDER BY merchant_id DESC, order_id ASC";
		$list = D()->query($sql)->fetch_array_all();
		return $list;
	}
	
	/**
	 * 现在是需要几个表格：
        1.开通d代理的人员总表以及开通了什么代理
        2.代理领取了什么套餐
        3.套餐是怎么组合的
	 * @param integer $from_time
	 * @param integer $to_time
	 * @return array
	 */
	static function getAgentPackageDetail($from_time, $to_time) {
	    $where = "";
	    if ($from_time) {
	        $where .= " AND po.`pay_time`>=".$from_time;
	    }
	    if ($to_time) {
	        $where .= " AND po.`pay_time`<=".$to_time;
	    }
	    $sql = "select u.user_id, u.nick_name,p.order_id,u.level as clevel,p.level as blevel,o.money_paid,po.pay_time,po.order_id,pp.name as package_name,pp.actual_price,pp.goods_ids 
             from shp_agent_payment p left join shp_premium_package pp on p.premium_id  = pp.pid 
            left join shp_users u on  p.user_id = u.user_id left join shp_order_info o on p.order_id = o.order_id
	        left join shp_order_info po on o.order_id = po.relate_order_id and po.is_separate=1 
            where is_paid = 1 and p.premium_id > 0 $where order by po.pay_time asc ";
	    $list = D()->query($sql)->fetch_array_all();
	    foreach ($list as &$item){
    	    $total_income = 0;
	        $goods_desc = '';
	        if($item['goods_ids']){
    	        $sql = "select og.goods_id,og.income_price from shp_order_goods og join shp_order_info oi 
    	            on og.order_id = oi.order_id where oi.relate_order_id = ".$item['order_id']." and oi.is_separate=0 ";
    	        $goods = D()->query($sql)->fetch_array_all();
    	        foreach ($goods as $gd){
    	            $goods_desc .= ($gd['goods_id']."(".$gd['income_price'].")").";";
    	            $total_income += doubleval($gd['income_price']);
    	        }
	        }
	        $item['clevel'] = AgentPayment::getAgentNameByLevel($item['clevel']);
	        $item['blevel'] = AgentPayment::getAgentNameByLevel($item['blevel']);
	        $item['pay_time'] = date('Y-m-d H:i:s', simphp_gmtime2std($item['pay_time']));
	        $item['goods_desc'] = $goods_desc;
	        $item['total_income'] = $total_income;
	    }
	    return $list;
	}
	
	/**
	 * 获取商家领取套餐总收入列表
	 * @param integer $from_time
	 * @param integer $to_time
	 * @return array
	 */
	static function getMerchantPackageOrderDetail($from_time, $to_time, $canSettlement = false) {
	    $where = "";
	    if ($from_time) {
	        $where .= " AND o.`pay_time`>=".$from_time;
	    }
	    if ($to_time) {
	        $where .= " AND o.`pay_time`<=".$to_time;
	    }
	    $where .= " and o.relate_order_id > 0 ";
        $time = simphp_gmtime() - (7*86400);
        //无退换货 t+7结算
	    if($canSettlement){
	        $where .= " and (shipping_status = ".SS_RECEIVED." or (shipping_status > 0 and shipping_status <> 2 and shipping_time > 0 and shipping_time <= {$time})) ";
	    }else{
	        $where .= " and (shipping_status = 0 or (shipping_status > 0 and shipping_status <> 2 and shipping_time > 0 and shipping_time > {$time})) ";
	    }
	    $sql = "SELECT o.`merchant_ids` AS merchant_id, IFNULL(m.facename,'【测试商家】') AS merchant_name,o.relate_order_id, o.`order_id`,o.parent_id,o.`order_sn`, o.`pay_trade_no`, o.`money_paid`, (o.`money_paid`-o.`commision`) AS income_price, o.`commision`, o.`pay_time`, o.`invoice_no`
	    FROM  `shp_order_info` AS o LEFT JOIN `shp_merchant` AS m ON o.`merchant_ids` = m.merchant_id
	    WHERE o.`pay_status`=2 AND o.`is_separate`=0 {$where}
	    ORDER BY merchant_id DESC, order_id ASC";
	    $list = D()->query($sql)->fetch_array_all();
	    foreach ($list as &$it){
	        $relate_order_id = $it['relate_order_id'];
	        $order = Order::load($relate_order_id);
	        $money = $order->money_paid;
	        $radio = 0;
	        if($money == 698){
	            $radio = 500;
	        }else if($money == 398){
	            $radio = 300;
	        }
	        $it['discount'] = $it['money_paid'];
	        if($radio){
	            $it['discount'] = number_format(($it['money_paid']/$money), 3) * $radio;
	            $it['commision'] = $it['discount'] - $it['income_price'];
	        }
	    }
	    /* if($canSettlement){
	        foreach ($list as $order){
	            D()->query("insert into tb_tmp_settlement(order_id) values($order[order_id])");
	        }
	    } */
	    return $list;
	}
	
	/**
	 * 供应商零售收入
	 * @param integer $from_time
	 * @param integer $to_time
	 * @return array
	 */
	static function getMerchantNormalOrderDetail($from_time, $to_time, $canSettlement = false) {
	    $where = "";
	    if ($from_time) {
	        $where .= " AND o.`pay_time`>=".$from_time;
	    }
	    if ($to_time) {
	        $where .= " AND o.`pay_time`<=".$to_time;
	    }
	    $time7 = simphp_gmtime() - (7*86400);
	    $time14 = simphp_gmtime() - (14*86400);
	    //无退换货 t+7结算
	    if($canSettlement){//已收货并且收货时间大于等于7天，或者已发货并且发货时间大于等于14天
	        $where .= " and ((shipping_status = ".SS_RECEIVED." and shipping_confirm_time <= $time7) or (shipping_status > 0 and shipping_status <> 2 and shipping_time > 0 and shipping_time <= {$time14})) ";
	    }else{//已收货但收货时间小于7天，没发货 或 已发货，发货时间小于1天
	        $where .= " and ((shipping_status = ".SS_RECEIVED." and shipping_confirm_time > $time7) or (shipping_status = 0 or (shipping_status > 0 and shipping_status <> 2 and shipping_time > 0 and shipping_time > {$time14}))) ";
	    }
	    $sql = "SELECT o.`merchant_ids` AS merchant_id, IFNULL(m.facename,'【测试商家】') AS merchant_name, o.`order_id`,o.parent_id,o.`order_sn`, o.`pay_trade_no`, o.`money_paid`, (o.`money_paid`-o.`commision`) AS income_price, o.`commision`, o.`pay_time`, o.`invoice_no`
	    FROM  `shp_order_info` AS o LEFT JOIN `shp_merchant` AS m ON o.`merchant_ids` = m.merchant_id
	    WHERE o.`pay_status`=2 AND o.`is_separate`=0 {$where} and o.order_flag=0 
	    ORDER BY merchant_id DESC, order_id ASC";
	    $list = D()->query($sql)->fetch_array_all();
	    return $list;
	}
	
	static function getRefundList($from_time, $to_time){
	    if ($from_time) {
	        $where .= " AND reund.succ_time >= '".date('Y-m-d H:i:s',$from_time)."' ";
	    }
	    if ($to_time) {
	        $where .= " AND reund.succ_time <= '".date('Y-m-d H:i:s',$to_time)."' ";
	    }
	    $sql = "select m.facename,o.order_sn,reund.refund_sn,reund.pay_refund_no,o.money_paid,reund.refund_money,reund.succ_time 
	    from shp_order_refund reund join shp_order_info o on reund.order_sn = o.order_sn 
        left join shp_merchant m on o.merchant_ids = m.merchant_id where reund.succ_time <> '' $where order by m.merchant_id ";
	    $list = D()->query($sql)->fetch_array_all();
	    return $list;
	    
	}
}
/*----- END FILE: cwstat.php -----*/