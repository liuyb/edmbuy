<?php
/**
 * 通用模块，所有模块被调用时，都会先执行的模块
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Common_Controller extends Controller {

  /**
   * hook menu
   *
   * @return array
   */
  public static function menu_init() {
    return array(
      '!^login[a-z_]*$!i'  => 'user/$0',
      '!^logout[a-z_]*$!i' => 'user/$0',
      '!^home$!i' => 'user/index',
      //Info
      '!^channel[a-z0-9_/]*$!i' => 'info/$0',    
      '!^content[a-z0-9_/]*$!i' => 'info/$0',
    );
  }
  
  
}
 
/*----- END FILE: Common_Controller.php -----*/