<?php
/**
 * AdminUser2 Node Model
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
class AdminUser2 extends StorageNode {

	protected static function meta() {
		return array(
				'table'   => '{admin_user}',
				'key'     => 'uid',
				'columns' => array(
						'uid'          => 'admin_uid',
						'admin_uname'  => 'admin_uname',
						'admin_upass'  => 'admin_upass',
						'admin_salt'   => 'admin_salt',
						'admin_perms'  => 'admin_perms',
						'lastlogin'    => 'last_login',
						'lastip'       => 'last_ip',
						'admin_state'  => 'admin_state',
				)
		);
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
 
/*----- END FILE: class.AdminUser2.php -----*/