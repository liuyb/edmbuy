<?php
/**
 * 默认(一般首页)模块控制器，此控制器必须
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Default_Controller extends MobileController {
  
  /**
   * hook init
   * 
   * @param string $action
   * @param Request $request
   * @param Response $response
   */
  public function init($action, Request $request, Response $response)
  {
    $this->nav_flag1 = 'home';
		parent::init($action, $request, $response);
  }
  
  /**
   * hook menu
   * @see Controller::menu()
   */
  public function menu()
  {
    return [
      'default/about'  => 'about'
    ];
  }
  
  /**
   * default action 'index'
   * 
   * @param Request $request
   * @param Response $response
   */
  public function index(Request $request, Response $response)
  {
    $this->v->set_tplname('mod_default_index');
    $this->nav_no    = 1;
    $this->v->set_page_render_mode(View::RENDER_MODE_GENERAL);
    
    $debug = $request->get('debug',0);
    if (!$debug) {
    	Fn::show_error_message('页面开发中，敬请关注...', false, '页面提示');
    }
    
    throw new ViewResponse($this->v);
  }

  /**
   * action 'about'
   *
   * @param Request $request
   * @param Response $response
   */
  public function about(Request $request, Response $response)
  {
    $this->v->set_tplname('mod_default_about');
    $this->nav_flag1 = 'about';
    
    if ($request->is_hashreq()) {
      
    }
    else {
      
    }
    $response->send($this->v);
  }
  
}
 
/*----- END FILE: Default_Controller.php -----*/