<?php
/**
 * Material控制器
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class News_Controller extends Controller {

  private $_nav = 'xw';
  
  /**
   * hook menu
   *
   * @return array
   */
  public function menu() 
  {
    return [
      'news/add/%s'    => 'add',
      'news/%d'        => 'detail',
      'news/%d/edit'   => 'add',
      'news/%d/delete' => 'delete',
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
    // Set submenu
    $node_type = $request->arg(1);
    $node_type = '';
    $this->v->assign('nav_second', $node_type);
    
    //BEGIN list order
    $orderinfo = $this->v->set_listorder('nid', 'sort');
    $extraurl  = '';
    $extraurl .= $orderinfo[2];
    $this->v->assign('extraurl', $extraurl);
    $this->v->assign('qparturl', "#/news".(''!=$node_type ? '/'.$node_type : ''));
    //END list order
    
    // Game List
    $limit = 20;
    $recordList = News_Model::getList($orderinfo[0],$orderinfo[1],$limit);
    $recordNum  = count($recordList);
    $totalNum   = $GLOBALS['pager_totalrecord_arr'][0];
    
    $this->v->set_tplname('mod_news_'.($node_type ? $node_type : 'index'));
    $this->v->assign('recordList', $recordList)
            ->assign('recordNum', $recordNum)
            ->assign('totalNum', $totalNum)
            ;
    $response->send($this->v);
  }

  public function add(Request $request, Response $response)
  {
    if ($request->is_post()) {
      
      $nid          = $request->post('nid', 0);
      $title        = $request->post('title','');
      $content      = $request->post('content','');
      $img      = $request->post('img', '');
      $recommend = $request->post('recommend',0);
      $sort = $request->post('sort', 0);

      $ret = ['flag' => 'ERR', 'msg' => ''];
      
      if (''==$title) {
        $ret['msg'] = '新闻标题';
        $response->sendJSON($ret);
      }

      if (''==$content) {
        $ret['msg'] = '新闻详情不能为空';
        $response->sendJSON($ret);
      }

      if(''==$img){
        $ret['msg'] = '封面不能空';
        $response->sendJSON($ret); 
      }
    
      
      $info = [];
      if ($nid) {
        $info = News_Model::getInfo($nid);
      }
      
      $now = simphp_time();
      $uid = $_SESSION['logined_uid'];
      $params = [
        'title'        => $title,
        'content'      => $content,
        'img'      => $img,
        'recommend' => $recommend,
        'sort'  => $sort,
        'createdby'    => $uid,
        'created'      => $now,
        'changedby'    => $uid,
        'changed'      => $now,
        'status'       => 'R',
      ];
      
      if (empty($info)) { // new insert
        
        $ninfo['nid'] = D()->insert('news', $params);
        
        $ret['flag'] = 'OK';
        $ret['msg'] = '添加成功！';
        $response->sendJSON($ret);
      }
      else { // edit
        
        unset($params['createdby'], $params['created'], $params['status']);
        D()->update('news', $params, ['nid'=>$nid]);
        
        $ret['flag'] = 'OK';
        $ret['msg'] = '编辑成功！';
        $response->sendJSON($ret);
      }
    }
    else {
      
      //
      $nid = $request->arg(1);
      $nid = intval($nid);
      $is_edit = $nid ? TRUE : FALSE;
      $ainfo = $is_edit ? News_Model::getInfo($nid) : [];
      
      // Node Type
      $node_type = '';
      $this->v->assign('nav_second', $node_type);

      $this->v->set_tplname('mod_news_add');
      $this->v->assign('ninfo', $ainfo)
              ->assign('is_edit', $is_edit)
              ;
      $response->send($this->v);
    }
  }

  public function detail(Request $request, Response $response)
  {

  }

  public function delete(Request $request, Response $response)
  {
    if ($request->is_post()) {
      $ids = $request->post('rids');
      
      $ret = News_Model::delete($ids);
      $response->sendJSON(['flag'=>'OK', 'rids'=>$ret]);
    }
  }
}
