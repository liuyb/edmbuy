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
	protected $backurl    = '';      //back url
	protected $extra_css  = '';      //添加给section.scrollArea的额外css类
	
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
	}
	
	/**
	 * set page view
	 * @param Request $request
	 * @param Response $response
	 * @param string $basetpl
	 */
	public function setPageView(Request $request, Response $response, $basetpl = '_page') {
		$this->v = new PageView('', $basetpl);
		$sys_css = '';
		if ($basetpl=='_page_mpa') {
			if ($request->isIOS()) {
				$sys_css = 'iOS';
			}
		}
		$this->v->add_render_filter(function(View $v) use ($sys_css){
			if (''!=$sys_css) {
				$this->extra_css = $sys_css . (''==$this->extra_css?'':' ') . $this->extra_css;
			}
			$v->assign('nav_no',  $this->nav_no)
			->assign('topnav_no',  $this->topnav_no)
			->assign('nav_flag1',  $this->nav_flag1)
			->assign('nav_flag2',  $this->nav_flag2)
			->assign('backurl',    $this->backurl)
			->assign('extra_css',  $this->extra_css)
			;
		});
		$cart_num = 0;
		if ($GLOBALS['user']->uid) {
			$cart_num = $GLOBALS['user']->cart_num();
		}
		$this->v->assign('user_cart_num', $cart_num);
	}
	
}
 
/*----- END FILE: MobileController.php -----*/