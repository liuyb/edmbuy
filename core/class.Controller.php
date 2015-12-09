<?php
/**
 * SimPHP Controller Base Class
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Controller {
  
  /**
   * a View object instance
   * @var View
   */
  protected $v;
  
  /**
   * reserved method set
   * @var array
   */
  protected static $reserved_method_name = array('menu','init','action_exists','global_init');
  
  /**
   * Constructor
   */
  public function __construct() {
    
  }
  
  /**
   * check whether action exists
   * 
   * @param string $name
   * @return boolean
   */
  public function action_exists($name) {
    return method_exists(get_called_class(), $name) && !in_array($name, self::$reserved_method_name);
  }
  
  /**
   * hook menu
   * 
   * @return array
   */
  public function menu() {
    return array();
  }
  
  /**
   * hook init
   * @param string $action
   * @param Request $request
   * @param Response $response
   */
  final public function global_init($action, Request $request, Response $response) {
    
  }
  
  /**
   * hook init
   * @param string $action
   * @param Request $request
   * @param Response $response
   */
  public function init($action, Request $request, Response $response) {
    
  }
  
  /**
   * default action 'index'
   * @param Request $request
   * @param Response $response
   */
  public function index(Request $request, Response $response) {
    
  }
  
  /**
   * magic method '__call'
   * 
   * @param string $method
   * @param array $arguments
   */
  public function __call($method,$arguments) {
    throw new NotFoundException();
  }
  
}
 
 
/*----- END FILE: class.Controller.php -----*/