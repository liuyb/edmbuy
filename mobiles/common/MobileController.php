<?php
/**
 * Mobile common controller
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class MobileController extends Controller {
	
	protected $nav_no     = 1;       //主导航id，当为0时表示不存在
	protected $topnav_no  = 0;       //顶部导航id，当为0时表示不存在
	protected $nav_flag1  = '';      //导航标识1
	protected $nav_flag2  = '';      //导航标识2
	
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
			$v->assign('nav_no',  $this->nav_no)
			  ->assign('topnav_no',  $this->topnav_no)
			  ->assign('nav_flag1',  $this->nav_flag1)
			  ->assign('nav_flag2',  $this->nav_flag2)
			;
		});
	}
	
}
 
/*----- END FILE: MobileController.php -----*/