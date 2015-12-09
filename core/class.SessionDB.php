<?php
/**
 * DB Session Class
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class SessionDB extends SessionBase {
  
  /**
   * DB link identifier
   * @var DB
   */
  private $_db;
  
  /**
   * DB session table
   * @var string
   */
  private $_dbtable = 'tb_session';
  
  public function open($save_path, $sess_name) {
    $this->_db = D();
    $dbtable = Config::get("storage.session.{$this->_sessnode}.dbtable");
    if (!empty($dbtable)) {
      $this->_dbtable = $dbtable;
    }
    return TRUE;
  }
  
  public function read($sess_id) {
    global $user;
    
		// Handle the case of first time visitors and clients that don't store cookies (eg. web crawlers).
		if (!isset($_COOKIE[session_name()])) {
		  $this->anonymous_user($user);
			return '';
		}

		// Query db and see user status
		$sess = $this->_db->get_one("SELECT s.`uid`, s.`lastip`, s.`data` AS session FROM `{$this->_dbtable}` s WHERE s.`sid`='%s'", $sess_id);
		if (empty($sess)) {
		  $this->anonymous_user($user, isset($user->session) ? $user->session : '');
		}
		else {
		  $user->uid    = $sess['uid'];
		  $user->lastip = $sess['lastip'];
		  $user->session= $sess['session'];
		}
		
		return $user->session;
  }
  
  public function write($sess_id, $sess_data) {
    global $user;
    
    // If saving of session data is disabled or if the client doesn't have a session,
    // and one isn't being created ($value), do nothing. This keeps crawlers out of
    // the session table. This reduces memory and server load, and gives more useful
    // statistics. We can't eliminate anonymous session table rows without breaking
    // the throttle module and the "Who's Online" block.
    if (!$this->is_save_session() || (empty($_COOKIE[session_name()]) && empty($sess_data))) {
      return TRUE;
    }
    
    $this->_db->query("UPDATE `{$this->_dbtable}` SET `uid`=%d, `lasttime`=%d, `lastip`='%s', `data`='%s' WHERE `sid`='%s'", $user->uid, $this->_timestamp, get_clientip(), $sess_data, $sess_id);
    if (!$this->_db->affected_rows()) {	//update error, indicating no the session
      $this->_db->query("INSERT INTO `{$this->_dbtable}` (`sid`, `uid`, `lasttime`, `lastip`, `data`) VALUES('%s', %d, %d, '%s', '%s')", $sess_id, $user->uid, $this->_timestamp, get_clientip(), $sess_data);
    }
    return TRUE;
  }
  
  public function close() {
    return TRUE;
  }
  
  public function destroy($sess_id) {
    $this->_db->query("DELETE FROM `{$this->_dbtable}` WHERE `sid`='%s'", $sess_id);
    return TRUE;
  }
  
  public function gc($maxlifetime) {
    $expiretime = $this->_timestamp - $maxlifetime;
    $this->_db->query("DELETE FROM `{$this->_dbtable}` WHERE `lasttime`<%d", $expiretime);
    return TRUE;
  }
  
  /**
   *  regenerate id hook
   *  
   * @see SessionBase::regenerate_id_hook()
   */
  protected function regenerate_id_hook($old_sid, $new_sid) {
    $this->_db->query("UPDATE `{$this->_dbtable}` SET sid = '%s' WHERE sid = '%s'", $new_sid, $old_sid);
  }
  
}
 
/*----- END FILE: class.SessionDB.php -----*/