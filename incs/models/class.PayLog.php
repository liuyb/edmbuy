<?php
/**
 * Storage Node Model
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class PayLog extends StorageNode {
	
	protected static function meta() {
		return array(
				'table' => '`shp_pay_log`',
				'key'   => 'log_id',
				'columns' => array(
						'log_id'         => 'log_id',
						'order_id'       => 'order_id',
						'order_sn'       => 'order_sn',
						'order_amount	'  => 'order_amount	',
						'order_type'     => 'order_type',
						'is_paid'        => 'is_paid',
				)
		);
	}
	
	/**
	 * 将支付LOG插入数据表
	 *
	 * @access  public
	 * @param   integer     $order_id   订单编号
	 * @param   string      $order_sn   订单号
	 * @param   float       $amount     订单金额
	 * @param   integer     $type       支付类型
	 * @param   integer     $is_paid    是否已支付
	 *
	 * @return  int
	 */
	static function insert($order_id, $order_sn, $amount, $type = PAY_ORDER, $is_paid = 0) {
		$insert = [
				'order_id'    => $order_id,
				'order_sn'    => $order_sn,
				'order_amount'=> $amount,
				'order_type'  => $type,
				'is_paid'     => $is_paid,
		];
		$log_id = D()->insert(self::table(), $insert);
		return $log_id;
	}
	
}
 
/*----- END FILE: class.PayLog.php -----*/