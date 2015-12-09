<?php
/**
 * System log class
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class SystemLog {
  
  //private static $_log_prefix = 'simphp_';
  private static $_log_prefix = '';
  
  public static function local_log($name, $msg) {
    @file_put_contents(LOG_DIR . DS . self::$_log_prefix . $name . '.log',
                       '['.date('Y-m-d H:i:s') . "]: {$msg}\n",
                       FILE_APPEND | LOCK_EX);
  }
  
}
 
/*----- END FILE: class.SystemLog.php -----*/