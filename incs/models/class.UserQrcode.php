<?php
/**
 * UserQrcode Node Model
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class UserQrcode extends StorageNode {
	
	protected static function meta() {
		return array(
				'table' => '`shp_user_qrcode`',
				'key'   => 'rid',
				'columns' => array(
						'rid'       => 'rid',
						'user_id'   => 'user_id',
						'scene_id'  => 'scene_id'
				));
	}
	
}
 
/*----- END FILE: class.UserQrcode.php -----*/