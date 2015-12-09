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
      'other/ad/%d/edit' => 'ad_edit',
      'other/ad/add'     => 'ad_edit',
      'other/ad/list/%d/edit/%d' => 'ad_list_edit',
      'other/ad/list/%d/edit' => 'ad_list_edit',
      'other/ad/list/%d' => 'ad_list',
      'other/ad/list/del' => 'ad_del',
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

    if((new Weixin('fxm'))->createMenu($menuConfig)){
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

  /**
   * 广告位管理
   * @param  Request  $request  [description]
   * @param  Response $response [description]
   * @return [type]             [description]
   */
  public function ad(Request $request, Response $response)
  {
    $menu_name = 'ad';

    //BEGIN list order
    $orderinfo = $this->v->set_listorder('ad_id', 'desc');
    $extraurl  = '';
    $extraurl .= $orderinfo[2];
    $this->v->assign('extraurl', $extraurl);
    $this->v->assign('qparturl', "#/other/ad");
    //END list order

    $page_size = 30;
    $recordes = Other_Model::getAdList($orderinfo[0], $orderinfo[1], $page_size);

    $recordNum  = count($recordes);
    $totalNum   = $GLOBALS['pager_totalrecord_arr'][0];

    $this->v->assign('recordNum', $recordNum)->assign('totalNum', $totalNum);
    $this->v->assign('recordList', $recordes);
    $this->v->assign('nav_second', $menu_name);
    $this->v->set_tplname('mod_other_ad');
    $response->send($this->v);
  }

  /**
   * 编辑广告位
   * @param  Request  $request  [description]
   * @param  Response $response [description]
   * @return [type]             [description]
   */
  public function ad_edit(Request $request, Response $response){

    $menu_name = 'ad';
    if ($request->is_post()) {
      $res = ['flag'=>'FAIL', 'msg'=>''];

      $ad_id = $request->post('ad_id', 0);
      $ad_name = $request->post('ad_name', '');
      $ad_desc = $request->post('ad_desc', '');
      $max_num = $request->post('max_num', 0);
      $pic_size = $request->post('pic_size', '');


      if($ad_name==''){
        $res['msg'] = '请输入广告位名称';
        $response->sendJSON($res);
      }
      $max_num = intval($max_num);

      $data = [
        'ad_name'  => $ad_name,
        'ad_desc'  => $ad_desc,
        'max_num'  => $max_num,
        'pic_size' => $pic_size 
      ];
      if($ad_id==0){//添加
        $cur_time = time();
        $data['created'] = $cur_time;

        $ad_id = Other_Model::addAd($data);
        if($ad_id>0){
          $res['flag'] = 'SUC';
          $res['msg'] = '添加成功';
        }else{
          $res['msg'] = '添加失败';
        }

        $response->sendJSON($res);
      }else{//编辑

        $affected = Other_Model::editAd($data, $ad_id);
        if($affected>0){
          $res['flag'] = 'SUC';
          $res['msg'] = '更新成功';
        }else{
          $res['msg'] = '更新失败';
        }
        $response->sendJSON($res);
      }

    }else{
      $ad_id = $request->arg(2);
      $ad_id = intval($ad_id);
      $is_edit = $ad_id ? TRUE : FALSE;
      $ad = $is_edit ? Other_Model::getAdInfo($ad_id) : [];

      $this->v->assign('ad', $ad);
    }

    $this->v->assign('nav_second', $menu_name);
    $this->v->set_tplname('mod_other_ad_add');
    $response->send($this->v);
  }

  /**
   * 广告位图片列表
   * @param  Request  $request  [description]
   * @param  Response $response [description]
   * @return [type]             [description]
   */
  public function ad_list(Request $request, Response $response){
    $menu_name = 'ad';
    $ad_id = $request->arg(3);
    $ad_id = intval($ad_id);

    //BEGIN list order
    $orderinfo = $this->v->set_listorder('sort', 'desc');
    $extraurl  = '';
    $extraurl .= $orderinfo[2];
    $this->v->assign('extraurl', $extraurl);
    $this->v->assign('qparturl', "#/other/ad/list/".$ad_id);
    //END list order

    $page_size = 30;
    $recordes = Other_Model::getAdPicList($ad_id, $orderinfo[0], $orderinfo[1], $page_size);

    $recordNum  = count($recordes);
    $totalNum   = $GLOBALS['pager_totalrecord_arr'][0];

    $this->v->assign('ad_id', $ad_id);
    $this->v->assign('recordNum', $recordNum)->assign('totalNum', $totalNum);
    $this->v->assign('recordList', $recordes);
    $this->v->assign('nav_second', $menu_name);
    $this->v->set_tplname('mod_other_ad_list');
    $response->send($this->v);
  }

  public function ad_list_edit(Request $request, Response $response){
    $menu_name = 'ad';
    if ($request->is_post()) {
      $res = ['flag'=>'FAIL', 'msg'=>''];

      $ad_id = $request->post('ad_id', 0);
      $pic_id = $request->post('pic_id', 0);
      $title = $request->post('title', '');
      $link = $request->post('link', '');
      $pic_path = $request->post('pic_path', 0);
      $sort = $request->post('sort', 0);

      $ad_id = intval($ad_id);
      $pic_id = intval($pic_id);
      $sort = intval($sort);

      $data = [
        'title'    => $title,
        'link'     => $link,
        'pic_path' => $pic_path,
        'ad_id'    => $ad_id,
        'sort'     => $sort
      ];
      if($pic_id==0){//添加
        $cur_time = time();
        $data['created'] = $cur_time;

        $pic_id = Other_Model::addAdPic($data);
        if($ad_id>0){
          $res['flag'] = 'SUC';
          $res['msg'] = '添加成功';
        }else{
          $res['msg'] = '添加失败';
        }

        $response->sendJSON($res);
      }else{//编辑

        $affected = Other_Model::editAdPic($data, $pic_id);
        if($affected>0){
          $res['flag'] = 'SUC';
          $res['msg'] = '更新成功';
        }else{
          $res['msg'] = '更新失败';
        }
        $response->sendJSON($res);
      }

    }else{
      $ad_id = $request->arg(3);
      $ad_id = intval($ad_id);
      $pic_id = $request->arg(5);
      $pic_id = intval($pic_id);
      $is_edit = $pic_id ? TRUE : FALSE;
      $pic = $is_edit ? Other_Model::getAdPicInfo($pic_id) : [];

      $this->v->assign('pic', $pic);
    }

    $this->v->assign('ad_id', $ad_id);
    $this->v->assign('nav_second', $menu_name);
    $this->v->set_tplname('mod_other_ad_pic_add');
    $response->send($this->v);
  }

  public function ad_del(Request $request, Response $response){
    if ($request->is_post()) {
      $ids = $request->post('rids');
      
      $ret = Other_Model::deleteAdList($ids);
      $response->sendJSON(['flag'=>'OK', 'rids'=>$ret]);
    }
  }

}