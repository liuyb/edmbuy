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
				'key'     => 'uid',
				'columns' => array(
						'uid'          => 'user_id',
						'user_name'    => 'user_name',
						'email'        => 'email',
						'password'     => 'password',
						'ec_salt'      => 'ec_salt',
						'add_time'     => 'add_time',
						'lastlogin'    => 'last_login',
						'lastip'       => 'last_ip',
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
	
	/**
	 * Check whether user logined
	 * @return boolean
	 */
	static function is_logined() {
		return $GLOBALS['user']->uid ? true : false;
	}
	
	/**
	 * Set the current user to 'logined' status
	 */
	public function set_logined_status() {
	
		//设置登录session uid
		$GLOBALS['user']->uid = $this->uid;

		//重新变更session id
		SimPHP::$session->regenerate_id();
		
		//新起一个对象来编辑，避免过多更新
		$nUser = new self($this->uid);
		$nUser->lastlogin = simphp_time();
		$nUser->lastip    = Request::ip();
		$nUser->save();
	}
	
}
 
/*----- END FILE: class.AdminUser.php -----*/