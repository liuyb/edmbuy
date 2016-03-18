<?php
/**
 * User Model 
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class User_Model extends Model {
  
  /**
   * 检查login
   * @param string $uname
   * @param string $upass_raw
   * @param array $output
   * @return integer -1: no the user; 0: password error; 1: ok
   */
  static function check_logined($uname, $upass_raw, &$output = []) {
  	$where = "idname='%s'";
  	if ($uname) {
  		
  	}
  	
    $admin = D()->get_one("SELECT * FROM `shp_merchant` WHERE {$where}", $uname);
    if (empty($admin)) {
      return -1;
    }
    
    //check db password
    $upass_enc = gen_salt_password($upass_raw,$admin['salt']);
    if ($admin['admin_upass']!=$upass_enc) {
      return 0;
    }
    
    $output = $admin;
    return 1;
  }

}
 
/*----- END FILE: User_Model.php -----*/