<?php
/**
 * AdminUser Node Model
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
class AdminUser extends StorageNode {

	protected static function meta() {
		return array(
				'table'   => '`shp_admin_user`',
				'key'     => 'user_id',
				'columns' => array(
						'user_id'      => 'user_id',
						'user_name'    => 'user_name',
						'email'        => 'email',
						'password'     => 'password',
						'ec_salt'      => 'ec_salt',
						'add_time'     => 'add_time',
						'last_login'   => 'last_login',
						'last_ip'      => 'last_ip',
						'action_list'  => 'action_list',
						'nav_list'     => 'nav_list',
						'lang_type'    => 'lang_type',
						'agency_id'    => 'agency_id',
						'suppliers_id' => 'suppliers_id',
						'todolist'     => 'todolist',
						'role_id'      => 'role_id',
						'merchant_id'  => 'merchant_id'
				)
		);
	}

	static function getNameByAdminUid($admin_uid) {
		$ret = D()->from(self::table())->where("user_id=%d", $admin_uid)->select("user_name")->result();
		return $ret;
	}
	
}
 
/*----- END FILE: class.AdminUser.php -----*/