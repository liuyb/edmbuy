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
				'title'   => '一起享',
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
				'eqx/%d' => 'item'
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
	
		}
		else { //登录页面
			$this->v->set_tplname('mod_eqx_login');
			$this->topnav_no = 1;
			$this->nav_no = 0;
			throw new ViewResponse($this->v);
		}
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
				$mobile = trim($mobile);
				
				if (''==$mobile || !Fn::check_mobile($mobile)) {
					$ret['msg'] = '手机号不对';
					$response->sendJSON($ret);
				}
				
				$_SESSION['eqx_mobi'] = $mobile;
				$ret = ['flag' => 'SUCC', 'msg'=>''];
				$response->sendJSON($ret);
			}
			else {
				
			}
		}
		else { //登录页面
			$this->v->set_tplname('mod_eqx_reg');
			$this->topnav_no = 1;
			$this->nav_no = 0;
			
			if (1==$step) {
				if (isset($_SESSION['eqx_mobi'])) {
					unset($_SESSION['eqx_mobi']);
				}
			}
			else {
				if (!isset($_SESSION['eqx_mobi']) OR !Fn::check_mobile($_SESSION['eqx_mobi'])) {
					$response->redirect('/eqx/reg');
				}
			}
			
			$this->v->assign('step', $step);
			throw new ViewResponse($this->v);
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
	
}

 
/*----- END FILE: Eqx_Controller.php -----*/