<?php
/**
 * Payment公用Model
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Payment extends StorageNode {
	
	protected static function meta() {
		return array(
				'table' => '`shp_payment`',
				'key'   => 'pay_id',
				'columns' => array(
						'pay_id'     => 'pay_id',
						'pay_code'   => 'pay_code',
						'pay_name'   => 'pay_name',
						'pay_fee'    => 'pay_fee',
						'pay_desc'   => 'pay_desc',
						'pay_order'  => 'pay_order',
						'pay_config' => 'pay_config',
						'enabled'    => 'enabled',
						'is_cod'     => 'is_cod',
						'is_online'  => 'is_online'
				)
		);
	}
	
}

/*----- END FILE: class.Payment.php -----*/