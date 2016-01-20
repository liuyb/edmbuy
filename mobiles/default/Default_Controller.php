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
  	/*
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
    */
  	
  	//SEO信息
  	$seo = [
  			'title'   => '你的益多米推广图片',
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
  
  /**
   * action 'app_activate'
   *
   * @param Request $request
   * @param Response $response
   */
  public function app_activate(Request $request, Response $response)
  {
  	$this->v = new PageView('','_page_spa');
  	$this->v->set_tplname('mod_default_app_activate');
  	
  	$cid   = $request->get('cid',0);
  	$refer = $request->get('refer','');
  	$this->v->assign('cid',   $cid);
  	$this->v->assign('refer', $refer);
  	
  	$appUser = TymUser::load($cid);
  	$this->v->assign_by_ref('appUser', $appUser);
  	
  	throw new ViewResponse($this->v);
  }
  
  /**
   * action 'app_doactivate'
   *
   * @param Request $request
   * @param Response $response
   */
  public function app_doactivate(Request $request, Response $response)
  {
  	$this->v = new PageView('','_page_spa');
  	$this->v->set_tplname('mod_default_app_doactivate');
  	
  	$cid   = $request->get('cid',0);
  	$refer = $request->get('refer','');
  	
  	$local_refer = U('/app_doactivate',['cid'=>$cid, 'refer'=>$refer]);
  	if (!Users::is_logined()) { //未登录，先用基本授权(user/oauth会在基本授权获取不到unionid的情况下自动使用详细授权)
  		(new Weixin())->authorizing_base('login_tym', $local_refer);
  	}
  	else {
  		global $user;
  		$situation = 1; //1: 本人和上级都已激活益多米; 2: 本人激活到益多米，但上级未激活; 3: 本身是益多米的用户
  		if ($user->from != TymUser::APP_ID) {
  			$situation = 3;
  		}
  		
  		$appUser = TymUser::load($cid);
  		$appParent = new TymUser();  //主要便于对象操作
  		if (!$appUser->is_exist()) { //本地库不存在，需要查甜玉米的接口
  			//TODO 查询甜玉米的接口
  			$tym_url = sprintf(TymUser::QUERY_URL, $cid);
  		}
  		if (!$appUser->is_exist()) { //可能查询接口后仍然不存在，则提示错误
  			throw new ViewException($this->v, '当前用户不是甜玉米用户，不能平移');
  		}
  		
  		if ($appUser->synctimes > 0) { //自己已同步过，则查询上级是否已同步
  			$appParent = TymUser::load($appUser->parent_userid);
  			if (0==$appParent->synctimes) { //上级没同步过，则提醒上级
  				$situation = 2;
  				//TODO 提醒上级逻辑
  			}
  		}
  		else { //说明自身没同步过数据，则将tb_tym_user中的数据同步到益多米
  			
  			//保存当前甜玉米信息到益多米
  			$upUser = new Users($user->uid);
  			$upUser->mobilephone  = $appUser->mobile;
  			$upUser->regtime      = strtotime($appUser->regtime);
  			$upUser->businessid   = $appUser->business_id;
  			$upUser->businesstime = $appUser->business_time;
  			$upUser->appuserid    = $appUser->userid;
  			
  			//空时才覆盖的信息
  			if (empty($user->nickname)) {
  				$upUser->nickname   = $appUser->nick;
  			}
  			if (empty($user->logo)) {
  				$upUser->logo       = $appUser->logo;
  			}
  			if (empty($user->wxqr)) {
  				$upUser->wxqr       = $appUser->qrcode;
  			}
  			
  			//确认上级
  			$appParent = TymUser::load($appUser->parent_userid);
  			$usrParent = Users::load_by_mobile($appParent->mobile); //这时只能通过手机号来确认上级
  			$now = simphp_time();
  			$situation = 3;
  			if ($now < strtotime(TymUser::MIGRATE_DEADLINE)) { //在封闭期内，则完全同步上下级
  				if ($usrParent->is_exist()) { //上级存在，说明上级已经激活同步
  					$upUser->parentid = $usrParent->uid;
  				}
  				else { //上级没找到，则先不变更已有上级，仅提醒上级激活
  					$situation = 2;
  					//TODO 提醒上级逻辑
  				}
  			}
  			else { //已经出了封闭期，则有上级的不可再变更，没上级的判断是否是来自甜玉米的用户，是的话查找甜玉米的上级来设定
  				if( !$user->parentid && $user->from==TymUser::APP_ID) { //还不存在上级，且当前用户来自于甜玉米，则设定为甜玉米的上级
  					if ($usrParent->is_exist()) { //上级存在，说明上级已经激活同步
  						$upUser->parentid = $usrParent->uid;
  					}
  					else { //上级不存在，说明上级还没激活同步，暂时设上级为平台，同时提醒上级激活
  						$situation = 2;
  						//TODO 提醒上级逻辑
  					}
  				}
  			}
  			
  			//保存平移信息
  			$upUser->save(Storage::SAVE_UPDATE);
  			
  			//更新自身下级的上级为自己
  			$sql = "UPDATE ".Users::table()." SET `parent_id`=%d WHERE `app_userid` IN (SELECT `userid` FROM ".TymUser::table()." WHERE `parent_userid`=%d)";
  			D()->query($sql, $user->uid, $appUser->userid);
  			
  			//更新自身同步状态
  			D()->query("UPDATE ".TymUser::table()." SET synctimes=synctimes+1 WHERE `userid`=%d", $appUser->userid);
  			
  			$user->refresh();
  		}
  		$this->v->assign_by_ref('appUser', $appUser);
  		$this->v->assign_by_ref('appParent', $appParent);
  		$this->v->assign('situation', $situation);
  		$this->v->assign('refer', $refer);
  		
  	}
  	
  	throw new ViewResponse($this->v);
  }
}
 
/*----- END FILE: Default_Controller.php -----*/