<?php
/**
 * admin通用控制器
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class AdminController extends Controller {
	
	protected $nav        = 'sy';
	protected $nav_second = '';
	
	/**
	 * hook init
	 *
	 * @param string $action
	 * @param Request $request
	 * @param Response $response
	 */
	public function init($action, Request $request, Response $response)
	{
		
		$this->v = new PageView();
		$this->v->add_render_filter(function(View $v){
			$v->assign('nav',        $this->nav)
			  ->assign('nav_second', $this->nav_second)
			;
		});
		
	}
	
}

 
/*----- END FILE: AdminController.php -----*/