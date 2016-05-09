<?php
/**
 * 一起享 模块
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Eqx_Controller extends MobileController {

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
		
		//SEO信息
		$seo = [
				'title'   => '一起享/益多米',
				'keyword' => '一起享,益多米',
				'desc'    => '一起享,益多米'
		];
		$this->v->assign('seo', $seo);
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
			
			$ret = ['flag'=>'FAIL', 'msg'=>''];			
			$ret_code = Users::login_account($mobile, $passwd);
			if ($ret_code > 0) {
				$ret = ['flag'=>'SUCC', 'msg'=>'登录成功', 'logined_uid'=>$ret_code];
			}
			else {
				switch ($ret_code) {
					case -1:
						$ret['msg'] = '手机号非法';
						break;
					case -2:
						$ret['msg'] = '密码不能为空';
						break;
					case -3:
						$ret['msg'] = '帐号不存在';
						break;
					case -4:
						$ret['msg'] = '密码不对';
						break;
					default:
						$ret['msg'] = '登录失败';
				}
			}
			$response->sendJSON($ret);
		}
		else { //登录页面
			if (Users::is_account_logined()) {
				$response->redirect('/eqx/home');
			}
			
			$this->v->set_tplname('mod_eqx_login');
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
		
		// Reload current pag
		$response->redirect('/eqx/login');
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
				$mobile = $request->post('mobile','');
				$is_verify = $request->post('is_verify',0);
				$mobile = trim($mobile);
				$is_verify = intval($is_verify);
				
				if (''==$mobile || !Fn::check_mobile($mobile)) {
					$ret['msg'] = '手机号不对';
					$response->sendJSON($ret);
				}
				if (!$is_verify) {
					$ret['msg'] = '尚未验证';
					$response->sendJSON($ret);
				}
				
				$_SESSION['eqx_mobi'] = $mobile;
				$ret = ['flag' => 'SUCC', 'msg'=>'手机号正确'];
				$response->sendJSON($ret);
			}
			elseif(2==$step) {
				$mobile = $request->post('mobile','');
				$passwd = $request->post('passwd','');
				$vcode  = $request->post('vcode','');
				$parent_id = $request->post('parent_id',0);
				
				if (!$GLOBALS['user']->uid) {
					$ret['msg'] = '请先微信授权登录';
					$response->sendJSON($ret);
				}
				
				if (''==$mobile || !Fn::check_mobile($mobile)) {
					$ret['msg'] = '手机号不对';
					$response->sendJSON($ret);
				}
				
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
				
				if (''==$passwd) {
					$ret['msg'] = '密码不能为空';
					$response->sendJSON($ret);
				}
				elseif (strlen($passwd)<6) {
					$ret['msg'] = '密码需6位或以上';
					$response->sendJSON($ret);
				}
				
				if (Users::check_mobile_exist($mobile)) {
					$ret['msg'] = '该手机号已注册，不能重新注册';
					$response->sendJSON($ret);
				}
				
				if (!$parent_id && !empty($_SESSION['eqx_referee_uid'])) {
					$parent_id = $_SESSION['eqx_referee_uid'];
				}
				
				if (Users::reg_account($mobile, $passwd, $parent_id, $GLOBALS['user']->uid)) {
					if (isset($_SESSION['eqx_referee_uid'])) unset($_SESSION['eqx_referee_uid']);
					if (isset($_SESSION['eqx_mobi'])) unset($_SESSION['eqx_mobi']);
					
					Users::set_account_logined();
					$ret = ['flag' => 'SUCC', 'msg'=>'注册成功', 'uid'=>$GLOBALS['user']->uid];
					$response->sendJSON($ret);
				}
				else {
					$ret['msg'] = '注册失败';
					$response->sendJSON($ret);
				}
				
			}
		}
		else { //注册页面
			
			if (Users::is_account_logined()) {
				$response->redirect('/eqx/home');
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
			if ($referee_uid) {
				$_SESSION['eqx_referee_uid'] = $referee_uid;
			}
			
			if (1==$step) {
				if (isset($_SESSION['eqx_mobi'])) {
					unset($_SESSION['eqx_mobi']);
				}
			}
			else {
				if (!isset($_SESSION['eqx_mobi']) OR !Fn::check_mobile($_SESSION['eqx_mobi'])) {
					$response->redirect('/eqx/reg');
				}
				$this->v->assign('mobile', $_SESSION['eqx_mobi']);
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
			if (!isset($_SESSION['eqx_mobi']) OR !Fn::check_mobile($_SESSION['eqx_mobi'])) {
				$ret['msg'] = '手机号不存在';
				$response->send($ret);
			}
			
			$type = 'reg_account';
			$row_vc = D()->query("SELECT `id`,`overdueTime`,`verifyCode` FROM ".UsersmsLog::table()." WHERE `receivePhone`='%s' AND `type`='%s' AND `result`=1 ORDER BY `id` DESC LIMIT 0,1",
			                     $_SESSION['eqx_mobi'], $type)->get_one();
			$now = simphp_time();
			if (!empty($row_vc) && $row_vc['overdueTime']>simphp_time()) {
				$ret['msg'] = '一分钟后才能重新获取';
				$response->send($ret);
			}
			
			$ret = ['flag'=>'SUCC', 'msg'=>'发送成功'];
			if (!Sms::sendVCode($_SESSION['eqx_mobi'], $type, rand_code(6))) {
				$ret['msg'] = '发送失败';
			}
			$response->sendJSON($ret);
			
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
			$this->topnav_no = 1;
			$this->nav_no = 0;
			
			$is_agent = in_array($GLOBALS['user']->level, [Users::USER_LEVEL_3,Users::USER_LEVEL_4]) ? 1 : 0;
			$this->v->assign('is_agent', $is_agent);
			
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
	
}

 
/*----- END FILE: Eqx_Controller.php -----*/