<?php
/**
 * 运营统计
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
$root = substr(getcwd(), 0, -9);
require ($root.'/core/init.php');

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
    $from_time = $from_time ? simphp_gmtime($from_time) : 0;
    $to_time   = $to_time   ? simphp_gmtime($to_time)   : 0;
    
	$html  =<<<HEREDOC
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8"/>
<title>益多米运营数据下载</title>
<style type="text/css">
html,body,h1,div,p,ul,li{ margin: 0;padding: 0; }
h1 { text-align: center;margin-bottom: 15px;font-size: 18px;border-bottom: 2px solid #ccc;padding: 10px 0; }
li { padding: 5px 10px; }
</style>
</head>
<body>
	<h1>益多米运营数据下载</h1>
	<ul>
	  <li><a href="?type=1&from={$from_date}&to={$to_date}">会员发展的一级排名统计</a></li>
	  <li><a href="?type=2&from={$from_date}&to={$to_date}">团队挑战赛(统计时间：{$stat_time_text})</a></li>
	  <li><a href="?type=3&from={$from_date}&to={$to_date}">会员统计(统计时间：{$stat_time_text})</a></li>
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
	
	$csv = "用户ID,用户名称,用户级别,总一级会员,米客,米商,银牌,金牌,入驻商家".CSV_LN;
	//获取商家总收入列表
	$list = ThisFn::getRangedUsers();
	if (!empty($list)) {
		$actotalPlatCommision = 0.00;
		foreach ($list AS $it) {
		    $user_id = $it['user_id'];
		    $total = $it['c'];
		    $user = Users::load($user_id);
		    $ul = Users::displayUserLevel($user->level);
			$rows = ThisFn::getUserChild($user_id);
			$mk = 0;$ms = 0;$yp=0;$jp=0;$sj=0;
			foreach ($rows as $r){
			    $l = $r['level'];
			    $c = $r['c'];
			    switch ($l){
			        case 0:
			            $mk += $c;
			            break;
			        case 1:
			            $ms += $c;
			            break;
			        case 3:
			            $yp += $c;
			            break;
			        case 4:
			             $jp += $c;
			             break;
			        case 5:
			             $sj += $c;
			    }
			}
			$csv .= '"'.$user_id.'"'.CSV_SEP.'"'.$user->nickname.'"'.CSV_SEP.'"'.$ul.'"'.CSV_SEP.$total.CSV_SEP.$mk.CSV_SEP.$ms.CSV_SEP.$yp.CSV_SEP.$jp.CSV_SEP.$sj.CSV_LN;
			
		}
	}
	
}else if(2 == $type){
    $from_time = $from_time ? simphp_gmtime($from_time) : 0;
    $to_time   = $to_time   ? simphp_gmtime($to_time)   : 0;
    $csv = "用户ID,用户名称,手机号,佣金".CSV_LN;
    $team = array(
        '天地人战队' => '2145,2961,109467,2128,120437,1602,109228,137140,58306,126269'
    );
    foreach ($team as $tn => $tv){
        $csv .= '"'.$tn.'"'.CSV_LN;
        $teamData = ThisFn::getTeamData($tv,$from_time,$to_time);
        $total = 0;
        foreach ($teamData as $data){
            $total += $data['s'];
            $csv .= '"'.$data['user_id'].'"'.CSV_SEP.'"'.$data['nick_name'].'"'.CSV_SEP.'"'.$data['mobile'].'"'.CSV_SEP.'"'.$data['s'].'"'.CSV_LN;
        }
        $csv .= '合计'.CSV_SEP.'--'.CSV_SEP.'--'.CSV_SEP.$total.CSV_LN;
    }
    
}else if(3 == $type){
    $csv = "会员总数,米客总数,米商总数,银牌总数,金牌总数,商家总数".CSV_LN;
    $rows = ThisFn::getMemberData($from_time, $to_time);
    $mk = 0;$ms = 0;$yp=0;$jp=0;$sj=0;$total=0;
	foreach ($rows as $r){
	    $l = $r['level'];
	    $c = $r['c'];
	    $total += $c;
	    switch ($l){
	        case 0:
	            $mk += $c;
	            break;
	        case 1:
	            $ms += $c;
	            break;
	        case 3:
	            $yp += $c;
	            break;
	        case 4:
	             $jp += $c;
	             break;
	        case 5:
	             $sj += $c;
	    }
	}
	$csv .= $total.CSV_SEP.$mk.CSV_SEP.$ms.CSV_SEP.$yp.CSV_SEP.$jp.CSV_SEP.$sj.CSV_LN;
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

    static function getRangedUsers(){
        $sql = "select u.user_id,ifnull(t.c, 0) c from (select user_id from shp_users where level in(3,4,5) and mobile <> '') 
u left join (select count(*) c,parent_id from shp_users where parent_id in(
SELECT user_id FROM `shp_users` where level in(3,4,5) and mobile <> '')
group by parent_id) t on u.user_id = t.parent_id order by t.c desc";
        return D()->query($sql)->fetch_array_all();
    }
    
    static function getUserChild($user_id){
        $sql = "select count(1) c,level from shp_users where parent_id = $user_id group by level ";
        return D()->query($sql)->fetch_array_all();
    }
    
    static function getTeamData($ids,$from_time,$to_time){
        $where = "";
        if ($from_time) {
            $where .= " AND c.`paid_time`>=".$from_time;
        }
        if ($to_time) {
            $where .= " AND c.`paid_time`<=".$to_time;
        }
        $sql = "select sum(c.commision) s,u.user_id,u.nick_name,u.mobile from shp_user_commision c left join shp_users u on c.user_id = u.user_id  
            where u.user_id in ($ids) $where group by u.user_id order by s desc";
        return D()->query($sql)->fetch_array_all();
    }
    
    static function getMemberData($from_time,$to_time){
        $where = "";
        if ($from_time) {
            $where .= " AND u.`reg_time`>=".$from_time;
        }
        if ($to_time) {
            $where .= " AND u.`reg_time`<=".$to_time;
        }
        $sql = "select count(1) c,level from shp_users u where mobile <> '' $where group by level";
        return D()->query($sql)->fetch_array_all();
    }
}