<?php
/**
 * Created by PhpStorm.
 * User: houchao
 * Date: 2016-03-18
 * Time: 12:19
 */
error_reporting(E_ALL);
ini_set('display_errors', 'on');

require(__DIR__ . '/core/init.php');


$request  = new Request();
$response = new Response();

SimPHP::I()->boot();

define('ONE_DAY_TIME', 86400);
define('DAY_SEP', ' ');
define('DAY_BEGIN', DAY_SEP.'00:00:00');
define('DAY_END',   DAY_SEP.'23:59:59');
define('CSV_SEP', ',');
define('CSV_LN', "\n");

@header("Content-Type: text/html;charset=UTF-8");
$startDate = @$_REQUEST['startDate'];
$endDate = @$_REQUEST['endDate'];

$page = @$_REQUEST['page'] ? $_REQUEST['page'] : 1;
if (!isdate($startDate) || !isdate($endDate)
	&& !empty($endDate) && !empty($startDate)
) {
	echo "<span style='color: red;'>*格式必须为xxxx-xx-xx！</span>";
}
if ($startDate > $endDate) {
	echo "开始时间必须大于结束时间！";
}

$startTime = strtotime($startDate);
$endTime = strtotime($endDate.DAY_END);
$html = "";
if ($startTime && $endTime) {
	$html = getHtmlInfo($startTime, $endTime, $page);
	// var_dump(D()->getSqlFinal());
}

function isdate($str, $format = "Y-m-d")
{
	$strArr = explode("-", $str);
	if (empty($strArr)) {
		return false;
	}
	foreach ($strArr as $val) {
		if (strlen($val) < 2) {
			$val = "0" . $val;
		}
		$newArr[] = $val;
	}
	$str = implode("-", $newArr);
	$unixTime = strtotime($str);
	$checkDate = date($format, $unixTime);
	if ($checkDate == $str) {
		return true;
	} else {
		return false;
	}
}

/**
 * 获取日周月的浏览量
 * @param $getLevelNum
 */
//$data =  GetInfo::getDayNumber($time);
function getResult($data)
{
	if (!is_array($data)) {
		return ['day' => 0, 'week' => 0, 'month' => 0];
	};
	$list = [];
	//   var_dump($data);exit;
	for ($i = 0; $i < count($data); $i++) {
		$result = $data[$i]['totalVie'] - $data[$i]['userCount'];
		switch ($i) {
			case 0:
				$list['day'] = $result;
				break;
			case 1:
				$list['week'] = $result;
				break;
			case 2:
				$list['month'] = $result;
				break;
		}
	}
	return $list;

}

/**
 *得到第一,二页数据的HTML
 * @param $starTime
 * @param $endTime
 */
function  getHtmlInfo($starTime, $endTime, $page = 1)
{
	$html = "";
	if ($page == 1) {
		for ($star_t = $starTime; $star_t <= $endTime; $star_t += ONE_DAY_TIME) {
			$end_t = $star_t + ONE_DAY_TIME - 1;
			$ectb_order = Order::table();
			$order = GetInfo::getTotalOrderNumber($ectb_order, $star_t, $end_t);//订单成交量
			$perNum = GetInfo::susPayPerNum($star_t, $end_t);
			$quitOrder = GetInfo::quitPayMoney($star_t, $end_t);
			$MaxOrderNum = GetInfo::getMaxOrderNum($star_t, $end_t);
			$html .= '<tr>';
			$html .= '<td>' . date('Y-m-d', $star_t) . '</td>';
			$html .= '<td>' . $order['total_order'] . '</td>';
			$html .= '<td>' . $order['order_amount'] . '</td>';
			$html .= '<td>' . $perNum['user_num'] . '</td>';
			$html .= '<td> ' . $perNum['order_amount'] . '</td>';
			$html .= '<td> ' . $perNum['order_num'] . ' </td>';
			$html .= '<td>' . $quitOrder['totalMoney'] . '</td>';
			$html .= '<td>' . $MaxOrderNum['goodsNumber'] . '</td>';
			$html .= '<td>' . $MaxOrderNum['money_paid'] . '</td>';
			$html .= '</tr>';
		}
	} elseif ($page == 2) {
		for ($star_t = $starTime; $star_t <= $endTime; $star_t += ONE_DAY_TIME) {
			$end_t = $star_t + ONE_DAY_TIME - 1;

			$getLevelNum = GetInfo::getLevelNum($star_t, $end_t);//chnum 最大一级的人数

			$maxLevelNum = GetInfo::getMaxLevelNum($star_t, $end_t);//todo 最大一二三级人数
			$maxCommision = GetInfo::getMaxCommision($star_t, $end_t);//commision 最大佣金
			$maxOrderNum = GetInfo::getPlantMoney($star_t, $end_t);//平台收益 int

			$userNumber = GetInfo::getUserNumber($star_t, $end_t);//userNum平台用户数
			$vieNumber = GetInfo::getVieNumber($star_t, $end_t);//pv uv ip数目

			$dayNumber = GetInfo::getDayNumber($star_t);//获取日 周 月活跃数 vieCount - userCount
			//var_dump($dayNumber);exit;
			$result = getResult($dayNumber); //day ,week,month
			$html .= '<tr>';
			$html .= '<td>' . date('Y-m-d', $star_t) . '</td>';
			$html .= '<td>' . $getLevelNum['chnum'] . '</td>';
			$html .= '<td>' . $maxLevelNum. '</td>';
			$html .= '<td>' . $maxCommision['commision'] . '</td>';
			$html .= '<td> ' . $maxOrderNum . '</td>';
			$html .= '<td> ' . $userNumber['userNum'] . ' </td>';
			$html .= '<td> ' . "--" . ' </td>';
			$html .= '<td>' . $vieNumber['pv'] . '</td>';
			$html .= '<td>' . $vieNumber['uv'] . '</td>';
			$html .= '<td>' . $vieNumber['ip'] . '</td>';
			$html .= '<td>' . $result['day'] . '</td>';
			$html .= '<td>' . $result['week'] . '</td>';
			$html .= '<td>' . $result['month'] . '</td>';
			$html .= '</tr>';
		}

	}
	return $html;
}


class GetInfo
{
	/**求订单的总数，以及订单的总金额
	 * @param $ectb_order
	 * @param $startTime
	 * @param $endTime
	 * @return array
	 */
	static function getTotalOrderNumber($ectb_order, $startTime, $endTime)
	{
		$sql = "SELECT count(*) as total_order ,sum(order_amount) as order_amount FROM
              {$ectb_order} WHERE  is_separate = 0 AND add_time BETWEEN
              $startTime AND  $endTime";
		$result = D()->query($sql)->get_one();
		$result['total_order'] = empty($result['total_order']) ? 0 : $result['total_order'];
		$result['order_amount'] = empty($result['order_amount']) ? 0 : $result['order_amount'];
		return $result;
	}

	/**获取成交的数量
	 * @param $starTime
	 * @param $endTime
	 * @return int
	 */
	static function  susPayPerNum($starTime, $endTime)
	{
		$sql = "select count(DISTINCT user_id) as user_num,count('order_id') as order_num ,SUM(order_amount) as order_amount from shp_order_info
                where pay_status = 2 and pay_time BETWEEN $starTime and $endTime and is_separate = 0";
		$result = D()->query($sql)->get_one();
		$result['user_num'] = empty($result['user_num']) ? 0 : $result['user_num'];
		$result['order_num'] = empty($result['order_num']) ? 0 : $result['order_num'];
		$result['order_amount'] = empty($result['order_amount']) ? 0 : $result['order_amount'];
		return $result;
	}

	/**
	 * 已经退款的金额
	 * @param $starTime
	 * @param $endTime
	 */
	static function quitPayMoney($starTime, $endTime)
	{
		$sql = "select sum(money_paid) as totalMoney from shp_order_info where pay_status in (3,4) AND pay_time BETWEEN $starTime and $endTime;";
		$result = D()->query($sql)->get_one();
		if (empty($result['totalMoney'])) {
			return ['totalMoney' => 0];
		}
		return $result;
	}

	/**
	 * 最大单品成交订单数,最大成交额
	 * @param $starTime
	 * @param $endTime
	 */
	static function getMaxOrderNum($starTime, $endTime)
	{
		$sql = "select sum(goods.goods_number) as goodsNumber,goods.goods_id as
              goods_id ,info.order_id,sum(info.money_paid) as money_paid from shp_order_info info LEFT  JOIN shp_order_goods
              goods on info.order_id=goods.order_id where  info.pay_time BETWEEN $starTime and $endTime and info.pay_status=2 GROUP BY
              goods.goods_id
              ORDER BY goods_id desc LIMIT 1";
		$result = D()->query($sql)->get_one();
		if (empty($result)) {
			return ['goodsNumber' => 0, 'money_paid' => 0];
		}
		return $result;
	}

	/**
	 * 最大一级用户人数@page2
	 * @param $starTime
	 * @param $endTime
	 */
	static function getLevelNum($starTime, $endTime)
	{
		$sql = "select max(childnum_1) as chnum from shp_users where reg_time BETWEEN $starTime and $endTime";
		$result = D()->query($sql)->get_one();
		if (empty($result['chnum'])) {
			return ['chnum' => 0];
		}
		return $result;
	}

	/**
	 * 最大一，二，三级用户人数@page2
	 * @param $starTime
	 * @param $endTime
	 */
	static function getMaxLevelNum($starTime, $endTime)
	{
		$sql = "select max(childnum_1) as chnum_1,max(childnum_2) as chnum_2,max(childnum_3) as chnum_3
             from shp_users where reg_time BETWEEN  $starTime and $endTime";
		$result = D()->query($sql)->get_one();
		$result['chnum_1'] = empty($result['chnum_1']) ? 0 : $result['chnum_1'];
		$result['chnum_2'] = empty($result['chnum_2']) ? 0 : $result['chnum_2'];
		$result['chnum_2'] = empty($result['chnum_3']) ? 0 : $result['chnum_3'];
		$num=$result['chnum_1']+$result['chnum_2']+$result['chnum_2'];
		return $num;
	}
	//commision
	/**
	 * 最大佣金
	 * @param $starTime
	 * @param $endTime
	 */
	static function getMaxCommision($starTime, $endTime)
	{
		$sql = "select max(commision) as commision from shp_order_info where pay_status = 2
              and shipping_time BETWEEN  $starTime AND $endTime";
		$commision = D()->query($sql)->get_one();
		$commision['commision'] = empty($commision['commision']) ? 0 : $commision['commision'];
		return $commision;
	}

	/**
	 * 平台收益额 = 商品卖出的总价钱-供货价-给米客的佣金
	 * @param $starTime
	 * @param $endTime
	 */
	static function getPlantMoney($starTime, $endTime)
	{
		$it = self::goods_sell_list($starTime, $endTime);
		$totalMoney = 0;
		foreach ($it as $val) {
			$totalMoney += $val['order_goods_num'] * $val['commision'] * PLATFORM_COMMISION;
		}
		return $totalMoney;

	}

	/**
	 * 平台用户数目
	 * @param $starTime
	 * @param $endTime
	 */
	static function getUserNumber($starTime, $endTime)
	{
		$sql = "select count(user_id) as userNum from shp_users  where reg_time BETWEEN $starTime AND $endTime";
		$userNum = D()->query($sql)->get_one();
		$userNum['userNum'] = empty($userNum['userNum']) ? 0 : $userNum['userNum'];
		return $userNum;
	}

	/**获取pv uv ip数目
	 * @param $starTime
	 * @param $endTime
	 */
	static function getVieNumber($starTime, $endTime)
	{
		$sql = "select count(1) as pv , count(DISTINCT ip) as ip ,count(DISTINCT uv) as uv  from tb_visiting
                where created BETWEEN $starTime AND $endTime";
		$vieNumber = D()->query($sql)->get_one();
		$vieNumber['pv'] = empty($vieNumber['pv']) ? 0 : $vieNumber['pv'];
		$vieNumber['ip'] = empty($vieNumber['ip']) ? 0 : $vieNumber['ip'];
		$vieNumber['uv'] = empty($vieNumber['uv']) ? 0 : $vieNumber['uv'];
		return $vieNumber;

	}


	static function money_yuan($val)
	{
		$val = number_format($val, 2, '.', '');
		if (preg_match('/\.0+$/', strval($val))) {
			$val = intval($val);
		}
		return $val;
	}

	/**
	 * 商品销售列表
	 */
	static function goods_sell_list($startTime, $endTime)
	{
		$sql = "SELECT g.goods_id,g.goods_name,g.income_price,g.shop_price,g.paid_order_count,g.commision,m.facename,SUM(og.goods_number) AS order_goods_num
			FROM `shp_goods` g INNER JOIN `shp_merchant` m ON g.merchant_uid=m.admin_uid
				INNER JOIN `shp_order_goods` og ON g.goods_id=og.goods_id
			WHERE m.created BETWEEN $startTime and $endTime
			GROUP BY g.goods_id
			ORDER BY paid_order_count DESC";
		$list = D()->query($sql)->fetch_array_all();
		return $list;
	}

	/**获取日 周 月活跃数 =浏览的数目 - 注册的数目
	 * @param $starTime
	 * @param $endTime
	 */
	static function getDayNumber($starTime)
	{
		//取出当天
		$lastDay = $starTime + ONE_DAY_TIME - 1;
		$weekTime = self::getDateStam($starTime);
		$weekStart = $weekTime['start_day'];
		$weekEnd = $weekTime['end_day'];
		$monthTime = self::getMonthStam($starTime);
		$monthStart = $monthTime['start_day'];
		$monthEnd = $monthTime['end_day'];
		$sql = "select count(users.user_id) as userCount from shp_users users where users.reg_time BETWEEN {$starTime} and {$lastDay}
		        UNION ALL
		        select count(users.user_id) as userCount from shp_users users where users.reg_time BETWEEN {$weekStart} and {$weekEnd}
		        UNION ALL
		        select count(users.user_id) as userCount from shp_users users where users.reg_time BETWEEN {$monthStart} and {$monthEnd}";
		$result = D()->query($sql)->fetch_array_all();
		$sql = "select count(distinct uid) as vieNum from tb_visiting  where created BETWEEN $starTime and $lastDay UNION ALL
              select count(distinct uid) as vieNum from tb_visiting  where created BETWEEN $weekStart and $weekEnd UNION ALL
              select count(distinct uid) as vieNum from tb_visiting  where created BETWEEN $monthStart and $monthEnd ";
		$data = D()->query($sql)->fetch_array_all();
		//var_dump(D()->getSqlFinal());exit;
		foreach ($data as $val1) {
			foreach ($result as &$val2) {
				$val2['totalVie'] = $val1['vieNum'];
			}
		}
		return $result;

	}

	/**
	 * 获取本周的第一天和最后一天
	 */

	static function getDateStam($stratTime)
	{
		$date = new DateTime();
		$date->setTimestamp($stratTime);
		$date->modify("this week");
		$first_day_of_week = $date->format('Y-m-d');
		$date->modify('this week +6 days');
		$end_day_of_week = $date->format('Y-m-d');
		$result['start_day'] = strtotime($first_day_of_week);
		$result['end_day'] = strtotime($end_day_of_week);
		return $result;
	}

	/**
	 * 获取本月的第一天和最后一天
	 */

	static function  getMonthStam($time)
	{
		$beginThismonth = mktime(0, 0, 0, date('m', $time), 1, date('Y', $time));
		$endThismonth = mktime(23, 59, 59, date('m', $time), date('t', $time), date('Y', $time));
		$result['start_day'] = $beginThismonth;
		$result['end_day'] = $endThismonth;
		return $result;
		//  echo date("Y-m-d",$beginThismonth)."-----".date("Y-m-d",$endThismonth);
	}
}

?>
<style>
	* {
		margin: 0;
		padding: 0;
	}

	.midle {
		/*border: 1px solid red;*/
		margin: 0 auto;
		width: 800px;
		height: auto;
		position: relative;
	}

	.midle button {
		background: blueviolet;
		color: white;
		width: 100px;
	}

	.midle input {
		width: 100px;
	}

	table {
		width: 100%;
		border-spacing: 0;
	}

	th, td {
		border-style: solid;
		border-color: #ddd;
		border-width: 0 0 1px;
		padding: 5px 2px;
	}

	td {
		text-align: center;
	}

	th {
		font-weight: bold;
	}

	h1 {
		text-align: center;
		margin-bottom: 15px;
		font-size: 18px;
		border-bottom: 2px solid #ccc;
		padding: 10px 0;
	}

	.page {
		position: fixed;
		background: blueviolet;
		color: #FFFFFF;
		font-weight: 100;
		font-size: 14px;
		border-radius: 5px;
		text-align: center;
		height: 20px;
		line-height: 20px;
		width: 90px;
		left: 95%;
		bottom: 5%;
	}

	.page a {
		display: inline-block;
		font-size: 14px;
		font-weight: 800;
		color: white;
	}
</style>
<h1>统计</h1>
<form action="sdtat.php?page=<?= $page ?>" method="post">
	<div class="midle">
		请输入开始日期：<input type="text" placeholder="<?php echo empty($startDate) ? '请输入开始日期' : $startDate ?>"
					   name="startDate"/>
		请输入结束日期：<input type="text" placeholder="<?php echo empty($endDate) ? '请输入结束日期' : $endDate ?>" name="endDate"/>
		<button type="submit">提交</button>
	</div>
</form>
<table>
	<?php if ($page == 1): ?>
		<tr>
			<th>日期时间</th>
			<th>拍下订单数</th>
			<th>拍下总金额(GMV)</th>
			<th>成交用户数</th>
			<th>成交金额</th>
			<th>成交订单数</th>
			<th>退款金额</th>
			<th>最大成品成交订单数</th>
			<th>最大单品成交金额</th>
		</tr>
		<!--            --><?= $html ?>
		<div class="page"><a
				href="sdtat.php?page=<?= $page + 1 ?>&startDate=<?= $startDate ?>&endDate=<?= $endDate ?>"><b>下一页</b></a>
		</div>
	<?php endif; ?>
	<?php if ($page == 2): ?>
		<tr>
			<th>日期时间</th>
			<th>最大一级用户数</th>
			<th>最大一,二,三级用户数</th>
			<th>最大佣金收益金额</th>
			<th>平台收益额</th>
			<th>平台用户数</th>
			<th>平台公众号数目</th>
			<th>PV数</th>
			<th>UV数</th>
			<th>IP数</th>
			<th>日活跃数</th>
			<th>周活跃数</th>
			<th>月活跃数</th>
		</tr>
		<?= $html ?>
		<div class="page"><a
				href="sdtat.php?page=<?= $page - 1 ?>&startDate=<?= $startDate ?>&endDate=<?= $endDate ?>"><b>上一页</b></a>
		</div>
	<?php endif; ?>
</table>



