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
		'!^forgetPwd[a-z_]*$!i'  => 'user/$0',
		'!^checkSmsCode[a-z_]*$!i'  => 'user/$0',
		'!^setpassword[a-z_]*$!i'  => 'user/$0',
		'!^forgotSavePwd[a-z_]*$!i'  => 'user/$0',
    );
  }
  
  /**
   * 登录白名单，白名单中的请求地址不需登录，其他都需要。
   * @var array
   */
  public static $loginWhiteList = [
  		'user/login',
  		'user/logout',
	  	'user/forgetPwd',
	  	'user/checkSmsCode',
	  	'user/setpassword',
	  	'user/getPhoneCodeAjax',
	  	'user/forgotSavePwd',
  ];
  
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
  	if(!$loginIgnore && !Merchant::is_logined()){
  		$response->redirect('/login');
  	}

  	//读取最新用户信息以客户端缓存
  	global $user;
  	if ($user->uid) {
  		$curUser = Merchant::load($user->uid);
  		$curUser->session = $user->session; //改写之前要先保存已有的session
  		$user = $curUser;
  		unset($curUser);
  	}
  	
  }
  
}
 
/*----- END FILE: Common_Controller.php -----*/