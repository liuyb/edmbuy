<?php
/**
 * 
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Zt_Controller extends MobileController {
	
	/**
	 * hook menu
	 * @see Controller::menu()
	 */
	public function menu()
	{
		return [
				'zt' => 'index',
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
		$this->nav_flag1 = 'zt';
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
		$this->setPageView($request, $response, '_page_spa');
		$this->v->set_tplname('mod_zt_index');
		throw new ViewResponse($this->v);
	}
	
	/**
	 * action 'newtea'
	 *
	 * @param Request $request
	 * @param Response $response
	 */
	public function newtea(Request $request, Response $response)
	{
		$this->setPageView($request, $response, '_page_spa');
		$this->v->set_tplname('mod_zt_newtea');
		
		//分享信息
		$share_info = [
				'title' => '初茶一舍2016雨前上品龙井新茶预定',
				'desc'  => '西湖，龙井的故乡，茶树已悄然破芽，与柳枝竹梢编织着三月的水墨江南。',
				'link'  => U('zt/newtea', 'spm='.Spm::user_spm(), true),
				'pic'   => U('misc/images/tea/newtea_01.jpg','',true),
		];
		$this->v->assign('share_info', $share_info);
		
		throw new ViewResponse($this->v);
	}
}

/*----- END FILE: Zt_Controller.php -----*/