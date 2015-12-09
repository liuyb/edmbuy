<?php
/**
 * Member控制器
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Member_Controller extends Controller {

  private $_nav = 'yh';
  private $_nav_second = '';//二级导航

  public function menu(){
  	return [
  		'member/%d/edit'=>'member_edit',
  		'member/save' => 'member_save',
  		'member/loginlog' => 'member_loginlog',
  		
  	];
  }
  /**
   * default action 'index'
   * @param Request $request
   * @param Response $response
   */
  public function index(Request $request, Response $response)
  {
  	$search=array();
  	
  	$search['time_type']=0;
  	$search['stime']='';
  	$search['etime']='';
  	$search['username']='';
    $search['nickname']='';
  	$search['uid']='';
  	$search['has_coin']=0;
  	$search['disabled_user']=0;
  	$search['sort']='uid_desc';//排序字段,example:coin_asc,coin_desc
  	
  	//不更新查询条件
  	if(empty($_POST)){
  		if(isset($_SESSION['query']['member_list'])){
  			$search = $_SESSION['query']['member_list'];
  		}else{
  			$_SESSION['query']['member_list'] = $search;
  		}
  	}else{
  		//更新查询条件
  		$search['time_type']=empty($_POST['time_type'])?0:intval($_POST['time_type']);
  		$search['stime']=empty($_POST['stime'])?'':addslashes(trim($_POST['stime']));
  		$search['etime']=empty($_POST['etime'])?'':addslashes(trim($_POST['etime']));
  		$search['username']=empty($_POST['username'])?'':addslashes(trim($_POST['username']));
      $search['nickname']=empty($_POST['nickname'])?'':addslashes(trim($_POST['nickname']));
  		$search['uid']=empty($_POST['uid'])?'':addslashes(trim($_POST['uid']));
  		$search['has_coin'] = isset($_POST['has_coin']) ?  intval($_POST['has_coin']):0;
  		$search['disabled_user'] = isset($_POST['disabled_user']) ?  intval($_POST['disabled_user']):0;
  		$search['sort']=empty($_POST['sort'])? 'uid_desc':addslashes(trim($_POST['sort']));
  	
  		$_SESSION['query']['member_list'] = $search;
  	}
  	
  	$where = "";
  	if($search['username']!=''){
  		$where .=" and username='".$search['username']."'";
  	}
    if($search['nickname']!=''){
      $where .=" and nickname='".$search['nickname']."'";
    }
  	if($search['uid']!=''){
  		$where .=" and uid='".$search['uid']."'";
  	}
  	if($search['time_type']==1){//注册时间
  		if($search['stime']!=''){
  			$where .=" and regtime>=".strtotime($search['stime']);
  		}
  		if($search['etime']!=''){
  			$where .=" and regtime<=".strtotime($search['etime']);
  		}
  	}
  	if($search['time_type']==2){//登录时间
  		if($search['stime']!=''){
  			$where .=" and lasttime>=".strtotime($search['stime']);
  		}
  		if($search['etime']!=''){
  			$where .=" and lasttime<=".strtotime($search['etime']);
  		}
  	}
  	
  	//用户是否有平台币
  	if($search['has_coin']>0){
  		$where .= " AND coin!=0 ";
  	}
  	//已封停用户
  	if($search['disabled_user']>0){
  		$where .= " AND state=0 ";
  	}
  	
  	//排序
  	$sort_where = "";
  	if($search['sort']!=''){
  		$sort = explode('_',$search['sort']);
  		if(count($sort)==2){
  			switch($sort[0]){
  				case 'coin':	$sort_field = 'coin ';break;
  				case 'score':	$sort_field = 'score';break;
  				default:		$sort_field = 'uid';
  			}
  			switch ($sort[1]){
  				case 'asc':	$sort_sequ = ' ASC ';break;
  				default :	$sort_sequ = ' DESC ';break;
  			}
  			$sort_where = ' '.$sort_field.$sort_sequ;
  		}
  	}
  	
  	$members = Member_Model::getMembersByWhere($where,$sort_where);  
  	
    $v = new PageView('mod_member_index');
    $v->assign('nav', $this->_nav)->assign('nav_second', 'member');
    $v->assign('members',$members)->assign('search', $search);
     
    $response->send($v);
  }
  public function member_edit(Request $request, Response $response){
  	$uid = arg(1);
  	$member = Member_Model::getMemberById($uid);
  	if(empty($member)){
  		$msg = '用户不存在';
  		$response->send($msg);
  	}
  	
  	$v = new PageView('mod_member_info');
  	$v->assign('nav', $this->_nav)->assign('nav_second', 'member');
  	$v->assign('member',$member);
  	 
  	$response->send($v);
  	
  }
  
  public function member_save(Request $request, Response $response){
  	$rs = ['flag'=>'FAIL','msg'=>''];
  	
  	$uid = isset($_POST['uid']) ? intval($_POST['uid']):0;
  	$password = isset($_POST['password']) ? $_POST['password']:'';
  	$mobile = isset($_POST['mobile']) ? $_POST['mobile']:'';
  	$email = isset($_POST['email']) ? $_POST['email']:'';
	
  	$member = [];
  	
  	if($password!=''){
  		$salt = create_randcode(6);
	  	$member['password'] = md5(md5($password).$salt);
	  	$member['salt'] = $salt;
  	}
  	
  	if($mobile!=''){
  		list($flag,$error) = Member::checkMobile($mobile);
  		if(!$flag){
  			$rs['msg'] = $error;
  			Response::sendJSON($rs);
  		}
  	}
  	$member['mobile'] = $mobile;
  	if($email!=''){
  		list($flag,$error) = Member::checkEmail($email);
  		if(!$flag){
  			$rs['msg'] = $error;
  			Response::sendJSON($rs);
  		}
  	}
  	$member['email'] = $email;
  	 
  	$affected = Member_Model::updateMemberById($uid, $member);
  	if($affected<1){
  		$rs['msg'] = '修改用户信息失败';
  		Response::sendJSON($rs);
  	}
  	
  	$rs['flag'] = 'SUC';
  	$rs['msg'] = '修改用户信息成功';
  	Response::sendJSON($rs);
  }
  
  public function unAmount(Request $request, Response $response){
  	$rs = ['flag'=>'FAIL','msg'=>''];
  	$uid = isset($_POST['uid']) ? intval($_POST['uid']):0;
  	$afftected = Member_Model::updateMemberById($uid, ['state'=>0]);
  	if($afftected<0){
  		$rs['msg'] = '封停用户账号失败';
  		Response::sendJSON($rs);
  	}
  	$rs['flag']='SUC';
  	$rs['msg'] = '封停用户账号成功';
  	Response::sendJSON($rs);
  } 

  public function amount(Request $request, Response $response){
  	$rs = ['flag'=>'FAIL','msg'=>''];
  	$uid = isset($_POST['uid']) ? intval($_POST['uid']):0;
  	$afftected = Member_Model::updateMemberById($uid, ['state'=>1]);
  	if($afftected<0){
  		$rs['msg'] = '解封用户账号失败';
  		Response::sendJSON($rs);
  	}
  	$rs['flag']='SUC';
  	$rs['msg'] = '解封用户账号成功';
  	Response::sendJSON($rs);
  }
  public function member_loginlog(Request $request, Response $response){
  	$search=array();
  	 
  	$search['stime']='';
  	$search['etime']='';
  	$search['username']='';
  	$search['uid']='';
  	$search['sort']='id_desc';//排序字段,example:coin_asc,coin_desc
  	 
  	//不更新查询条件
  	if(empty($_POST)){
  		if(isset($_SESSION['query']['loginlog'])){
  			$search = $_SESSION['query']['loginlog'];
  		}else{
  			$_SESSION['query']['loginlog'] = $search;
  		}
  	}else{
  		//更新查询条件
  		$search['stime']=empty($_POST['stime'])?'':addslashes(trim($_POST['stime']));
  		$search['etime']=empty($_POST['etime'])?'':addslashes(trim($_POST['etime']));
  		$search['username']=empty($_POST['username'])?'':addslashes(trim($_POST['username']));
  		$search['uid']=empty($_POST['uid'])?'':addslashes(trim($_POST['uid']));
  		$search['sort']=empty($_POST['sort'])? 'id_desc':addslashes(trim($_POST['sort']));
  		 
  		$_SESSION['query']['loginlog'] = $search;
  	}
  	 
  	$where = "";
  	if($search['username']!=''){
  		$where .=" and username='".$search['username']."'";
  	}
  	if($search['uid']!=''){
  		$where .=" and uid='".$search['uid']."'";
  	}
  	if($search['stime']!=''){
  		$where .=" and login_time>=".strtotime($search['stime']);
  	}
  	if($search['etime']!=''){
  		$where .=" and login_time<=".strtotime($search['etime']);
  	}
  	 
  	//排序
  	$sort_where = "";
  	if($search['sort']!=''){
  		$sort = explode('_',$search['sort']);
  		if(count($sort)==2){
  			switch($sort[0]){
  				case 'uid':	$sort_field = 'uid ';break;
  				case 'time':	$sort_field = 'login_time';break;
  				default:		$sort_field = 'log_id';
  			}
  			switch ($sort[1]){
  				case 'asc':	$sort_sequ = ' ASC ';break;
  				default :	$sort_sequ = ' DESC ';break;
  			}
  			$sort_where = ' '.$sort_field.$sort_sequ;
  		}
  	}
  	 
  	$log = Member_Model::getMemberLoginLog($where,$sort_where);
  	 
  	$v = new PageView('mod_member_loginlog');
  	$v->assign('nav', $this->_nav)->assign('nav_second', 'loginlog');
  	$v->assign('log',$log)->assign('search', $search);
  	 
  	$response->send($v);
  }
}
 
/*----- END FILE: Member_Controller.php -----*/