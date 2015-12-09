<?php
/**
 * Cookie Class
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Cookie {
  
  /**
   * Cookie configure
   * @var array
   */
  protected static $_cookie_conf = array();
  
  /**
   * set or unset(when $value===null) a cookie
   * @param string $name the name of the cookie, when with '|raw' postfix, indicating raw cookie set
   * @param mixed $value the value of the cookie
   * @param integer $expire how many seconds to expire, expire parameter of setcookie
   * @param bool $httponly httponly parameter of setcookie
   * @return boolean
   */
  public static function set($name, $value = '', $expire = -1, $httponly = false) {
    $conf = self::conf();
    $name = strpos($name, '|raw') === false ? $conf['prefix'].'['.$name.']' : str_replace('|raw', '', $name);
    $expire = $expire >= 0 ? $expire : $conf['lifetime'];
    $expire = null === $value ? (time() - 86400) : (time() + $expire);
    $secure = $_SERVER['SERVER_PORT'] == '443' ? true : false;
    return setcookie($name, $value, $expire, $conf['path'], $conf['domain'], $secure, $httponly);
  }
  
  /**
   * Get a cookie value
   * @param string $name
   * @return mixed
   */
  public static function get($name) {
    $prefix = self::conf('prefix');
    return isset($_COOKIE[$prefix][$name]) ? $_COOKIE[$prefix][$name] : false;
  }
  
  /**
   * Update a cookie value 
   * @param string $name
   * @param string $value
   */
  public static function update($name, $value) {
    $prefix = self::conf('prefix');
    $_COOKIE[$prefix][$name] = $value;
  }
  
  /**
   * Remove a cookie
   * @param string $name
   */
  public static function remove($name) {
    self::set($name, null);
  }
  
  /**
   * set or unset(when $value===null) a cookie (raw version of Cookie::set)
   * @param string $name the name of the cookie
   * @param mixed $value the value of the cookie
   * @param integer $expire how many seconds to expire, expire parameter of setcookie
   * @param bool $httponly httponly parameter of setcookie
   * @return boolean
   */
  public static function raw_set($name, $value = '', $expire = -1, $httponly = false) {
    return self::set($name.'|raw', $value, $expire, $httponly);
  }
  
  /**
   * Get a cookie value (raw version of Cookie::get)
   * @param string $name
   * @return mixed
   */
  public static function raw_get($name) {
    return isset($_COOKIE[$name]) ? $_COOKIE[$name] : false;
  }
  
  /**
   * Update a cookie value (raw version of Cookie::update)
   * @param string $name
   * @param string $value
   */
  public static function raw_update($name, $value) {
    $_COOKIE[$name] = $value;
  }
  
  /**
   * Remove a cookie (raw version of Cookie::remove)
   * @param string $name
   */
  public static function raw_remove($name) {
    self::raw_set($name, null);
  }
  
  /**
   * Get cookie configure
   * @param string $key
   * @param string $node, default to 'default'
   * @return mixed(array or string)
   */
  protected static function conf($key = null) {
    $node = SimPHP::$gConfig['sessnode'];
    if ( empty($node) ) $node = 'default';
    if ( !isset(self::$_cookie_conf[$node]) ) {
      self::$_cookie_conf[$node] = Config::get("storage.cookie.{$node}");
    }
    if (null === $key) {
      return self::$_cookie_conf[$node];
    }
    return isset(self::$_cookie_conf[$node][$key]) ? self::$_cookie_conf[$node][$key] : null;
  }
}
 
/*----- END FILE: class.Cookie.php -----*/