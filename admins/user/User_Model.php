<?php
/**
 * User Model 
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class User_Model extends Model {
  
  /**
   * 
   * @param string $uname
   * @param string $upass_raw
   * @param array $output
   * @return integer -1: no the user; 0: password error; 1: ok
   */
  public static function check_logined($uname, $upass_raw, &$output = []) {
    $admin = D()->get_one("SELECT * FROM {admin_user} WHERE LOWER(`admin_uname`)='%s'", strtolower($uname));
    if (empty($admin)) {
      return -1;
    }
    
    //check db password
    $upass_enc = gen_salt_password($upass_raw,$admin['admin_salt']);
    if ($admin['admin_upass']!=$upass_enc) {
      return 0;
    }
    
    $output = $admin;
    return 1;
  }

}
 
/*----- END FILE: User_Model.php -----*/