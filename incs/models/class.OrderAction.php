<?php
/**
 * Storage Node Model
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class OrderAction extends StorageNode {

	protected static function meta() {
		return array(
				'table' => '`shp_order_action`',
				'key'   => 'action_id',
				'columns' => array(
						'action_id'      => 'action_id',
						'order_id'       => 'order_id',
						'action_user'    => 'action_user',
						'order_status'   => 'order_status',
						'shipping_status'=> 'shipping_status',
						'pay_status'     => 'pay_status',
						'action_place'   => 'action_place',
						'action_note'    => 'action_note',
						'log_time'       => 'log_time',
				)
		);
	}

}
 
/*----- END FILE: class.OrderAction.php -----*/