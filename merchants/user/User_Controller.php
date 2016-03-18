<?php
/**
 * User控制器
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class User_Controller extends MerchantController {
  
  /**
   * default action 'index'
   * @param Request $request
   * @param Response $response
   */
  public function index(Request $request, Response $response)
  {
    if (1) {
      exit('index');
    }
    else {
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
    $retmsg    = '';
    $retuname  = '';
    $retupass  = '';
    
    if (isset($_POST['loginname']) && isset($_POST['password'])) {
      $loginname = trim($_POST['loginname']); 
      $password  = trim($_POST['password']);
      $verifycode= isset($_POST['verifycode']) ? trim($_POST['verifycode']) : '';
      unset($_POST['loginname'],$_POST['password'],$_POST['verifycode']);
      $retuname  = $loginname;
      $retupass  = $password;
      
      if (''==$loginname) {
        $retmsg  = '请输入用户名';
      }
      elseif (''==$password) {
        $retmsg  = '请输入密码';
      }
      elseif (''==$verifycode) {
        $retmsg  = '请输入验证码';
      }
      elseif (0&&$verifycode != $_SESSION['verifycode']) {
        $retmsg  = '请输入正确的验证码';
      }
      else {
        $check = User_Model::check_logined($loginname, $password, $login_uinfo);
        if ($check < 0) {
          $retmsg  = '不存在该用户';
        }
        elseif (0 === $check) {
          $retmsg  = '密码错误！';
        }
        else { //Final Login Success
          $retmsg  = '登录成功！';
          unset($_SESSION['verifycode']);
          $cMerchantUser = new Merchant($login_uinfo['merchant_id']);
          $cMerchantUser->set_logined_status();
          
          $response->redirect('/home');
        }
      }
    }
    
    if ($show_page) {
      $v = new PageView('mod_user_login','_page_front');
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
  
}
 
/*----- END FILE: User_Controller.php -----*/