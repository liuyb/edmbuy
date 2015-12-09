<?php
/**
 * Mall Controller
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class User_Controller extends Controller {
  
  private $nav_no     = 1;       //主导航id
  private $topnav_no  = 0;       //顶部导航id
  private $nav_flag1  = 'user';  //导航标识1
  private $nav_flag2  = '';      //导航标识2
  private $nav_flag3  = '';      //导航标识3
  
  public function menu() {
    return [
      'user' => 'index',
      'user/oauth/%s' => 'oauth',
      'user/collect/cancel' => 'collect_cancel',
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
    if (!$request->is_post()) {
      $this->v = new PageView();
      $this->v->add_render_filter(function(View $v){
        $v->assign('nav_no',     $this->nav_no)
          ->assign('topnav_no',  $this->topnav_no)
          ->assign('nav_flag1',  $this->nav_flag1)
          ->assign('nav_flag2',  $this->nav_flag2)
          ->assign('nav_flag3',  $this->nav_flag3);
      });
    }
  }
  
  /**
   * default action 'index'
   *
   * @param Request $request
   * @param Response $response
   */
  public function index(Request $request, Response $response)
  {
    $this->v->set_tplname('mod_user_index');
    $userInfo = Member::getTinyInfoByUid($GLOBALS['user']->uid);
    
    if ($request->is_hashreq()) {
      $this->v->assign('userInfo', $userInfo);
    }
    else {
      //检查用户信息完成度，nickname或logo没有的话都重定向请求OAuth2详细认证获取资料
      User_Model::checkUserInfoCompleteDegree($userInfo, '/user/');
    }
    
    $response->send($this->v);
  }
  
  /**
   * [feedback description]
   * @param  Request  $request  [description]
   * @param  Response $response [description]
   * @return [type]             [description]
   */
  public function feedback(Request $request, Response $response){
    
    if ($request->is_post()) {
      
      $res = ['flag'=>'FAIL', 'msg'=>''];
      
      $content = $request->post('content', '');
      $contact = $request->post('contact', '');
      $content = trim($content);
      $contact = trim($contact);
      
      if($content==''){
        $res['msg'] = '内容不能为空';
        $response->sendJSON($res);
      }
      
      $fid = User_Model::saveFeedback(['msg_content'=>$content, 'user_email'=> $contact]);
      if($fid>0){
        $res['flag'] = 'SUC';
        $res['backurl'] = U('user');
      }else{
        $res['msg']= '系统繁忙，请稍后再试！';
      }
      $response->sendJSON($res);
      
    }
    else {
      
      $this->v->set_tplname('mod_user_feedback');
      if ($request->is_hashreq()) {
      
      }
      $response->send($this->v);
      
    }
  }
  
  public function collect(Request $request, Response $response){
    $this->v->set_tplname('mod_user_collect');

    if ($request->is_hashreq()) {
      $collect_list = Goods::getUserCollectList();
      $this->v->assign('collect_list', $collect_list);
      $this->v->assign('collect_num', count($collect_list));
    }

    $response->send($this->v); 
  }
  
  public function collect_cancel(Request $request, Response $response){
    
    if ($request->is_post()) {
      
      $res = ['flag'=>'FAIL','msg'=>'取消失败'];
      
      $ec_user_id = $GLOBALS['user']->ec_user_id;
      if (!$ec_user_id) {
        $res['msg'] = '未登录, 请登录';
        $response->sendJSON($res);
      }
      
      $rec_id = $request->post('rec_id', 0);
      if (!$rec_id) {
        $res['msg'] = '记录id为空';
        $response->sendJSON($res);
      }
      
      $b = Goods::goodsCollectCancel($rec_id);
      if ($b) {
        $res = ['flag'=>'SUC','msg'=>'取消成功'];
      }
      
      $response->sendJSON($res);
      
    }
    
  }

  /**
   * 登录
   * 
   * @param Request $request
   * @param Response $response
   */
  public function login(Request $request, Response $response)
  {
    $refer = $request->url();
    if(!Member::isLogined()) {
      $token = $request->get('token','');
      if(''!=$token) { //token登录优先，便于测试
        $this->tokenLogin($request, $response);
      }
      elseif(!Weixin::isWeixinBrowser()) { //不是微信内置浏览器
        $this->tips($request, $response);
      }
      else { //先用base方式获取微信OAuth2授权，以便于取得openid
        (new Weixin())->authorizing('http://'.$request->host().'/user/oauth/weixin?act=login&refer='.$refer);
      }
    }
    else {
      $response->redirect($refer);
    }
  }
  
  /**
   * action 'oauth', the OAuth callback
   * 
   * @param Request $request
   * @param Response $response
   */
  public function oauth(Request $request, Response $response)
  {
    //trace_debug('weixin_oauth2_callback_doing', $_GET);
    $code = $request->get('code', '');
    if (''!=$code) { //授权通过
      $state = $request->get('state', '');
      $refer = $request->get('refer', '/');
      $from  = $request->arg(2);
      if (empty($from)) $from = 'weixin';
      $auth_action = $request->get('act','');
      
      //收获地址base oauth回调
      if ('jsapi_address'==$auth_action) {
        $response->redirect($refer.'&code='.$code.'&state='.$state);
      }
      
      //授权出错
      if (!in_array($state, array('base','detail'))) {
        Fn::show_error_message('授权出错，不能访问应用！');
      }
      
      $wx = new Weixin();
      
      //用code换取access token
      $code_ret = $wx->request_access_token($code);
      if (!empty($code_ret['errcode'])) {
        Fn::show_error_message('微信授权错误<br/>'.$code_ret['errcode'].'('.$code_ret['errmsg'].')');
      }
      
      //获取到openid
      $openid = $code_ret['openid'];
      $uid    = 0;
      
      //查询本地是否存在对应openid的用户
      $uinfo_bd = Member::getTinyInfoByOpenid($openid, $from);
      if (!empty($uinfo_bd)) { //用户已存在，对state='base'，则仅需设置登录状态；而对state='detail'，需保存或更新用户数据
        $uid = intval($uinfo_bd['uid']);
        
        if ('detail'===$state) { //detail认证模式，需更新用户数据
          
          $auth_method = 'oauth2_detail';//OAuth2详细认证方式
          
          $uinfo_wx = $wx->userInfoByOAuth2($openid, $code_ret['access_token']);
          if (!empty($uinfo_wx['errcode'])) { //失败！则报错
            Fn::show_error_message('微信获取用户信息出错！<br/>'.$uinfo_wx['errcode'].'('.$uinfo_wx['errmsg'].')');
          }
          
          //保存微信用户信息到本地库
          $udata = [
            //'openid'   => $openid,
            'unionid'  => isset($uinfo_wx['unionid']) ? $uinfo_wx['unionid'] : '',
            'subscribe'=> isset($uinfo_wx['subscribe']) ? $uinfo_wx['subscribe'] : 0,
            'subscribe_time'=> isset($uinfo_wx['subscribe_time']) ? $uinfo_wx['subscribe_time'] : 0,
            'nickname' => isset($uinfo_wx['nickname']) ? $uinfo_wx['nickname'] : '',
            'logo'     => isset($uinfo_wx['headimgurl']) ? $uinfo_wx['headimgurl'] : '',
            'sex'      => isset($uinfo_wx['sex']) ? $uinfo_wx['sex'] : 0,
            'lang'     => isset($uinfo_wx['language']) ? $uinfo_wx['language'] : '',
            'country'  => isset($uinfo_wx['country']) ? $uinfo_wx['country'] : '',
            'province' => isset($uinfo_wx['province']) ? $uinfo_wx['province'] : '',
            'city'     => isset($uinfo_wx['city']) ? $uinfo_wx['city'] : '',
            'auth_method'=> $auth_method
          ];
          Member::updateUser($udata,$openid,$from);
          
          //尝试用基本型接口获取用户信息，以便确认用户是否已经关注(基本型接口存在 50000000次/日 调用限制，且仅对关注者有效)
          if (!$uinfo_bd['subscribe'] && !$udata['subscribe']) {
            $uinfo_wx = $wx->userInfo($openid);
            //trace_debug('weixin_basic_userinfo', $uinfo_wx);
            if (!empty($uinfo_wx['errcode'])) { //失败！说明很可能没关注，维持现状不处理
              
            }
            else { //成功！说明之前已经关注，得更新关注标记
              $udata = [
                'subscribe'=> isset($uinfo_wx['subscribe']) ? $uinfo_wx['subscribe'] : 0,
                'subscribe_time'=> isset($uinfo_wx['subscribe_time']) ? $uinfo_wx['subscribe_time'] : 0,
              ];
              Member::updateUser($udata,$openid,$from);
            }
          }
          
        } //End: if ('detail'===$state)
        
      }
      else { //用户不存在，则要尝试建立
        
        if ('base'===$state) { //基本授权方式
          
          $auth_method = 'oauth2_base';//基本认证方式
          
          //保存微信用户信息到本地库
          $udata = [
            'openid'     => $openid,
            'auth_method'=> $auth_method
          ];
          $uid = Member::createUser($udata, $from);
          
        }


      } //End: if (!empty($uinfo_bd)) else
      
      //设置本地登录状态
      if ('login'==$auth_action) {
        
        if (empty($uid)) {
          Fn::show_error_message('微信授权登录失败！');
        }
        
        Member::setLocalLogin($uid);
      }
      
      //跳转
      $response->redirect($refer);
    }
    else {
      //授权未通过
      Fn::show_error_message('未授权，不能访问应用！');
    }
  }

  /**
   * 退出登录
   * 
   * @param Request $request
   * @param Response $response
   */
  public function logout(Request $request, Response $response){
    // Unset all of the session variables.
    session_destroy();
    $_SESSION = array();
    
    // If it's desired to kill the session, also delete the session cookie.
    // Note: This will destroy the session, and not just the session data!
    if (isset($_COOKIE[session_name()])) {
      Cookie::raw_remove(session_name());
    }
    
    // Finally, destroy the session.
    SIMPHP::$session->anonymous_user($GLOBALS['user']);
    
    // Reload current pag
    $response->reload();
  }

  /**
   * token登录(用于测试)
   * 
   * @param Request $request
   * @param Response $response
   */
  public function tokenLogin(Request $request, Response $response){
    
    //检查token
    $token = $request->get('token','');
    if(''==$token){
      $this->tips($request, $response);
    }
    
    //检查数据库token，以获取openid
    $openid = User_Model::checkAccessToken($token);
    if($openid === FALSE){
      $this->tips($request, $response);
    }
    
    //通过openid 获取用户信息
    $userInfo = Member::getTinyInfoByOpenid($openid);
    if(empty($userInfo)){
      Fn::show_error_message();
    }
    
    //设置本地登录状态
    Member::setLocalLogin($userInfo['uid']);
    
    //Token登录后去到当前页(避免session没写成功走正常流程)
    $response->redirect($request->url());
  }
  
  /**
   * tips页显示
   * @param Request $request
   * @param Response $response
   */
  public function tips(Request $request, Response $response){
    $this->v = new PageView('','tips');
    $response->send($this->v);
  }
  
}

/*----- END FILE: User_Controller.php -----*/