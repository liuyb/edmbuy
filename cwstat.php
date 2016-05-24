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
if (!in_array($type, [0,1,2])) {
	$type = 0;
}

//获取统计时间
$from = $request->get('from', '');
$to   = $request->get('to',   '');
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
	
	$csv = "商家名称,商家ID,平台总收入,商家收入,产生总佣金,米商佣金收入,平台佣金收入".CSV_LN;
	
	//获取商家总收入列表
	$list = ThisFn::getMerchantTotalIncome($from_time, $to_time);
	if (!empty($list)) {
		$totalIncome        = 0.00;
		$totalMerchantIncome= 0.00;
		$totalCommision     = 0.00;
		$totalMsIncome      = 0.00;
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
			
			$csv .= '"'.$it['merchant_name'].'"'.CSV_SEP.$it['merchant_id'].CSV_SEP.$it['total_income'].CSV_SEP.$merchant_income.CSV_SEP.$total_commistion.CSV_SEP.$ms_income.CSV_SEP.Fn::money_yuan($total_commistion-$ms_income).CSV_LN;
			$totalIncome += $it['total_income'];
			$totalMerchantIncome += $merchant_income;
			$totalCommision += $total_commistion;
		}
		$csv .= '合计'.CSV_SEP.'--'.CSV_SEP.$totalIncome.CSV_SEP.$totalMerchantIncome.CSV_SEP.$totalCommision.CSV_SEP.$totalMsIncome.CSV_SEP.Fn::money_yuan($totalCommision-$totalMsIncome).CSV_LN;
	}
	
}
elseif (2==$type) { //结算给供应商统计

	$from_time = $from_time ? simphp_gmtime($from_time) : 0;
	$to_time   = $to_time   ? simphp_gmtime($to_time)   : 0;
	
	$csv = "商家名称,商家ID,订单号,微信交易号,订单金额,进货价(商家收入),产生佣金,订单时间,快递单号".CSV_LN;
	
	//获取商家收入订单详情
	$list = ThisFn::getMerchantOrderDetail($from_time, $to_time);
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
	static function getMerchantTotalIncome($from_time, $to_time) {
		$where = "";
		if ($from_time) {
			$where .= " AND o.`pay_time`>=".$from_time;
		}
		if ($to_time) {
			$where .= " AND o.`pay_time`<=".$to_time;
		}
		$sql = "SELECT o.`merchant_ids` AS merchant_id, SUM( o.`money_paid` ) AS total_income, IFNULL(m.facename,'【测试商家】') AS merchant_name
FROM  `shp_order_info` AS o LEFT JOIN `shp_merchant` AS m ON o.`merchant_ids` = m.merchant_id
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
	 * 获取商家总收入列表
	 * @param integer $from_time
	 * @param integer $to_time
	 * @return array
	 */
	static function getMerchantOrderDetail($from_time, $to_time) {
		$where = "";
		if ($from_time) {
			$where .= " AND o.`pay_time`>=".$from_time;
		}
		if ($to_time) {
			$where .= " AND o.`pay_time`<=".$to_time;
		}
		$sql = "SELECT o.`merchant_ids` AS merchant_id, IFNULL(m.facename,'【测试商家】') AS merchant_name, o.`order_id`, o.`order_sn`, o.`pay_trade_no`, o.`money_paid`, (o.`money_paid`-o.`commision`) AS income_price, o.`commision`, o.`pay_time`, o.`invoice_no` 
FROM  `shp_order_info` AS o LEFT JOIN `shp_merchant` AS m ON o.`merchant_ids` = m.merchant_id
WHERE o.`pay_status`=2 AND o.`is_separate`=0 {$where}
ORDER BY merchant_id DESC, order_id ASC";
		$list = D()->query($sql)->fetch_array_all();
		return $list;
	}
}
/*----- END FILE: cwstat.php -----*/