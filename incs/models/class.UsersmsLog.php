<?php
/**
 * UsersmsLog table
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
class UsersmsLog extends StorageNode {
	
	protected static function meta() {
		return array(
				'table'   => '`shp_usersms_log`',
				'key'     => 'rid',
				'columns' => array(
						'rid'          => 'id',
						'receivePhone' => 'receivePhone',
						'touchTime'    => 'touchTime',
						'overdueTime'  => 'overdueTime',
						'verifyCode'   => 'verifyCode',
						'sendContent'  => 'sendContent',
						'result'       => 'result',
						'type'         => 'type'
				)
		);
	}
	
	/**
	 * 检查验证码是否有效
	 * @param string $vcode
	 * @param string $mobile
	 * @param string $type
	 * @return boolean
	 */
	static function check_vcode($vcode, $mobile, $type='reg_account') {
		$row = D()->query("SELECT * FROM ".self::table()." WHERE `receivePhone`='%s' AND `verifyCode`='%s' AND `type`='%s' AND `result`=1",
		                  $mobile,$vcode,$type)->get_one();
		if (!empty($row)) {
			if ((simphp_time()-$row['touchTime']) < 60*5) { //验证码5分钟内有效
				return true;
			}
		}
		return false;
	}
	
}
 
/*----- END FILE: class.UsersmsLog.php -----*/