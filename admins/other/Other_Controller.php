<?php
/**
 * 控制器
 *
 * @author afarliu
 */
defined('IN_SIMPHP') or die('Access Denied');

class Other_Controller extends Controller {

  private $_nav = 'other';
  
  /**
   * hook menu
   *
   * @return array
   */
  public function menu() 
  {
    return [
    		
    ];
  }
  
  /**
   * hook init
   * @param string $action
   * @param Request $request
   * @param Response $response
   */
  public function init($action, Request $request, Response $response)
  {
    $this->v = new PageView();
    $this->v->assign('nav', $this->_nav);
  }
  
  /**
   * default action 'index'
   * @param Request $request
   * @param Response $response
   */
  public function index(Request $request, Response $response)
  {
    $menu_name = 'menu';
    
    $menuConfig = config_get('wxmenu');
    if (empty($menuConfig)) {
      $menuConfig = [];
    }
    $menuConfig = json_encode($menuConfig,JSON_UNESCAPED_UNICODE);
    
    $this->v->assign('menuConfig', $menuConfig);
    $this->v->set_tplname('mod_other_index');
    $this->v->assign('nav_second', $menu_name);
    $response->send($this->v);
  }

  public function updateMenu(Request $request, Response $response)
  {
    $rs = array('flag'=>'FAIL','msg'=>'');
    $menuConfig = $request->post('menuConfig', '');
    $menuConfig = json_decode($menuConfig, TRUE);
    if(empty($menuConfig)){
      $rs['msg'] = '请检查数据';
      $response->sendJSON($rs);
      exit;
    }
    
    config_set('wxmenu', $menuConfig, 'J');

    if((new Weixin())->createMenu($menuConfig)){
      $rs['flag'] = 'SUC';
      $rs['msg'] = 'fxm菜单更新成功';
      if ((new Weixin('zfy'))->createMenu($menuConfig)) {
        $rs['msg'] = 'zfy菜单更新成功';
      }
      else {
        $rs['flag'] = 'FAIL';
        $rs['msg'] = 'zfy菜单更新失败';
      }
    }
    else {
      $rs['msg'] = 'fxm菜单更新失败';
    }
    
    $response->sendJSON($rs);
  }

}