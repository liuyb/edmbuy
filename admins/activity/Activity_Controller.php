<?php
/**
 * Material控制器
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Activity_Controller extends Controller {

  private $_nav = 'hd';
  
  /**
   * hook menu
   *
   * @return array
   */
  public function menu() 
  {
    return [
      'activity/add/%s'    => 'add',
      'activity/%d'        => 'detail',
      'activity/%d/edit'   => 'add',
      'activity/%d/delete' => 'delete',
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
    $orderinfo = $this->v->set_listorder('aid', 'desc');
    $extraurl  = '';
    $extraurl .= $orderinfo[2];
    $this->v->assign('extraurl', $extraurl);
    $this->v->assign('qparturl', "#/activity".(''!=$node_type ? '/'.$node_type : ''));
    //END list order
    
    // Game List
    $limit = 20;
    $recordList = Activity_Model::getList($orderinfo[0],$orderinfo[1],$limit);
    $recordNum  = count($recordList);
    $totalNum   = $GLOBALS['pager_totalrecord_arr'][0];
    
    $this->v->set_tplname('mod_activity_'.($node_type ? $node_type : 'index'));
    $this->v->assign('recordList', $recordList)
            ->assign('recordNum', $recordNum)
            ->assign('totalNum', $totalNum)
            ;
    $response->send($this->v);
  }

  public function add(Request $request, Response $response)
  {
    if ($request->is_post()) {
      
      $aid          = $request->post('aid', 0);
      $title        = $request->post('title','');
      $content      = $request->post('content','');
      $img          = $request->post('img', '');
      $start_time   = $request->post('start_time','');
      $end_time     = $request->post('end_time','');
      $link         = $request->post('link', '');
      $tpl          = $request->post('tpl',0);

      $ret = ['flag' => 'ERR', 'msg' => ''];
      
      if (''==$title) {
        $ret['msg'] = '活动标题不能为空';
        $response->sendJSON($ret);
      }
      
      if (''==$img) {
        $ret['msg'] = '活动封面不能为空';
        $response->sendJSON($ret);
      }
      
      /*else if (!preg_match('!^http://.{4,}!', $img)) {
        $ret['msg'] = '活动封面地址无效';
        $response->sendJSON($ret);
      }*/

      if (''==$content) {
        $ret['msg'] = '活动详情不能为空';
        $response->sendJSON($ret);
      }
      
      if(!empty($start_time) && strlen($start_time)!=19){
        $ret['msg'] = '活动开始时间格式不正确';
        $response->sendJSON($ret);
      }
      if(!empty($end_time) && strlen($end_time)!=19){
        $ret['msg'] = '活动结束时间格式不正确';
        $response->sendJSON($ret);
      }
      
      $aid = intval($aid);
      $start_time = strtotime($start_time);
      $end_time   = strtotime($end_time);
      $start_time = intval($start_time);
      $end_time   = intval($end_time);
      
      $info = [];
      if ($aid) {
        $info = Activity_Model::getInfo($aid);
      }
      
      $now = simphp_time();
      $uid = $_SESSION['logined_uid'];
      $params = [
        'title'        => $title,
        'tpl'        => $tpl,
        'content'      => $content,
        'img'          => $img,
        'start_time'   => $start_time,
        'end_time'     => $end_time,
        'link'         => $link,
        'createdby'    => $uid,
        'created'      => $now,
        'changedby'    => $uid,
        'changed'      => $now,
        'status'       => 'R',
      ];
      
      if (empty($info)) { // new insert
        
        $ninfo['aid'] = D()->insert('activity', $params);
        
        $ret['flag'] = 'OK';
        $ret['msg'] = '添加成功！';
        $response->sendJSON($ret);
      }
      else { // edit
        
        unset($params['createdby'], $params['created'], $params['status']);
        D()->update('activity', $params, ['aid'=>$aid]);
        
        $ret['flag'] = 'OK';
        $ret['msg'] = '编辑成功！';
        $response->sendJSON($ret);
      }
    }
    else {
      
      //
      $aid = $request->arg(1);
      $aid = intval($aid);
      $is_edit = $aid ? TRUE : FALSE;
      $ainfo = $is_edit ? Activity_Model::getInfo($aid) : [];
      
      // Node Type
      $node_type = '';
      $this->v->assign('nav_second', $node_type);

      $this->v->set_tplname('mod_activity_add');
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
      
      $ret = Activity_Model::delete($ids);
      $response->sendJSON(['flag'=>'OK', 'rids'=>$ret]);
    }
  }

  public function related(Request $request, Response $response){
    $aid = $request->arg(2);
    if(empty($aid)){
      exit('缺少参数');
    }

    import('Node/Node_Model');
    $typelist = Node_Model::getTypeList();

    $types = [];
    foreach($typelist as $v){
      $types[$v['type_id']]=$v['type_name'];
    }


    $type_id = '';//类别筛选
    $viewRelated = '';//只显示已关联  
    if(!empty($_POST)){
      $type_id = $request->post('type_id', '');//类别筛选
      $viewRelated = $request->post('viewRelated', '');//只显示已关联  

      $_query['type_id'] = $type_id;
      $_query['viewRelated'] = $viewRelated;
      $_SESSION['_query']['activity_related'] = $_query;
    }elseif(isset($_SESSION['_query']['activity_related'])){
      $_query = $_SESSION['_query']['activity_related'];

      $type_id = $_query['type_id'];//类别筛选
      $viewRelated = $_query['viewRelated'];//只显示已关联
    }


    $page_size = 20;
    $where = [];
    $where['aid'] = $aid;
    if($type_id!=''){
      $where['type_id'] = $type_id;
    }
    if($viewRelated!=''){
      $where['viewRelated'] = $viewRelated;
    }

    $list = Activity_Model::getRelatedList($page_size, $where);
    $totalNum = $GLOBALS['pager_totalrecord_arr'][0];
    $recordNum = $GLOBALS['pager_totalpage_arr'][0];
    $qparturl = '#/activity/related/'.$aid;
    $extraurl = '';


    $this->v->assign('types', $types);
    $this->v->assign('typelist', $typelist)->assign('type_id', $type_id)->assign('viewRelated', $viewRelated);
    $this->v->assign('list', $list)->assign('aid', $aid);
    $this->v->assign('totalNum',$totalNum)->assign('recordNum', $recordNum);
    $this->v->assign('qparturl', $qparturl)->assign('extraurl', $extraurl);
    $this->v->set_tplname('mod_activity_related');
    $response->send($this->v);
  }

  /**
   * 更新记录
   * @param  Request  $request  [description]
   * @param  Response $response [description]
   * @return [type]             [description]
   */
  public function relatedU(Request $request, Response $response){
    if ($request->is_post()) {
      
      $aid = $request->arg(2); 
      $ids = $request->post('rids', '');
      $act = $request->post('act',0);
      
      $ret = Activity_Model::relatedUpdate($aid,$ids,$act);
      $response->sendJSON(['flag'=>'OK', 'rids'=>$ret]);
    }
  }
  /**
   * 更新关联数据的排序值
   * @param  Request  $request  [description]
   * @param  Response $response [description]
   * @return [type]             [description]
   */
  public function relatedRank(Request $request, Response $response){
    if ($request->is_post()) {
      $res = ['flag'=>'FAIL', 'msg'=>''];
      $aid = $request->post('aid','');
      $nid = $request->post('nid','');
      $new_rank = $request->post('new_rank',0);

      if(Activity_Model::updateRelatedRank($aid,$nid,$new_rank)){
        $res['flag'] = 'SUC';
        $res['msg'] = '更新成功';
      }else{ 
        $res['msg'] = '系统繁忙，请稍后再试';
      }
      $response->sendJSON($res);
    }
  }
}
