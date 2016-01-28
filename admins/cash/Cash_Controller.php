<?php
/**
 * Cash Controller
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Cash_Controller extends AdminController {
	
	/**
	 * hook init
	 *
	 * @param string $action
	 * @param Request $request
	 * @param Response $response
	 */
	public function init($action, Request $request, Response $response)
	{
		$this->nav = 'cash';
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
		$this->v->set_tplname('mod_cash_index');
		$this->nav_second = 'cash';
	
		$response->send($this->v);
	
	}
	
}
 
/*----- END FILE: Cash_Controller.php -----*/