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
    return [
    
    ];
  }
  
  
}
 
/*----- END FILE: Common_Controller.php -----*/