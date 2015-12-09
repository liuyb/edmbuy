<?php
/**
 * 共用Model
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Common_Model extends Model {

  /**
   * check whether admin is logined.
   * @return boolean
   */
  public static function admin_logined() {
    return isset($_SESSION['logined_uid']) && $_SESSION['logined_uid']>0 ? true : false;
  }
}

/*----- END FILE: Common_Model.php -----*/