<?php
/**
 * Redis Session Class
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class SessionRedis extends SessionBase {
	
	/**
	 * Redis link identifier
	 * @var object
	 */
	private $_mm;
	
	/**
	 * Variable Prefix
	 * @var string
	 */
	private $_prefix = 'PSESS_';
	
	// session method 'open'
	public function open($save_path, $sess_name) {
		$this->_mm = M('redis');
		return TRUE;
	}
	
	// session method 'read'
	public function read($sess_id) {
		global $user;
	
		//~ Handle the case of first time visitors and clients that don't store cookies (eg. web crawlers).
		if (!isset($_COOKIE[session_name()])) {
			$this->anonymous_user($user);
			return '';
		}
	
		//~ Query and see user status
		$sess = $this->_mm->get($sess_id);
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
	
	// session method 'write'
	public function write($sess_id, $sess_data) {
		global $user;
	
		// If saving of session data is disabled or if the client doesn't have a session,
		// and one isn't being created ($value), do nothing. This keeps crawlers out of
		// the session table. This reduces memory and server load, and gives more useful
		// statistics. We can't eliminate anonymous session table rows without breaking
		// the throttle module and the "Who's Online" block.
		//if (!$this->is_save_session() || ($user->uid == 0 && empty($_COOKIE[session_name()]) && empty($sess_data))) {
		if (!$this->is_save_session() || (empty($_COOKIE[session_name()]) && empty($sess_data))) {
			return TRUE;
		}
		
		$sessinfo = array(
			'uid'     => $user->uid,
			'lastip'  => get_clientip(),
			'session' => $sess_data
		);
		
		$this->_mm->set($sess_id, $sessinfo, $this->_lifetime);
		if ($user->uid) {	// just record logined user
			$online_key = 'online_users';
			$online_users = $this->_mm->get($online_key);
			if (empty($online_users)) {
				$online_users = array('u'.$user->uid => $this->_timestamp);
				$this->_mm->set($online_key, $online_users);
			}
			else {
				$online_users['u'.$user->uid] = $this->_timestamp;
				$this->_mm->set($online_key, $online_users);
			}
		}
		return TRUE;
	}
	
	// session method 'close'
	public function close() {
		$this->_mm->close();
		return TRUE;
	}
	
	// session method 'destroy'
	public function destroy($sess_id) {
		$this->_mm->delete($sess_id);
		return TRUE;
	}
	
	// session method 'gc'
	public function gc($maxlifetime) {
		$this->do_gc($maxlifetime);
		return TRUE;
	}
	
	/**
	 * Do gc operation
	 *
	 * @param integer $maxlifetime
	 * @param integer $is_clear, whether clear garbage data, default to TRUE
	 * @return number
	 */
	private function do_gc($maxlifetime, $is_clear = TRUE) {
		$online_key = 'online_users';
		$online_users = $this->_mm->get($online_key);
		$online_users_true = array();
		if (!empty($online_users)) {
			foreach($online_users AS $k => $v) {
				if ( ($this->_timestamp - $v) > $maxlifetime ) {
					if ($is_clear) {
						unset($online_users[$k]);
					}
				}
				else {
					$online_users_true[$k] = $v;
				}
			}
			if ($is_clear) {
				$this->_mm->set($online_key, $online_users);
			}
			return count($online_users_true);
		}
		return 0;
	}
	
	/**
	 * regenerate id hook
	 * @see SessionBase::regenerate_id_hook()
	 */
	protected function regenerate_id_hook($old_sid, $new_sid) {
		$sessinfo = $this->_mm->get($old_sid);
		$this->_mm->set($new_sid, $sessinfo, $this->_lifetime);
		$this->_mm->delete($old_sid);
	}
	
}
 
/*----- END FILE: class.SessionRedis.php -----*/