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
	
	/**
	 * 设置查询条件里的日期区间
	 * @param unknown $searchinfo
	 */
	public function setQueryDateRange(&$searchinfo){
	    if (strlen($searchinfo['from_date'])!=10) { //format: 'YYYY-MM-DD'
	        $searchinfo['from_date'] = '';
	    }
	    if (strlen($searchinfo['to_date'])!=10) { //format: 'YYYY-MM-DD'
	        $searchinfo['to_date'] = '';
	    }
	    if (!empty($searchinfo['from_date']) && !empty($searchinfo['to_date']) && $searchinfo['from_date'] > $searchinfo['to_date']) { //交换
	        $t = $searchinfo['from_date'];
	        $searchinfo['from_date'] = $searchinfo['to_date'];
	        $searchinfo['to_date'] = $t;
	    }
	}
	
	public function getPagerLimit(){
	    return 30;
	}
	
}

 
/*----- END FILE: AdminController.php -----*/