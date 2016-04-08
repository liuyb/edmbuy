<?php
/**
 * Merchant通用控制器
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

define('DAY_SEP', ' ');
define('DAY_BEGIN', DAY_SEP.'00:00:00');
define('DAY_END',   DAY_SEP.'23:59:59');

class MerchantController extends Controller {
	
	protected $nav        = 'home';
	protected $nav_second = '';

	/**
	 * a PageView object instance
	 * @var PageView
	 */
	protected $v;
	
	/**
	 * hook init
	 *
	 * @param string $action
	 * @param Request $request
	 * @param Response $response
	 */
	public function init($action, Request $request, Response $response)
	{
		$this->setPageView($request, $response);
		//拦截处理页面头部菜单高亮显示
		$q = $request->q();
		$q = explode('/', $q);
		if($q && count($q) > 0){
    		$this->setSystemNavigate($q[0]);
		}else{
		    $this->setSystemNavigate('index');
		}
	}
	
	/**
	 * set page view
	 * @param Request $request
	 * @param Response $response
	 * @param string $basetpl
	 */
	public function setPageView(Request $request, Response $response, $basetpl = '_page') {
		$this->v = new PageView('', $basetpl);
		$this->v->add_render_filter(function(View $v){
			$v->assign('nav',        $this->nav)
			  ->assign('nav_second', $this->nav_second)
			;
		});
	}
	
	/**
	 * 
	 * @param unknown $module 模块
	 * @param unknown $item 当前项高亮
	 */
	public function setPageLeftMenu($module, $item){
	    $this->v->assign("left_module", $module);
	    $this->v->assign("left_item", $item);
	}
	
	/**
	 * 设置当前系统菜单显示
	 * @param unknown $module
	 */
	public function setSystemNavigate($module){
	    $this->v->assign("s_module", $module);
	}
	
}

/*----- END FILE: MerchantController.php -----*/