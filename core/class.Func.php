<?php
/**
 * Static Functions Class 'Func::'
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
class Func extends CStatic {
  
  /**
   * create a directory recursively
   *
   * @param string $dir, the directory path
   * @param string $mode, the mode is 0777 by default, optional
   * @param bool $recursive, whether create directory recursively
   */
  public static function mkdirs($dir, $mode = '', $recursive = TRUE) {
    if (!$mode) {
      $mode = 0777;
    }
  
    mkdir($dir, $mode, $recursive);
    chmod($dir, $mode);
  
    return is_dir($dir);
  }
  
  /**
   * create a length = $len random charactor string
   * @param $len
   * @return string
   */
  public static function randchar($len = 6) {
    $str = '';
    for ($i = 0; $i < $len; $i++){
      $str .= chr(mt_rand(97, 122));
    }
    return $str;
  }
  
  /**
   * gen password salt
   */
  public static function gen_salt() {
    return substr(uniqid(rand()), -6);
  }
  
  /**
   * gen encoded password
   * @param string $password_raw
   * @param string $salt
   * @return encoded password
   */
  public static function gen_salt_password($password_raw, $salt = NULL, $len = 40) {
    $len = in_array($len,array(32,40)) ? $len : 32;
    $encfunc = $len==40 ? 'sha1' : 'md5';
    $password_enc = preg_match("/^\w{{$len}}$/", $password_raw) ? $password_raw : $encfunc($password_raw);
    if (!isset($salt)) {
      $salt = gen_salt();
    }
    return strtoupper($encfunc($password_enc . $salt));
  }
  
  /**
   * get client ip
   */
  public static function get_clientip() {
    static $CLI_IP = NULL;
    if (isset($CLI_IP)) {
      return $CLI_IP;
    }
  
    //~ get client ip
    if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
      $CLI_IP = getenv('HTTP_PHP_IP');
    }
    elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
      $CLI_IP = getenv('HTTP_X_FORWARDED_FOR');
    }
    elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
      $CLI_IP = getenv('REMOTE_ADDR');
    }
    elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
      $CLI_IP = $_SERVER['REMOTE_ADDR'];
    }
    preg_match("/[\d\.]{7,15}/", $CLI_IP, $ipmatches);
    $CLI_IP = $ipmatches[0] ? $ipmatches[0] : 'unknown';
  
    return $CLI_IP;
  }
  
  /**
   * 正值表达式比对解析$_SERVER['HTTP_USER_AGENT']中的字符串，获取访问用户的浏览器的信息
   */
  public static function get_client_platform() {
    $Agent = $_SERVER['HTTP_USER_AGENT'];
    $browserplatform='';
    if (preg_match('/win/i',$Agent) && strpos($Agent, '95')) {
      $browserplatform="Windows 95";
    }
    elseif (preg_match('/win 9x/i',$Agent) && strpos($Agent, '4.90')) {
      $browserplatform="Windows ME";
    }
    elseif (preg_match('/win/i',$Agent) && preg_match('/98/',$Agent)) {
      $browserplatform="Windows 98";
    }
    elseif (preg_match('/win/i',$Agent) && preg_match('/nt 5.0/i',$Agent)) {
      $browserplatform="Windows 2000";
    }
    elseif (preg_match('/win/i',$Agent) && preg_match('/nt 5.1/i',$Agent)) {
      $browserplatform="Windows XP";
    }
    elseif (preg_match('/win/i',$Agent) && preg_match('/nt 6.0/i',$Agent)) {
      $browserplatform="Windows Vista";
    }
    elseif (preg_match('/win/i',$Agent) && preg_match('/nt 6.1/i',$Agent)) {
      $browserplatform="Windows 7";
    }
    elseif (preg_match('/win/i',$Agent) && preg_match('/nt 6.2/i',$Agent)) {
      $browserplatform="Windows 8";
    }
    elseif (preg_match('/win/i',$Agent) && preg_match('/32/',$Agent)) {
      $browserplatform="Windows 32";
    }
    elseif (preg_match('/win/i',$Agent) && preg_match('/nt/i',$Agent)) {
      $browserplatform="Windows NT";
    }elseif (preg_match('/Mac OS X [0-9._]{1,10}/i',$Agent,$version)) {
      $browserplatform=$version[0];
    }elseif (preg_match('/Mac OS/i',$Agent)) {
      $browserplatform="Mac OS";
    }
    elseif (preg_match('/linux/i',$Agent)) {
      $browserplatform="Linux";
    }
    elseif (preg_match('/unix/i',$Agent)) {
      $browserplatform="Unix";
    }
    elseif (preg_match('/sun/i',$Agent) && preg_match('/os/i',$Agent)) {
      $browserplatform="SunOS";
    }
    elseif (preg_match('/ibm/i',$Agent) && preg_match('/os/i',$Agent)) {
      $browserplatform="IBM OS/2";
    }
    elseif (preg_match('/Mac/i',$Agent) && preg_match('/PC/i',$Agent)) {
      $browserplatform="Macintosh";
    }
    elseif (preg_match('/PowerPC/i',$Agent)) {
      $browserplatform="PowerPC";
    }
    elseif (preg_match('/AIX/i',$Agent)) {
      $browserplatform="AIX";
    }
    elseif (preg_match('/HPUX/i',$Agent)) {
      $browserplatform="HPUX";
    }
    elseif (preg_match('/NetBSD/i',$Agent)) {
      $browserplatform="NetBSD";
    }
    elseif (preg_match('/BSD/i',$Agent)) {
      $browserplatform="BSD";
    }
    elseif (preg_match('/OSF1/',$Agent)) {
      $browserplatform="OSF1";
    }
    elseif (preg_match('/IRIX/',$Agent)) {
      $browserplatform="IRIX";
    }
    elseif (preg_match('/FreeBSD/i',$Agent)) {
      $browserplatform="FreeBSD";
    }
    if ($browserplatform=='') {$browserplatform = "Unknown";}
  
    return $browserplatform;
  }
  
  /**
   * 正值表达式比对解析$_SERVER['HTTP_USER_AGENT']中的字符串，获取访问用户的浏览器的信息
   */
  public static function get_client_browser() {
    $Agent = $_SERVER['HTTP_USER_AGENT'];
    $browseragent   = '';      //浏览器
    $browserversion = '';      //浏览器的版本
    $version        = array(); //保存匹配结果
    if (preg_match('/MSIE ([0-9.]{1,5})/',$Agent,$version)) {
      $browserversion = $version[1];
      $browseragent   = "Internet Explorer";
    }
    elseif (preg_match('/Opera\/([0-9.]{1,5})/',$Agent,$version)) {
      $browserversion = $version[1];
      $browseragent   = "Opera";
    }
    elseif (preg_match('/Firefox\/([0-9.]{1,5})/',$Agent,$version)) {
      $browserversion = $version[1];
      $browseragent   = "Firefox";
    }
    elseif (preg_match('/Chrome\/([0-9.]{1,15})/',$Agent,$version)) {
      $browserversion = $version[1];
      $browseragent   = "Chrome";
    }
    elseif (preg_match('/Safari\/([0-9.]{1,15})/',$Agent,$version)) {
      $browserversion = $version[1];
      $browseragent   = "Safari";
    }
    else {
      $browserversion = '';
      $browseragent   = 'Unknown';
    }
  
    return $browseragent." ".$browserversion;
  }
  
  /**
   * truncate string safely
   * @param string $string
   * @param integer $length optional default 80
   * @param string $etc optional default '...'
   * @param boolean $middle optional default false
   * @return string
   */
  public static function mb_truncate($string, $length = 80, $etc = '...', $middle = false) {
    if ($length == 0)
      return '';
  
    $string = strip_tags($string);
    $len 	= strlen($string);
    $mb_len	= mb_strlen($string, 'UTF-8');
    if ( $len == $mb_len ) {		// all of $string is ansi char
      //$length <<= 1;
      if ( $len > $length ) {
        if( !$middle ) {
          return substr($string, 0, $length) . $etc;
        } else {
          return substr($string, 0, $length>>1) . $etc . substr($string, -$length>>1, $length>>1);
        }
      } else {
        return $string;
      }
    } else {		// contain non-ansi char
      if ( $mb_len > $length ) {
        if( !$middle ) {
          return mb_substr($string, 0, $length, 'UTF-8') . $etc;
        } else {
          return mb_substr($string, 0, $length>>1, 'UTF-8') . $etc . mb_substr($string, -$length>>1, $length>>1, 'UTF-8');
        }
      } else {
        return $string;
      }
    }
  }
  
  /**
   * print debug info to response header using FirePHP
   * @param mixed $var, printing message
   * @param string $label, label, option
   * @return void
   */
  public static function header_debug($var,$label=''){
    static $firephp = NULL;
    if (!isset($firephp)) {
      include(SIMPHP_CORE.'/libs/FirePHPCore/FirePHP.class.php');
      $firephp = FirePHP::getInstance(true);
    }
    $firephp->log($var,$label);
  }
  
}
 
/*----- END FILE: class.Func.php -----*/