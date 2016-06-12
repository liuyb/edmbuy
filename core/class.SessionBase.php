<?php
/**
 * 实现 SessionHandlerInterface 接口的Session基类
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class SessionBase implements SessionHandlerInterface {
  
  protected $_interval;				  // session interval time
  protected $_lifetime;				  // session lifetime
  protected $_lifetime_cookie;  // session cookie lifetime
  protected $_timestamp;        // session current timestamp
  protected $_sessname;         // session name
  protected $_sessnode;         // session name
  
  /**
   * Constructor
   * @param string $node, optional value: 'default','adm'... default to 'default'
   */
  public function __construct($node = 'default') {
    $this->_sessnode = $node;
    $this->init();
  }
  
  protected function init() {
    
    // Set session config 
    $this->_interval = Config::get("storage.session.{$this->_sessnode}.interval", 900); // default 15 minutes
    $this->_lifetime = Config::get("storage.session.{$this->_sessnode}.lifetime", 1440);// default 24 minutes
    $this->_lifetime_cookie = Config::get("storage.cookie.{$this->_sessnode}.lifetime", 2592000);// default 30 days
    $this->_sessname = 'PHPSESSID'; // default session name
    $this->_interval = intval($this->_interval);
    $this->_lifetime = intval($this->_lifetime);
    $this->_lifetime_cookie = intval($this->_lifetime_cookie);
    
    // Set cookie domain, for muti-host sharing sesssion id
    ini_set('session.cookie_domain', Config::get("storage.cookie.{$this->_sessnode}.domain", ''));
    ini_set('session.cookie_path',   Config::get("storage.cookie.{$this->_sessnode}.path", '/'));
    
    // No transmit session id and use cookie
    ini_set('session.use_trans_sid', 0);
    ini_set('session.use_cookies', 1);
    
    // Set cookie lifetime and gc_maxlifetime
    ini_set('session.cookie_lifetime',  $this->_lifetime_cookie);
    ini_set('session.gc_maxlifetime',   $this->_lifetime);
    ini_set('session.gc_probability',   1);
    ini_set('session.gc_divisor', 1000);//The probability is calculated by using gc_probability/gc_divisor
    
    // Change session handler
    session_module_name('user');
    session_set_save_handler($this, TRUE);
    
    // Set appointed session name, must call before session_start()
    $sess_name = Config::get("storage.session.{$this->_sessnode}.sessname");
    if (!empty($sess_name)) {
      $this->_sessname = $sess_name;
      session_name($this->_sessname);
    }
    
    // Hack swfupload cookie bug, must call before session_start()
    if (isset($_POST[$this->_sessname]) && $_POST[$this->_sessname] != session_id()) {
      session_id($_POST[$this->_sessname]);
    }
    
    // OK, session start
    $this->_timestamp= time();
    session_start();
  }
  
  public function open($save_path, $sess_name) {
    return TRUE;
  }
  
  public function read($sess_id) {
    return '';
  }
  
  public function write($sess_id, $sess_data) {
    return TRUE;
  }
  
  public function close() {
    return TRUE;
  }
  
  public function destroy($sess_id) {
    return TRUE;
  }
  
  public function gc($maxlifetime) {
    return TRUE;
  }
  
  /**
   * Count how many users have sessions. Can count either anonymous sessions or authenticated sessions.
   *
   * @param boolean $anonymous
   *   TRUE counts only anonymous users.
   *   FALSE counts only authenticated users.
   * @return  int
   *   The number of users with sessions.
   */
  public function count($anonymous = FALSE) {
    return 0;
  }
  
  /**
   * check user whether online
   *
   * @param int $uid
   * @return bool true or false
   */
  public function is_online($uid = 0) {
    if (!$uid) return FALSE;
    return TRUE;
  }

  /**
   * Determine whether to save session data of the current request.
   *
   * This function allows the caller to temporarily disable writing of session data,
   * should the request end while performing potentially dangerous operations, such as
   * manipulating the global $user object.
   *
   * @param $status
   *   Disables writing of session data when FALSE, (re-)enables writing when TRUE.
   * @return
   *   FALSE if writing session data has been disabled. Otherwise, TRUE.
   */
  public function is_save_session($status = NULL) {
    static $save_session = TRUE;
    if (isset($status)) {
      $save_session = $status;
    }
    return ($save_session);
  }
  
  /**
   * Called when an anonymous user becomes authenticated or vice-versa.
   * 
   * @return
   *   void
   */
  public function regenerate_id() {
    $old_sid = session_id();
    session_regenerate_id();
    $this->regenerate_id_hook($old_sid, session_id());
  }
  
  /**
   * regenerate id hook
   * 
   * @param string $old_sid
   * @param string $new_sid
   * @return
   *   void
   */
  protected function regenerate_id_hook($old_sid, $new_sid) {
    
  }
  
  /**
   * Generates a default anonymous $user object.
   *
   * @param object $_user
   * @param string $_session
   * @return object - the user object.
   */
  public static function anonymous_user($_user, $_session = '') {
    $_user->uid       = '';
    $_user->lastip    = get_clientip();
    $_user->cached    = array();
    $_user->session   = $_session;
    return $_user;
  }
  
}

 
/*----- END FILE: class.SessionBase.php -----*/