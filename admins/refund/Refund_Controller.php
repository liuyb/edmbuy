<?php
/**
 * Refund Controller
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Refund_Controller extends AdminController {
	
	/**
	 * hook init
	 *
	 * @param string $action
	 * @param Request $request
	 * @param Response $response
	 */
	public function init($action, Request $request, Response $response)
	{
		$this->nav = 'refund';
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
		$this->v->set_tplname('mod_refund_index');
		$this->nav_second = 'refund';
		
		$response->send($this->v);
		
	}
	
	/**
	 * action 'add'
	 * 
	 * @param Request $request
	 * @param Response $response
	 */
	public function add(Request $request, Response $response)
	{
		$this->v->set_tplname('mod_refund_add');
		$this->nav_second = 'refund_add';
		
		$response->send($this->v);
		
	}
	
	
}
 
/*----- END FILE: Refund_Controller.php -----*/