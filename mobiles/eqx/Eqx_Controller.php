<?php
/**
 * 一起享 模块
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Eqx_Controller extends MobileController {

	static $reset_passwd_url = 'http://eqx.edmbuy.com/regtemp.jsp?flag=2&appid=4';
	
	/**
	 * hook init
	 *
	 * @param string $action
	 * @param Request $request
	 * @param Response $response
	 */
	public function init($action, Request $request, Response $response)
	{
		$this->nav_flag1 = 'eqx';
		parent::init($action, $request, $response);
		$this->v->assign('reset_passwd_url', self::$reset_passwd_url);
		
		//SEO信息
		$seo = [
				'title'   => '益多米',
				'keyword' => '一起享,益多米',
				'desc'    => '一起享,益多米'
		];
		$this->v->assign('seo', $seo);
		
		//分享信息
		$share_info = [
    		'title' => '收藏了很久的特价商城，各种超划算！',
    		'desc'  => '便宜又实惠，品质保证，生活中的省钱利器！',
    		'link'  => U('eqx/reg', 'spm='.Spm::user_spm(), true),
    		'pic'   => U('misc/images/napp/touch-icon-144.png','',true),
    ];
    $this->v->assign('share_info', $share_info);
		
		//refer
		$refer = $request->get('refer','');
		$this->v->assign('refer', $refer);
	}
	
	/**
	 * hook menu
	 * 
	 * @see Controller::menu()
	 */
	public function menu()
	{
		return [
				
		];
	}
	
	/**
	 * 介绍信
	 * @param Request $request
	 * @param Response $response
	 * @throws ViewResponse
	 */
	public function letter(Request $request, Response $response)
	{
		$this->v->set_tplname('mod_eqx_letter');
		$this->topnav_no = 0;
		$this->nav_no = 0;
		
		$nologin = $request->get('nologin',0);
		if (!$nologin && Users::is_account_logined()) {
			$response->redirect('/user');
		}
		
		//分享信息
		$share_info = [
				'title' => '一起共享人脉和项目，就在一起享',
				'desc'  => '一套人网，多个项目，重复变现',
				'link'  => U('eqx/intro', 'spm='.Spm::user_spm(), true),
				'pic'   => U('misc/images/napp_eqx/touch-icon-144.png','',true),
		];
		$this->v->assign('share_info', $share_info);
		
		throw new ViewResponse($this->v);
	}
	
	/**
	 * 介绍页
	 * @param Request $request
	 * @param Response $response
	 * @throws ViewResponse
	 */
	public function intro(Request $request, Response $response)
	{
		$this->v->set_tplname('mod_eqx_intro');
		$this->topnav_no = 0;
		$this->nav_no = 1;
		
		//分享信息
		$share_info = [
				'title' => '一起共享人脉和项目，就在一起享',
				'desc'  => '一套人网，多个项目，重复变现',
				'link'  => U('eqx/intro', 'spm='.Spm::user_spm(), true),
				'pic'   => U('misc/images/napp_eqx/touch-icon-144.png','',true),
		];
		$this->v->assign('share_info', $share_info);
		
		throw new ViewResponse($this->v);
	}
	
	/**
	 * 帐号登录
	 *
	 * @param Request $request
	 * @param Response $response
	 */
	public function login(Request $request, Response $response)
	{
		if ($request->is_post()) {
			$mobile = $request->post('mobile','');
			$passwd = $request->post('passwd','');
			
			$ret = ['flag'=>'FAIL', 'msg'=>'', 'code'=>0];			
			//$ret_code = Users::login_account($mobile, $passwd);
			$ret_eqx = ApiEqx::dologin($mobile, $passwd);
			if (0==$ret_eqx['code']) {
				ApiEqx::sync_login([ApiEqx::$argName => $ret_eqx['res']]); //将信息同步到本地
				$cUser = Users::load_by_mobile($mobile);
				if ($cUser->is_exist()) {
					$cUser->set_logined_status();
					$ret = ['flag'=>'SUCC', 'msg'=>'登录成功', 'code'=>0, 'logined_uid'=>$cUser->id];
					$ret['sync_login_url'] = ApiEqx::gen_eqx_loginurl($cUser->guid, $cUser->mobile);
				}
				else {
					$ret['code'] = -100;
					$ret['msg'] = '登录失败';
				}
			}
			else {
				switch ($ret_eqx['code']) {
					case 4000:
					case 4001:
						$ret['code'] = -1;
						$ret['msg'] = '手机号非法';
						break;
					case 4002:
						$ret['code'] = -2;
						$ret['msg'] = '密码不能为空';
						break;
					case 5001:
						$ret['code'] = -3;
						$ret['msg'] = '你输入的手机号还未注册！';
						break;
					case 5002:
						$ret['code'] = -4;
						$ret['msg'] = '你输入的密码不正确！';
						break;
					case -5:
						$ret['code'] = -5;
						$ret['msg'] = '当前手机号没有绑定该微信<br>(一个手机号只能绑定一个微信号)';
						break;
					default:
						$ret['code'] = -100;
						$ret['msg'] = '登录失败';
				}
			}
			$response->sendJSON($ret);
		}
		else { //登录页面
			$refer = $request->get('refer','');
			$refer = $refer ? : U('user');
			if (Users::is_logined()) {
				$response->redirect($refer);
			}
			
			$this->v->set_tplname('mod_eqx_login');
			$this->v->assign('refer', $refer);
			$this->topnav_no = 1;
			$this->nav_no = 0;
			throw new ViewResponse($this->v);
		}
	}
	
	/**
	 * 退出会员登录
	 * @param Request $request
	 * @param Response $response
	 */
	public function logout(Request $request, Response $response)
	{
		if (isset($_SESSION[Users::AC_LOGINED_KEY])) {
			unset($_SESSION[Users::AC_LOGINED_KEY]);
		}
		
		$GLOBALS['user']->uid = 0;
		
		// Reload current pag
		$response->redirect(U('eqx/login'));
	}
	
	/**
	 * 帐号注册
	 *
	 * @param Request $request
	 * @param Response $response
	 */
	public function reg(Request $request, Response $response)
	{
		$step = $request->get('step', 1);
		if ($step!=1 && $step!=2) $step = 1;
		
		if ($request->is_post()) {
			$ret = ['flag' => 'FAIL', 'msg'=>''];
			if (1==$step) {
				$mobile    = $request->post('mobile','');
				$vcode     = $request->post('vcode','');
				$parent_id = $request->post('parent_id',0);
				$mobile    = trim($mobile);
				$vcode     = trim($vcode);
				$parent_id = intval($parent_id);

				// 检查手机号
				if (''==$mobile) {
					$ret['msg'] = '请输入手机号';
					$response->sendJSON($ret);
				}
				elseif (!Fn::check_mobile($mobile)) {
					$ret['msg'] = '手机号不对';
					$response->sendJSON($ret);
				}
				
				// 检查验证码
				if (''==$vcode) {
					$ret['msg'] = '验证码不能为空';
					$response->sendJSON($ret);
				}
				elseif (strlen($vcode)!=6) {
					$ret['msg'] = '验证码不对';
					$response->sendJSON($ret);
				}
				elseif (!UsersmsLog::check_vcode($vcode, $mobile, 'reg_account')) {
					$ret['msg'] = '验证码无效';
					$response->sendJSON($ret);
				}
				
				// 检查手机号是否已注册
				if (Users::check_mobile_exist($mobile)) {
					$ret['msg'] = '该手机号已注册，请登录或者换一个手机号注册';
					$response->sendJSON($ret);
				}
				
				// 检查上级ID(多米号)
				if ($parent_id) {
					$pUser = Users::load($parent_id);
					if (!$pUser->is_exist()) {
						$parent_id = 0;
					}
				}
				
				$_SESSION['eqx_mobi'] = $mobile;
				$ret = ['flag' => 'SUCC', 'msg'=>'手机验证通过'];
				$response->sendJSON($ret);
			}
			elseif(2==$step) {
				$passwd    = $request->post('passwd','');
				$parent_id = $request->post('parent_id',0);
				
				if (''==$passwd) {
					$ret['msg'] = '密码不能为空';
					$response->sendJSON($ret);
				}
				elseif (strlen($passwd)<6) {
					$ret['msg'] = '密码需6位或以上';
					$response->sendJSON($ret);
				}
				$mobile = isset($_SESSION['eqx_mobi']) ? $_SESSION['eqx_mobi'] : '';
				if (empty($mobile) || !Fn::check_mobile($mobile)) {
					$ret['msg'] = '手机号没验证，请重新注册';
					$response->sendJSON($ret);
				}
				if (Users::check_mobile_exist($mobile)) {
					$ret['msg'] = '该手机号已注册，请登录或者换一个手机号注册';
					$response->sendJSON($ret);
				}

				if ($parent_id) {
					$pUser = Users::load($parent_id);
					if (!$pUser->is_exist()) {
						$parent_id = 0;
					}
				}
				$extra = [];
				if (!$parent_id) { //当前没推荐人，则尝试从pending user里面找
					$c_openid = isset($_SESSION[Users::AC_WXAUTH_KEY]) ? $_SESSION[Users::AC_WXAUTH_KEY] : '';
					if ($c_openid) {
						$cUP = UsersPending::load_by_openid($c_openid);
						if ($cUP->is_exist()) {
							$parent_id = $cUP->parent_id;
							$extra['unionid']= $cUP->unionid;
							$extra['openid'] = $cUP->openid;
							$extra['subscribe'] = $cUP->subscribe;
							$extra['subscribe_time'] = $cUP->subscribe_time;
							$extra['gender'] = $cUP->gender;
							$extra['nick']   = $cUP->nick;
							$extra['logo']   = $cUP->logo;
						}
					}
				}
				
				$ret_eqx = ApiEqx::doreg($mobile, $passwd, $parent_id, $extra);
				if ($ret_eqx) {
					if (isset($_SESSION['eqx_mobi'])) unset($_SESSION['eqx_mobi']);
					
					$cUser = Users::load_by_mobile($mobile);
					if ($cUser->is_exist()) {
						$cUser->set_logined_status();
						if ($cUser->parentid) {
							//微信模板消息
							$cUser->notify_reg_succ();
						}
						$ret = ['flag' => 'SUCC', 'msg'=>'注册成功', 'uid'=>$GLOBALS['user']->uid];
					}
					else {
						$ret['msg'] = '注册失败！请稍后再试。';
					}
					
					$response->sendJSON($ret);
				}
				else {
					$ret['msg'] = '注册失败！请稍后再试。';
					$response->sendJSON($ret);
				}
				
			}
		}
		else { //注册页面
			
			if (Users::is_account_logined()) {
				$response->redirect('/user');
			}
			
			$this->v->set_tplname('mod_eqx_reg');
			$this->topnav_no = 1;
			$this->nav_no = 0;
			
			//Spm信息
			$referee_uid = 0;
			$spm = Spm::check_spm();
			if ($spm && preg_match('/^user\.(\d+)(\.\w+)?$/i', $spm, $matchspm)) {
				$referee = Users::load($matchspm[1]);
				if ($referee->is_exist()) {
					$referee_uid = $referee->uid;
				}
			}
			$this->v->assign('referee_uid', $referee_uid);
			
			if (1==$step) {
				if (isset($_SESSION['eqx_mobi'])) {
					unset($_SESSION['eqx_mobi']);
				}
			}
			else {
				if (!isset($_SESSION['eqx_mobi']) OR !Fn::check_mobile($_SESSION['eqx_mobi'])) {
					$response->redirect(U('eqx/reg'));
				}
			}
			
			$this->v->assign('step', $step);
			throw new ViewResponse($this->v);
		}
	}
	
	/**
	 * 获取手机验证码
	 * @param Request $request
	 * @param Response $response
	 */
	public function get_vcode(Request $request, Response $response)
	{
		if ($request->is_post()) {
			
			$ret = ['flag'=>'FAIL', 'msg'=>''];
			$mobile =  $request->post('mobile', '');
			if (''==$mobile OR !Fn::check_mobile($mobile)) {
				$ret['msg'] = '请输入手机号';
				$response->sendJSON($ret);
			}
			
			$type = 'reg_account';
			$row_vc = D()->query("SELECT `id`,`overdueTime`,`verifyCode` FROM ".UsersmsLog::table()." WHERE `receivePhone`='%s' AND `type`='%s' AND `result`>0 ORDER BY `id` DESC LIMIT 0,1",
			                     $mobile, $type)->get_one();
			$now = simphp_time();
			if (!empty($row_vc) && $row_vc['overdueTime']>$now) {
				$ret['msg'] = '一分钟后才能重新获取';
				$response->sendJSON($ret);
			}
			
			$send_code = Sms::sendVCode($mobile, $type, randnum());
			if ($send_code > 0) {
				$ret = ['flag'=>'SUCC', 'msg'=>'发送成功'];
				$response->sendJSON($ret);
			}
			else {
				$ret['msg'] = '发送失败！<br>原因：'.Sms::code_msg($send_code);
				$response->sendJSON($ret);
			}
			
		}
	}
	
	/**
	 * 找回密码
	 *
	 * @param Request $request
	 * @param Response $response
	 */
	public function findpass(Request $request, Response $response)
	{
		if ($request->is_post()) {
	
		}
		else { //登录页面
			$this->v->set_tplname('mod_eqx_findpass');
			$this->topnav_no = 1;
			$this->nav_no = 0;
			
			throw new ViewResponse($this->v);
		}
	}
	
	/**
	 * 登录后首页
	 *
	 * @param Request $request
	 * @param Response $response
	 */
	public function home(Request $request, Response $response)
	{
		if ($request->is_post()) {
	
		}
		else { //登录页面
			
			if (!Users::is_account_logined()) {
				$response->redirect('/eqx/login');
			}
			
			$this->v->set_tplname('mod_eqx_home');
			$this->topnav_no = 0;
			$this->nav_no = 0;
			
			$is_agent = in_array($GLOBALS['user']->level, Users::getAgentArray()) ? 1 : 0;
			$this->v->assign('is_agent', $is_agent);
			
			$my_parent = Users::load($GLOBALS['user']->parentid);
			$this->v->assign('my_parent', $my_parent);
			
			throw new ViewResponse($this->v);
		}
	}
	
	/**
	 * 去推广
	 *
	 * @param Request $request
	 * @param Response $response
	 */
	public function tui(Request $request, Response $response)
	{
		if ($request->is_post()) {
	
		}
		else { //登录页面
			$this->v->set_tplname('mod_eqx_tui');
			$this->topnav_no = 1;
			$this->nav_no = 0;
			
			$usertotal = Users::user_total();
			$this->v->assign('usertotal', $usertotal);
			
			throw new ViewResponse($this->v);
		}
	}
	
	/**
	 * 二维码推广
	 *
	 * @param Request $request
	 * @param Response $response
	 */
	public function tuiqr(Request $request, Response $response)
	{
		if ($request->is_post()) {
				
		}
		else { //登录页面
			$this->v->set_tplname('mod_eqx_tuiqr');
			$this->topnav_no = 1;
			$this->nav_no = 0;
			
			//分享信息
			$share_info = [
					'title' => '一起共享人脉和项目，就在一起享',
					'desc'  => '一套人网，多个项目，重复变现',
					'link'  => U('eqx/intro', 'spm='.Spm::user_spm(), true),
					'pic'   => U('misc/images/napp_eqx/touch-icon-144.png','',true),
			];
			$this->v->assign('share_info', $share_info);
			
			$qrcode  = Users::eqx_tuiqr();
			$baseimg = SIMPHP_ROOT . '/mobiles/eqx/img/top1.png';
			$qrcode_tui = $qrcode.'tui.jpg';
			if (!file_exists(SIMPHP_ROOT.$qrcode_tui)) {
				File::add_watermark($baseimg, SIMPHP_ROOT.$qrcode_tui, SIMPHP_ROOT.$qrcode, ['x'=>191,'y'=>475,'w'=>258,'h'=>258], 100, '#FFFFFF', ['png2jpg'=>true]);
			}
			$this->v->assign('qrcode_tui', $qrcode_tui);
			
			throw new ViewResponse($this->v);
		}
	}
	

	/**
	 * 同步一起享登录
	 *
	 * @param Request $request
	 * @param Response $response
	 */
	public function sync_login(Request $request, Response $response)
	{
		$cUid = ApiEqx::parseEqxRequest();
		if ($cUid) {
			(new Users($cUid))->set_logined_status();
		}
		$response->redirect(U('user'));
	}
	
}

 
/*----- END FILE: Eqx_Controller.php -----*/