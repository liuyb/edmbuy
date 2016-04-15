<?php
/**
 * 需要经常跑，并且短时的作业(5分钟执行一次)
 *
 * @author Gavin<laigw.vip@gmail.com>
 */

class FrequentJob extends CronJob {
	
	/**
	 * 14天时间秒数
	 * @var constant
	 */
	const TIME_14DAYS = 1209600; //=86400*14
	
	public function main($argc, $argv) {
		
		// 批量更新上级昵称
		$this->upParentNick();
		
		// 批量更新用户的一级下级数
		$this->upChildNum1();
		
		// 批量更新收货时间
		$this->upShippingConfirmTime();
		
		// 检查父订单的子订单是否已经全部确认收货，是的话确认父订单收货
		$this->upShippingConfirmTime_ParentOrder();
		
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
		$the_time = simphp_gmtime() - self::TIME_14DAYS;
		$sql = "UPDATE `shp_order_info`"
				 . " SET `order_status`=".OS_CONFIRMED.",`shipping_status`=".SS_RECEIVED.",`shipping_confirm_time`=`pay_time`+%d"
		     . " WHERE `pay_status`=".PS_PAYED." AND `shipping_status`=".SS_SHIPPED." AND `pay_time`<={$the_time}";
		D()->query($sql, self::TIME_14DAYS);
		$this->log("OK. affected rows: ".D()->affected_rows());
	}
	
	/**
	 * 更新某订单的收货时间
	 * @param integer $order_id
	 * @param integer $status_to
	 * @param integer $max_confirm_ship_time
	 */
	private function upShippingConfirmTime_ChildOrder($order_id, $status_to, $max_confirm_ship_time) {
		if (!in_array($status_to, [SS_RECEIVED, SS_RECEIVED_PART])) {
			return;
		}
		$this->log("update child order shipping confirm time, order_id={$order_id}...");
		$sql = "UPDATE `shp_order_info`"
				 . " SET `order_status`=".OS_CONFIRMED.",`shipping_status`=%d,`shipping_confirm_time`=%d"
				 . " WHERE `order_id`=%d";
		D()->query($sql, $status_to, $max_confirm_ship_time, $order_id);
		$this->log("--OK. affected rows: ".D()->affected_rows());
	}
	
	/**
	 * 检查父订单的子订单是否已经全部确认收货，是的话确认父订单收货
	 */
	private function upShippingConfirmTime_ParentOrder() {
		$this->log("update parent order shipping confirm time(14 days after pay_time)...");
		$list_sql = "SELECT order_id FROM `shp_order_info` WHERE is_separate=1 AND pay_status=%d AND shipping_status NOT IN(".SS_RECEIVED.",".SS_RECEIVED_PART.")";
		$parent_list = D()->query($list_sql, PS_PAYED)->fetch_array_all();
		if (!empty($parent_list)) {
			$total = count($parent_list);
			foreach ($parent_list AS $order) {
				$order_id   = $order['order_id'];
				$child_sql  = "SELECT order_id,shipping_status,pay_status,shipping_confirm_time FROM `shp_order_info` WHERE parent_id=%d AND is_separate=0";
				$child_list = D()->query($child_sql, $order_id)->fetch_array_all();
				if (!empty($child_list)) {
					$ship_status_to = SS_UNSHIPPED;
					$finish_count   = 0; // "已确认收货" 数
					$noeffect_count = 0; // "确定无效" 数
					$max_confirm_ship_time = 0;
					foreach ($child_list AS $child_order) {
						if ($child_order['shipping_status']==SS_RECEIVED && $child_order['pay_status']==PS_PAYED) {
							$finish_count++;
							$max_confirm_ship_time = $child_order['shipping_confirm_time'] > $max_confirm_ship_time ? $child_order['shipping_confirm_time'] : $max_confirm_ship_time;
						}
						if ($child_order['pay_status']==PS_REFUND || $child_order['pay_status']==PS_REFUNDING) {
							$noeffect_count++;
						}
					}
					
					$total_child = count($child_list);
					$this->log("parent order({$order_id}) has child records: {$total_child}");
					if ($finish_count == $total_child) { //表明所有子订单都已经确认收货
						$this->upShippingConfirmTime_ChildOrder($order_id, SS_RECEIVED, $max_confirm_ship_time);
					}
					elseif ($finish_count > 0) { //表明只有部分订单已经确认收货
						if ($finish_count == $total_child-$noeffect_count) { //表明剩下的子订单都是已退款无效的订单，则总订单可以设置为“全部确认收货”
							$this->upShippingConfirmTime_ChildOrder($order_id, SS_RECEIVED, $max_confirm_ship_time);
						}
						else { //只能设定为部分收货
							$this->upShippingConfirmTime_ChildOrder($order_id, SS_RECEIVED_PART, $max_confirm_ship_time);
						}
					}
				}
			}
			$this->log("OK. {$total} parent records found.");
		}
		else {
			$this->log("OK. No record found.");
		}
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