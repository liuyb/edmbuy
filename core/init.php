<?php
/**
 * initialize file, all SimPHP request must require/include this file first
 *
 * @author Gavin<laigw.vip@gmail.com>
 */

/**
 * Define Global Constants
 */
define('IN_SIMPHP', TRUE);
define('SIMPHP_ROOT', substr(dirname(__FILE__), 0, -5)); //no the trailing slash unless it is the root directory
define('SIMPHP_CORE', SIMPHP_ROOT.'/core');
define('SIMPHP_INCS', SIMPHP_ROOT.'/incs');
define('IS_CLI', 'cli'===PHP_SAPI);
define('DS', DIRECTORY_SEPARATOR);

/**
 * The current system version.
 */
define('SIMPHP_VERSION', '1.0');

/**
 * Minimum supported version of PHP.
 */
define('SIMPHP_PHP_MIN_VERSION', '5.4.0');

/**
 * Running Component(RC): nothing
 */
define('RC_NONE', 0);

/**
 * Running Component(RC): database
 */
define('RC_DATABASE', 1);

/**
 * Running Component(RC): memcache
 */
define('RC_MEMCACHE', 2);

/**
 * Running Component(RC): session
 */
define('RC_SESSION', 4);

/**
 * Running Component(RC): all
 */
define('RC_ALL', 2047);

/**
 * require constant configure
 */
require (SIMPHP_ROOT.'/conf/const.php');

/**
 * require global needed function files
 */
require (SIMPHP_CORE.'/func.simphp.php');
require (SIMPHP_CORE.'/func.global.php');
require (SIMPHP_INCS.'/func.common.php');
require (SIMPHP_CORE.'/class.Query.php');

/**
 * set core class autoload
 */
SimPHP::registerAutoload();

/**
 * Log dir
 * @var string constant
 */
define('LOG_DIR', SIMPHP_ROOT.Config::get('env.log_dir', '/var/log'));

/**
 * Whether on production env
 * @var string constant
 */
define('ON_PRO', Config::get('env.env')=='pro' ? TRUE : FALSE);

/**
 * 系统启动类
 *
 */
class SimPHP {
  
  const TIME_FORMAT_STD = 'std'; //std时间格式: "Y-m-d H:i:s", 19位
  const TIME_FORMAT_W3C = 'w3c'; //w3c时间格式: "Y-m-d\TH:i:sP", 20~25位
  const MENU_HOLDER_REGEXP = '/(%d|%s|%S)/';
  
  /**
   * a DB instance
   * @var DB
   */
  public static $db;
  
  /**
   * a SessionBase instance
   * @var SessionBase
   */
  public static $session;
  
  /**
   * session user object
   * @var stdClass
   */
  public static $user;
  
  /**
   * global config
   * @var array
   */
  public static $gConfig = array();
  
  /**
   * Application Configure
   * @var array
   */
  protected $_appConfig = array(
      'qsep'    => '/',        // 'q' separator, optional value: '/','.'... and so on
      'modroot' => 'modules',  // module root dir name, optional value: 'default','mobile','cron','apps'... and so on
      'confdir' => 'conf',     // configure file dir, may be: 'conf','conf-xx'...and so on
      'sessnode'=> 'default',  // session node
    );
  
  /**
   * The singleton instance
   * @var SimPHP
   */
  protected static $_instance;
  
  /**
   * Get the singleton instance
   * @param array $appConfig
   * @return SimPHP
   */
  public static function I(Array $appConfig = array()) {
    if ( !isset(self::$_instance) ) {
      self::$_instance = new self($appConfig);
    }
    return self::$_instance;
  }
  
  /**
   * Constructor
   */
  public function __construct(Array $appConfig=array()) {
    
    // Start a page timer:
    timer_start('page');
    
    // Merge configure
    $this->_appConfig = array_merge($this->_appConfig, $appConfig);
    
    // Initializing
    $this->init();
    
  }
  
  /**
   * Initializing
   * @throws PHPMinVersionException
   */
  protected function init() {
    
    if (version_compare(PHP_VERSION, SIMPHP_PHP_MIN_VERSION,'<')) {
      throw new PHPMinVersionException();
    }
    
    // initialize global config to the latest appConfig
    self::$gConfig = $this->_appConfig;
    
    // Enforce E_ALL, except E_NOTICE, but allow users to set levels not part of E_ALL.
    error_reporting(E_ALL ^ E_NOTICE | error_reporting());
    
    // Set the custom error handler.
    //set_error_handler(array('ErrorHandler', 'handleError'));
    //set_exception_handler(array('ErrorHandler', 'handleException'));
    //register_shutdown_function(array('ErrorHandler', 'handleShutdown'));
    
    // timezone setting
    date_default_timezone_set(Config::get('env.timezone','UTC'));
    
    // Initializing running time
    $this->init_runtime();
    
  }
  
  /**
   * Initializing running time
   */
  protected function init_runtime() {
    
    $run_dir  = SIMPHP_ROOT . '/var/run';
    if (!is_dir($run_dir) && !mkdirs($run_dir)) {
      throw new DirWritableException($run_dir);
    } 
    
    $file_ver = SIMPHP_ROOT . '/var/run/~ver.php';
    if (!file_exists($file_ver)) {
      $verno = uniqid();
      $file_data = "<?php define('STATIC_VERSION', '{$verno}');?>";
      if (FALSE === file_put_contents($file_ver, $file_data)) {
        throw new DirWritableException($run_dir);
      }
    }
    include ($file_ver);
    
  }
  
  /**
   * Bootstrap Running Component
   *
   * @param integer $rc, Running Component, 
   *       optional value: RC_NONE,RC_DATABASE,RC_MEMCACHE,RC_SESSION,RC_ALL
   *       or: RC_DATABASE | RC_MEMCACHE | RC_SESSION
   *       or: RC_ALL ^ RC_MEMCACHE ^ RC_SESSION
   *       or: RC_ALL & ~RC_MEMCACHE & ~RC_SESSION
   * @return SimPHP
   */
  public function boot($rc=RC_NONE) {
    
    if ($rc>0) {
      
      //$rc_set = [RC_NONE,RC_DATABASE,RC_MEMCACHE,RC_SESSION,RC_ALL];
      if ( (RC_DATABASE & $rc) === RC_DATABASE ) { //Bootstrap with db
        D();
      }
      if ( (RC_MEMCACHE & $rc) === RC_MEMCACHE ) { //Bootstrap with memcache
        M();
      }
      if ( (RC_SESSION & $rc) === RC_SESSION ) { //Bootstrap with session
        $GLOBALS['user'] = new stdClass();
        SessionBase::anonymous_user($GLOBALS['user']);
        
        $sessnode  = $this->sessnode;
        $sess_handler = Config::get("storage.session.{$sessnode}.handler",'file');
        if ('mm' == $sess_handler) {
          self::$session = new SessionMM($sessnode);
        }
        if ('redis' == $sess_handler) {
          self::$session = new SessionRedis($sessnode);
        }
        elseif ('db' == $sess_handler) {
          self::$session = new SessionDB($sessnode);
        }
        else { // default to file session
          session_start();
        }
      }
      
    }
    
    return $this;
    
  }

  /**
   * Dispatch request to Controller
   * 
   */
  public function dispatch(Request $request=NULL, Response $response=NULL) {
    
    $request  = isset($request)  ? $request  : new Request();
    $response = isset($response) ? $response : new Response();
    
    // Import common module
    $flag = import('common/*');
    
    // Adjust $_GET['q']
    $request->adjust_q();
    
    // Call possible hooks
    if(method_exists('Common_Controller', 'on_shutdown')) {
      register_shutdown_function(array('Common_Controller', 'on_shutdown'), $request, $response);
    }
    if(method_exists('Common_Controller', 'on_dispatch_before')) {
      Common_Controller::on_dispatch_before($request, $response);
    }
    
    $module   = '';
    $action   = '';
    $q        = $request->q();
    if (''==$q) {
      $q = 'default'.$this->qsep.'index';
    }
    
    $qarr = explode($this->qsep, $q);
    if (count($qarr)>0) {
      
      $module = strtolower($qarr[0]);
      $action = isset($qarr[1]) ? strtolower($qarr[1]) : 'index';
      $moddir = $this->modRootDir().$module;
      
      if (is_dir($moddir)) {
        
        $ctlname = ucfirst($module).'_Controller';
        $modfile = $moddir.'/'.$ctlname.'.php';
        
        if (file_exists($modfile)) {
          
          // import all class under "$this->modroot/$module/*"
          import($module, $this->modroot);
          current_module($module); //save current module
          
          // find controller action and dispatch
          $ctrl = new $ctlname();
          $menu = $ctrl->menu(); //hook menu
          
          if (count($menu)) {
            
            foreach ($menu AS $key => $val) {
              if (self::qMatchPattern($key, $q)) {
                $action = $val;
                break;
              }
            }
          }
          
          // dispatch
          if (''!=$action && $ctrl->action_exists($action)) {
            
            // hook init
            $ctrl->init($action,$request,$response);
            
            // dispatch action
            $ctrl->$action($request,$response);
            
            // tigger dispatch after hook
            if(method_exists('Common_Controller', 'on_dispatch_after')) {
              Common_Controller::on_dispatch_after($request, $response);
            }
            
            exit;
          }
          
        }
        
      }
      
    }
    
    throw new NotFoundException();
    
  }
  
  /**
   * Helper function for menu holder place
   */
  protected static function _menu_holder_callback($match) {
    switch ($match[1]) {
      case '%d':
        return '(\d+)'; // Number regex
      case '%s':
        return '([\w-]+)'; // String regex
      case '%S':
      default:
        return '([\x{4e00}-\x{9fa5}\w%=-]+)'; // String regex, more wide character
                                              // !Notice: something wrong for utf8 preg_match
    }
  }
  
  /**
   * Generate menu regular expression by menu pattern
   *
   * @param string $pattern
   * @return string
   */
  public static function genMenuRegexp($pattern) {
    if (empty($pattern)) return '';
    $key_regex = preg_replace_callback(self::MENU_HOLDER_REGEXP, array(get_class(),'_menu_holder_callback'), preg_quote($pattern, '/'));
    $key_regex = '/^'.$key_regex.'$/i'.(FALSE===strrpos($pattern, '%S')?'':'u');
    return $key_regex;
  }
  
  /**
   * Check q & menu pattern whether match
   * 
   * @param string $pattern
   * @param string $q
   * @return boolean
   */
  public static function qMatchPattern($pattern, $q = NULL) {
    if (!isset($q)) $q = Request::q();
    
    $match = false;
    if ($pattern==$q) {
      $match = true;
    }
    else {
      if (preg_match(SimPHP::genMenuRegexp($pattern), $q)) {
        $match = true;
      }
    }
    
    return $match;
  }
  
  /**
   * get module root dir, with the trailing slash
   */
  public function modRootDir() {
    return SIMPHP_ROOT.DS.$this->modroot.DS;
  }
  
  /**
   * Register autoload function
   *
   * @param string $func
   * @param boolean $enable
   * @return boolean
   */
  public static function registerAutoload($func = 'SimPHP::loadPubClass', $enable = TRUE) {
    return $enable ? spl_autoload_register($func) : spl_autoload_unregister($func);
  }
  
  /**
   * Load public access class
   *
   * @param string $className
   * @return boolean
   */
  protected static function loadPubClass($className) {
    
    $class_folder = array('core', 'core/interfaces', 'incs', 'incs/models');
    $class_file = 'class.'.$className.'.php';
    foreach ($class_folder as $folder) {
    	if (file_exists(SIMPHP_ROOT . "/{$folder}/{$class_file}")) {
    		require (SIMPHP_ROOT . "/{$folder}/{$class_file}");
    		break;
    	}
    }
    
    return self::isClassLoaded($className);
  }
  
  /**
   * Check whether a class or interface exists
   * @param string $className
   * @return boolean
   */
  public static function isClassLoaded($className) {
    return (class_exists($className, FALSE) || interface_exists($className, FALSE));
  }
  
  /**
   * magic method '__get'
   */
  public function __get($name) {
    return array_key_exists($name, $this->_appConfig) ? $this->_appConfig[$name] : NULL;
  }
  
  /**
   * magic method '__set'
   */
  public function __set($name, $value) {
    $this->_appConfig[$name] = $value;
  }
  
  
}


/**
 * SimPHP Root Exception
 */
class SimPHPException extends Exception {
  public function __construct($message = null, $code = null) {
    parent::__construct($message, $code);
  }
}

/**
 * Not Found Exception
 */
class NotFoundException extends SimPHPException {
  public function __construct($message = null, $code = null) {
    parent::__construct($message?$message:'404 Not Found.',$code);
  }
}

/**
 * Not Singleton Exception
 */
class NotSingletonException extends SimPHPException {
  public function __construct($className = null) {
    $className = $className ? $className : '';
    parent::__construct( sprintf('Only single %s instance allowed.', $className) );
  }
}

/**
 * PHP Min Version Exception
 */
class PHPMinVersionException extends SimPHPException {
  public function __construct() {
    parent::__construct( sprintf("Your PHP installation is too old, SimPHP requires at least PHP %s.",SIMPHP_PHP_MIN_VERSION) );
  }
}

/**
 * Directory not writable exception
 */
class DirWritableException extends SimPHPException {
  public function __construct($dir) {
    parent::__construct( "Dir '{$dir}' requires writable permission." );
  }  
}

/**
 * File not Exist Exception
 */
class FileNotExistException extends SimPHPException {
  public function __construct($file) {
    parent::__construct("File '{$file}' Not Exist.");
  }
}

/**
 * Some functions
 */

/**
 * PHP script assert
 * 
 * @return void
 */
function ps_assert() {
  //defined('IN_SIMPHP') or die('Access Denied');
  if (!defined('IN_SIMPHP')) {
    echo "<h1>Access Denied</h1>";
    echo "<pre>";
    print_r(debug_backtrace());
    echo "</pre>";
    exit(1);
  }
}






/*----- END FILE: init.php -----*/