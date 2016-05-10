<?php
/**
 * Mall Controller
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class User_Controller extends MobileController
{

    private $user_header_img_dir = '/a/user/logo/';

    private $user_qrcode_img_dir = '/a/user/qrcode/';

    public function menu()
    {
        return [
            'user' => 'index',
            'user/index/ajax' => 'index_ajax',
            'user/setting' => 'user_setting',
            'user/logo/upload' => 'upload_logo',
            'user/wxqr/show' => 'show_wxqr',
            'user/wxqr/update' => 'update_wxqr',
            'user/mobile/show' => 'show_mobile',
            'user/mobile/update' => 'update_mobile',
            'user/oauth/%s' => 'oauth',
            'user/commission' => 'commission',
            'user/nickname/update' => 'update_nickname',
            'user/merchant/checkin' => 'merchant_checkIn',
            'user/merchant/payment' => 'merchant_payment',
            'user/merchant/getcode' => 'merchant_getcode',
            'user/merchant/openshop' => 'merchant_openshop_onestep',
            'user/merchant/doonestep' => 'merchant_do_onestep',
            'user/merchant/twostep' => 'merchant_shop_twostep',
            'user/merchant/dotwostep' => 'merchant_shop_dotwostep',
            'user/merchant/dosuccess' => 'merchant_reg_succ',
            'user/merchant/paysuc' => 'merchant_pay_success',
            'user/favorite/shop' => 'show_collect_shop',
            'user/favorite/shop/list' => 'show_collect_shoplist',
            'user/favorite/goods' => 'show_collect_goods',
            'user/favorite/goods/list' => 'show_collect_goodslist',
            'user/my/wallet' => 'my_wallet',
            'user/income/detail' => 'my_income_detail',
            'user/income/detail/list' => 'my_income_detaillist',
            'user/pay/merchant/order' => 'pay_merchant_order'
        ];
    }

    /**
     * hook init
     *
     * @param string $action
     * @param Request $request
     * @param Response $response
     */
    public function init($action, Request $request, Response $response)
    {
        $this->nav_flag1 = 'user';
        parent::init($action, $request, $response);
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
        $this->v->set_page_render_mode(View::RENDER_MODE_GENERAL);
        $this->v->set_tplname('mod_user_index');
        //检查用户信息完成度，nickname或logo没有的话都重定向请求OAuth2详细认证获取资料
        Users::check_detail_info();
        $u = $this->showUserBaseInfo();
        $agent = AgentPayment::getAgentByUserId($u->uid, $u->level);
        $this->v->assign('agent', $agent);
        $collect_shop_count = User_Model::getCollectShopCount();
        $collect_goods_count = User_Model::getCollectGoodsCount();
        
        $is_account_logined  = Users::is_account_logined();
        $this->v->assign('shop_count', $is_account_logined ? $collect_shop_count : 0);
        $this->v->assign('goods_count', $is_account_logined ?$collect_goods_count: 0);
        $this->v->assign('is_account_logined', $is_account_logined);
        throw new ViewResponse($this->v);
    }

    public function index_ajax(Request $request, Response $response)
    {
        $uid = $GLOBALS['user']->uid;
        $status = array('pay_status' => constant('PS_UNPAYED'), 'shipping_status' => array(constant('SS_UNSHIPPED'), constant('SS_SHIPPED')));
        $orderStatusMap = User_Model::findOrderStatusCountByUser($uid, $status);
        $response->sendJSON($orderStatusMap);
    }

    /**
     * 用户信息设置
     * @param Request $request
     * @param Response $response
     * @throws ViewResponse
     */
    public function user_setting(Request $request, Response $response)
    {
        $this->setPageView($request, $response, '_page_mpa');
        $this->v->set_tplname('mod_user_setting');
        $this->topnav_no = 1;
        if ($request->is_hashreq()) {
            //$user = $this->showUserBaseInfo();
        }
        $user = $this->showUserBaseInfo();
        throw new ViewResponse($this->v);
    }

    public function upload_logo(Request $request, Response $response)
    {
        if ($request->is_post()) {
            $ret = $this->uploadImg($this->user_header_img_dir);

            if ($ret['flag'] == 'SUC') {
                User_Model::updateUserInfo(array('logo' => $ret['result']));
            }

            $response->sendJSON($ret);
        }
    }

    public function show_wxqr(Request $request, Response $response)
    {
        $this->setPageView($request, $response, '_page_mpa');
        $this->v->set_tplname('mod_user_wxqr');
        $this->topnav_no = 1;
        if (1 || $request->is_hashreq()) {
            $user = $this->showUserBaseInfo();
        }
        throw new ViewResponse($this->v);
    }

    public function update_wxqr(Request $request, Response $response)
    {
        if ($request->is_post()) {
            $ret = $this->uploadImg($this->user_qrcode_img_dir);

            if ($ret['flag'] == 'SUC') {
                User_Model::updateUserInfo(array('wxqr' => $ret['result']));
            }

            $response->sendJSON($ret);
        }
    }

    private function uploadImg($imgDIR)
    {
        $img = $_POST["img"];
        $upload = new Upload($img, $imgDIR);
        $upload->fixed_id = $GLOBALS['user']->uid;
        $result = $upload->saveImgData();
        $ret = $upload->buildUploadResult($result);
        return $ret;
    }

    public function show_mobile(Request $request, Response $response)
    {
        $this->setPageView($request, $response, '_page_mpa');
        $this->v->set_tplname('mod_user_mobile');
        $this->topnav_no = 1;
        if (1 || $request->is_hashreq()) {
            $mobile = $request->get('mobile', '');
            $nickname = $request->get('nickname', '');
            $this->v->assign('ismoblie', false);
            if (is_numeric($mobile)) {
                $this->v->assign('ismoblie', true);
            }
            $this->v->assign('nickname', $nickname);
            $this->v->assign('mobile', $mobile);
        }
        throw new ViewResponse($this->v);
    }

    public function update_mobile(Request $request, Response $response)
    {
        if ($request->is_post()) {
            $mobile = isset($_POST['mobile']) ? $_POST['mobile'] : '';
            global $user;
            if ($user->mobilephone == $mobile) {
                return;
            }
            $nuser = Users::load_by_mobile($mobile);
            if ($nuser && !empty($nuser->uid)) {
                $ret = ['result' => 'FAIL', 'msg' => '手机号已经在系统存在！'];
                $response->sendJSON($ret);
            }
            User_Model::updateUserInfo(array('mobilephone' => $mobile));
            $ret = ['result' => 'SUC', 'msg' => '修改成功'];
            $response->sendJSON($ret);
        }
    }

    /**
     * 登录
     *
     * @param Request $request
     * @param Response $response
     */
    public function login(Request $request, Response $response)
    {
        $refer = $request->url();
        if (!Users::is_logined()) {
            $token = $request->get('token', '');
            if ('' != $token) { //token登录优先，便于测试
                $this->tokenLogin($request, $response);
            } elseif (!Weixin::isWeixinBrowser()) { //不是微信内置浏览器
                $this->tips($request, $response);
            } else { //先用base方式获取微信OAuth2授权，以便于取得openid
                (new Weixin())->authorizing_base('login', $refer);
            }
        } else {
            $response->redirect($refer);
        }
    }

    /**
     * 手机帐号登录
     *
     * @param Request $request
     * @param Response $response
     */
    public function login_account(Request $request, Response $response)
    {
    	if ($request->is_post()) {
    		
    	}
    	else { //登录页面
    		$this->v->set_tplname('mod_user_login_account');
    		throw new ViewResponse($this->v);
    	}
    }

    /**
     * 手机帐号登录
     *
     * @param Request $request
     * @param Response $response
     */
    public function logout_account(Request $request, Response $response)
    {
    	if ($request->is_post()) {
    		
    	}
    	else { //登录页面
				if (isset($_SESSION[Users::AC_LOGINED_KEY])) {
					unset($_SESSION[Users::AC_LOGINED_KEY]);
				}
				
				// Reload current pag
				$response->redirect('/');
    	}
    }
    
    /**
     * 手机帐号注册
     * 
     * @param Request $request
     * @param Response $response
     */
    public function reg_account(Request $request, Response $response)
    {
    	if ($request->is_post()) {
    	
    	}
    	else { //注册页面
    		$this->v->set_tplname('mod_user_reg_account');
    		$step = $request->get('step', 1);
    		$this->v->assign('step', $step);
    		throw new ViewResponse($this->v);
    	}
    }

    /**
     * action 'go_detail_oauth', request detail oauth
     * @param Request $request
     * @param Response $response
     */
    public function go_detail_oauth(Request $request, Response $response)
    {
        $refer = $request->get('refer', '/');
        (new Weixin())->authorizing_detail('', $refer);
    }

    /**
     * action 'oauth', the OAuth callback
     *
     * @param Request $request
     * @param Response $response
     */
    public function oauth(Request $request, Response $response)
    {
        //trace_debug('weixin_oauth2_callback_doing', $_GET);
        $code = $request->get('code', '');
        $code = trim($code);
        if ('' == $code) { //授权未通过
            Fn::show_error_message('未授权，不能访问应用！');
        }

        //~ 授权通过逻辑
        $state = $request->get('state', '');
        $refer = $request->get('refer', '/');
        $from = $request->arg(2);
        if (empty($from)) $from = 'weixin';
        $auth_action = $request->get('act', '');

        //收获地址base oauth回调
        if ('jsapi_address' == $auth_action) {
            $response->redirect($refer . '&code=' . $code . '&state=' . $state);
        }

        //授权出错
        if (!in_array($state, Weixin::$allowOAuthScopes)) {
            Fn::show_error_message('授权出错，不能访问应用！');
        }

        $wx = new Weixin();

        //用code换取access token
        $code_ret = $wx->request_access_token($code);
        if (!empty($code_ret['errcode'])) {
            Fn::show_error_message('微信授权错误<br/><span style="font-size:16px;">' . $code_ret['errcode'] . '(' . $code_ret['errmsg'] . ')</span>');
        }

        //获取到openid
        $openid = $code_ret['openid'];
        $unionid = isset($code_ret['unionid']) ? $code_ret['unionid'] : '';
        //trace_debug('weixin_oauth2_code_set', $code_ret);

        if (!empty($unionid)) {
            $localUser = Users::load_by_unionid($unionid);
        } else {
            $localUser = Users::load_by_openid($openid);
        }

        //查询本地是否存在对应openid的用户
        $auth_method = "oauth2_{$state}"; //OAuth2认证方式
        $loginedUser = $localUser;
        if ($localUser->is_exist()) { //用户已存在，如果对base授权，不用进行保存操作(没有额外信息可保存)；如果对detail授权，则要保存详细信息，但不会变更上下级关系

            if (Weixin::OAUTH_BASE == $state) {
                if (empty($localUser->openid)) { //openid为空，就更新
                    D()->update(Users::table(), ['openid' => $openid], ['user_id' => $localUser->uid]);
                }
                if (empty($localUser->nickname) || empty($localUser->logo)) { //基本登录后，如果昵称或头像没有设置，则重定向到详细授权获取信息
                    $wx->authorizing_detail($auth_action, $refer);
                }
            } elseif (Weixin::OAUTH_DETAIL == $state) { //detail认证模式，需更新用户数据

                $uinfo_wx = $wx->userInfoByOAuth2($openid, $code_ret['access_token']);
                if (!empty($uinfo_wx['errcode'])) { //失败！则报错
                    Fn::show_error_message('微信获取用户信息出错！<br/><span style="font-size:16px;">' . $uinfo_wx['errcode'] . '(' . $uinfo_wx['errmsg'] . ')</span>');
                }

                //保存微信用户信息到本地库
                $upUser = new Users($localUser->uid);
                $upUser->openid = $openid;
                $upUser->lasttime = simphp_dtime();
                $upUser->lastip = Request::ip();
                if (isset($uinfo_wx['subscribe'])) {
                    $upUser->subscribe = $uinfo_wx['subscribe'];
                    $upUser->subscribetime = $uinfo_wx['subscribe_time'];
                }
                if (empty($localUser->nickname)) {
                    $upUser->nickname = isset($uinfo_wx['nickname']) ? $uinfo_wx['nickname'] : '';
                }
                if (empty($localUser->logo)) {
                    $upUser->logo = isset($uinfo_wx['headimgurl']) ? $uinfo_wx['headimgurl'] : '';
                }
                if (empty($localUser->sex)) {
                    $upUser->sex = isset($uinfo_wx['sex']) ? $uinfo_wx['sex'] : 0;
                }
                if (empty($localUser->lang)) {
                    $upUser->lang = isset($uinfo_wx['language']) ? $uinfo_wx['language'] : '';
                }
                if (empty($localUser->country)) {
                    $upUser->country = isset($uinfo_wx['country']) ? $uinfo_wx['country'] : '';
                }
                if (empty($localUser->province)) {
                    $upUser->province = isset($uinfo_wx['province']) ? $uinfo_wx['province'] : '';
                }
                if (empty($localUser->city)) {
                    $upUser->city = isset($uinfo_wx['city']) ? $uinfo_wx['city'] : '';
                }

                //尝试用基本型接口获取用户信息，以便确认用户是否已经关注(基本型接口存在 50000000次/日 调用限制，且仅对关注者有效)
                if (!$localUser->subscribe && !$upUser->subscribe) {
                    $uinfo_wx = $wx->userInfo($openid);
                    if (!empty($uinfo_wx['errcode'])) { //失败！说明很可能没关注，维持现状不处理

                    } else { //成功！说明之前已经关注，得更新关注标记
                        $upUser->subscribe = isset($uinfo_wx['subscribe']) ? $uinfo_wx['subscribe'] : 0;
                        if ($upUser->subscribe) {
                            $upUser->subscribetime = $uinfo_wx['subscribe_time'];
                        }
                    }
                }

                $upUser->save(Storage::SAVE_UPDATE);
                $loginedUser = $upUser;

            } //End: if (Weixin::OAUTH_DETAIL===$state)

        } else { //用户不存在，则要尝试建立

            //用base授权获取不到unionid(从2016-01-16日开始，snsapi_base不返回unionid，微信你大爷！)，则要接着用detail授权
            if (empty($unionid) || Weixin::OAUTH_BASE == $state) {
                $wx->authorizing_detail($auth_action, $refer);
            }

            $upUser = new Users();
            $upUser->unionid = $unionid;
            $upUser->openid = $openid;
            $upUser->regip = $request->ip();
            $upUser->regtime = simphp_time();
            $upUser->lasttime = simphp_dtime();
            $upUser->lastip = $request->ip();
            $upUser->salt = gen_salt();
            $upUser->parentid = 0;
            $upUser->state = 0; //0:正常;1:禁止
            $upUser->from = $auth_action == 'login_tym' ? TymUser::APP_ID : $from;
            $upUser->authmethod = $auth_method;

            //检查spm
            $parent_id = 0;
            $parent_nick = '';
            $spm = Spm::check_spm($refer);
            if ($spm && preg_match('/^user\.(\d+)(\.\w+)?$/i', $spm, $matchspm)) {
                $pUser = Users::load($matchspm[1]);
                if ($pUser->is_exist()) {
                    $parent_id = $pUser->id;
                    $parent_nick = $pUser->nickname;
                }
            }
            $upUser->parentid0 = $parent_id;
            //$upUser->parentnick = $parent_nick;

            $upUser->save(Storage::SAVE_INSERT_IGNORE); //先快速保存insert
            $upUser = new Users($upUser->id);    //再新建一个对象更新，避免过多并发重复插入

            if (Weixin::OAUTH_DETAIL == $state) { //对不存在的用户，初始登录使用detail授权，则得保存用户详细信息

                $uinfo_wx = $wx->userInfoByOAuth2($openid, $code_ret['access_token']);
                if (!empty($uinfo_wx['errcode'])) { //失败！则报错
                    Fn::show_error_message('微信获取用户信息出错！<br/><span style="font-size:16px;">' . $uinfo_wx['errcode'] . '(' . $uinfo_wx['errmsg'] . ')</span>');
                }

                $upUser->subscribe = isset($uinfo_wx['subscribe']) ? $uinfo_wx['subscribe'] : 0;
                $upUser->subscribetime = isset($uinfo_wx['subscribetime']) ? $uinfo_wx['subscribetime'] : 0;
                $upUser->nickname = isset($uinfo_wx['nickname']) ? $uinfo_wx['nickname'] : '';
                $upUser->logo = isset($uinfo_wx['headimgurl']) ? $uinfo_wx['headimgurl'] : '';
                $upUser->sex = isset($uinfo_wx['sex']) ? $uinfo_wx['sex'] : 0;
                $upUser->lang = isset($uinfo_wx['language']) ? $uinfo_wx['language'] : '';
                $upUser->country = isset($uinfo_wx['country']) ? $uinfo_wx['country'] : '';
                $upUser->province = isset($uinfo_wx['province']) ? $uinfo_wx['province'] : '';
                $upUser->city = isset($uinfo_wx['city']) ? $uinfo_wx['city'] : '';

                //尝试用基本型接口获取用户信息，以便确认用户是否已经关注(基本型接口存在 50000000次/日 调用限制，且仅对关注者有效)
                if (!$upUser->subscribe) {
                    $uinfo_wx = $wx->userInfo($openid);
                    if (!empty($uinfo_wx['errcode'])) { //失败！说明很可能没关注，维持现状不处理

                    } else { //成功！说明之前已经关注，得更新关注标记
                        $upUser->subscribe = $uinfo_wx['subscribe'];
                        $upUser->subscribetime = $upUser->subscribe ? $uinfo_wx['subscribe_time'] : 0;
                    }
                }

            }

            $upUser->save(Storage::SAVE_UPDATE);
            $loginedUser = $upUser;

            //微信模板消息通知
            if (0&&$parent_id) {
                $loginedUser = Users::load($upUser->id, true);
                $loginedUser->notify_reg_succ();
            }

        } //END: if ($localUser->is_exist()) else

        //设置本地登录状态
        if (preg_match('/^login/i', $auth_action)) {

            if (!$loginedUser->is_exist()) {
                Fn::show_error_message('微信授权登录失败！');
            }

            $loginedUser->set_logined_status();
        }

        //跳转
        $response->redirect($refer);
    }

    /**
     * 退出登录
     *
     * @param Request $request
     * @param Response $response
     */
    public function logout(Request $request, Response $response)
    {
        // Unset all of the session variables.
        session_destroy();
        $_SESSION = array();

        // If it's desired to kill the session, also delete the session cookie.
        // Note: This will destroy the session, and not just the session data!
        if (isset($_COOKIE[session_name()])) {
            Cookie::raw_remove(session_name());
        }

        // Finally, destroy the session.
        SIMPHP::$session->anonymous_user($GLOBALS['user']);

        // Reload current pag
        $response->reload();
    }

    /**
     * token登录(用于测试)
     *
     * @param Request $request
     * @param Response $response
     */
    public function tokenLogin(Request $request, Response $response)
    {

        //检查token
        $token = $request->get('token', '');
        if ('' == $token) {
            $this->tips($request, $response);
        }

        //检查数据库token，以获取openid或unionid
        $unionid = User_Model::checkAccessToken($token, 'unionid');
        if (FALSE === $unionid) {
            $this->tips($request, $response);
        }

        //通过openid或unionid获取用户信息
        $user = Users::load_by_unionid($unionid);
        if (!$user->is_exist()) {
            Fn::show_error_message();
        }

        //设置本地登录状态
        $user->set_logined_status();

        //Token登录后去到当前页(避免session没写成功走正常流程)
        $response->redirect(preg_replace('/\??&?token=[a-z0-9]+/i', '', $request->url()));
    }

    /**
     * Tips页显示
     * @param Request $request
     * @param Response $response
     */
    public function tips(Request $request, Response $response)
    {
        $this->v = new PageView('', 'tips');
        $q = $request->q();
        $qrcode = '/misc/images/qrcode/edmbuy_258.jpg';
        if (preg_match('/^item\/(\d+)$/', $q, $matches)) {

            $id = $matches[1];
            $item = Items::load($id);
            if ($item->is_exist()) {
                $dir = Fn::gen_qrcode_dir($id, 'item', true);
                $locfile = $dir . $id . '.png';
                if (!file_exists($locfile)) {
                    if (mkdirs($dir)) {
                        $qrinfo = $item->url('qrcode');
                        include_once SIMPHP_INCS . '/libs/phpqrcode/qrlib.php';
                        QRcode::png($qrinfo, $locfile, QR_ECLEVEL_L, 7, 3);
                        if (file_exists($locfile)) {
                            $qrcode = str_replace(SIMPHP_ROOT, '', $locfile);
                        }
                    }
                } else {
                    $qrcode = str_replace(SIMPHP_ROOT, '', $locfile);
                }
            }

        }
        $this->v->assign('qrcode', $qrcode);
        $response->send($this->v);
    }

    public function showUserBaseInfo()
    {
        $uid = $GLOBALS['user']->uid;
        $currentUser = Users::load($uid);
        $this->v->assign("uid", $uid);
        $this->v->assign('curuser', $currentUser);
        $this->v->assign("nickname", $currentUser->nickname);
        $this->v->assign("level", $currentUser->level);
        $this->v->assign("logo", $currentUser->logo);
        $this->v->assign("mobile", $currentUser->mobilephone);
        $this->v->assign("wxqr", $currentUser->wxqr);
        if ($currentUser->parentid) {
            $parentUser = User_Model::findUserInfoById($currentUser->parentid);
            $this->v->assign("parentUid", $parentUser->uid);
            $this->v->assign("parentNickName", $parentUser->nickname);
            $this->v->assign("parentLevel", $parentUser->level);
            $this->v->assign("ParentMobile", $parentUser->mobilephone);
            $this->v->assign("ParentWxqr", $parentUser->wxqr);
        }
        return $currentUser;
    }

    public function chmobile(Request $request, Response $response)
    {
        if ($request->is_post()) {
            $mobile = $request->post('mobile', '');
            $ret = ['flag' => 'FAIL', 'msg' => ''];
            if (!$mobile || !preg_match('/^\d{11,15}$/', $mobile)) {
                $ret['msg'] = '手机号非法';
                $response->sendJSON($ret);
            }

            global $user;
            if (!$user->mobilephone) { //有手机号不给覆盖
                $upUser = new Users($user->uid);
                $upUser->mobilephone = $mobile;
                $upUser->save(Storage::SAVE_UPDATE);
            }
            $ret = ['flag' => 'SUCC', 'msg' => '更新成功'];

            $response->sendJSON($ret);
        }
    }


    /**
     * 通知完善资料
     *
     * @param Request $request
     * @param Response $response
     */
    public function notify_profile(Request $request, Response $response)
    {
        if ($request->is_post()) {
            $target_uid = $request->post('user_id', 0);

            global $user;
            $ret = ['flag' => 'SUCC', 'msg' => '通知成功'];
            if (!$user->uid) {
                $ret = ['flag' => 'FAIL', 'msg' => '请先登录'];
                $response->sendJSON($ret);
            }
            if (!$target_uid) {
                $ret = ['flag' => 'FAIL', 'msg' => 'target uid无效'];
                $response->sendJSON($ret);
            }

            $targetUser = Users::load($target_uid);
            if ($targetUser->is_exist() && !empty($targetUser->openid)) {
                $extra = ['org_name' => '益多米', 'info_required' => '个人信息', 'info_remark' => '手机号和微信二维码'];
                WxTplMsg::required_info($targetUser->openid, "你的米友({$user->nickname})希望你尽快完善资料", '完善个人信息，方便米友跟你一起学习成长！', U('user/setting', '', true), $extra);
            }

            $response->sendJSON($ret);
        }
    }

    /**
     * 通知页面
     * @param Request $request
     * @param Response $response
     */
    public function commission(Request $request, Response $response)
    {
        $this->v->set_tplname('mod_user_commission');
        $this->v->set_page_render_mode(View::RENDER_MODE_GENERAL);
        $this->nav_no = 0;
        $user_id = $request->get("user_id", 0);
        $order_id = $request->get("order_id", 0);
        $level = $request->get('level', "1");
        $uid = $GLOBALS['user']->uid;
        //if ($request->is_hashreq()) {
            $flat = true;
            //是否为其他人浏览
            if ($user_id != $uid) {
                $flat = false;
            }
            $cUser = User_Model::findUserInfoById($user_id);
            //根据user_id获得用户的信息 获取每一个订单号下的商品
            $data = User_Model::getOrderInfo($order_id, $user_id);
            $this->v->assign('userInfo', $data['userInfo']);
            $this->v->assign('goodsInfo', $data['goodsInfo']);
            $this->v->assign('level', $level);
            $this->v->assign("is_user", $flat);
            $this->v->assign("cUser", $cUser);
            $this->v->assign('ismBusiness', $data['ismBusiness']);
            $this->v->assign('commision', $data['commision']);
        //} else {
            //分享信息
            $share_info = [
                'title' => '收藏了很久的特价商城，超划算！',
                'desc' => '便宜又实惠，品质保证，生活中的省钱利器！',
                'link' => U('/user/commission', 'order_id=' . $order_id . "&level=" . $level . "&user_id=" . $user_id . '&spm=' . Spm::user_spm(), true),
                'pic' => U('misc/images/napp/touch-icon-144.png', '', true),
            ];
            $this->v->assign('share_info', $share_info);
        //}
        throw new ViewResponse($this->v);
    }

    public function update_nickname(Request $request, Response $response)
    {
        if ($request->is_post()) {
            $nickname = isset($_POST['nickname']) ? $_POST['nickname'] : '';
            global $user;
            if ($user->nickname == $nickname) {
                return;
            }/*
            $nuser = Users::load_by_nickname($nickname);
            if ($nuser && !empty($nuser->uid)) {
                $ret = ['result' => 'FAIL', 'msg' => '昵称' . $nickname . '已经在系统存在！'];
                $response->sendJSON($ret);
            }*/
            User_Model::updateUserInfo(array('nickname' => $nickname));
            $ret = ['result' => 'SUC', 'msg' => '修改成功'];
            $response->sendJSON($ret);
        }
    }

    /**
     * 商家入驻
     * @param Request $request
     * @param Response $response
     */
    public function merchant_checkIn(Request $request, Response $response)
    {
        $this->nav_flag1=0;
        $this->nav_no = 0;
        $this->setPageView($request, $response, '_page_mpa');
        $this->v->set_page_render_mode(View::RENDER_MODE_GENERAL);
        $this->v->set_tplname("mod_user_checkin");
        $this->redirectByMerchantStatus($response);
        //得到商城信息
        $response->send($this->v);
    }

    /**
     * 商家支付
     * @param Request $request
     * @param Response $response
     */
    public function merchant_payment(Request $request, Response $response)
    {
        $this->nav_flag1=0;
        $this->nav_no = 0;
        $this->setPageView($request, $response, '_page_mpa');
        $this->v->set_page_render_mode(View::RENDER_MODE_GENERAL);
        $this->v->set_tplname("mod_user_merpayment");
        $response->send($this->v);
    }

    /**
     * 商家入驻第一步
     * @param Request $request
     * @param Response $response
     */
    public function merchant_openshop_onestep(Request $request, Response $response)
    {
        $this->nav_flag1=0;
        $this->nav_no = 0;
        $this->setPageView($request, $response, '_page_mpa');
        $this->v->set_page_render_mode(View::RENDER_MODE_GENERAL);
        $this->v->set_tplname('mod_user_onestep');
        $this->redirectByMerchantStatus($response);
        $_SESSION['step'] = 1;
        $u = Users::load($GLOBALS['user']->uid);
        $this->v->assign('user', $u);
        if($u->parentid){
            $this->v->assign('parent', Users::load($u->parentid));
        }
        $this->v->set_page_render_mode(View::RENDER_MODE_GENERAL);
        $response->send($this->v);
    }

    /**
     * 获取注册验证码
     * @param Request $request
     * @param Response $response
     */
    public function merchant_getcode(Request $request, Response $response)
    {
        //step1验证手机号
        $phone = $request->post("mobile");
        $result = User_Model::ckeckMobile($phone);//检验米商手机号是否被注册
        if ($result) {
            $ret['retmsg'] = "此手机号已注册";
            $ret['status'] = 0;
            $response->sendJSON($ret);
        } elseif (!verify_phone($phone)) {
            $ret['retmsg'] = "手机号码格式不正确！";
            $ret['status'] = 0;
            $response->sendJSON($ret);
        }
        //todo
        $result = Sms::sendSms($phone, "merchant_reg");
        if ($result!==false) {
//            $_SESSION['merchant_reg'] = 8888;
            $_SESSION['mobile'] = $phone;
            $ret['retmsg'] = "发送验证码成功";
            $ret['status'] = 1;
            $response->sendJSON($ret);
        }else{
            $ret['retmsg'] = "发送验证码失败!";
            $ret['status'] = 0;
            $response->sendJSON($ret);
        }

    }

    /**
     * 用户注册step1
     * @param Request $request
     * @param Response $response
     */
    public function merchant_do_onestep(Request $request, Response $response)
    {
        $this->v->set_page_render_mode(View::RENDER_MODE_GENERAL);
        $mobile = $request->post("mobile");
        $mobileCode = $request->post("mobile_code");
        $mobileCode = intval($mobileCode);

        $verifycode = $request->post("verifycode");
        $verifycode = intval($verifycode);

//        $read = $request->post("read");
        if (empty($mobileCode)) {
            $ret['retmsg'] = "手机验证码不能为空！";
            $ret['status'] = 0;
            $response->sendJSON($ret);
        } elseif ($_SESSION['verifycode'] != $verifycode) {
            $ret['retmsg'] = "图形验证码不正确！";
            $ret['status'] = 0;
            $response->sendJSON($ret);
        } elseif ($verifycode == '') {
            $ret['retmsg'] = "图形验证码不能为空！";
            $ret['status'] = 0;
            $response->sendJSON($ret);
        } elseif (!verify_phone($mobile)) {
            $ret['retmsg'] = "手机号码格式不正确！";
            $ret['status'] = 0;
            $response->sendJSON($ret);
        } elseif ($mobileCode != $_SESSION['merchant_reg']) {
            $ret['retmsg'] = "手机验证码不匹配！";
            $ret['status'] = 0;
            $response->sendJSON($ret);
        } elseif ($_SESSION['mobile'] != $mobile) {
            unset($_SESSION['mobile']);
            $ret['retmsg'] = "手机号码有误请重新获取验证码！";
            $ret['status'] = 0;
            $response->sendJSON($ret);
        }
        //验证邮箱是否已经被使用
//        $res = User_Model::checkMerchantEmeil($email);

        //检验米商手机号是否被注册
        $result = User_Model::ckeckMobile($mobile);
        if ($result) {
            $ret['retmsg'] = "此手机号已注册";
            $ret['status'] = 0;
            $response->sendJSON($ret);
        }
        unset($_SESSION['verifycode']);
        unset($_SESSION['merchant_reg']);
        $_SESSION['step'] = 2;
        $ret['retmsg'] = "校验成功";
        $ret['status'] = 1;
        $response->sendJSON($ret);
    }

    /**
     * 商家入驻第二步骤
     * @param Request $request
     * @param Response $response
     */
    public function merchant_shop_twostep(Request $request, Response $response)
    {
        $this->nav_flag1=0;
        $this->nav_no = 0;
        $this->setPageView($request, $response, '_page_mpa');
        $this->v->set_page_render_mode(View::RENDER_MODE_GENERAL);
        $this->v->set_tplname('mod_user_twostep');
        if (!isset($_SESSION['step']) || $_SESSION['step'] != 2) {
            $response->redirect("/user/merchant/checkin");
        }
        $this->redirectByMerchantStatus($response);
        $u = Users::load($GLOBALS['user']->uid);
        $this->v->assign("user",$u);
        if($u->parentid){
            $this->v->assign('parent', Users::load($u->parentid));
        }
        $response->send($this->v);
    }

    /**
     * 保存第二步骤
     * 商家入驻第步骤
     */
    public function merchant_shop_dotwostep(Request $request, Response $response)
    {
        $this->v->set_page_render_mode(View::RENDER_MODE_GENERAL);
        if($request->is_post()){
            $invite_code = $request->post("invite_code");
            $u = Users::load($invite_code);
            if(!$u->is_exist()){
                $ret['retmsg'] ="推荐人不存在";
                $ret['status'] =0;
                $response->sendJSON($ret);
            }elseif($u->level != Users::USER_LEVEL_3 && $u->level != Users::USER_LEVEL_4){
                $ret['retmsg'] ="当前推荐人不是代理!";
                $ret['status'] =0;
                $response->sendJSON($ret);
            }else{
                $shopPass = $request->post('shopPass', '');
                $confirmShopPass = $request->post('confirmShopPass', '');
                if(!$shopPass || !$confirmShopPass || $confirmShopPass != $shopPass){
                    $ret['retmsg'] ="密码输入不正确!";
                    $ret['status'] =0;
                    $response->sendJSON($ret);
                }
                $mobile = isset($_SESSION['mobile']) ? $_SESSION['mobile'] : '';
                if(!$mobile){
                    $ret['retmsg'] ="手机号码不存在!";
                    $ret['status'] =0;
                    $response->sendJSON($ret);
                }
                $merchant_id = User_Model::saveMerchantInfo($mobile, $invite_code, $shopPass);
                if(!$merchant_id){
                    $ret['retmsg'] ="注册失败!";
                    $ret['status'] =0;
                    $response->sendJSON($ret);
                }
                Sms::sendSms($mobile, 'reg_success');
                unset($_SESSION['mobile']);
                unset($_SESSION['reg_success']);
                $_SESSION['step'] = 3;
                $ret['status'] =1;
                $response->sendJSON($ret);
            }
        }

    }

    /**
     * 入驻时根据当前状态跳转
     * @param Response $response
     */
    private function redirectByMerchantStatus(Response $response){
        $result = User_Model::checkIsPaySuc();
        if ($result && $result > 0) {
            $response->redirect("/user/merchant/dosuccess");
        }
        $merchant = Merchant::getMerchantByUserId($GLOBALS['user']->uid);
        if($merchant->is_exist()){
            $response->redirect("/trade/order/confirm_sysbuy");
        }
    }

    /**
     * @deprecated 不再使用了
     * 支付成功 
     * @param Request $request
     * @param Response $response
     */
    public function merchant_pay_success(Request $request, Response $response)
    {
        /**
         * 支付成功后保存商家信息
         */
        if (!$request->post()) {
            $this->v->set_tplname("mod_user_paysuc");
            $order_price = round(MECHANT_ORDER_AMOUNT,2);
            $this->v->assign("order_price",$order_price);
            $response->send($this->v);
        }
        if (!isset($_SESSION['step'])) {
            $ret['url'] = "/user/merchant/openshop";
            $response->sendJSON($ret);
        }
        $result = User_Model::checkIsPaySuc();
        if (!empty($result)) {
            $ret['url'] = "/user/merchant/dosuccess";
            $response->sendJSON($ret);
        }
        $this->v->set_page_render_mode(View::RENDER_MODE_GENERAL);
        $order_id = $request->post("order_id");
        $order_sn = $request->post("order_sn");
        $password = rand_code();
        $_SESSION['password'] = $password;
        $mobile = $_SESSION['mobile'];
        $invite_code = isset($_SESSION['invite_code']) ? $_SESSION['invite_code'] : '';
        //$facename = isset($_SESSION['facename']) ? $_SESSION['facename'] : '';
        //$merchant_id = User_Model::saveMerchantInfo($mobile, $invite_code, $password);
        if (!empty($merchant_id)) {
            //User_Model::UpdataMerchantInfo($merchant_id, $order_id, $order_sn);
            //todo 发送短信
            Sms::sendSms($mobile, 'reg_success');
            unset($_SESSION['verifycode']);
            unset($_SESSION['invite_code']);
            unset($_SESSION['merchant_reg']);
            unset($_SESSION['reg_success']);
        }
        if ($request->post()) {
            $ret['url'] = "/user/merchant/paysuc";
            $response->sendJSON($ret);
        }
    }

    /**
     * 商家成功注册
     * @param Request $request
     * @param Response $response
     */
    public function merchant_reg_succ(Request $request, Response $response)
    {
        $this->nav_flag1=0;
        $this->nav_no = 0;
        $this->setPageView($request, $response, '_page_mpa');
        $this->v->set_page_render_mode(View::RENDER_MODE_GENERAL);
        $this->v->set_tplname('mod_user_regsuc');

        $urlarr = C("storage.cookie.mch");
        $url = $urlarr["domain"];
        $url = "http://" . $url;

        $merchant = Merchant::getMerchantByUserId($GLOBALS['user']->uid);
        $this->v->assign("mobile", $merchant->mobile);
        $this->v->assign("url", $url);
        $response->send($this->v);
    }
    
    /**
     * 我收藏的店铺
     * @param Request $request
     * @param Response $response
     * @throws ViewResponse
     */
    public function show_collect_shop(Request $request, Response $response){
        $this->setPageView($request, $response, '_page_mpa');
        $this->v->set_page_render_mode(View::RENDER_MODE_GENERAL);
        $this->v->set_tplname('mod_user_collect_shop');
        $this->topnav_no = 1;
        $this->nav_no = 0;
        throw new ViewResponse($this->v);
    }
    
    /**
     * 我收藏的宝贝
     * @param Request $request
     * @param Response $response
     * @throws ViewResponse
     */
    public function show_collect_goods(Request $request, Response $response){
        $this->setPageView($request, $response, '_page_mpa');
        $this->v->set_page_render_mode(View::RENDER_MODE_GENERAL);
        $this->v->set_tplname('mod_user_collect_goods');
        $this->topnav_no = 1;
        $this->nav_no = 0;
        throw new ViewResponse($this->v);
    }
    
    public function show_collect_shoplist(Request $request, Response $response){
        $curpage = $request->get('curpage', 1);
        $orderby = $request->get('orderby', '');
        $pager = new PagerPull($curpage, null);
        User_Model::getCollectShopList($pager, array('orderby' => $orderby));
        $ret = $pager->outputPageJson();
        $response->sendJSON($ret);
    }
    
    public function show_collect_goodslist(Request $request, Response $response){
        $curpage = $request->get('curpage', 1);
        $pager = new PagerPull($curpage, null);
        User_Model::getCollectGoodsList($pager);
        $ret = $pager->outputPageJson();
        $response->sendJSON($ret);
    }
    
    /**
     * 我的钱包
     * @param Request $request
     * @param Response $response
     * @throws ViewResponse
     */
    public function my_wallet(Request $request, Response $response){
        $this->setPageView($request, $response, '_page_mpa');
        $this->v->set_page_render_mode(View::RENDER_MODE_GENERAL);
        $this->v->set_tplname('mod_user_wallet');
        $this->topnav_no = 1;
        $this->nav_no = 0;
        $uid = $GLOBALS['user']->uid;
        $commision = UserCommision::get_commision_income($uid);
        $totalIncome    = 0.00;
        foreach ($commision as $item => $val){
            if(in_array($item, [UserCommision::STATE_ACTIVE,UserCommision::STATE_CASHED])){ //总收入
                $totalIncome += $val;
            }
        }
        $type_commision = UserCommision::get_commision_income_bytype($uid);
        $rebate_commision = UserCommision::get_rebate_commision($uid);
        $this->v->assign('commision', $commision);
        $this->v->assign('totalIncome', $totalIncome);
        $this->v->assign('type_commision', $type_commision);
        $this->v->assign('rebate_commision', $rebate_commision);
        throw new ViewResponse($this->v);
    }
    
    /**
     * 我的收入明细
     * @param Request $request
     * @param Response $response
     * @throws ViewResponse
     */
    public function my_income_detail(Request $request, Response $response){
        $this->setPageView($request, $response, '_page_mpa');
        $this->v->set_page_render_mode(View::RENDER_MODE_GENERAL);
        $this->v->set_tplname('mod_user_income_detail');
        $this->topnav_no = 1;
        $this->nav_no = 0;
        $type = $request->get('type');
        $state = $request->get('state');
        $rebate = $request->get('rebate', 0);
        $this->v->assign('type', $type);
        $this->v->assign('state', $state);
        $this->v->assign('rebate', $rebate);
        throw new ViewResponse($this->v);
    }
    
    public function my_income_detaillist(Request $request, Response $response){
        $curpage = $request->get('curpage', 1);
        $type = $request->get('type', -1);
        $state = $request->get('state', -1);
        $rebate = $request->get('rebate', 0);
        $pager = new PagerPull($curpage, null);
        UserCommision::get_commision_list($pager, array('type' => $type, 'state' => $state, 'rebate' => $rebate));
        $ret = $pager->outputPageJson();
        $response->sendJSON($ret);
    }
    
    /**
     * 支付商家系统单独
     * @param Request $request
     * @param Response $response
     */
    public function pay_merchant_order(Request $request, Response $response){
        $mid = $request->get('mid');
        $merchant = Merchant::load($mid);
        $uid = $GLOBALS['user']->uid;
        //还不是商家 或者 店铺用户跟当前用户不是同一人
        if(!$merchant->is_exist() || $merchant->user_id != $uid){
            Fn::show_error_message('无效的链接.');
        }
        //还不是商家 - 我要入驻
        /* if(!$merchant->is_exist()){
            $response->redirect('/user/merchant/checkin');
        } */
        //已是商家 - 已经支付 - 支付成功
        if(Merchant::checkIsPaySuc()){
            $response->redirect("/user/merchant/dosuccess");
        }
        //已是商家  - 还没支付 - 未付款 未取消 订单存在
        $order_id = User_Model::getUnPaidOrderForMerchant();
        if($order_id && $order_id > 0){
            $url = U('trade/order/record','spm='.(isset($_GET['spm'])?$_GET['spm']:'').'&status=wait_pay');
            $response->redirect($url);
        }
        //已是商家  - 还没支付 - 订单不存在
        $response->redirect('/trade/order/confirm_sysbuy');
        
    }

}
/*----- END FILE: User_Controller.php -----*/