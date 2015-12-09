<?php
/**
 * Configure file loading
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Config {
  
  private static $cfile  = array();
  private static $cache  = array();

  /**
   * get configure value according to config path
   * @param string $path
   * @param mixed(string or array) $default
   * @return Ambigous <unknown, multitype:>
   */
  public static function get($path, $default = NULL) {
    $c = self::match($path);
    return isset($c) ? $c : $default;
  }
  
  /**
   * set configure value, may be overwrite the configure in conf/ dir
   * @param string $path
   * @param mixed(string or array) $value
   */
  public static function set($path, $value) {
    self::match($path); // Make sure load the default configure first
    self::$cache[$path] = $value;
  }
  
  /**
   * matching configure
   */
  private static function match($path) {
    
    if (!isset(self::$cache[$path])) {
      $nodes = explode('.', $path);
      $root  = array_shift($nodes);
      self::$cache[$path] = self::find($nodes, self::load($root));
    }
    
    return self::$cache[$path];
  }
  
  /**
   * find configure from loaded config files recursively
   */
  private static function find($nodes, $config) {
    
    while ($nodes) {
      
      $node = array_shift($nodes);
      if (!isset($config[$node])) {
        $config = null;
        break;
      }
      $config = $config[$node];
    }
    
    return $config;
  }
  
  /**
   * load configure file
   */
  private static function load($root) {
    
    if(!isset(self::$cfile[$root])) {
      $confdir = isset(SimPHP::$gConfig['confdir']) ? SimPHP::$gConfig['confdir'] : 'conf';
      $config = include (SIMPHP_ROOT.DS.$confdir.DS.$root.'.php');
      self::$cfile[$root] = false === $config ? array() : $config;
    }
    
    return self::$cfile[$root];
  }
}
 
/*----- END FILE: class.Config.php -----*/