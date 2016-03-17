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
            'user/nickname/update'=>'update_nickname',
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
        $this->v->set_tplname('mod_user_index');
        if ($request->is_hashreq()) {
            $this->showUserBaseInfo();
        } else {
            //检查用户信息完成度，nickname或logo没有的话都重定向请求OAuth2详细认证获取资料
            Users::check_detail_info();
        }
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
        $this->v->set_tplname('mod_user_setting');
        $this->topnav_no = 1;
        if ($request->is_hashreq()) {
            $user = $this->showUserBaseInfo();
        }
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
        $this->v->set_tplname('mod_user_wxqr');
        $this->topnav_no = 1;
        if ($request->is_hashreq()) {
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
        $this->v->set_tplname('mod_user_mobile');
        $this->topnav_no = 1;
        if ($request->is_hashreq()) {
            $mobile=$request->get('mobile','');
            $nickname=$request->get('nickname','');
            $this->v->assign('ismoblie',false);
            if(is_numeric($mobile)){
                $this->v->assign('ismoblie',true);
            }
            $this->v->assign('nickname',$nickname);
            $this->v->assign('mobile',$mobile);
        }
             throw new ViewResponse($this->v);
    }

    public function update_mobile(Request $request, Response $response)
    {
        if ($request->is_post()) {
            $mobile = isset($_POST['mobile'])?$_POST['mobile']:'';
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
            if ($spm && preg_match('/^user\.(\d+)$/', $spm, $matchspm)) {
                $pUser = Users::load($matchspm[1]);
                if ($pUser->is_exist()) {
                    $parent_id = $pUser->id;
                    $parent_nick = $pUser->nickname;
                }
            }
            $upUser->parentid = $parent_id;
            $upUser->parentnick = $parent_nick;

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
            if ($parent_id) {
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
        global $user;
        $currentUser = $user;
        $this->v->assign("uid", $uid);
        $this->v->assign("nickname", $currentUser->nickname);
        $this->v->assign("level", $currentUser->level);
        $this->v->assign("logo", $currentUser->logo);
        $this->v->assign("mobile", $currentUser->mobilephone);
        $this->v->assign("wxqr", $user->wxqr);
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
        $this->nav_no = 0;
        $user_id = $request->get("user_id",0);
        $order_id = $request->get("order_id",0);
        $level=$request->get('level',"1");
        $uid = $GLOBALS['user']->uid;
        if ($request->is_hashreq()) {
            $flat=true;
            //是否为其他人浏览
            if($user_id!=$uid){
                $flat=false;
            }
            $cUser = User_Model::findUserInfoById($user_id);
            //根据user_id获得用户的信息 获取每一个订单号下的商品
            $data = User_Model::getOrderInfo($order_id);
            $this->v->assign('userInfo',$data['userInfo']);
            $this->v->assign('goodsInfo',$data['goodsInfo']);
            $this->v->assign('level',$level);
            $this->v->assign("is_user",$flat);
            $this->v->assign("cUser",$cUser);
            $this->v->assign('ismBusiness',$data['ismBusiness']);
            $this->v->assign('commision',$data['commision']);

            //分享信息
            $share_info = [
                'title' => '难得的好商城，值得关注！',
                'desc'  => '消费购物，推广锁粉，疯狂赚钱统统不耽误',
                'link'  => U('/user/commission?order_id='.$order_id."&level=".$level."&user_id=".$user_id.'&spm='.Spm::user_spm(), true),
                'pic'   => U('misc/images/napp/touch-icon-144.png','',true),
            ];
            $this->v->assign('share_info', $share_info);
        }
        throw new ViewResponse($this->v);
    }
    public function update_nickname(Request $request, Response $response){
        if ($request->is_post()) {
            $nickname = isset($_POST['nickname'])?$_POST['nickname']:'';
            global $user;
            if($user->nickname == $nickname){
                return;
            }
            $nuser = Users::load_by_nickname($nickname);
            if ($nuser && !empty($nuser->uid)) {
                $ret = ['result' => 'FAIL', 'msg' => '昵称'.$nickname.'已经在系统存在！'];
                $response->sendJSON($ret);
            }
            User_Model::updateUserInfo(array('nickname' => $nickname));
            $ret = ['result' => 'SUC', 'msg' => '修改成功'];
                $response->sendJSON($ret);
        }
    }

}

/*----- END FILE: User_Controller.php -----*/