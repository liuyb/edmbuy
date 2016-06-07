<?php
/**
 * 
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class UsersPending extends StorageNode {
	
	protected static function meta() {
		return array(
				'table' => '`shp_users_pending`',
				'key'   => 'rid',
				'columns' => array(
						'rid'         => 'rid',
						'unionid'     => 'unionid',
						'openid'      => 'openid',
						'subscribe'   => 'subscribe',
						'subscribe_time'=> 'subscribe_time',
						'parent_id'   => 'parent_id',
						'nick'        => 'nick',
						'logo'        => 'logo',
						'gender'      => 'gender',
						'lang'        => 'lang',
						'country'     => 'country',
						'province'    => 'province',
						'city'        => 'city',
						'auth_method' => 'auth_method',
						'touch_time'  => 'touch_time',
						'update_time' => 'update_time'
				));
	}
	

	/**
	 * Load pending user by UnionID
	 *
	 * @param string $unionid
	 * @return UsersPending
	 */
	static function load_by_unionid($unionid) {
		return self::find_one(new Query('unionid', $unionid));
	}
	
	/**
	 * Load pending user by OpenID
	 * @param unknown $openid
	 * @return UsersPending
	 */
	static function load_by_openid($openid) {
		return self::find_one(new Query('openid', $openid));
	}
	
}
 
/*----- END FILE: class.UsersPending.php -----*/