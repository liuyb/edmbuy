<?php
/**
 * 个人推广控制器
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class T_Controller extends MobileController {
	
	/**
	 * hook init
	 *
	 * @param string $action
	 * @param Request $request
	 * @param Response $response
	 */
	public function init($action, Request $request, Response $response)
	{
		$this->nav_flag1 = 't';
		parent::init($action, $request, $response);
	}
	
	/**
	 * hook menu
	 * @see Controller::menu()
	 */
	public function menu()
	{
		return [
			't/%d'    => 'go',
			't/%d/%s' => 'go',
		];
	}
	
	/**
	 * default action 'index'
	 *
	 * @param Request $request
	 * @param Response $response
	 */
	public function index(Request $request, Response $response)
	{
		$this->setPageView($request, $response, '_page_mpa');
		$this->v->set_tplname('mod_t_index');
		$this->v->set_page_render_mode(View::RENDER_MODE_GENERAL);
		$this->nav_no = 0;
	
		//分享信息
		$share_info = [
				'title' => '收藏了很久的特价商城，各种超划算！',
				'desc'  => '便宜又实惠，品质保证，生活中的省钱利器！',
				'link'  => U('', 'spm='.Spm::user_spm(), true),
				'pic'   => U('misc/images/napp/touch-icon-144.png','',true),
		];
		$this->v->assign('share_info', $share_info);
	
		throw new ViewResponse($this->v);
	}
	
	/**
	 * action 'go'
	 *
	 * @param Request $request
	 * @param Response $response
	 */
	public function go(Request $request, Response $response)
	{
		$the_uid = $request->arg(1);
		$the_uid = intval($the_uid);
		$flag = $request->arg(2);
		if ($flag && $flag=='eqx') {
			$response->redirect('/eqx/intro?spm='.Spm::user_spm($the_uid));
		}
		else {
			$response->redirect('/?spm='.Spm::user_spm($the_uid));
		}
	}
	
	/**
	 * action 'myqr'
	 *
	 * @param Request $request
	 * @param Response $response
	 */
	public function myqr(Request $request, Response $response)
	{
		$this->setPageView($request, $response, '_page_mpa');
		$this->v->set_tplname('mod_t_myqr');
		$this->v->set_page_render_mode(View::RENDER_MODE_GENERAL);
		$this->nav_no = 0;
		$this->extra_css = 'greybg';
	
		if ($request->is_post()) {
			$regen = $request->post('regen',0);
			$ret = ['flag'=>'FAIL','msg'=>'no action'];
			if ($regen) {
				
				if (!Users::is_logined()) {
					$ret['msg'] = '未登录，请先登录';
					$response->sendJSON($ret);
				}
				
				global $user;
				$my_tgqr = Users::my_tgqr(NULL,'',TRUE);
				$prom_qr = $user->wx_qrpromote(TRUE);
				$user->regen_randver();
				$ret = ['flag'=>'SUCC','msg'=>'重新生成成功','tgqr'=>$my_tgqr,'promote_qr'=>$prom_qr];
				$response->sendJSON($ret);
			}
			$response->sendJSON($ret);
		}
		else {
			if (!Users::is_logined()) {
				throw new ViewException($this->v, '未登录，请先登录');
			} else {
			
				$qrcode = Users::my_tgqr();
				$this->v->assign('qrcode', $qrcode);
					
				//分享信息
				$share_info = [
						'title' => '收藏了很久的特价商城，各种超划算！',
						'desc'  => '便宜又实惠，品质保证，生活中的省钱利器！',
						'link'  => U('', 'spm='.Spm::user_spm(), true),
						'pic'   => U('misc/images/napp/touch-icon-144.png','',true),
				];
				$this->v->assign('share_info', $share_info);
					
			}
		}
	
		throw new ViewResponse($this->v);
	}
	
}

 
/*----- END FILE: T_Controller.php -----*/