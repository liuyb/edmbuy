<?php
/**
 * Activity Controller
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Activity_Controller extends Controller {
  
  private $_nav_no     = 1;
  private $_nav        = 'activity';
  private $_nav_second = '';
  
  public function menu() {
    return [
      
    ];
  }
  
  /**
   * hook init
   *
   * @param string $action
   * @param Request $request
   * @param Response $response
   */
  public function init($action, Request $request, Response $response)
  {
    $this->v = new PageView();
    $this->v->assign('nav_no',     $this->_nav_no)
            ->assign('nav',        $this->_nav)
            ->assign('nav_second', $this->_nav_second);
  }
  
  /**
   * default action 'index'
   *
   * @param Request $request
   * @param Response $response
   */
  public function index(Request $request, Response $response)
  {
    $this->v->set_tplname('mod_activity_index');
    if ($request->is_hashreq()) {
      $page_size = 5;
      $aList = Activity_Model::getList('created', 'DESC', $page_size);

      foreach($aList as &$val){
        $jNum = Activity_Model::getJoinNumByAid($val['aid'], 'join');

        $val['jNum'] = intval($jNum);
      }

      $next_page = $GLOBALS['pager_currpage_arr'][0]+1;
      $total_page = $GLOBALS['pager_totalpage_arr'][0];

      $show_blk = ['blk_1'];//显示区域
      if(isset($_GET['p'])){
        $show_blk = [];
      }
      $this->v->assign('show_blk',$show_blk);
      $this->v->assign('total_page',$total_page)->assign('next_page', $next_page);

      $this->v->assign('list', $aList);
    }
    $response->send($this->v);
  }

  public function detail(Request $request, Response $response)
  {
    

    if ($request->is_hashreq()) {
      $aid = $request->arg(2);
      $info = Activity_Model::getActivityByAid($aid);

      //模板
      if($info['tpl']==0){
        $this->v->set_tplname('mod_activity_detail');
        $this->v->assign('nav_no', 4);
      }elseif($info['tpl']==1){
        $this->v->set_tplname('mod_activity_1');
        $this->v->assign('nav_no', 0);
      }

      $is_voted = 0;
      $user = Member::getUser();
      if(Member::isLogined()){
        $is_voted = Activity_Model::isJoin($aid, $user['uid'], 'vote');
      }
      $this->v->assign('info', $info);
      $this->v->assign('is_voted', $is_voted);

      //关联信息
      $relation = '';
      $type_id = 'music';
      $music = Activity_Model::getRelated($aid,$type_id);

      import('Node/Node_Model');
      foreach($music as &$val){
        $val['love'] = Node_Model::actionRecord($val['nid'], $user['uid'], 'love');
      }
      
      $cur_dir = dirname(__FILE__);
      $music_tpl = $cur_dir.'/tpl/mod_activity_detail_music.tpl.htm';
      if($music){
        ob_start();
        include($music_tpl);
        $relation = ob_get_contents();
        ob_end_clean();  
      }

      $this->v->assign('relation', $relation);
    }
    $response->send($this->v);
  }

  public function join(Request $request, Response $response){
    $res = ['flag'=>'FAIL', 'msg'=>''];
    if(Member::isLogined()){
      $user = Member::getUser();
      $uid = $user['uid'];
    }else{
      $res['msg'] = '请先登录';
      $response->sendJSON($res);
    }

    $aid = $request->post('aid', 0);
    $act = $request->post('act', '');
    $info = Activity_Model::getActivityByAid($aid);
    if(empty($info)){
      $res['msg'] = '该活动不存在';
      $response->sendJson($res);
    }
    if(!in_array($act, ['join', 'vote'])){
      $res['msg'] = '未知操作';
      $response->sendJson($res); 
    }

    $inc = 1;
    if($act=='join'){
      $cur_time = time();
      if($info['start_time']>$cur_time){
        $res['msg'] = '活动还没有开始'; 
        $response->sendJson($res);
      }
      if($info['end_time']<$cur_time){
        $res['msg'] = '活动已结束'; 
        $response->sendJson($res);
      }
      if(Activity_Model::isJoin($aid, $uid, 'join')){
        $inc = -1;
        $res['msg'] = '您已经参与过本次活动了';
        $response->sendJson($res);
      }
    }elseif($act=='vote'){
      if(Activity_Model::isJoin($aid, $uid, 'vote')){
        $inc = -1;/*
        $res['msg'] = '已赞'; 
        $response->sendJson($res);*/
      }
    }

    $jid = Activity_Model::joinActivity(['aid'=>$aid, 'uid'=>$uid, 'act'=>$act ,'timeline'=>time()], $inc);
    if($jid>0){
      $res['flag'] = 'SUC';
      if($act=='join'){
        $res['msg'] = '参与成功';  
      }elseif($act=='vote'){
        if ($inc > 0) $res['msg'] = '已赞';
        else $res['msg'] = '已取消赞';
      }
    }else{
      $res['msg'] = '系统繁忙，请稍后再试';  
    }
    $response->sendJson($res);

  }


  /**
   * [subject description]
   * @param  Request  $request  [description]
   * @param  Response $response [description]
   * @return [type]             [description]
   */
  public function subject(Request $request, Response $response){
    $context = '/mobiles/activity';

    $subid = $request->arg(2);
    $subid = intval($subid);

    $this->v->set_tplname('mod_activity_subject_'.$subid);

    $this->v->assign('nav_no', 0);
    $this->v->assign('context',$context);
    $response->send($this->v);
  }

}