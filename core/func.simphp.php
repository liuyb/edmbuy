<?php
/**
 * Core functions of SimPHP
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

/**
 * Validates that a hostname (for example $_SERVER['HTTP_HOST']) is safe.
 *
 * @return
 *  TRUE if only containing valid characters, or FALSE otherwise.
 */
function simphp_valid_http_host($host) {
  return preg_match('/^\[?(?:[a-zA-Z0-9-:\]_]+\.?)+$/', $host);
}

/**
 * Returns the requested URL path of the page being viewed.
 *
 * Examples:
 * - http://example.com/node/306 returns "node/306".
 * - http://example.com/folder/node/306 returns "node/306" while
 *   base_path() returns "/folder/".
 * - http://example.com/path/alias (which is a path alias for node/306) returns
 *   "path/alias" as opposed to the internal path.
 * - http://example.com/index.php returns an empty string (meaning: front page).
 * - http://example.com/index.php?page=1 returns an empty string.
 *
 * @return
 *   The requested SimPHP URL path.
 *
 * @see current_path()
 */
function simphp_request_path($new_path=NULL) {
  static $path;
  
  if (isset($new_path)) {
    $path = $new_path;
    return $path;
  }
  
  if (isset($path)) {
    return $path;
  }

  if (isset($_GET['q']) && is_string($_GET['q'])) {
    // This is a request with a ?q=foo/bar query string. $_GET['q'] is
    // overwritten in simphp_path_initialize(), but simphp_request_path() is called
    // very early in the bootstrap process, so the original value is saved in
    // $path and returned in later calls.
    $path = $_GET['q'];
  }
  elseif (isset($_SERVER['REQUEST_URI'])) {
    // This request is either a clean URL, or 'index.php', or nonsense.
    // Extract the path from REQUEST_URI.
    $request_path = strtok($_SERVER['REQUEST_URI'], '?');
    $base_path_len = strlen(rtrim(dirname($_SERVER['SCRIPT_NAME']), '\/'));
    // Unescape and strip $base_path prefix, leaving q without a leading slash.
    $path = substr(urldecode($request_path), $base_path_len + 1);
    // If the path equals the script filename, either because 'index.php' was
    // explicitly provided in the URL, or because the server added it to
    // $_SERVER['REQUEST_URI'] even when it wasn't provided in the URL (some
    // versions of Microsoft IIS do this), the front page should be served.
    if ($path == basename($_SERVER['PHP_SELF'])) {
      $path = '';
    }
  }
  else {
    // This is the front page.
    $path = '';
  }

  // Under certain conditions Apache's RewriteRule directive prepends the value
  // assigned to $_GET['q'] with a slash. Moreover we can always have a trailing
  // slash in place, hence we need to normalize $_GET['q'].
  $path = trim($path, '/');

  return $path;
}

/**
 * Checks whether a string is valid UTF-8.
 *
 * All functions designed to filter input should use simphp_validate_utf8
 * to ensure they operate on valid UTF-8 strings to prevent bypass of the
 * filter.
 *
 * When text containing an invalid UTF-8 lead byte (0xC0 - 0xFF) is presented
 * as UTF-8 to Internet Explorer 6, the program may misinterpret subsequent
 * bytes. When these subsequent bytes are HTML control characters such as
 * quotes or angle brackets, parts of the text that were deemed safe by filters
 * end up in locations that are potentially unsafe; An onerror attribute that
 * is outside of a tag, and thus deemed safe by a filter, can be interpreted
 * by the browser as if it were inside the tag.
 *
 * This function exploits preg_match behaviour (since PHP 4.3.5) when used
 * with the u modifier, as a fast way to find invalid UTF-8. When the matched
 * string contains an invalid byte sequence, it will fail silently.
 *
 * preg_match may not fail on 4 and 5 octet sequences, even though they
 * are not supported by the specification.
 *
 * The specific preg_match behaviour is present since PHP 4.3.5.
 *
 * @param $text
 *   The text to check.
 * @return
 *   TRUE if the text is valid UTF-8, FALSE if not.
 */
function simphp_validate_utf8($text) {
  if (strlen($text) == 0) {
    return TRUE;
  }
  return (preg_match('/^./us', $text) == 1);
}

/**
 * Encode special characters in a plain-text string for display as HTML.
 *
 * Uses simphp_validate_utf8 to prevent cross site scripting attacks on
 * Internet Explorer 6.
 */
function simphp_check_plain($text) {
  return simphp_validate_utf8($text) ? htmlspecialchars($text, ENT_QUOTES) : '';
}

/**
 * Convert data to UTF-8
 *
 * Requires the iconv, GNU recode or mbstring PHP extension.
 *
 * @param $data
 *   The data to be converted.
 * @param $encoding
 *   The encoding that the data is in
 * @return
 *   Converted data or FALSE.
 */
function simphp_convert_to_utf8($data, $encoding) {
  if (simphp_validate_utf8($data)) return $data;

  if (function_exists('iconv')) {
    $out = @iconv($encoding, 'utf-8', $data);
  }
  else if (function_exists('mb_convert_encoding')) {
    $out = @mb_convert_encoding($data, 'utf-8', $encoding);
  }
  else if (function_exists('recode_string')) {
    $out = @recode_string($encoding .'..utf-8', $data);
  }
  else {
    //simphp_msg('Unsupported encoding %s. Please install iconv, GNU recode or mbstring for PHP.', array('%s' => $encoding));
    return FALSE;
  }

  return $out;
}

/**
 * return page header expires datetime, format is "Y-m-d H:i:s"
 */
function simphp_page_expires() {
  return date('Y-m-d H:i:s', time() + (isset($_GET['maxage'])?intval($_GET['maxage']):0));
}
/**
 * Safely strip all no-need charactors for rendering html
 */
function simphp_ziphtml($html) {
  $html = preg_replace(array('!/\*.*\*/!'), array(''), $html); //这句视情况决定是否需要
  $html = trim($html); // 清除首尾空白
  $tarr = explode("\n", $html);
  if (count($tarr)) {
    $html = '';
    $str  = '';
    foreach ($tarr AS $it) {
      $str = trim($it);
      
      //对单行注释(//)特殊处理
      $pos = strpos($str, '//');
      if (false === $pos) { //不存在标记 '//'，则直接拼接
        $html .= $str;
      }
      else { //存在标记 '//'，则需加上换行"\n"拼接
        $html .= $str."\n";
      }
    }
  }
  return $html;
}

/**
 * merge array recursively, like array_merge_recursive(), it will merge recursively; 
 * But and like array_merge(), the later one will overcome the previous one
 * @return Ambigous <mixed, unknown>
 */
function simphp_array_merge() {

  $arrays = func_get_args();
  $base = array_shift($arrays);

  foreach ($arrays as $array) {
    reset($base); //important
    while (list($key, $value) = @each($array)) {
      if (is_array($value) && @is_array($base[$key])) {
        $base[$key] = simphp_array_merge($base[$key], $value);
      } else {
        $base[$key] = $value;
      }
    }
  }

  return $base;
}

/**
 * Get the current GMT timestamp or translate time to GMT(Greenwich Time)
 * 
 * @param integer $time a appointing time, optional
 * @return number
 */
function simphp_gmtime($time = NULL) {
  if (!isset($time)) $time = time();
  return ($time - date('Z'));
}

/**
 * Translate GTM timestamp to current timezone timestamp
 * @param integer $gmtime
 * @return number
 */
function simphp_gmtime2std($gmtime) {
	return ($gmtime + date('Z'));
}

/**
 * Return system require timestamp
 *
 * @param $strict, when $strict is TRUE, then use microtime(TRUE) for current strict time
 * @return
 *   $_SERVER["REQUEST_TIME"]
 */
function simphp_time($strict = FALSE) {
  return $strict ? microtime(TRUE) : (isset($_SERVER['REQUEST_TIME']) ? $_SERVER['REQUEST_TIME'] : time());
}

/**
 * Return the current system timestamp with milliseconds union
 * 
 * @param float $time
 * @return number 13 bitwidth integer
 */
function simphp_msec($time = NULL) {
	$time = isset($time) ? $time : microtime(TRUE);
	return round($time*1000);
}

/**
 * Return system require datetime, using DateTime::W3C format: 'Y-m-d\TH:i:sP'
 *
 * Require PHP 5 >= 5.2.0
 * @param string $format: format type: 'std' or 'w3c'
 * @param int $time, optional, is an integer Unix timestamp that defaults to the current local time if a timestamp is not given
 * @return
 *   date('Y-m-d\TH:i:sP',$time)
 */
function simphp_dtime($format = 'std',$time = NULL) {
  $dtformat = $format=='std'?'Y-m-d H:i:s':DateTime::W3C;
  return date($dtformat, isset($time) ? $time : (isset($_SERVER['REQUEST_TIME']) ? $_SERVER['REQUEST_TIME'] : time()));
}

/**
 * transform datetime string to UNIX timestamp
 *
 * @param string $datetime
 * @return int
 */
function simphp_str2time($datetime) {
  return strtotime($datetime);
}

/**
 * transform zone datetime string to standard(use app setting timezone) UNIX timestamp
 * @param string $datetime
 * @param string $timezone
 * @return string
 */
function simphp_zonetime2stdtime($datetime, $timezone='') {
  if (empty($timezone)) return strtotime($datetime);
  $dt = new DateTime($datetime,new DateTimeZone($timezone));
  return $dt->getTimestamp();
}

/**
 * Return a component of the current SimPHP path.
 *
 * When viewing a page at the path "admin/content/types", for example, arg(0)
 * would return "admin", arg(1) would return "content", and arg(2) would return
 * "types".
 *
 * Avoid use of this function where possible, as resulting code is hard to read.
 * Instead, attempt to use named arguments in menu callback functions. See the
 * explanation in menu.inc for how to construct callbacks that take arguments.
 *
 * @param $index
 *   The index of the component, where each component is separated by a '/'
 *   (forward-slash), and where the first component has an index of 0 (zero).
 *
 * @return
 *   The component specified by $index, or NULL if the specified component was
 *   not found.
 */
function arg($index = NULL, $path = NULL) {
  static $arguments;

  if (!isset($path)) {
    $path = simphp_request_path();
  }
  if (!isset($arguments[$path])) {
    $arguments[$path] = explode('/', $path);
  }
  if (!isset($index)) {
    return $arguments[$path];
  }
  if (isset($arguments[$path][$index])) {
    return $arguments[$path][$index];
  }
}

/**
 * Starts the timer with the specified name.
 *
 * If you start and stop the same timer multiple times, the measured intervals
 * will be accumulated.
 *
 * @param $name
 *   The name of the timer.
 */
function timer_start($name) {
  global $timers;

  $timers[$name]['start'] = microtime(TRUE);
  $timers[$name]['count'] = isset($timers[$name]['count']) ? ++$timers[$name]['count'] : 1;
}

/**
 * Reads the current timer value without stopping the timer.
 *
 * @param $name
 *   The name of the timer.
 *
 * @return
 *   The current timer value in ms.
 */
function timer_read($name) {
  global $timers;

  if (isset($timers[$name]['start'])) {
    $stop = microtime(TRUE);
    $diff = round(($stop - $timers[$name]['start']) * 1000, 2);

    if (isset($timers[$name]['time'])) {
      $diff += $timers[$name]['time'];
    }
    return $diff;
  }
  return $timers[$name]['time'];
}

/**
 * Stops the timer with the specified name.
 *
 * @param $name
 *   The name of the timer.
 *
 * @return
 *   A timer array. The array contains the number of times the timer has been
 *   started and stopped (count) and the accumulated timer value in ms (time).
 */
function timer_stop($name) {
  global $timers;

  if (isset($timers[$name]['start'])) {
    $stop = microtime(TRUE);
    $diff = round(($stop - $timers[$name]['start']) * 1000, 2);
    if (isset($timers[$name]['time'])) {
      $timers[$name]['time'] += $diff;
    }
    else {
      $timers[$name]['time'] = $diff;
    }
    unset($timers[$name]['start']);
  }

  return $timers[$name];
}

/**
 * get or set current SimPHP module
 * @param string $mod
 * @return Ambigous <NULL, unknown>
 */
function current_module($mod=null) {
  static $cmod = null;

  if (isset($mod)) {
    $cmod = $mod;
  }
  elseif (!isset($cmod)) {
    $cmod = strtolower(arg(0));
  }

  return $cmod;
}

/**
 * generate SimPHP a link url
 * @param string $q
 */
function A($q = '') {
  return View::link($q);
}

/**
 * return SimPHP link url connector
 * @return string
 */
function AC() {
  return View::link_connector();
}

/**
 * alias function of Config::get
 * @param string $path
 * @param mixed(string or array) $default
 * @return Ambigous <unknown, multitype:>
 */
function C($path, $default = NULL) {
  return Config::get($path,$default);
}

/**
 * entry function of DB::I()
 * 
 * @return DB
 */
function D() {
  if ( !isset(SimPHP::$db) || !(SimPHP::$db instanceof DB) ) {
    $config_write = C('storage.mysql-'.DB::WRITABLE);
    $config_read  = C('storage.mysql-'.DB::READONLY);
    $config_extra = C('storage.mysql-config');
    $len_write    = count($config_write);
    $len_read     = count($config_read);
    $idx_write    = $len_write < 2 ? 0 : mt_rand(0, $len_write-1);
    $idx_read     = $len_read < 2 ? 0 : mt_rand(0, $len_read-1);
    SimPHP::$db   = DB::I(isset($config_write[$idx_write]) ? $config_write[$idx_write] : array(),
                          isset($config_read[$idx_read]) ? $config_read[$idx_read] : array(),
                          $config_extra);
  }
  return SimPHP::$db;
}

/**
 * Memcache/Redis object generator
 * 
 * @param  string $type cache type, option values: 'memcache':Memcache; 'redis':Redis
 * @return object
 */
function M($type = 'memcache') {
  static $_mm = array();
  if (!in_array($type, array('memcache','redis'))) {
  	$type = 'memcache';
  }
  if (!isset($_mm[$type])) {
    $mm_node = C('storage.'.$type.'.node');
    $mm_node = $mm_node[0];
    if ('memcache'==$type) {
    	$_mm[$type] = new Memcache();
    	$_mm[$type]->connect($mm_node['host'], $mm_node['port']) or die('Connect to Memcache Fail.');
    }
    elseif ('redis'==$type) {
    	$_mm[$type] = new Redis();
    	$_mm[$type]->connect($mm_node['host'], $mm_node['port']) or die('Connect to Redis Fail.');
    	$_mm[$type]->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
    	$_mm[$type]->setOption(Redis::OPT_PREFIX, $mm_node['prefix']);
    }
  }
  return $_mm[$type];
}

/**
 * get language display by name
 * 
 * @param string $name, when NULL, return all lang value
 * @return string or array
 */
function L($name = NULL) {
  static $_lang;
  if (!isset($_lang)) {
    $lang  = Config::get('env.lang','zh-cn');
    $_lang = include (SIMPHP_ROOT."/lang/{$lang}.php");
  }
  return isset($name) ? (isset($_lang[$name]) ? $_lang[$name] : '') : $_lang;
}

/**
 * Generating php template absolute path by tpl name
 * 
 * @param string $tpl_name template name
 * @return string
 */
function T($tpl_name) {
  $tpl = Template::$_instance;
  if ('PlainTpl' === $tpl->driverClass) {
    $tplrealpath  = $tpl->driverObj->compile(View::tpl_realpath($tpl_name));
    return $tplrealpath;
  }
  return '';
}

/**
 * Packaging a valid URL
 * 
 * @param string $uri the input uri
 * @param string|array $vars the input parameters
 * @param boolean $domain whether display domain, default to FALSE
 * @param boolean $no_spm whether attached spm info auto
 * @return string
 */
function U($uri = '', $vars = '', $domain = FALSE, $no_spm = FALSE) {
  
  // Cache some variables
  static $is_clean, $ctx_path, $site_domain;
  if (!isset($is_clean)) {
    $is_clean = Config::get('env.cleanurl',0);
  }
  if (!isset($ctx_path)) {
    $ctx_path = Config::get('env.contextpath','/');
  }
  if (!isset($site_domain)) {
    $site_domain = Config::get('env.site.mobile','');
  }
  
  // Check query parameters
  if(is_string($vars)) { // var1=1&var2=2 translate to array
    parse_str($vars, $vars);
  }
  elseif(!is_array($vars)){
    $vars = array();
  }
  
  // Check input uri
  if (preg_match('/^\//', $uri)) { //begin as '/'
    $uri = substr($uri, 1); //strip the beginning '/'
  }
  
  // Whether clean url
  if ($is_clean) {
    $uri  = $ctx_path . $uri;
  }
  else {
    $uri  = $ctx_path . '?q=' .$uri;
  }
  
  // spm
  if (!$no_spm && !isset($vars['spm'])) {
  	$spm = isset($_GET['spm']) ? $_GET['spm'] : '';
  	if (!empty($spm)) {
  		$vars['spm'] = $spm;
  	}
  }
  
  // Append query string
  if (!empty($vars)) {
    $vars = http_build_query($vars);
    $uri .= (strpos($uri,'?')===false?'?':'&'). $vars;
  }
  
  // Check domain
  if ($domain && ''!=$site_domain) {
    $uri = $site_domain . $uri;
  }
  
  return $uri;
}

/**
 * 
 * @return boolean
 */
function is_fpipe() {
  static $bval;
  if (!isset($bval)) {
    if (isset($_GET['__nofpipe__'])) {
      $bval = !empty($_GET['__nofpipe__']) ? FALSE : TRUE;
    }
    else {
      $bval = $GLOBALS['user']->uid ? TRUE : FALSE;
    }
  }
  $bval = false;
  return $bval;
}

/**
 * import models of other module
 * 
 * usage:
 *   import('user/User_Model'); // just import User_Model class
 *   import('user/*');
 *   import('user/');
 *   import('user');
 * @param string $model_name
 * @param string $dir_prefix, dir prefix, no the trailing slash, default equal to SimPHP::$gConfig['modroot']
 * @return mixed(boolean or object)
 */
function import($model_name, $dir_prefix = '') {

  if (''==$model_name) {
    return FALSE;
  }

  // find class name and dir prefix
  $modroot = SimPHP::$gConfig['modroot'];
  if (''!=$dir_prefix) {
    $modroot = rtrim($dir_prefix,'/');
  }
  $moddir  = SIMPHP_ROOT . DS . $modroot;
  $clsname = '';
  $tarr = explode('/', $model_name);
  $tarr[0] = strtolower($tarr[0]); //make sure dir is lower
  if (count($tarr)>1) {
    $clsname = array_pop($tarr); //the last one is the checking one
    $moddir .= DS.implode(DS, $tarr);
  }
  else { //When import like Model::import('user');，equal to Model::import('user/*');
    $clsname = '';
    $moddir .= DS.$tarr[0];
  }
  if (!is_dir($moddir)) {
    return FALSE;
  }
  
  $is_import_dir = '*'==$clsname || ''==$clsname ? TRUE : FALSE;
  
  // Only import a class
  if (!$is_import_dir) {
    if (SimPHP::isClassLoaded($clsname)) return TRUE;
    $class_file = $moddir.'/'.$clsname.'.php';
    if (file_exists($class_file)) {
      include ($class_file);
    }
    return SimPHP::isClassLoaded($clsname);
  }

  // Set class auto load
  SimPHP::registerAutoload(function($className) use($moddir) {
    if (SimPHP::isClassLoaded($className)) return TRUE;
    $class_file = $moddir.'/'.$className.'.php';
    if (file_exists($class_file)) {
      include ($class_file);
    }
    return SimPHP::isClassLoaded($className);
  });

  return TRUE;

}

/**
 * Print debug msg to response header by FirePHP
 *
 * @param mixed $var, printing message
 * @param string $label, label, option
 */
function fire_debug($var,$label=''){
  static $loaded = NULL;
  if (!$loaded) {
    $loaded=include(SIMPHP_CORE.'/libs/FirePHPCore/FirePHP.class.php');
  }
  $firephp = FirePHP::getInstance(true);
  $firephp->log($var,$label);
}

/**
 * 根据简称计算Uninx时间戮
 *
 * $short: 简称，可选的值有:
 *   	'xz'|'now': 现在Unix时间戮
 *   	'qt':    前天起始Unix时间戮
 *   	'zt':    昨天起始Unix时间戮
 * 		'jt':    今天起始Unix时间戮
 * 		'mt':    明天起始Unix时间戮
 * 		'ht':    后天起始Unix时间戮
 * 		'jn':    今年起始Unix时间戮
 *
 * 		'-3h':   过去3小时的Unix时间戮
 * 		'+3h':   未来3小时的Unix时间戮
 * 		'-15m':  过去15分钟的Unix时间戮
 * 		'+15m':  未来15分钟的Unix时间戮
 * 		'-10s':  过去10秒的Unix时间戮
 * 		'+10s':  未来10秒的Unix时间戮
 *
 * 		'-10Y':  过去10年的Unix时间戮
 * 		'+10Y':  未来10年的Unix时间戮
 * 		'-1M':   过去1个月的Unix时间戮
 * 		'+1M':   未来1个月的Unix时间戮
 * 		'-14D':  过去14日(两周)的Unix时间戮
 * 		'+14D':  未来14日(两周)的Unix时间戮
 *
 * 		'z-14D':  14日前起始的Unix时间戮
 * 		'z+14D':  14日后起始的Unix时间戮
 *    ...
 *    其他类同
 * $format: 可选参数，格式同date()函数一样，默认为''，如果输入非fotmat的TRUE值，则默认使用格式：'Y-m-d H:i:s';
 */
function shorttotime($short, $format='') {
  $thetime = 0;
  if ($short == 'xz' || $short == 'now') {
    $thetime = time();
  }
  elseif ($short == 'qt') {
    $thetime = strtotime('today')-2*86400;
  }
  elseif ($short == 'zt') {
    $thetime = strtotime('yesterday');
  }
  elseif ($short == 'jt') {
    $thetime = strtotime('today');
  }
  elseif ($short == 'mt') {
    $thetime = strtotime('tomorrow');
  }
  elseif ($short == 'ht') {
    $thetime = strtotime('today')+2*86400;
  }
  elseif ($short == 'jn') {
    $thetime = strtotime(date('Y').'-01-01');
  }
  else {
    $key_arr = array("/h/",   "/m/",     "/s/",     "/Y/",   "/M/",    "/D/");
    $rep_arr = array(" hour", " minute", " second", " year", " month", " day");
    $flag = substr($short, 0, 1);
    if ($flag!='+' && $flag!='-') {
      $short = substr($short, 1);
      $flag  = 'z';
    }

    $num = intval(substr($short, 1, -1));
    if ($num > 1) {
      $rep_arr = array(" hours"," minutes"," seconds"," years"," months"," days");
    }
    $short = preg_replace_once($key_arr, $rep_arr, $short);
    $thetime = strtotime($short);

    if ($flag == 'z') {
      $thedate = date('Y-n-j', $thetime);
      $thedate = explode('-', $thedate);
      $thetime= mktime(0,0,0,$thedate[1], $thedate[2], $thedate[0]);
    }
  }

  if ( !empty($format) ) {
    if (intval($format) == 1) {
      $format = 'Y-m-d H:i:s';
    }
    return date($format, $thetime);
  }
  return $thetime;
}

/**
 * 返回页面会话TOKEN，用于确定客户端来源，防止非法提交
 * @return string
 */
function sess_token() {
	$enckey = defined('SESS_TOKEN') ? SESS_TOKEN : md5('SimPHP Session Token');
	return md5(session_id().$enckey);
}

/*----- END FILE: func.simphp.php -----*/