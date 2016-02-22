<?php
/**
 * 提现银行 Node Model
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class CashingBank extends StorageNode {
	
	protected static function meta() {
		return array(
				'table' => '`shp_cashing_bank`',
				'key'   => 'bank_id',
				'columns' => array(
						'bank_id'      => 'bank_id',
						'bank_code'    => 'bank_code',
						'bank_name'    => 'bank_name',
						'cashing_fee'  => 'cashing_fee',
						'sortorder'    => 'sortorder',
						'enabled'      => 'enabled',
				)
		);
	}
	
}

/*----- END FILE: class.CashingBank.php -----*/