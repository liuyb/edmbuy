<?php
/**
 * Cash model
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Cash_Model extends Model {

	const CASHING_MIN_INTERVAL = 3600; //60*60(1 hour)
	
	static function check_cashing_interval($user_id) {
		if (empty($user_id)) return false;
		$rows = D()->from(UserCashing::table())->where('user_id=%d',$user_id)
		           ->order_by("`cashing_id` DESC")->limit(0,2)
		           ->select("cashing_id,apply_time")->fetch_array_all();
		if ( count($rows) < 2 || 
				 ($rows[0]['apply_time'] - $rows[1]['apply_time']) > self::CASHING_MIN_INTERVAL ) { //时间间隔大于才能重新提出提现申请
			return true;
		}
		return false;
	}
	
}
 
/*----- END FILE: Cash_Model.php -----*/