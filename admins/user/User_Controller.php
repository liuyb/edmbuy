<?php
/**
 * User控制器
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class User_Controller extends Controller {
  
  private $_nav = 'sy';
  
  /**
   * default action 'index'
   * @param Request $request
   * @param Response $response
   */
  public function index(Request $request, Response $response)
  {
    if (AdminUser2::is_logined()) {
      $admin = D()->get_one("SELECT * FROM {admin_user} WHERE admin_uid=%d", $GLOBALS['user']->uid);
      $v = new PageView('mod_user_index');
      $v->assign('admin', $admin);
      $v->assign('nav', $this->_nav);
      
      if ($request->is_hashreq()) {
        $srvinfo = array(
          'PHP_OS'          => PHP_OS,
          'PHP_VERSION'     => PHP_VERSION,
          'SERVER_SOFTWARE' => $_SERVER['SERVER_SOFTWARE'],
          'SYS_VERSION'     => C('env.version'),
          'SYS_TIME'        => date('Y-m-d H:i:s'),
          'SYS_TIMEZONE'    => date_default_timezone_get(),
          'DB_VERSION'      => 'MySQL',
          'DB_TIME'         => date('Y-m-d H:i:s'),
          'PHP_EXECTIME'    => 0,
          'PHP_UPLOAD'      => '',
          'CLIENT_IP'       => $request->ip(),
          'CLIENT_UA'       => get_client_platform() . ' - '. get_client_browser(),
        );
        
        // DB info
        $dbinfo = D()->get_one("SELECT VERSION() AS db_ver, UNIX_TIMESTAMP() AS db_time");
        $srvinfo['DB_VERSION'] = 'MySQL '. $dbinfo['db_ver'];
        $srvinfo['DB_TIME']    = date('Y-m-d H:i:s', $dbinfo['db_time']);
        
        // PHP detail
        $srvinfo['PHP_EXECTIME'] = ini_get('max_execution_time').' 秒';
        $srvinfo['PHP_UPLOAD']   = @ini_get('file_uploads') ? '最大 '.ini_get('upload_max_filesize') : '<font color="red">禁止上传</font>';
        
        $v->assign('srvinfo', $srvinfo);
      }
      
      $response->send($v);
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
        $check = User_Model::check_logined($loginname, $password, $admin);
        if ($check < 0) {
          $retmsg  = '不存在该用户';
        }
        elseif ($check === 0) {
          $retmsg  = '密码错误！';
        }
        else { //Final Login Success
          $retmsg  = '登录成功！';
          unset($_SESSION['verifycode']);
          $cAdmin = new AdminUser2($admin['admin_uid']);
          $cAdmin->set_logined_status();
          
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