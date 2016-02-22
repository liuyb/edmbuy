<?php
/**
 * Cash Model
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Cash_Model extends Model {
	
	static function stateTxt($state) {
		//待审核(red),已审核(blue),转账中(yellow),提现成功(green),提现失败(gray)
		$state_txt = '';
		if (in_array($state, [UserCashing::STATE_CHECK_PENDING,UserCashing::STATE_SUBMIT_AUTOCHECK,UserCashing::STATE_NOPASS_AUTOCHECK,UserCashing::STATE_SUBMIT_MANUALCHECK])) {
			$state_txt = '<span style="color:red">待审核</span>';
		}
		elseif (in_array($state, [UserCashing::STATE_PASS_AUTOCHECK,UserCashing::STATE_PASS_MANUALCHECK])) {
			$state_txt = '<span style="color:blue">已审核</span>';
		}
		elseif (in_array($state, [UserCashing::STATE_PASS_AUTOCHECK,UserCashing::STATE_SUBMIT_BANK])) {
			$state_txt = '<span style="color:#ff6600">转账中</span>';
		}
		elseif ($state==UserCashing::STATE_SUCC) {
			$state_txt = '<span style="color:green">提现成功</span>';
		}
		else {
			$state_txt = '<span style="color:gray">提现失败</span>';
		}
		return $state_txt;
	}
	
	static function getCashingList($orderby='cashing_id', $order='DESC', $limit=30, Array $query_conds=array(), &$statinfo=array())
	{
		$where_cond = '';
		if (isset($query_conds['from_date']) && $query_conds['from_date']) {
			$starttime = strtotime($query_conds['from_date'].DAY_BEGIN);
			$where_cond .= " AND c.`apply_time`>=".$starttime;
		}
		if (isset($query_conds['to_date']) && $query_conds['to_date']) {
			$endtime = strtotime($query_conds['to_date'].DAY_END);
			$where_cond .= " AND o.`apply_time`<=".$endtime;
		}
	
		$table  = UserCashing::table();
		$sql    = "SELECT c.* FROM {$table} c WHERE 1 {$where} ORDER BY `%s` %s";
		$sqlcnt = "SELECT COUNT(1) FROM {$table} c WHERE 1 {$where}";
	
		$result = D()->pager_query($sql,$limit,$sqlcnt,0,$orderby,$order)->fetch_array_all();
		$statinfo = ['total_pay'=>0, 'current_pay'=>0];
		if (!empty($result)) {
			foreach ($result AS &$it) {
				$it['state_txt'] = self::stateTxt($it['state']);
			}
		}
		return $result;	
	}
	
	static function getCashingOrderList($commision_ids) {
		if (empty($commision_ids)) return [];
		$sql = "SELECT uc.order_sn,uc.order_amount,uc.commision,o.pay_trade_no,o.pay_name 
				FROM `shp_user_commision` uc INNER JOIN `shp_order_info` o ON uc.order_id=o.order_id
				WHERE uc.rid IN(%s)
				";
		$list = D()->query($sql,$commision_ids)->fetch_array_all();
		return $list;
	}
	
}
 
/*----- END FILE: Cash_Model.php -----*/