<?php
/**
 * 默认(一般首页)模块控制器，此控制器必须
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Default_Controller extends Controller {
  
  /**
   * default action 'index'
   * @param Request $request
   * @param Response $response
   */
  public function index(Request $request, Response $response)
  {
    if (Merchant::is_logined()) {
      $response->redirect('/home');
    }
    else {
      $response->redirect('/login');
    }
  }

}
 
/*----- END FILE: Default_Controller.php -----*/