<?php
/**
 * 默认(一般首页)模块控制器，此控制器必须
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Default_Controller extends MobileController {
  
  /**
   * hook init
   * 
   * @param string $action
   * @param Request $request
   * @param Response $response
   */
  public function init($action, Request $request, Response $response)
  {
    $this->nav_flag1 = 'home';
		parent::init($action, $request, $response);
  }
  
  /**
   * hook menu
   * @see Controller::menu()
   */
  public function menu()
  {
    return [
      'default/about'  => 'about'
    ];
  }
  
  /**
   * default action 'index'
   * 
   * @param Request $request
   * @param Response $response
   */
  public function index(Request $request, Response $response)
  {
    $this->v->set_tplname('mod_default_index');
    $this->nav_no    = 1;
    $this->v->set_page_render_mode(View::RENDER_MODE_GENERAL);
    
    $debug = $request->get('debug',0);
    if (!$debug) {
    	Fn::show_error_message('页面开发中，敬请关注...', false, '页面提示');
    }
    
    throw new ViewResponse($this->v);
  }

  /**
   * action 'about'
   *
   * @param Request $request
   * @param Response $response
   */
  public function about(Request $request, Response $response)
  {
  	$this->v = new PageView('','_page_spa');
    $this->v->set_tplname('mod_default_about');
    $this->v->assign('extra_css', 'greybg');
    
    if (!Users::is_logined()) {
    	throw new ViewException($this->v, '未登录，需要在微信客户端中登录');
    }
    else {
    	//重定向请求OAuth2详细认证获取资料
    	Users::check_detail_info(U('about'));
    }
    
    $type = $request->get('t','');
    
    //分享信息
    $share_info = [
    		'title' => '益多米是什么？',
    		'desc'  => '益多米是新型社交电商购物平台，为广大消费者提供琳琅满目的优质商品，满足大家消费需求的同时，采用三级分销的模式，让消费者转变为消费商，通过分销商品赚取佣金。',
    		'link'  => U('about', 'spm='.Spm::user_spm(), true),
    		'pic'   => U('misc/images/napp/touch-icon-144.png','',true),
    ];
    
    if (''!==$type) {
    	$share_info['title'] = '益多米';
    	$share_info['link'] .= '&t='.$type;
    	$this->v->set_tplname('mod_default_'.$type);
    }
    
    $this->v->assign('share_info', $share_info);
    
    throw new ViewResponse($this->v);
  }

  /**
   * action 'riceplan'
   *
   * @param Request $request
   * @param Response $response
   */
  public function riceplan(Request $request, Response $response)
  {
  	$this->v = new PageView('','_page_spa');
    $this->v->set_tplname('mod_default_riceplan');
    
    if (!Users::is_logined()) {
    	throw new ViewException($this->v, '未登录，需要在微信客户端中登录');
    }
    else {
    	//重定向请求OAuth2详细认证获取资料
    	Users::check_detail_info(U('riceplan'));
    }
     
    //分享信息
    $share_info = [
    		'title' => '米商专属特权',
    		'desc'  => '益多米是新型社交电商购物平台，为广大消费者提供琳琅满目的优质商品，满足大家消费需求的同时，采用三级分销的模式，让消费者转变为消费商，通过分销商品赚取佣金。',
    		'link'  => U('about', 'spm='.Spm::user_spm(), true),
    		'pic'   => U('misc/images/napp/touch-icon-144.png','',true),
    ];
    $this->v->assign('share_info', $share_info);
    
    throw new ViewResponse($this->v);
  }
  
  /**
   * action 'comeon'
   * 
   * @param Request $request
   * @param Response $response
   */
  public function comeon(Request $request, Response $response)
  {
  	$this->v = new PageView('','_page_spa');
  	$this->v->set_tplname('mod_default_comeon');
  	
  	if (!Users::is_logined()) {
  		throw new ViewException($this->v, '未登录，需要在微信客户端中登录');
  	}
  	else {
  		$check = $request->get('check',0);
  		if (!$check && $GLOBALS['user']->required_uinfo_empty()) {
  			$this->v->assign('go_oauth_uri', U('comeon',['check'=>1]));
  			throw new ViewException($this->v, 'required_oauth');
  		}
  		else {
  			//重定向请求OAuth2详细认证获取资料
  			Users::check_detail_info(U('comeon'));
  		}
  	}
  	
  	$qrimg = $GLOBALS['user']->wx_qrpromote();
  	$this->v->assign('qrimg', $qrimg);
  	
  	//推荐人
  	$promoter    = [];
  	$parent_user = Users::load($GLOBALS['user']->parentid);
  	if ($parent_user->is_exist()) {
  		$promoter['uid'] = $parent_user->uid;
  		$promoter['nickname'] = $parent_user->nickname;
  	}
  	$this->v->assign('promoter', $promoter);
  	
  	//当前spm
  	$curr_promoter = [];
    $spm = Spm::check_spm();
    if ($spm && preg_match('/^user\.(\d+)$/', $spm, $matchspm)) {
    	$curr_promote_user = Users::load($matchspm[1]);
    	if ($curr_promote_user->is_exist()) {
    		$curr_promoter['uid'] = $curr_promote_user->uid;
    		$curr_promoter['nickname'] = $curr_promote_user->nickname;
    	}
    }
    $this->v->assign('curr_promoter', $curr_promoter);
  	
  	//SEO信息
  	$seo = [
  			'title'   => L('appname'),
  			'keyword' => L('appname').',购物,赚钱,电商,商城',
  			'desc'    => '上联: 一次购物两个身份三级佣金四季发财;下联:五天推广六千人脉七万营收八面威风;横批:购物赚钱。购物就上益多米，放心购物，轻松赚钱，品质生活从这里开始'
  	];
  	$this->v->assign('seo', $seo);
  	
    //分享信息
    $share_info = [
    		'title' => '上联: 一次购物两个身份三级佣金四季发财;下联:五天推广六千人脉七万营收八面威风;横批:购物赚钱',
        'desc'  => '购物就上益多米，放心购物，轻松赚钱，品质生活从这里开始',
        'link'  => U('comeon', 'spm='.Spm::user_spm(), true),
        'pic'   => U('misc/images/napp/touch-icon-144.png','',true),
    ];
  	$this->v->assign('share_info', $share_info);
  	
  	throw new ViewResponse($this->v);
  }
  
}
 
/*----- END FILE: Default_Controller.php -----*/