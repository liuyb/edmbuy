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
						'overdueTime'  => 'overdueTime',
						'verifyCode'   => 'verifyCode',
						'sendContent'  => 'sendContent',
						'result'       => 'result',
						'type'         => 'type',
				)
		);
	}
	
}
 
/*----- END FILE: class.UsersmsLog.php -----*/