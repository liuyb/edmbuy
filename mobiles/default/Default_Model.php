<?php
/**
 * 默认Model 
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Default_Model extends Model {
  
  static function getAd($ad_name){
  	$sql = "SELECT * FROM {ad} WHERE ad_name='%s' ";
  	$ad = D()->get_one($sql, $ad_name);
  	if(empty($ad)){
  		return [];
  	}else{
  		$sql = "SELECT * FROM {ad_pic} WHERE ad_id = %d ORDER BY `sort` DESC LIMIT %d ";
  		$ad_list = D()->query($sql, $ad['ad_id'], $ad['max_num'])->fetch_array_all();
  		return $ad_list;
  	}
  }
  
}
 
/*----- END FILE: Default_Model.php -----*/