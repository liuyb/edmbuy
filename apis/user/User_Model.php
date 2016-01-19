<?php
/**
 * 用户同步接口Model
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class User_Model extends Model {

	static function saveAppUser(Array $data, $app = 'tym') {
		$userid = isset($data['userid']) ? $data['userid'] : 0;
		if (empty($userid)) return 0;
		
		$table = '`tb_tym_user`';
		D()->realtime_query = TRUE;
		$row = D()->query("SELECT * FROM {$table} WHERE `userid`=%d", $userid)->get_one();
		if (empty($row)) { //未存在，插入
			$effrows = D()->insert($table, $data, false);
		}
		else { //已存在，更新可能要更新的数据
			if (isset($data['userid'])) unset($data['userid']);
			if (isset($data['regtime'])) unset($data['regtime']);
			if (!empty($row['parent_userid']) && isset($data['parent_userid'])) unset($data['parent_userid']);
			$effrows = D()->update($table, $data, ['userid' => $userid]);
		}
		return $effrows ? $userid : false;
	}
	
}

/*----- END FILE: User_Model.php -----*/