<?php
/**
 * SimPHP Base Class
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
class CBase {
  
  /**
   * unset object properties set
   * @var array
   */
  protected $__DATA__ = array();
  
  /**
   * magic method '__get'
   *
   * @param string $name
   */
  public function __get($name) {
    return array_key_exists($name, $this->__DATA__) ? $this->__DATA__[$name] : null;
  }
  
  /**
   * magic method '__set'
   *
   * @param string $name
   * @param string $value
   */
  public function __set($name, $value) {
    $this->__DATA__[$name] = $value;
  }
  
	/**
	 * magic method '__isset'
	 * 
	 * @param string $name
	 */
	public function __isset($name) {
		return isset($this->__DATA__[$name]);
	}
	
	/**
	 * magic method '__unset'
	 * 
	 * @param string $name
	 */
	public function __unset($name) {
		if (isset($this->__DATA__[$name])) unset($this->__DATA__[$name]);
	}
	
}
 
/*----- END FILE: class.CBase.php -----*/