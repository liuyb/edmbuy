<?php
/**
 * User控制器
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class User_Controller extends MerchantController
{

    public function menu()
    {
        return [
            'user/home/trade/data'    => 'get_merchant_trade_data'
        ];
    }
    
    /**
     * default action 'index'
     * @param Request $request
     * @param Response $response
     */
    public function index(Request $request, Response $response)
    {
        if (Merchant::is_logined()) {
            $this->setPageLeftMenu('user', 'home');
            $this->v->set_tplname('mod_user_index');
            $muid = $GLOBALS['user']->uid;
            $wait_pay_count = Merchant::getOrderTotalByStatus(CS_AWAIT_PAY, $muid);
            $wait_ship_count = Merchant::getOrderTotalByStatus(CS_AWAIT_SHIP, $muid);
            $wait_refund_count = Merchant::getWaitRefundOrderTotal($muid);
            $goods_total = Merchant::getGoodsTotalByIsSale(-1, $muid);
            $unsale_goods_total = Merchant::getGoodsTotalByIsSale(0, $muid);
            $warn_goods_number = Merchant::getGoodsNumberWarning($muid, 10);
            $shop = Merchant::getShopInfo($muid);
            $totalSales = Merchant::getOrderSalesMoney($muid);
            $this->v->assign('wait_pay_count', $wait_pay_count);
            $this->v->assign('wait_ship_count', $wait_ship_count);
            $this->v->assign('wait_refund_count', $wait_refund_count);
            $this->v->assign('goods_total', $goods_total);
            $this->v->assign('unsale_goods_total', $unsale_goods_total);
            $this->v->assign('warn_goods_number', $warn_goods_number);
            $this->v->assign('totalSales', $totalSales);
            $this->v->assign('shop', $shop);
            $response->send($this->v);
        } else {
            $response->redirect('/login');
        }
    }
    
    /**
     * 商家运营数据
     * @param Request $request
     * @param Response $response
     */
    public function get_merchant_trade_data(Request $request, Response $response){
        $gaps = [array('time' => '昨日', 'gap' => 'yesterday'),
            array('time' => '近7天', 'gap' => 1),
            array('time' => '近30天', 'gap' => 30)
        ];
        foreach ($gaps as &$gap){
            $result = Home_Model::getMerchantDataByTime($GLOBALS['user']->uid, $gap['gap']);
            $gap = array_merge($gap, $result);
        }
        $response->sendJSON($gaps);
    }

    /**
     * action 'login'
     * @param Request $request
     * @param Response $response
     */
    public function login(Request $request, Response $response)
    {
        $cooies = Cookie::get("member_me");
        if (!empty($cooies)) {
            $cMerchantUser = new Merchant($cooies);
            $cMerchantUser->set_logined_status();
            $response->redirect('/home');
        }
        $show_page = true;
        $retmsg = '';
        $retuname = '';
        $retupass = '';
        if (isset($_POST['loginname']) && isset($_POST['password']) && isset($_POST['erro'])) {
            $loginname = trim($_POST['loginname']);
            $password = trim($_POST['password']);
            $erro = trim($_POST['erro']);
            $verifycode = isset($_POST['verifycode']) ? trim($_POST['verifycode']) : '';
            unset($_POST['loginname'], $_POST['password'], $_POST['verifycode'], $_POST['erro']);
            $retuname = $loginname;
            $retupass = $password;
            if ('' == $loginname) {
                $ret['retmsg'] = '请输入用户名';
                $ret['status'] =-1;
                $_SESSION['erro'] = 1;
                $response->sendJSON($ret);
            } elseif ('' == $password) {
                $ret['retmsg'] = '请输入密码';
                $ret['status'] =-2;
                $_SESSION['erro'] = 1;
                $response->sendJSON($ret);
            } else {
                if ($erro == 1) {
                    if ('' == $verifycode) {
                        $ret['retmsg'] = '请输入验证码';
                        $ret['status'] =-3;
                        $_SESSION['erro'] = 1;
                        $response->sendJSON($ret);
                    } elseif ($verifycode != $_SESSION['verifycode']) {
                        $ret['retmsg'] = '请输入正确的验证码';
                        $ret['status'] =-3;
                        $_SESSION['erro'] = 1;
                        $response->sendJSON($ret);
                    } elseif ('' == $loginname) {
                        $ret['retmsg'] = '请输入用户名';
                        $ret['status'] =-1;
                        $_SESSION['erro'] = 1;
                        $response->sendJSON($ret);
                    } elseif ('' == $password) {
                        $ret['retmsg'] = '请输入密码';
                        $ret['status'] =-1;
                        $_SESSION['erro'] = 1;
                        $response->sendJSON($ret);
                    }
                }
                if($retmsg==""){
                    $check = User_Model::check_logined($loginname, $password, $login_uinfo);
                    if ($check < 0) {
                        $ret['retmsg']= '不存在该用户';
                        $ret['status']=-1;
                        $_SESSION['erro'] = 1;
                        $response->sendJSON($ret);
                    } elseif (0 === $check) {
                        $ret['retmsg']= '密码错误！';
                        $ret['status']=-2;
                        $_SESSION['erro'] = 1;
                        $response->sendJSON($ret);
                    } elseif($check['activation']==0) {
                        $ret['retmsg']= '帐号未激活！';
                        $ret['status']=-4;
                        $_SESSION['erro'] = 1;
                        $response->sendJSON($ret);
                    }else{ //Final Login Success
                        $ret['retmsg'] = '登录成功！';
                        $ret['status'] = 1;
                        if (isset($_POST['member_me'])) {
                            Cookie::set('member_me', $login_uinfo['merchant_id'], 3600 * 24 * 7);
                        }
                        unset($_SESSION['erro']);
                        unset($_SESSION['verifycode']);
                        $cMerchantUser = new Merchant($login_uinfo['merchant_id']);
                        $cMerchantUser->set_logined_status();
                        $response->sendJSON($ret);
                    }
                }
            }
            $_SESSION['erro'] = 1;
        }
        if ($show_page) {
            $v = new PageView('mod_user_login', '_page_box');
            $v->assign('retmsg', $retmsg)
                ->assign('retuname', $retuname)
                ->assign('retupass', $retupass);
            if(!empty($_SESSION['erro'])){
               $v->assign('erro', 1);
            }else{
               $v->assign('erro', 0);
            }
            $response->send($v);
        }
    }

    /**
     * action 'logout'
     * @param Request $request
     * @param Response $response
     */
    public function logout(Request $request, Response $response)
    {
        // Unset all of the session variables.
        $cookie = Cookie::get("member_me");
        if (!empty($cookie)) {
            Cookie::remove("member_me");//清除
        }
        session_destroy();
        $_SESSION = array();
        // If it's desired to kill the session, also delete the session cookie.
        // Note: This will destroy the session, and not just the session data!
        if (isset($_COOKIE[session_name()])) {
            Cookie::raw_remove(session_name());
        }

        // Finally, destroy the session.
        SimPHP::$session->anonymous_user($GLOBALS['user']);

        // Reload current pag
        $response->redirect('/');
    }

    /**
     * 忘记密码
     * @auth hc_edm
     * @param Request $request
     * @param Response $response
     */
    public function forgetPwd(Request $request, Response $response)
    {
//           unset($_SESSION['step']);
//          unset($_SESSION['phone']);
//           $_SESSION['step']=2;
//           $_SESSION['phone']=15728743912;
//        $_SESSION['phone']=18124682152;
        if (!empty($GLOBALS['user']->uid)) {
            $response->redirect("/home");
        }
        $show_page = true;
        $this->v->set_tplname('mod_user_forgetpwd');
        $step = $request->get('step');
        if (!empty($_SESSION['step'])) {
            $this->v->assign('step', $_SESSION['step']);
            if ($_SESSION['step'] == 3) {
                unset($_SESSION['step']);
            } elseif ($step == 1 && $_SESSION['step'] == 2) {
                unset($_SESSION['step']);
                unset($_SESSION['forget_pwd']);
                $this->v->assign('step', $step);
            } elseif ($step == 3 && $_SESSION['step'] == 2) {
                $this->v->assign('step', 2);//只有2不unset
            }
        } else {
            $this->v->assign('step', 1);
        }

        if (!empty($_SESSION['phone'])) {
            $this->v->assign('phone', $_SESSION['phone']);
        }
        if ($show_page) {
            $v = new PageView('mod_user_forgetpwd', '_page_box');
            $response->send($v);
        }
    }

    /**
     * 获取短信验证
     * @auth hc_edm
     * @param Request $request
     * @param Response $response
     */
    public function getPhoneCodeAjax(Request $request, Response $response)
    {
        $phone = $request->post('phone', '');

        //验证用户是否1分钟以内是否已经发过短信
        if (!verify_phone($phone)) {
            $data['retmsg'] = "手机号码输入有误！";
            $data['status'] = 0;
            $response->sendJSON($data);
        }
        $verifyphone = User_Model::userCheck($phone, 3);
        if (!$verifyphone) {
            $data['retmsg'] = "注册信息不存在！";
            $data['status'] = '0';
            $response->sendJSON($data);
        }

        $limit = User_Model::checkSmsLimit($phone);
        if (!$limit) {
            $data['retmsg'] = "发送超时！";
            $data['status'] = 0;
            $response->sendJSON($data);
        }
//      $result = Sms::sendSms($phone, $type = "forget_pwd");
        $result = "888888";
        $_SESSION['forget_pwd'] = "888888";
        if ($result) {
            $_SESSION['phone'] = $phone;
            Cookie::set("forget_pwd", $_SESSION['forget_pwd'], 60 * 5);//验证码5分钟过后过期
            $data['retmsg'] = "发送验证码成功！";
            $data['status'] = 1;
            $response->sendJSON($data);
        } else {
            $data['retmsg'] = "发送验证码失败！";
            $data['status'] = 0;
            $response->sendJSON($data);
        }

    }

    /**
     * 验证短信动态密码
     * @auth hc_edm
     * @param Request $request
     * @param Response $response
     */
    public function checkSmsCode(Request $request, Response $response)
    {
        $phone = $request->post("phone");
        $chkcode = $request->post("phoneCode");
        $imgCode = $request->post("imgCode");
        if ($imgCode != $_SESSION ['verifycode']) {
            $data['retmsg'] = "图形验证码不正确！";
            $data['status'] = '0';
            $response->sendJSON($data);
        }
        if (!verify_phone($phone)) {
            $data['retmsg'] = '手机号码不正确！';
            $data['status'] = 0;
            $response->sendJSON($data);
        }
        if (empty($chkcode)) {
            $data['retmsg'] = '动态密码不能为空！';
            $data['status'] = 0;
            $response->sendJSON($data);
        }
        if (!empty($_SESSION['phone']) && $phone != $_SESSION['phone']) {
            $data['retmsg'] = "手机号码输入有误！";
            $data['status'] = 0;
            $response->sendJSON($data);
        }

        if ($_SESSION['forget_pwd'] == $chkcode) {
            $_SESSION['step'] = "2";
            $_SESSION['phone'] = $phone;
            unset($_SESSION['verifycode']);
            $data['retmsg'] = '动态密码正确！';
            $data['status'] = 1;
            $response->sendJSON($data);
        };
        $data['retmsg'] = '动态密码不正确！';
        $data['status'] = 0;
        $response->sendJSON($data);
    }


    /**
     * 重置密码
     * @param Request $request
     * @param Response $response
     */
    public function forgotSavePwd(Request $request, Response $response)
    {
        $cookiePhone = Cookie::get("forget_pwd");
        if (empty($cookiePhone)) {
            unset($_SESSION['step']);
            $data['retmsg'] = "手机验证码已过期！";
            $data['status'] = -1;
            $response->sendJSON($data);
        }
        $phone = $request->post("phone");
        if ($_SESSION['phone'] != $phone) {
            unset($_SESSION['step']);
            $data['retmsg'] = "session已过期！";
            $data['status'] = -1;
            $response->sendJSON($data);
        }
        $password = $request->post("pwd");
        $confirmpassword = $request->post("confirmpwd");
        if (empty($password) || empty($confirmpassword)) {
            $data['retmsg'] = "密码不能为空！";
            $data['status'] = 0;
            $response->sendJSON($data);
        }
        if ($password != $confirmpassword) {
            $data['retmsg'] = "输入密码不一致！";
            $data['status'] = 0;
            $response->sendJSON($data);
        }
        if (empty($_SESSION['phone'])) {
            $data['retmsg'] = "对不起设置密码超时！";
            $data['status'] = 0;
            $response->sendJSON($data);
        }

        if (strlen($password) < 6 || strlen($password) > 12) {
            $data['retmsg'] = "密码不能小于6位数或大于12位数！";
            $data['status'] = 0;
            $response->sendJSON($data);
        }
        $result = User_Model::forgetPassword($phone, $password, 3);
        if ($result * 1 > 0) {
            unset($_SESSION["phone"],$_SESSION["forget_pwd"]);
            $_SESSION["step"] = 3;
            if(!empty($_SESSION['erro'])){
                unset($_SESSION['erro']);
            }
            $data['retmsg'] = "重置密码成功!";
            $data['status'] = 1;
            $response->sendJSON($data);
        } else {
            $data['retmsg'] = "设置密码失败!";
            $data['status'] = 0;
            $response->sendJSON($data);
        }
    }

}


/*----- END FILE: User_Controller.php -----*/