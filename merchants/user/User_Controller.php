<?php
/**
 * User控制器
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class User_Controller extends MerchantController
{


    /**
     * default action 'index'
     * @param Request $request
     * @param Response $response
     */
    public function index(Request $request, Response $response)
    {
        if (Merchant::is_logined()) {
            $this->v->set_tplname('mod_user_index');
            $response->send($this->v);
        } else {
            $response->redirect('/login');
        }
    }

    /**
     * action 'login'
     * @param Request $request
     * @param Response $response
     */
    public function login(Request $request, Response $response)
    {
        $show_page = true;
        $retmsg = '';
        $retuname = '';
        $retupass = '';

        if (isset($_POST['loginname']) && isset($_POST['password'])) {
            $loginname = trim($_POST['loginname']);
            $password = trim($_POST['password']);
            $verifycode = isset($_POST['verifycode']) ? trim($_POST['verifycode']) : '';
            unset($_POST['loginname'], $_POST['password'], $_POST['verifycode']);
            $retuname = $loginname;
            $retupass = $password;

            if ('' == $loginname) {
                $retmsg = '请输入用户名';
            } elseif ('' == $password) {
                $retmsg = '请输入密码';
            } elseif ('' == $verifycode) {
                $retmsg = '请输入验证码';
            } elseif (0 && $verifycode != $_SESSION['verifycode']) {
                $retmsg = '请输入正确的验证码';
            } else {
                $check = User_Model::check_logined($loginname, $password, $login_uinfo);
                if ($check < 0) {
                    $retmsg = '不存在该用户';
                } elseif (0 === $check) {
                    $retmsg = '密码错误！';
                } else { //Final Login Success
                    $retmsg = '登录成功！';
                    unset($_SESSION['verifycode']);
                    $cMerchantUser = new Merchant($login_uinfo['merchant_id']);
                    $cMerchantUser->set_logined_status();

                    $response->redirect('/home');
                }
            }
        }

        if ($show_page) {
            $v = new PageView('mod_user_login', '_page_box');
            $v->assign('retmsg', $retmsg)
                ->assign('retuname', $retuname)
                ->assign('retupass', $retupass);
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
        $show_page = true;
        $this->v->set_tplname('mod_user_forgetPwd');
        if (!empty($_SESSION['step'])) {
            $this->v->assign('step', $_SESSION['step']);
            if($_SESSION['step']==3){
                    unset($_SESSION['step']);
            }
        } else {
            $this->v->assign('step', 1);
        }
        if (!empty($_SESSION['phone'])) {
            $this->v->assign('phone', $_SESSION['phone']);
        }
        if ($show_page) {
            $v = new PageView('mod_user_forgetPwd', '_page_box');
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
        $phone = htmlspecialchars($phone);

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
//        $result = Sms::sendSms($phone, $type = "forgetPwd");
        $result = "888888";
         $_SESSION['forgetPwd'] = "888888";
        if ($result) {
            $_SESSION['phone']=$phone;
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
        $phoneCode = $request->post("phoneCode");
        $imgCode = $request->post("imgCode");
        $phone = htmlspecialchars($phone);
        $chkcode = htmlspecialchars($phoneCode);
        if ($imgCode != $_SESSION ['verifycode']) {
            $data['retmsg'] = "验证码不正确！";
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
        if(!empty($_SESSION['phone'])&&$phone!=$_SESSION['phone']){
            $data['retmsg'] = "手机号码输入有误！";
            $data['status'] = 0;
            $response->sendJSON($data);
        }

        if ($_SESSION['forgetPwd'] == $chkcode) {
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
        $phone = $request->post("phone");

        if ($_SESSION['phone'] != $phone) {
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

        if (strlen($password) < 6 || strlen($password) >12) {
            $data['retmsg'] = "密码格式错误";
            $data['status'] = 0;
            $response->sendJSON($data);
        }
        $result = User_Model::forgetPassword($phone, $password, 3);
        if ($result * 1 > 0) {
            $_SESSION["phone"] = null;
            $_SESSION["forgetPwd"] = null;
            $_SESSION["step"] = 3;
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