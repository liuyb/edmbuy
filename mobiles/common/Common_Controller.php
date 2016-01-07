<?php
/**
 * 通用模块，所有模块被调用时，都会先执行的模块
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Common_Controller extends Controller {

  /**
   * 登录白名单，白名单中的请求地址不需登录，其他都需要。
   * @var array
   */
  public static $loginWhiteList = [
    'user/oauth',
    'user/oauth/%s',
  ];
  
  /**
   * hook menu
   *
   * @return array
   */
  public static function menu_init() {
    return [
      '!^about$!i'           => 'default/about',
    ];
  }
  
  /**
   * on dispatch before hook
   * 
   * @param Request $request
   * @param Response $response
   */
  public static function on_dispatch_before(Request $request, Response $response) {
    
    // 检查q是否在白名单中
    $loginIgnore = false;
    $q = $request->q();
    if (!empty($q)) {
      foreach(self::$loginWhiteList AS $key) {
        if (SimPHP::qMatchPattern($key, $q)) {
          $loginIgnore = true;
          break;
        }
      }
    }
    
    // 检查登录状态
    if(!$loginIgnore && !Users::is_logined()){
      import('user/*');
      $user_Controller = new User_Controller();
      $user_Controller->login($request, $response);
      exit;
    }
    
    //读取最新用户信息以客户端缓存
    global $user;
    if ($user->uid) {
    	$curUser = Users::load($user->uid);
    	$curUser->session = $user->session; //改写之前要先保存已有的session
    	$user = $curUser;
    	unset($curUser);
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