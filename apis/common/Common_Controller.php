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
  
  /**
   * on dispatch before hook
   * 
   * @param Request $request
   * @param Response $response
   */
  public static function on_dispatch_before(Request $request, Response $response) {
  	
  	$q = $request->q();
  	if (!preg_match('/^(weixin|wxpay|alarm)/i', $q)) {

  		// 检查接口参数输入
  		Api::check($request, $response);
  		
  	}
    
  }
  
  /**
   * on dispatch after hook
   *
   * 注意：如果action中已经中途exit了，则这个方法不会被执行，可以使用on_shutdown
   * @param Request $request
   * @param Response $response
   */
  public static function on_dispatch_after(Request $request, Response $response) {
    //echo "<p>on dispatch after</p>";
  }
  
  /**
   * on shutdown hook
   * 
   * @param Request $request
   * @param Response $response
   */
  public static function on_shutdown(Request $request, Response $response) {
    //echo "<p>on shutdown</p>";
  }
  
}



/*----- END FILE: Common_Controller.php -----*/