<?php
/**
 * 需要经常跑，并且短时的作业(5分钟执行一次)
 *
 * @author Gavin<laigw.vip@gmail.com>
 */

class FrequentJob extends CronJob {
	
	public function main($argc, $argv) {
		
		// 批量更新上级昵称
		$this->upParentNick();
		
		// 批量更新用户的一级下级数
		$this->upChildNum1();
		
		// 批量更新收货时间
		$this->upShippingConfirmTime();
		
		// 批量更新佣金状态变化时间
		$this->upCommisionState();
	}
	
	/**
	 * 批量更新上级昵称
	 */
	private function upParentNick() {
		$this->log("update parent nick...");
		$sql = "UPDATE `shp_users` a, `shp_users` b
SET a.parent_nick=b.nick_name
WHERE a.parent_id<>0 AND a.parent_id=b.user_id";
		D()->query($sql);
		$this->log("OK. affected rows: ".D()->affected_rows());
	}
	
	/**
	 * 批量更新用户的一级下级数
	 */
	private function upChildNum1() {
		$this->log("update child 1 num...");
		$sql = "UPDATE `shp_users` AS a, (SELECT ib.parent_id,COUNT(DISTINCT ib.user_id) AS chnum FROM `shp_users` AS ia INNER JOIN `shp_users` AS ib ON ib.parent_id=ia.user_id WHERE 1 GROUP BY ib.parent_id) AS b
SET a.childnum_1=b.chnum
WHERE a.user_id=b.parent_id";
		D()->query($sql);
		$this->log("OK. affected rows: ".D()->affected_rows());
	}
	
	/**
	 * 批量更新收货时间
	 */
	private function upShippingConfirmTime() {
		$this->log("update shipping confirm time(14 days after pay_time)...");
		$the_time = simphp_gmtime() - 86400*14;
		$sql = "UPDATE `shp_order_info` "
				 . "SET `order_status`=".OS_CONFIRMED.",`shipping_status`=".SS_RECEIVED.",`shipping_confirm_time`=`pay_time`+1209600 "
		     . "WHERE `pay_status`=".PS_PAYED." AND `shipping_status`=".SS_SHIPPED." AND `pay_time`<={$the_time}";
		D()->query($sql);
		$this->log("OK. affected rows: ".D()->affected_rows());
	}
	
	/**
	 * 批量更新佣金状态变化时间
	 */
	private function upCommisionState() {
		$this->log("update commision state and time(7 days after shipping_confirm_time)...");
		$the_time = simphp_gmtime() - 86400*7;
		$now_time = simphp_time();
		$sql = "UPDATE `shp_user_commision` uc, `shp_order_info` o
SET uc.`state`=1, uc.`state_time`={$now_time}
WHERE o.`pay_status`=".PS_PAYED." AND o.`shipping_status`=".SS_RECEIVED." AND o.`shipping_confirm_time`<{$the_time} AND o.`shipping_confirm_time`>0 AND uc.`state`=0 AND uc.order_id=o.order_id";
		D()->query($sql);
		$this->log("OK. affected rows: ".D()->affected_rows());
	}
	
}
 
/*----- END FILE: FrequentJob.php -----*/