<?php
/**
 * Media Model Class
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Media extends Model {

  /**
   * new insert or update media table
   * @return boolean
   */
  public static function save($data = [], $mid = 0) {
    if (!$mid) { //edit
      $data['created'] = simphp_time();
      $data['changed'] = $data['created'];
      $data['status']  = 'N';
      return D()->insert_table('media', $data);
    }
    else { //insert
      $data['changed'] = simphp_time();
      D()->update_table('media', $data, ['mid' => $mid]);
      return $mid;
    }
  }
  
  /**
   * Get media list
   * @param $mids mixed(integer, integer array, string)
   * @return array()
   */
  public static function getMediaList($mids) {
    $mids_str = $mids;
    if (is_array($mids)) {
      $mids_str = implode(',', $mids);
    }
    
    $ret = [];
    if ($mids_str) {
      $ret = D()->query("SELECT * FROM {media} WHERE mid IN(%s) AND `status`<>'D'", $mids_str)->fetch_array_all();
    }
    
    return $ret;
  }
  
  
}
 
/*----- END FILE: Media.php -----*/