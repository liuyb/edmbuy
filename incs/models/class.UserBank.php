<?php
/**
 * 用户银行关联 Node Model
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class UserBank extends StorageNode {

	protected static function meta() {
		return array(
				'table' => '`shp_user_bank`',
				'key'   => 'rid',
				'columns' => array(
						'rid'             => 'rid',
						'user_id'         => 'user_id',
						'bank_code'       => 'bank_code',
						'bank_name'       => 'bank_name',
						'bank_province'   => 'bank_province',
						'bank_city'       => 'bank_city',
						'bank_distict'    => 'bank_distict',
						'bank_branch'     => 'bank_branch',
						'bank_no'         => 'bank_no',
						'bank_uname'      => 'bank_uname',
						'timeline'        => 'timeline',
				)
		);
	}

}
 
/*----- END FILE: class.UserBank.php -----*/