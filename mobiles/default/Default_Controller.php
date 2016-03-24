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
      'default/about'  => 'about',
      'default/goods' => 'goods_list'
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
  	$this->setPageView($request, $response, '_page_mpa');
    $this->v->set_tplname('mod_default_index');
    $this->nav_no    = 1;
    $this->v->set_page_render_mode(View::RENDER_MODE_GENERAL);
    
    //$pager = new PagerPull(1, 10);
    //首页推荐商品列表
    //Default_Model::findGoodsList($pager, true);
    //$this->v->assign("result", $pager->result);
    //首页
    //拿到限时抢购的商品列表
    $this->get_time_limit_goods();
    
    //分享信息
    $share_info = [
    		'title' => '难得的好商城，值得关注！',
    		'desc'  => '消费购物，推广锁粉，疯狂赚钱统统不耽误',
    		'link'  => U('', 'spm='.Spm::user_spm(), true),
    		'pic'   => U('misc/images/napp/touch-icon-144.png','',true),
    ];
    $this->v->assign('share_info', $share_info);
    
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
    		'link'  => U('riceplan', 'spm='.Spm::user_spm(), true),
    		'pic'   => U('misc/images/napp/touch-icon-144.png','',true),
    ];
    $pager = new PagerPull(1, 6);
    //首页推荐商品列表
    Default_Model::findGoodsList($pager, true);
    $this->v->assign("result", $pager->result);
    $this->v->assign('share_info', $share_info);
    
    throw new ViewResponse($this->v);
  }

  /**
   * action 'activity'
   *
   * @param Request $request
   * @param Response $response
   */
  public function activity(Request $request, Response $response)
  {
  	$this->v = new PageView('','_page_spa');
    $this->v->set_tplname('mod_default_activity');
     
    //SEO信息
    $seo = [
    		'title'   => '益多米年货嘉年华',
    		'keyword' => L('appname').',购物,赚钱,电商,商城',
    		'desc'    => '益多米是新型社交电商购物平台，为广大消费者提供琳琅满目的优质商品，满足大家消费需求的同时，采用三级分销的模式，让消费者转变为消费商，通过分销商品赚取佣金。'
    ];
    $this->v->assign('seo', $seo);
    
    //分享信息
    $share_info = [
    		'title' => '益多米年货嘉年华',
    		'desc'  => '消费购物，推广锁粉，疯狂赚钱统统不耽误',
    		'link'  => U('/activity', 'spm='.Spm::user_spm(), true),
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
  	$cid = intval($cid);
  	$this->v->assign('cid',   $cid);
  	$this->v->assign('refer', $refer);
  	
  	$appUser = TymUser::load($cid);
  	if (!$appUser->is_exist()) { //本地库不存在，需要查甜玉米的接口
  		$appUser = TymUser::queryAndSave($cid);
  	}
  	$this->v->assign_by_ref('appUser', $appUser);
  	
  	if ($appUser->is_exist()) {
  		if ($appUser->synctimes>0) {//同步过，直接跳过激活第一页(对甜玉米用户表，判断是否同步过就看synctimes)
  			$response->redirect(U('app_doactivate',['cid'=>$cid, 'refer'=>$refer]));
  		}
  	}
  	
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
  	$refer = $request->get('refer','/');
  	$this->v->assign('refer', $refer);
  	$cid = intval($cid);
  	
  	if (!Users::is_logined()) { //未登录，直接使用详细授权登录
  		$local_refer = U('app_doactivate',['cid'=>$cid, 'refer'=>$refer]);
  		(new Weixin())->authorizing_detail('login_tym', $local_refer);
  	}
  	else {
  		global $user;
  		$situation = 2; //1: 本人刚激活到益多米，上级也已激活到益多米; 2: 本人刚激活到益多米，但上级尚未激活; 3: 本人之前已激活过
  		
  		//~ 先同步甜玉米的用户过来
  		$appUser = TymUser::load($cid);
  		if (!$appUser->is_exist()) { //本地库不存在，需要查甜玉米的接口
  			$appUser = TymUser::queryAndSave($cid);
  		}
  		
  		//~ 可能查询接口后仍然不存在，则提示错误
  		if (!$appUser->is_exist()) {
  			throw new ViewException($this->v, '当前用户不是甜玉米用户，不能平移，请重新登录甜玉米再试');
  		}
  		
  		//~ 创建空对象便于对象操作
  		$appParent = new TymUser();
  		$usrParent = new Users();
  		
  		//~ 检查本地用户系统
  		if ( !empty($user->appuserid) ) { //对益多米的用户表，判断是否“正式”同步过的唯一依据是app_userid是否已经存在
  			$situation = 3;
  			if ($user->parentid) { //有上级就查询上级信息
  				$usrParent = Users::load($user->parentid);
  			}
  			if (!$appUser->synctimes) { //甜玉米用户表的synctimes可能未设置过，则设置便于其他依赖synctimes的逻辑判断
  				$appUser->updateSynctimes(1);
  			}
  		}
  		else { //没有同步过，则需要做所有同步工作
  			
  			//保存当前甜玉米信息到益多米
  			$upUser = new Users($user->uid);
  			$upUser->mobilephone  = $appUser->mobile; //用当前同步手机覆盖原有可能的手机
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
  				
  			//尝试通过甜玉米的上下级关系来确认在益多米的上级
  			$appParent = TymUser::load($appUser->parent_userid);
  			$can_update_childs = true; //是否能更新下级的上级uid为自身(甜玉米的用户可以，但益多米的用户不行)
  			if ($appParent->is_exist()) { //甜玉米的用户的上级可能会不存在，所以这里要判断一下
  				$usrParent = Users::load_by_appuid($appParent->id); //手机号可能会被覆盖变更，从而变更新的上级，只能通过app_userid来确认
  			}
  			if (!$usrParent->is_exist()) { //表明“暂时”无法通过甜玉米的上下级来确认益多米的上下级，则维持原有上下级不变(包括没有上级的情况)
  				
  			}
  			else { //表明通过甜玉米上下级关系能确定益多米的上下级，则根据是否封闭期的不一样来处理
  				if (simphp_time() < strtotime(TymUser::MIGRATE_DEADLINE)) { //在封闭期内，则完全使用甜玉米的上下级覆盖原有益多米的上下级
  					$upUser->parentid   = $usrParent->uid;
  					$upUser->parentnick = $usrParent->nickname;
  				}
  				else { //已经出了封闭期，则有上级的不可再变更，没上级的判断是否是来自甜玉米的用户，是的话查找甜玉米的上级来设定
  					if ($user->from==TymUser::APP_ID) { //来自甜玉米的用户
  						if( !$user->parentid ) { //还不存在上级，则设定为甜玉米的上级
  							$upUser->parentid   = $usrParent->uid;
  							$upUser->parentnick = $usrParent->nickname;
  						}
  					}
  					else {
  						$can_update_childs = false; //非甜玉米的用户不能批量更新下级关系
  					}
  				}
  			}
  				
  			//保存平移信息
  			$upUser->save(Storage::SAVE_UPDATE);
  				
  			//更新自身下级的上级为自己
  			if ($can_update_childs) {
  				$sql = "UPDATE ".Users::table()." SET `parent_id`=%d WHERE `app_userid` IN (SELECT `userid` FROM ".TymUser::table()." WHERE `parent_userid`=%d)";
  				D()->query($sql, $user->uid, $appUser->userid);
  			}
  				
  			//更新自身同步状态
  			$appUser->incSynctimes();
  			
  			//刷新当前用户信息
  			$user->refresh();
  			
  			//~ 查找在甜玉米上级是否已经激活到益多米
  			$situation = 2; //默认上级未激活
  			$appParent = TymUser::load($appUser->parent_userid);
  			if ($appParent->is_exist()) { //甜玉米的用户的上级可能会不存在，所以这里要判断一下
  				$usrParent = Users::load_by_appuid($appParent->id); //手机号可能会被覆盖变更，从而变更新的上级，只能通过app_userid来确认
  				if ($usrParent->is_exist()) { //上级也已经激活到益多米
  					$situation = 1;
  				}
  				else { //上级还没激活到益多米
  					//TODO 提醒上级激活
  				}
  			}
  			
  		}//END 没有同步过，则需要做所有同步工作
  		
  		$this->v->assign_by_ref('appParent', $appParent);
  		$this->v->assign_by_ref('usrParent', $usrParent);
  		$this->v->assign('situation', $situation);
  		
  	}
  	
  	throw new ViewResponse($this->v);
  }
  
  /**
   * 限时抢购
   */
  public function get_time_limit_goods(){
      $package = Default_Model::find_time_limit_activity();
      if(!empty($package)){
          $limit_goods = Default_Model::find_time_limit_goods_list($package['act_id']);
          if(!empty($limit_goods) && count($limit_goods) >0){
              $this->v->assign('package', $package);
              $this->v->assign('limit_goods', $limit_goods);
          }
      }
  }
  
  /**
   * 首页面 商品列表展示
   * @param Request $request
   * @param Response $response
   */
  public function goods_list(Request $request, Response $response){
      $curpage = isset($_REQUEST['curpage']) ? $_REQUEST['curpage'] : 1;
      $pager = new PagerPull($curpage, 50);
      $category = $request->get('category', Default_Model::CATEGORY_EAT);
      Default_Model::findGoodsListByCategory($pager, $category);
      $pageJson = $pager->outputPageJson();
      $ret = ["result" => $pager->result];
      if (!empty($ret['result'])) {
      	foreach ($ret['result'] AS &$it) {
      		$it['goods_name']  = str_replace(["\n","\r"], [" ",""], $it['goods_name']);
      		$it['goods_brief'] = str_replace(["\n","\r"], [" ",""], $it['goods_brief']);
      	}
      }
      $ret = array_merge($ret, $pageJson);
      $response->sendJSON($ret);
  }
  
}
