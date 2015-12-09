<?php
defined('IN_SIMPHP') or die('Access Denied');

class Upload_Model extends Model {
  
	public static function saveUpload($data){
		$data['timeline'] = time();
		return D()->insert_table('upload_pic', $data);
	}
	
}