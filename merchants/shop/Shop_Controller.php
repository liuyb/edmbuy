<?php
/**
 * 店铺控制器
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Shop_Controller extends MerchantController {

	public function menu()
	{
		return [

		];
	}
	/**
	 * default action 'index'
	 * @param Request $request
	 * @param Response $response
	 */
	public function index(Request $request, Response $response)
	{
		$this->v->set_tplname('mod_shop_index');
		$this->setSystemNavigate('shop');
		$this->setPageLeftMenu('shop', 'list');
		$response->send($this->v);
	}

	/**
	 * 首页轮播图
	 * @auth edm_hc
	 * @param Request $request
	 * @param Response $response
	 */
	public function headerPic(Request $request, Response $response){

	}
}
 
/*----- END FILE: Shop_Controller.php -----*/