<?php
/**
 * Model 
 *
 * @author afarliu
 */
defined('IN_SIMPHP') or die('Access Denied');

class Other_Model extends Model {

	public static function getAdList($sort_field, $sort ,$page_size){
		$sql = "SELECT * FROM {ad} ORDER BY `{$sort_field}` {$sort} ";
		$sqlcnt = "SELECT COUNT(ad_id) FROM {ad} ";

		return D()->pager_query($sql,$page_size, $sqlcnt)->fetch_array_all();
	}
	
	public static function getAdInfo($ad_id){
		$sql = "SELECT * FROM {ad} WHERE ad_id=%d ";
		return D()->get_one($sql, $ad_id);
	}

	public static function addAd($data){
		return D()->insert('ad', $data);
	}

	public static function editAd($data,$ad_id){
		return D()->update('ad', $data, ['ad_id'=>$ad_id]);
	}

	public static function getAdPicList($ad_id,$sort_field, $sort ,$page_size){
		$sql = "SELECT * FROM {ad_pic}  WHERE ad_id={$ad_id} ORDER BY `{$sort_field}` {$sort} ";
		$sqlcnt = "SELECT COUNT(pic_id) FROM {ad_pic}  WHERE ad_id={$ad_id} ";

		return D()->pager_query($sql,$page_size, $sqlcnt)->fetch_array_all();
	}

	public static function addAdPic($data){
		return D()->insert('ad_pic', $data);
	}

	public static function editAdPic($data, $pic_id){
		return D()->update('ad_pic', $data, ['pic_id'=>$pic_id]);
	}

	public static function getAdPicInfo($pic_id){
		$sql = "SELECT * FROM {ad_pic} WHERE pic_id=%d ";
		return D()->get_one($sql, $pic_id);
	}

	public static function deleteAdList($ids){
		if (!is_array($ids)) {
	      $ids = array($ids);
	    }
	    $res_ids = [];
	    foreach($ids as $id){
	    	$old = D()->get_one("SELECT * FROM {ad_pic} WHERE pic_id=%d ", $id);
	    	if(!empty($old)){
	    		$affected = D()->delete('ad_pic', ['pic_id'=>$id]);
	    		if($affected){
	    			$res_ids[] = $id;
	    			if($old['pic_path']!=''&&(!preg_match('/^http/', $old['pic_path']))){
	    				$realpath = SIMPHP_ROOT.$old['pic_path'];
	    				if(is_file($realpath)){
	    					@unlink($realpath);
	    				}
	    			}
	    		}
	    	}
	    }
	    return $res_ids;
	}
}