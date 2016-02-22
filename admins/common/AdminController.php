<?php
/**
 * admin通用控制器
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

define('DAY_SEP', ' ');
define('DAY_BEGIN', DAY_SEP.'00:00:00');
define('DAY_END',   DAY_SEP.'23:59:59');

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