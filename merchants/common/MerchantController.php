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
	
	protected $page_size = 10;
	
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
		$rq = $request->q();
		$q = explode('/', $rq);
		if($q && count($q) > 0){
		    $q = $q[0];
		    if($q == 'user'){//默认是user/index
		        $this->setSystemNavigate('index');
		    }else{
        		$this->setSystemNavigate($q);
		    }
		}else{
		    $this->setSystemNavigate('index');
		}
		$shop = Merchant::load($GLOBALS['user']->uid);
		$this->v->assign('global_shop', $shop);
		//得到企业客服ID，用来全局判断用户是否开通了客服
		$ent_id = Merchant::getMerchantKefuEntId($GLOBALS['user']->uid);
		$this->v->assign('ent_id', $ent_id);
		//检查商家是否支付
		$checkIgnore = false;
  		foreach(self::$interceptWhiteList AS $key) {
  		    if (SimPHP::qMatchPattern($key, $rq)) {
  		        $checkIgnore = true;
  		        break;
  		    }
  		}
		if($checkIgnore){
		    return;
		}
  		//商家没有激活处理
	    if(!$shop->activation){
	        $response->redirect('/shop/need/pay');
	    }
	    
	    //店铺模块需要ajax等请求，不能作跳转处理
	    if($q && $q == 'shop'){
	        return;
	    }
	    
		if($rq != 'shop/start'){
    		if(!$shop->is_completed){
    		    $response->redirect('/shop/start?incomplete=1');
    		}
		}
	}
	
	/**
	 * 请求拦截白名单，当前地址不需要做店铺拦截处理
	 * @var array
	 */
	public static $interceptWhiteList = [
	  	'user/login',
  		'user/logout',
	  	'user/forgetPwd',
	  	'user/checkSmsCode',
	  	'user/setpassword',
	  	'user/getPhoneCodeAjax',
	  	'user/forgotSavePwd',
	    'user/open/shop/guide',
	    'shop/need/pay'
	];
	
	/**
	 * set page view
	 * @param Request $request
	 * @param Response $response
	 * @param string $basetpl
	 */
	public function setPageView(Request $request, Response $response, $basetpl = '_page') {
		$this->v = new PageView('', $basetpl);
		$this->page_render_mode = View::RENDER_MODE_GENERAL;
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
	
	/**
	 * 当前用户是否是本人鉴权
	 * @param unknown $merchant_id
	 */
	public function checkPermission($merchant_id){
	    if($merchant_id != $GLOBALS['user']->uid){
	        Fn::show_pcerror_message();
	    }
	}
	
	/**
	 * Merchant 默认的pagesize
	 */
	public function getPageSize(){
	    return $this->page_size;
	}
	
}

/*----- END FILE: MerchantController.php -----*/