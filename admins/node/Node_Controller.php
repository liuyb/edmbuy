<?php
/**
 * Admin Node控制器
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Node_Controller extends Controller {

  private $_nav = 'sc';
  
  /**
   * hook menu
   *
   * @return array
   */
  public function menu() 
  {
    return [
      'node/card'      => 'index',
      'node/music'     => 'index',
      'node/gift'      => 'index',
      'node/add/%s'    => 'add',
      'node/%d'        => 'detail',
      'node/%d/edit'   => 'add',
      'node/%d/delete' => 'delete',
      'node/%d/orderEdit' => 'orderEdit',
      'node/%d/recommend' => 'recommend',
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

    //查询条件
    $_query_node = [];
    $_query_node['keyword'] = '';
    $_query_node['status'] = '';
    if(empty($_POST)&&isset($_SESSION['_query_node'])){
      $_query_node = $_SESSION['_query_node'];
    }else{
      $keyword = $request->post('keyword','');
      $status = $request->post('status', '');

      $_query_node['keyword'] = $keyword;
      $_query_node['status'] = $status;
      $_SESSION['_query_node'] = $_query_node;
    }


    // Set submenu
    $node_type = $request->arg(1);
    if (!in_array($node_type,['card','music','gift'])) {
      $node_type = '';
    }
    $this->v->assign('nav_second', $node_type);
    
    //BEGIN list order
    $orderinfo = $this->v->set_listorder('nid', 'desc');
    $extraurl  = '';
    $extraurl .= $orderinfo[2];
    $this->v->assign('extraurl', $extraurl);
    $this->v->assign('qparturl', "#/node".(''!=$node_type ? '/'.$node_type : ''));
    //END list order
    

    // Game List
    $limit = 20;
    $recordList = Node_Model::getNodeList(Node_Model::getTypeIdByName($node_type),$orderinfo[0],$orderinfo[1],$limit ,$_query_node);
    $recordNum  = count($recordList);
    $totalNum   = $GLOBALS['pager_totalrecord_arr'][0];
    
    $this->v->set_tplname('mod_node_'.($node_type ? $node_type : 'index'));
    $this->v->assign('recordList', $recordList)
            ->assign('recordNum', $recordNum)
            ->assign('totalNum', $totalNum)
            ->assign('_query_node', $_query_node);
            ;
    $response->send($this->v);
  }
  
  /**
   * action 'add'
   * @param Request $request
   * @param Response $response
   */
  public function add(Request $request, Response $response)
  {
    if ($request->is_post()) {
      
      $nid          = $request->post('nid', 0);
      $type_id      = $request->post('type_id');
      $cate_id      = $request->post('cate_id');
      $title        = $request->post('title','');
      $content      = $request->post('content','');
      $keyword      = $request->post('keyword');
      $tag          = $request->post('tag');
      $recommend    = $request->post('recommend',0);
      $sort         = $request->post('sort',0);
      $status       = $request->post('status','N');
      
      $ret = ['flag' => 'ERR', 'msg' => ''];
      
      if ('word'==$type_id && ''==$content) {
        $ret['msg'] = '内容不能为空';
        $response->sendJSON($ret);
      }
      
      $ninfo = [];
      if ($nid) {
        $ninfo = Node_Model::getNodeInfo($nid);
      }
      
      $now = simphp_time();
      $uid = $_SESSION['logined_uid'];
      $params = [
        'type_id'      => $type_id,
        'cate_id'      => $cate_id,
        'title'        => $title,
        'content'      => $content,
        'keyword'      => $keyword,
        'tag'          => $tag,
        'createdby'    => $uid,
        'created'      => $now,
        'changedby'    => $uid,
        'changed'      => $now,
        'recommend'    => $recommend,
        'sort'         => $sort,
        'status'       => $status
      ];
      
      if (empty($ninfo)) { // new insert
        $tag = explode(',',$params['tag']);
        $tages = [];
        foreach($tag as $val){
          $a = Node_Model::getTagByName($val);
          if(!empty($a)){
            $tages[$a['tag_id']] = $val;
          }
        }
        $params['tag'] = implode(',', $tages);

        $typeData = Node_Model::getTypeList();
        $typeList = [];
        foreach($typeData as $val){
          $typeList[$val['type_id']] = $val['type_name'];
        }

        $search = $typeList[$params['type_id']];
        $search .= ','.$params['keyword'].','.$params['tag'];

        $params['search'] = $search;
        $ntype = Node_Model::getTypeNameById($type_id);
        if($ntype=='word'){
          $params['title'] = $params['content'];
        }

        $ninfo['nid'] = D()->insert('node', $params);
        foreach($tages as $k=>$val){
          Node_Model::nodeRTag(['tag_id'=>$k,'nid'=>$ninfo['nid']]);
        }
        
        if ($ninfo['nid'] && in_array($type_id,['card','music','gift'])) {
          $params_ext = ['tnid' => $ninfo['nid']];
          switch ($ntype) {
            case 'card':
              $params_ext += [
                'cover_url' => $request->post('cover_url',''),
                'card_url'  => $request->post('card_url',''),
                'content_style' => $request->post('card_content_style',''),
              ];

              if($request->post('card_img_style','')){
                $params_ext += [
                  'has_img' => 1,
                  'img_url' => $request->post('card_img_url',''),
                  'img_style' => $request->post('card_img_style',''),
                ];
              }else{
                $params_ext += [
                  'has_img' => 0,
                  'img_url' => '',
                  'img_style' => '',
                ];
              }
              if($request->post('card_frame_style','')){
                $params_ext += [
                  'has_frame' => 1,
                  'frame_url' => $request->post('card_frame_url',''),
                  'frame_style' => $request->post('card_frame_style',''),
                ];
              }else{
                $params_ext += [
                  'has_frame' => 0,
                  'frame_url' => '',
                  'frame_style' => '',
                ];
              }
              if($request->post('card_to_style','')){
                $params_ext += [
                  'has_to' => 1,
                  'to_style' => $request->post('card_to_style',''),
                ];
              }else{
                $params_ext += [
                  'has_to' => 0,
                  'to_style' => '',
                ];
              }
              if($request->post('card_from_style','')){
                $params_ext += [
                  'has_from' => 1,
                  'from_style' => $request->post('card_from_style',''),
                ];
              }else{
                $params_ext += [
                  'has_from' => 0,
                  'from_style' => '',
                ];
              }

              break;
            case 'music':
              $params_ext += [
                'source_id' => $request->post('source_id',''),
                'singer_name'  => $request->post('singer_name',''),
                'singer_link'  => $request->post('singer_link',''),
                'singer_fans'  => $request->post('singer_fans',0),
                'icon_url'  => $request->post('icon_url',''),
                'bg_url'  => $request->post('bg_url',''),
                'music_url'  => $request->post('music_url',''),
                'music_url_hash' => md5($request->post('music_url',''))
              ];
              break;
            case 'gift':
              $params_ext += [
                'goods_url' => $request->post('goods_url',''),
                'source_id'  => $request->post('source_id',''),
                'goods_price'  => $request->post('goods_price',0),
                'promote_price'  => $request->post('promote_price',0),
                'brand' => $request->post('brand', ''),
                'count' => (int)$request->post('count', 0),
                'desc'  => $request->post('desc', ''),
                'standard' => $request->post('standard', '')
              ];
              $promote_start = $request->post('promote_start',0);
              $promote_end = $request->post('promote_end',0);  
              if(!empty($promote_start)){
                  $promote_start = strtotime($promote_start);
                  if($promote_start!==FALSE){
                    $params_ext['promote_start'] = $promote_start;     
                  }else{
                    $ret['msg'] = '促销开始时间格式不正确';
                    $response->sendJSON($ret);
                  }
              }
              if(!empty($promote_end)){
                  $promote_end = strtotime($promote_end);      
                  if($promote_end!==FALSE){
                    $params_ext['promote_end'] = $promote_end;
                  }else{
                    $ret['msg'] = '促销结束时间格式不正确';
                    $response->sendJSON($ret);
                  }
              }
              break;
          }
          D()->insert('node_'.$ntype, $params_ext);
        }
        
        $ret['flag'] = 'OK';
        $ret['msg'] = '添加成功！';
        $response->sendJSON($ret);
      }
      else { // edit
        if($ninfo['tag']!=$param['tag']){
          $tag = explode(',',$params['tag']);
          $tages = [];
          foreach($tag as $val){
            $a = Node_Model::getTagByName($val);
            if(!empty($a)){
              $tages[$a['tag_id']] = $val;
            }
          }
          $params['tag'] = implode(',', $tages);
          Node_Model::delNodeRTag($ninfo['nid']);

          foreach($tages as $k=>$val){
            Node_Model::nodeRTag(['tag_id'=>$k,'nid'=>$ninfo['nid']]);
          }
        }

        $typeData = Node_Model::getTypeList();
        $typeList = [];
        foreach($typeData as $val){
          $typeList[$val['type_id']] = $val['type_name'];
        }

        $search = $typeList[$params['type_id']];
        $search .= ','.$params['keyword'].','.$params['tag'];

        $params['search'] = $search;
        $ntype = Node_Model::getTypeNameById($type_id);
        if($ntype=='word'){
          $params['title'] = $params['content'];
        }

        unset($params['createdby'], $params['created']);
        D()->update('node', $params, ['nid'=>$nid]);
        
        if (D()->affected_rows() && in_array($type_id,['card','music','gift'])) {
          $params_ext = [];
          switch ($ntype) {
            case 'card':
              $params_ext += [
                'cover_url' => $request->post('cover_url',''),
                'card_url'  => $request->post('card_url',''),
                'content_style' => $request->post('card_content_style',''),
              ];
              if($request->post('card_img_style','')){
                $params_ext += [
                  'has_img' => 1,
                  'img_url' => $request->post('card_img_url',''),
                  'img_style' => $request->post('card_img_style',''),
                ];
              }else{
                $params_ext += [
                  'has_img' => 0,
                  'img_url' => '',
                  'img_style' => '',
                ];
              }
              if($request->post('card_frame_style','')){
                $params_ext += [
                  'has_frame' => 1,
                  'frame_url' => $request->post('card_frame_url',''),
                  'frame_style' => $request->post('card_frame_style',''),
                ];
              }else{
                $params_ext += [
                  'has_frame' => 0,
                  'frame_url' => '',
                  'frame_style' => '',
                ];
              }
              if($request->post('card_to_style','')){
                $params_ext += [
                  'has_to' => 1,
                  'to_style' => $request->post('card_to_style',''),
                ];
              }else{
                $params_ext += [
                  'has_to' => 0,
                  'to_style' => '',
                ];
              }
              if($request->post('card_from_style','')){
                $params_ext += [
                  'has_from' => 1,
                  'from_style' => $request->post('card_from_style',''),
                ];
              }else{
                $params_ext += [
                  'has_from' => 0,
                  'from_style' => '',
                ];
              }

              break;
            case 'music':
              $params_ext += [
                'source_id' => $request->post('source_id',''),
                'singer_name'  => $request->post('singer_name',''),
                'singer_link'  => $request->post('singer_link',''),
                'singer_fans'  => $request->post('singer_fans',0),
                'icon_url'  => $request->post('icon_url',''),
                'bg_url'  => $request->post('bg_url',''),
                'music_url'  => $request->post('music_url',''),
                'music_url_hash' => md5($request->post('music_url',''))
              ];
              break;
            case 'gift':
              $params_ext += [
                'goods_url' => $request->post('goods_url',''),
                'source_id'  => $request->post('source_id',''),
                'goods_price'  => $request->post('goods_price',0),
                'promote_price'  => $request->post('promote_price',0),
                'brand' => $request->post('brand', ''),
                'count' => (int)$request->post('count', 0),
                'desc'  => $request->post('desc', ''),
                'standard' => $request->post('standard', '')
              ];
              $promote_start = $request->post('promote_start',0);
              $promote_end = $request->post('promote_end',0);  
              if(!empty($promote_start)){
                  $promote_start = strtotime($promote_start);
                  if($promote_start!==FALSE){
                    $params_ext['promote_start'] = $promote_start;     
                  }else{
                    $ret['msg'] = '促销开始时间格式不正确';
                    $response->sendJSON($ret);
                  }
              }
              if(!empty($promote_end)){
                  $promote_end = strtotime($promote_end);      
                  if($promote_end!==FALSE){
                    $params_ext['promote_end'] = $promote_end;
                  }else{
                    $ret['msg'] = '促销结束时间格式不正确';
                    $response->sendJSON($ret);
                  }
              }
              break;
          }
          D()->update('node_'.$ntype, $params_ext, ['tnid'=>$nid]);
        }
        
        $ret['flag'] = 'OK';
        $ret['msg'] = '编辑成功！';
        $response->sendJSON($ret);
      }
    }
    else {
      
      // Node Info
      $nid = $request->arg(1);
      $nid = intval($nid);
      $is_edit = $nid ? TRUE : FALSE;
      $ninfo = $is_edit ? Node_Model::getNodeInfo($nid) : [];
      
      // Node Type
      $node_type = '';
      if (!$is_edit) {
        $node_type = $request->arg(2);
        if (!in_array($node_type, ['card','music','gift'])) {
          $node_type = '';
        }
      }
      else {
        $node_type = Node_Model::getTypeNameById($ninfo['type_id']);
      }
      $this->v->assign('nav_second', $node_type);

      // Type List
      $typeList = Node_Model::getTypeList();
      
      // Category List
      $cateList = Node_Model::getCategoryList();
      
      //individuation
      if($node_type=='gift'){
        $source_list = Node_Model::getSourceList($node_type);
        $this->v->assign('sourceList',$source_list);
      }elseif($node_type == 'music'){
        $source_list = Node_Model::getSourceList($node_type);
        $this->v->assign('sourceList',$source_list);
      }

      if($is_edit){
        $ninfo['content_style'] = json_decode($ninfo['content_style'], true);
        $ninfo['img_style'] = json_decode($ninfo['img_style'], true);
        $ninfo['frame_style'] = json_decode($ninfo['frame_style'], true);
        $ninfo['to_style'] = json_decode($ninfo['to_style'], true);
        $ninfo['from_style'] = json_decode($ninfo['from_style'], true);
      }

      //预定义tag
      $category = Node_Model::getCategoryList();
      $category2 = [];
      $categoryRTag = [];
      foreach($category as $c){
        $recordes = Node_Model::getCateRTags($c['cate_id']);
        $categoryRTag[$c['cate_id']] = $recordes;
        $category2[$c['cate_id']] = $c['cate_name'];
      }
      $category = json_encode($category2);
      $categoryRTag = json_encode($categoryRTag);


      $this->v->set_tplname('mod_node_add');
      $this->v->assign('ninfo', $ninfo)
              ->assign('typeList', $typeList)
              ->assign('cateList', $cateList)
              ->assign('is_edit', $is_edit)
              ->assign('category', $category)
              ->assign('categoryRTag', $categoryRTag);
      $response->send($this->v);
    }
  }
  
  /**
   * action 'delete'
   * @param Request $request
   * @param Response $response
   */
  public function delete(Request $request, Response $response)
  {
    if ($request->is_post()) {
      $ids = $request->post('rids');
      
      $ret = Node_Model::deleteNodes($ids);
      $response->sendJSON(['flag'=>'OK', 'rids'=>$ret]);
    }
  }
  
  /**
   * action 'recommend'
   * @param Request $request
   * @param Response $response
   */
  public function recommend(Request $request, Response $response)
  {
    if ($request->is_post()) {
      $ids = $request->post('rids');
      $act = $request->post('act',0);
      
      $ret = Node_Model::recommendNodes($ids, $act);
      $response->sendJSON(['flag'=>'OK', 'rids'=>$ret]);
    }
  }
  
  /**
   * action 'suspend'
   * @param Request $request
   * @param Response $response
   */
  public function suspend(Request $request, Response $response)
  {
    if ($request->is_post()) {
      $ids = $request->post('rids');
      $act = $request->post('act',0);
      
      $ret = Node_Model::suspendNodes($ids, $act);
      $response->sendJSON(['flag'=>'OK', 'rids'=>$ret]);
    }
  }

  public function order (Request $request, Response $response){

    // Set submenu
    $this->v->assign('nav_second', 'order');
    
    //BEGIN list order
    $orderinfo = $this->v->set_listorder('oid', 'desc');
    $extraurl  = '';
    $extraurl .= $orderinfo[2];
    $this->v->assign('extraurl', $extraurl);
    $this->v->assign('qparturl', "#/node/order");
    //END list order
      
    //条件查询
    //$_SESSION['_query_order'] = [];
    $_query_order = [
      'order_no'  => '',
      'nickname'  => '',
      'state'     => ''
    ];
    if(empty($_POST)&&isset($_SESSION['_query_order'])){
        $_query_order = $_SESSION['_query_order'];
    }else{
      if(isset($_POST['order_no'])){
        $_query_order['order_no'] = $_POST['order_no'];
      } 
      if(isset($_POST['nickname'])){
        $_query_order['nickname'] = $_POST['nickname'];
      }
      if(isset($_POST['state'])){
        $_query_order['state'] = $_POST['state'];
      }

      $_SESSION['_query_order'] = $_query_order;
    }

    $where = ''; 
    if($_query_order['order_no']!==''){
      $where['order_no'] = $_POST['order_no'];
    } 
    if($_query_order['nickname']!==''){
      $where['nickname'] = $_POST['nickname'];
    }
    if($_query_order['state']!==''){
      $where['state'] = $_POST['state'];
    }

    // order List
    $limit = 20;
    $recordList = Node_Model::getOrderList($where, $orderinfo[0],$orderinfo[1],$limit);
    foreach($recordList AS &$r){
      $r['state_str'] = getOrderState($r['state']);
      if(is_null($r['send_state'])){
        $r['send_state_str'] = '';
      }else{
        $r['send_state_str'] = getSendState($r['send_state']);
      }
    }
    $recordNum  = count($recordList);
    $totalNum   = $GLOBALS['pager_totalrecord_arr'][0];
    $orderState = getOrderState();

    $this->v->set_tplname('mod_node_order');
    $this->v->assign('recordList', $recordList)
            ->assign('recordNum', $recordNum)
            ->assign('totalNum', $totalNum)
            ->assign('orderState', $orderState)
            ->assign('_query_order', $_query_order);
    $response->send($this->v);
  }
  /**
   * 更新订单
   * @param  Request  $request  [description]
   * @param  Response $response [description]
   * @return [type]             [description]
   */
  public function orderEdit(Request $request, Response $response){
    // Set submenu
    $this->v->assign('nav_second', 'order');

    $oid = $request->arg(1);

    $order =  Node_Model::getOrderById($oid);
    $orderSend = Node_Model::getSendState($order['order_no']);

    $order_status = getOrderState();
    $sendState = getSendState();
    $sendType = get_send_type();

    $this->v->assign('oid', $oid)->assign('order',$order);
    $this->v->assign('status', $order_status)->assign('sendState', $sendState);
    $this->v->assign('orderSend', $orderSend)->assign('sendType', $sendType);
    $this->v->set_tplname('mod_node_orderEdit');
    $response->send($this->v);
  }
  /**
   * 更新订单的状态
   * @param  Request  $request  [description]
   * @param  Response $response [description]
   * @return [type]             [description]
   */
  public function updateOrder(Request $request, Response $response){
    $rs = ['flag'=>'FAIL','msg'=>'请稍后再试'];
    $order_no = $request->post('order_no','');
    $sendState = $request->post('sendState','');
    $sendType = $request->post('sendType','');
    $send_no = $request->post('send_no','');

    //查询订单状态
    $order = Node_Model::getOrderByOrderno($order_no);
    if($order['state']>0){//已支付，可更新发货信息
      //查询发货信息
      $orderSend = Node_Model::getSendState($order_no);
      $data = [
        'order_no'=>$order_no,
        'send_type'=>$sendType,
        'send_no'=> $send_no,
        'send_time'=> time(),
        'send_state'=> $sendState
      ];
      if($orderSend){
        unset($data['send_no']);
        if(Node_Model::updateOrderSend($order_no, $data)){
          $rs['flag'] = 'SUC';
          $rs['msg'] = '更新订单成功';
        }
      }else{
        if(Node_Model::addOrderSend($data)){
          $rs['flag'] = 'SUC';
          $rs['msg'] = '更新订单成功';
        }
      }
    }
    $response->sendJSON($rs);
  }
  /**
   * 类别管理
   * @param  Request  $request  [description]
   * @param  Response $response [description]
   * @return [type]             [description]
   */
  public function cate(Request $request, Response $response){
    // Set submenu
    $this->v->assign('nav_second', 'cate');    


    $category = Node_Model::getCategoryList();
    $categoryRTag = [];
    foreach($category as $c){
      $recordes = Node_Model::getCateRTags($c['cate_id']);
      $categoryRTag[$c['cate_id']] = $recordes;
    }


    $this->v->assign('category', $category)->assign('categoryRTag', $categoryRTag);
    $this->v->set_tplname('mod_node_cate');
    $response->send($this->v);
  }

  public function addTag(Request $request, Response $response){
    $rs = ['flag'=>'FAIL','msg'=>'请稍后再试'];
    $cate_id = $request->post('cate_id', 0);
    $tag_name = $request->post('tag_name', '');

    $tags = Node_Model::getTagByName($tag_name);
    if(empty($tags)){
      $tag_id = Node_Model::addTag(['tag_name'=>$tag_name]);
      if(!$tag_id){
        $response->sendJSON($rs);
      }
    }else{
      $tag_id = $tags['tag_id'];
    }

    $affected_rows = Node_Model::cateRTag(['cate_id'=>$cate_id, 'tag_id'=>$tag_id]);
    if($affected_rows>0){
      $rs['flag'] = 'SUC';
      $rs['msg'] = '添加成功！';
    }
    $response->sendJSON($rs);
  }

  public function delTag(Request $request, Response $response){
    $rs = ['flag'=>'FAIL','msg'=>'请稍后再试'];
    $cate_id = $request->post('cate_id', 0);
    $tag_id = $request->post('tag_id', 0);

    if(Node_Model::delTag(['cate_id'=>$cate_id,'tag_id'=>$tag_id])>0){
      $rs['flag']='SUC';
      $rs['msg']='删除成功！';
    }

    $response->sendJSON($rs);
  }
  
  public function updateTag(Request $request, Response $response){
    $rs = ['flag'=>'FAIL','msg'=>'请稍后再试'];
    $rank = intval($request->post('rank', 0));
    $tag_id = intval($request->post('tag_id', 0));
    $cate_id = intval($request->post('cate_id', 0));

    if(Node_Model::updateTagCate(['rank'=>$rank,'tag_id'=>$tag_id,'cate_id'=>$cate_id])>0){
      $rs['flag']='SUC';
      $rs['msg']='更新成功！';
    }

    $response->sendJSON($rs);
  }


  /**
   * action 'import'
   * @param Request $request
   * @param Response $response
   */
  public function import(Request $request, Response $response)
  {
    if ($request->is_post()) {
      $source_id  = $request->post('source_id');
      $source_url = $request->post('source_url');
      
      $ret = ['flag' => 'ERR', 'msg' => ''];
      
      if (!$source_id || !in_array($source_id,Node_Model::getSourceList('music',true))) {
        $ret['msg'] = '请选择有效的来源';
        $response->sendJSON($ret);
      }
      
      if (!$source_url || !preg_match('!^http://.{4,}!i', $source_url)) {
        $ret['msg'] = '请输入有效的URL地址';
        $response->sendJSON($ret);
      }

      Node_Model::importMusic($source_id,$source_url);
      
      $ret = ['flag' => 'OK', 'msg' => '导入成功！'];
      $response->sendJSON($ret);
    }
    else {
  
      // Node Info
      $import_ntype = $request->arg(2);

      $this->v->assign('nav_second', $import_ntype);
  
      // Music Source List
      $sourceList = Node_Model::getSourceList('music');
  
      $this->v->set_tplname('mod_node_import');
      $this->v->assign('sourceList', $sourceList);
      $response->send($this->v);
    }
  }
  public function updateSearch(Request $request, Response $response){
      $nodes = D()->query('SELECT * FROM {node} ')->fetch_array_all();

      $typeData = Node_Model::getTypeList();
      $typeList = [];
      foreach($typeData as $val){
        $typeList[$val['type_id']] = $val['type_name'];
      }

      foreach($nodes as $params){
        $search = $typeList[$params['type_id']];
        $search .= ','.$params['keyword'].','.$params['tag'];

        D()->update('node',['search'=>$search], ['nid'=>$params['nid']]);
      }

      header('Content-type:text/html;charset=utf-8');
      exit('更新搜索字段完成');
  }
  
}
 
/*----- END FILE: Node_Controller.php -----*/
