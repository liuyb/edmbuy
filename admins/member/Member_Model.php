<?php
/**
 * Member Model Class
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Member_Model extends Model {
	
	public static function getMembers(){
		$db = D();
		$sql = "SELECT * FROM {member} ORDER BY uid DESC ";
		return $db->query($sql)->fetch_array_all(); 
	}
	public static function getMemberById($uid){
		$db = D();
		$sql = "SELECT * FROM {member} WHERE uid=%d";
		return $db->get_one($sql,$uid);
	}
	public static function updateMemberById($uid,$data){
		$db = D();
		return $db->update_table('member', $data, ['uid' => $uid]);
	}
	public static function getMembersByWhere($where,$sort){
		$db = D();
		$sort_str = '';
		if($sort!=''){
			$sort_str .= ' ORDER BY '.$sort;
		}		
		$sql = "SELECT * FROM {member} WHERE 1 {$where} {$sort_str} ";
		return $db->query($sql)->fetch_array_all();
	}
	public static function getMemberLoginLog($where,$sort){
		$db = D();
		$sort_str = '';
		if($sort!=''){
			$sort_str .= ' ORDER BY '.$sort;
		}
		$sql = "SELECT * FROM {member_login_log} WHERE 1 {$where} {$sort_str} ";
		return $db->query($sql)->fetch_array_all();
	}
	

}
 
/*----- END FILE: Member_Model.php -----*/