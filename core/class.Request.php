<?php
/**
 * Request Base Class
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Request {
  
  const HASH_TAG = '_hr';
  
  protected static $singleton;
  public $params = [];
  
  /**
   * Constructor
   */
  public function __construct() {
    
    if (isset(self::$singleton) && !IS_CLI) {
      throw new NotSingletonException(get_called_class());
    }
    self::$singleton = TRUE;
    
    self::init();
  }
  
  /**
   * Request init
   */
  final protected static function init() {
    
    // unset nouse $GLOBALS
    self::unset_globals();
    
    // initialize environment
    self::init_env();
  }
  
  /**
   * Unsets all disallowed global variables. See $allowed for what's allowed.
   */
  protected static function unset_globals() {
    if (ini_get('register_globals')) {
      $allowed = array('_ENV' => 1, '_GET' => 1, '_POST' => 1, '_COOKIE' => 1, '_FILES' => 1, '_SERVER' => 1, '_REQUEST' => 1, 'GLOBALS' => 1);
      foreach ($GLOBALS as $key => $value) {
        if (!isset($allowed[$key]) || !$allowed[$key]) {
          unset($GLOBALS[$key]);
        }
      }
    }
  }
  
  /**
   * Initializes the PHP environment.
   */
  protected static function init_env() {
    if (!isset($_SERVER['HTTP_REFERER'])) {
      $_SERVER['HTTP_REFERER'] = '';
    }
    if (!isset($_SERVER['SERVER_PROTOCOL']) || ($_SERVER['SERVER_PROTOCOL'] != 'HTTP/1.0' && $_SERVER['SERVER_PROTOCOL'] != 'HTTP/1.1')) {
      $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.0';
    }
  
    if (isset($_SERVER['HTTP_HOST'])) {
      // As HTTP_HOST is user input, ensure it only contains characters allowed
      // in hostnames. See RFC 952 (and RFC 2181).
      // $_SERVER['HTTP_HOST'] is lowercased here per specifications.
      $_SERVER['HTTP_HOST'] = strtolower($_SERVER['HTTP_HOST']);
      if (!simphp_valid_http_host($_SERVER['HTTP_HOST'])) {
        // HTTP_HOST is invalid, e.g. if containing slashes it may be an attack.
        header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
        exit;
      }
    }
    else {
      // Some pre-HTTP/1.1 clients will not send a Host header. Ensure the key is
      // defined for E_ALL compliance.
      $_SERVER['HTTP_HOST'] = '';
    }
  
    // When clean URLs are enabled, emulate ?q=foo/bar using REQUEST_URI. It is
    // not possible to append the query string using mod_rewrite without the B
    // flag (this was added in Apache 2.2.8), because mod_rewrite unescapes the
    // path before passing it on to PHP. This is a problem when the path contains
    // e.g. "&" or "%" that have special meanings in URLs and must be encoded.
    $_GET['q'] = simphp_request_path();
  
    // Override PHP settings required for SimPHP to work properly.
    // sites/default/default.settings.php contains more runtime settings.
    // The .htaccess file contains settings that cannot be changed at runtime.
  
    // Don't escape quotes when reading files from the database, disk, etc.
    ini_set('magic_quotes_runtime', '0');
    // Use session cookies, not transparent sessions that puts the session id in
    // the query string.
    ini_set('session.use_cookies', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_trans_sid', '0');
    // Don't send HTTP headers using PHP's session handler.
    ini_set('session.cache_limiter', 'none');
    // Use httponly session cookies.
    ini_set('session.cookie_httponly', '1');
  
    // Set sane locale settings, to ensure consistent string, dates, times and
    // numbers handling.
    setlocale(LC_ALL, 'C');
  }

  /**
   * Adjust the $_GET['q'] variable to the proper normal path.
   */
  public static function adjust_q() {
    if (isset($_GET['q']) && is_string($_GET['q'])) {
      $qo = $qn = rtrim($_GET['q'],'/');
      if(method_exists('Common_Controller', 'menu_init')) {
        $menu = Common_Controller::menu_init();
        foreach ($menu as $key => $val) {
          $qn = preg_replace($key, $val, $qo);
          if ($qn != $qo) break;
        }
      }
      $_GET['q'] = simphp_request_path($qn);
    }
  }
  
  /**
   * Request scheme
   */
  public static function scheme() {
    return isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : ($_SERVER['SERVER_PORT'] == '443' ? 'https' : 'http');
  }
  
  /**
   * Request host
   */
	public static function host() {
		return $_SERVER['HTTP_HOST'];
	}
  
	/**
	 * Request port
	 */
	public static function port() {
		return $_SERVER['SERVER_PORT'];
	}

	/**
	 * Request referer
	 */
	public static function refer() {
		return isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
	}

	/**
	 * Request user agent
	 */
	public static function ua() {
		return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
	}

	/**
	 * Request method
	 * @return string
	 */
	public static function method() {
		return isset($_SERVER['REQUEST_METHOD']) ? strtolower($_SERVER['REQUEST_METHOD']) : '';
	}
	
	/**
	 * Request user ip
	 */
	public static function ip() {
		return get_clientip();
	}
	
	/**
   * Check whether is IE browser
   * 
   * @param string $ua, User Agent string, optional
   * @return boolean
	 */
	public static function isIE($ua = NULL) {
	  if (!isset($ua)) $ua = self::ua();
	  return strpos('MSIE', $ua)===-1 ? FALSE : TRUE;
	}
	
	/**
   * Check IE version, when return 0, indicating non-IE browser
   * 
   * @param string $ua, User Agent string, optional
   * @return string when return 0, indicating non-IE browser
	 */
	public static function ieVer($ua = NULL) {
	  if (!isset($ua)) $ua = self::ua();
	  $ver = 0;
	  if (preg_match("/MSIE (\d+\.\d+)/", $ua, $match)) {
	    $ver = $match[1];
	  }
	  return $ver;
	}
	
	/**
	 * Request context prefix
	 */
	public static function context_prefix() {
	  return isset($_SERVER['CONTEXT_PREFIX']) ? $_SERVER['CONTEXT_PREFIX'] : '';
	}
	
	/**
	 * Request application context path
	 */
	public static function context_path() {
	  static $_cp;
	  if (!isset($_cp)) {
	    $_cp = Config::get('env.contextpath','/');
	  }
	  return $_cp;
	}
	
	/**
	 * Request url path, no the system context prefix part
	 */
	public static function path() {
	  static $_tpath;
	  if (!isset($_tpath)) {
	    $_arr = explode('?', self::uri());
	    $_tpath = isset($_arr[0]) ? $_arr[0] : '';
	  }
	  return $_tpath;
	}

	/**
	 * Request uri, no the system context prefix part
	 */
	public static function uri() {
	  $_ctx = self::context_prefix();
	  if ($_ctx) {
	    return preg_replace("!^{$_ctx}!i", '', $_SERVER['REQUEST_URI']);
	  }
		return $_SERVER['REQUEST_URI'];
	}
	
	/**
	 * Request url(see the browser location)
	 */
	public static function url() {
	  $scheme = self::scheme();
	  $host   = self::host();
	  $port   = self::port();
	  $url    = $scheme.'://'.$host.($port=='80'?'':':'.$port).$_SERVER['REQUEST_URI'];
	  return $url;
	}
	
	/**
	 * get the 'q'
	 * @return Ambigous <The, unknown, string>
	 */
	public static function q() {
	  return simphp_request_path();
	}
	
	/**
   * Object version of function arg()
	 */
	public static function arg($index = NULL, $path = NULL) {
	  return arg($index, $path);
	}
	
	/**
	 * return post fields data
	 * @param $field GET field
	 * @param $default set default value
	 * @return Ambigous <The, unknown, string>
	 */
	public static function get($field = NULL, $default = NULL) {
	  $fv = !isset($field) ? $_GET : (isset($_GET[$field]) ? $_GET[$field] : '');
	  if (isset($field) && isset($default) && ''===$fv) {
	    $fv = $default;
	  }
	  return $fv;
	}
	
	/**
	 * return post fields data
	 * @param $field POST field
	 * @param $default set default value
	 * @return Ambigous <The, unknown, string>
	 */
	public static function post($field = NULL, $default = NULL) {
	  $fv = !isset($field) ? $_POST : (isset($_POST[$field]) ? (is_array($_POST[$field]) ? $_POST[$field] : trim($_POST[$field])) : '');
	  if (isset($field) && isset($default) && ''===$fv) {
	    $fv = $default;
	  }
	  return $fv;
	}
	
	/**
	 * return files fields data
	 * @param $field FILES field
	 * @param $default set default value
	 * @return Ambigous <The, unknown, string>
	 */
	public static function files($field = NULL, $default = NULL) {
	  $fv = !isset($field) ? $_FILES : (isset($_FILES[$field]) ?  $_FILES[$field]: array());
	  if (isset($field) && isset($default) && empty($fv)) {
	    $fv = $default;
	  }
	  return $fv;
	}
	
	/**
	 * check whether the current request is a post request
	 * @param  boolean  $secure_check  whether secure checking(check token), default checking
	 * @return boolean
	 */
	public static function is_post($secure_check = TRUE) {
		if (!$secure_check) return self::method()=='post';
		return self::is_token_post();
	}
	
	/**
	 * check whether the current post is a valid post
	 * @return boolean
	 */
	public static function is_token_post() {
		$token = self::post('token', '');
		return (empty($token) || $token!=sess_token()) ? FALSE : TRUE;
	}
	
	/**
	 * check whether the current request is hash request
	 * @return boolean
	 */
	public static function is_hashreq() {
	  return isset($_GET[self::HASH_TAG]) && $_GET[self::HASH_TAG] ? TRUE : FALSE;
	}
	
	/**
	 * check whether the current request has files data
	 * @return boolean
	 */
	public static function has_files() {
	  return !empty($GLOBALS['_FILES']) ? TRUE : FALSE;
	}
  
  /**
   * magic method '__get'
   */
  public function __get($name) {
    return array_key_exists($name, $this->params) ? $this->params[$name] : NULL;
  }
  
  /**
   * magic method '__set'
   */
  public function __set($name, $value) {
    $this->params[$name] = $value;
  }
  
}
 
 
/*----- END FILE: class.Request.php -----*/