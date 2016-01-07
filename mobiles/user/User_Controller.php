<?php
/**
 * Mall Controller
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class User_Controller extends MobileController {
  
  public function menu() {
    return [
      'user' => 'index',
      'user/oauth/%s' => 'oauth',
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
  	$this->nav_flag1 = 'user';
    parent::init($action, $request, $response);
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
    
    if ($request->is_hashreq()) {
    	//sleep(1);
    }
    else {
      //检查用户信息完成度，nickname或logo没有的话都重定向请求OAuth2详细认证获取资料
      Users::check_detail_info();
    }
    
    $response->send($this->v);
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
    if(!Users::is_logined()) {
      $token = $request->get('token','');
      if(''!=$token) { //token登录优先，便于测试
        $this->tokenLogin($request, $response);
      }
      elseif(!Weixin::isWeixinBrowser()) { //不是微信内置浏览器
        $this->tips($request, $response);
      }
      else { //先用base方式获取微信OAuth2授权，以便于取得openid
        (new Weixin())->authorizing('http://'.$request->host().'/user/oauth/weixin?act=login&refer='.rawurlencode($refer));
      }
    }
    else {
      $response->redirect($refer);
    }
  }
  
  /**
   * action 'go_detail_oauth', request detail oauth
   * @param Request $request
   * @param Response $response
   */
  public function go_detail_oauth(Request $request, Response $response)
  {
  	$refer = $request->get('refer','/');
  	(new Weixin())->authorizing('http://'.Request::host().'/user/oauth/weixin?act=&refer='.rawurlencode($refer), 'detail');
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
    $code = trim($code);
    if (''==$code) { //授权未通过
    	Fn::show_error_message('未授权，不能访问应用！');
    }
    
    //~ 授权通过逻辑
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
    $unionid= isset($code_ret['unionid']) ? $code_ret['unionid'] : '';
    
    //查询本地是否存在对应openid的用户
    $localUser   = Users::load_by_unionid($unionid);
    $loginedUser = $localUser;
    if ($localUser->is_exist()) { //用户已存在，对state='base'，则仅需设置登录状态；而对state='detail'，需保存或更新用户数据
    
    	if ('detail'==$state) { //detail认证模式，需更新用户数据
    
    		$auth_method = 'oauth2_detail';//OAuth2详细认证方式
    
    		$uinfo_wx = $wx->userInfoByOAuth2($openid, $code_ret['access_token']);
    		if (!empty($uinfo_wx['errcode'])) { //失败！则报错
    			Fn::show_error_message('微信获取用户信息出错！<br/>'.$uinfo_wx['errcode'].'('.$uinfo_wx['errmsg'].')');
    		}
    
    		//保存微信用户信息到本地库
    		$upUser = new Users($localUser->uid);
    		//$upUser->unionid   = $unionid;
    		$upUser->openid    = $openid;
    		if (isset($uinfo_wx['subscribe'])) {
    			$upUser->subscribe = $uinfo_wx['subscribe'];
    			$upUser->subscribetime = $uinfo_wx['subscribe_time'];
    		}
    		if ($localUser->required_uinfo_empty()) {
    			$upUser->nickname  = isset($uinfo_wx['nickname']) ? $uinfo_wx['nickname'] : '';
    			$upUser->logo      = isset($uinfo_wx['headimgurl']) ? $uinfo_wx['headimgurl'] : '';
    			$upUser->sex       = isset($uinfo_wx['sex']) ? $uinfo_wx['sex'] : 0;
    			$upUser->lang      = isset($uinfo_wx['language']) ? $uinfo_wx['language'] : '';
    			$upUser->country   = isset($uinfo_wx['country']) ? $uinfo_wx['country'] : '';
    			$upUser->province  = isset($uinfo_wx['province']) ? $uinfo_wx['province'] : '';
    			$upUser->city      = isset($uinfo_wx['city']) ? $uinfo_wx['city'] : '';
    		}
    
    		//尝试用基本型接口获取用户信息，以便确认用户是否已经关注(基本型接口存在 50000000次/日 调用限制，且仅对关注者有效)
    		if (!$localUser->subscribe && !$upUser->subscribe) {
    			$uinfo_wx = $wx->userInfo($openid);
    			if (!empty($uinfo_wx['errcode'])) { //失败！说明很可能没关注，维持现状不处理
    
    			}
    			else { //成功！说明之前已经关注，得更新关注标记
    				$upUser->subscribe = isset($uinfo_wx['subscribe']) ? $uinfo_wx['subscribe'] : 0;
    				$upUser->subscribetime = isset($uinfo_wx['subscribe_time']) ? $uinfo_wx['subscribe_time'] : 0;
    			}
    		}
    		
    		$upUser->save(Storage::SAVE_UPDATE);
    		$loginedUser = $upUser;
    
    	} //End: if ('detail'===$state)
    
    }
    else { //用户不存在，则要尝试建立
    	
    	if ('base'==$state) { //基本授权方式
    
	    	$auth_method = 'oauth2_base';//基本认证方式
	    	
	    	$upUser = new Users();
	    	$upUser->unionid = $unionid;
	    	$upUser->openid  = $openid;
	    	$upUser->regip   = $request->ip();
	    	$upUser->regtime = simphp_time();
	    	$upUser->salt    = gen_salt();
	    	$upUser->parentid= 0;
	    	$upUser->state   = 0; //0:正常;1:禁止
	    	$upUser->from    = $from;
	    	$upUser->authmethod = $auth_method;
	    	
	    	//检查spm
	    	$parent_id = 0;
	    	$spm = Spm::check_spm($refer);
	    	if ($spm && preg_match('/^user\.(\d+)$/', $spm, $matchspm)) {
	    		if (Users::id_exists($matchspm[1])) {
	    			$parent_id = $matchspm[1];
	    		}
	    	}
	    	$upUser->parentid = $parent_id;
	    	
	    	$upUser->save(Storage::SAVE_INSERT);
	    	$loginedUser = $upUser;
    
    	} //END: if ('base'==$state)
    
    } //END: if ($localUser->is_exist()) else
    
		//设置本地登录状态
		if ('login'==$auth_action) {
    
    		if (!$loginedUser->is_exist()) {
    			Fn::show_error_message('微信授权登录失败！');
    		}
    
    		$loginedUser->set_logined_status();
		}
		
		//跳转
		$response->redirect($refer);
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
    
    //检查数据库token，以获取openid或unionid
    $unionid = User_Model::checkAccessToken($token, 'unionid');
    if(FALSE === $unionid){
      $this->tips($request, $response);
    }
    
    //通过openid或unionid获取用户信息
    $user = Users::load_by_unionid($unionid);
    if(!$user->is_exist()){
      Fn::show_error_message();
    }
    
    //设置本地登录状态
    $user->set_logined_status();
    
    //Token登录后去到当前页(避免session没写成功走正常流程)
    $response->redirect(preg_replace('/\??&?token=[a-z0-9]+/i', '', $request->url()));
  }
  
  /**
   * Tips页显示
   * @param Request $request
   * @param Response $response
   */
  public function tips(Request $request, Response $response){
    $this->v = new PageView('','tips');
    $q = $request->q();
    $qrcode = '/misc/images/qrcode/edmbuy_258.jpg';
    if (preg_match('/^item\/(\d+)$/', $q, $matches)) {
    	
    	$id   = $matches[1];
    	$item = Items::load($id);
    	if ($item->is_exist()) {
    		$dir     = Fn::gen_qrcode_dir($id,'item',true);
    		$locfile = $dir . $id . '.png';
    		if (!file_exists($locfile)) {
    			if (mkdirs($dir)) {
    				$qrinfo  = $item->url('qrcode');
    				include_once SIMPHP_INCS .'/libs/phpqrcode/qrlib.php';
    				QRcode::png($qrinfo, $locfile, QR_ECLEVEL_L, 7, 3);
    				if (file_exists($locfile)) {
    					$qrcode = str_replace(SIMPHP_ROOT, '', $locfile);
    				}
    			}
    		}
    		else {
    			$qrcode = str_replace(SIMPHP_ROOT, '', $locfile);
    		}
    	}
    	
    }
    $this->v->assign('qrcode', $qrcode);
    $response->send($this->v);
  }
  
}

/*----- END FILE: User_Controller.php -----*/