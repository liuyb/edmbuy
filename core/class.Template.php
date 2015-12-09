<?php
/**
 * Template Interface of Template Driver Class
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Template {
  
  /**
   * template driver object
   * @var Object
   */
  public $driverObj  = null;
  
  /**
   * template configure array
   * @var array
   */
  protected $_tplConfig = array(
      'driverClass' => 'Smarty',                //template driver class, optional value: 'Smarty', 'PlainTpl'... 
      'driverEntry' => '/core/libs/smarty2/Smarty.class.php', //entry point file of driver
      'driverAssign'=> 'assign',                //driver assign function name
      'driverAssignRef'=> 'assign_by_ref',      //driver assign_by_ref function name, assign_by_ref for Smarty2 and assignByRef for Smarty3
      'driverRender'=> 'fetch',                 //driver assign function name
      'driverConfig'=> array(                   //driver configure, decided by driverClass
          'left_delimiter' => '<!--{',
          'right_delimiter'=> '}-->',
          //...and so on
        ),
    );
  
  //- gen singleton instance---------------BEGIN
  /**
   * The singleton instance
   * @var Template
   */
  public static $_instance;
  public static function I(Array $tplConfig = array()) {
    if ( !isset(self::$_instance) ) {
      self::$_instance = new self($tplConfig);
    }
    return self::$_instance;
  }
  //- gen singleton instance-----------------END
  
  /**
   * constructor, set to private for only singleton instance allowed
   * 
   * @param array $tplConfig
   * @throws TemplateException
   */
  private function __construct(Array $tplConfig = array()) {
    
    $this->_tplConfig = simphp_array_merge($this->_tplConfig, $tplConfig);
    if ('PlainTpl' === $this->driverClass) {
      $this->driverEntry = '/core/libs/plaintpl/PlainTpl.class.php';
    }
    //...other template engine
    
    $driverFile = SIMPHP_ROOT . $this->driverEntry;
    if (!file_exists($driverFile)) {
      throw new TemplateException("Template Driver Entry File '{$driverFile}' Not Found.");
    }
    
    include($driverFile);
    if (!SimPHP::isClassLoaded($this->driverClass)) {
      throw new TemplateClassNotFoundException($this->driverClass);
    }
    
    // Create and Configure Template Driver Class
    $this->driverObj = new $this->driverClass();
    if ($this->driverConfig) {
      foreach ($this->driverConfig AS $key=>$val) {
        $this->driverObj->$key = $val;
      }
    }
    
    // Register Template Functions
    $this->register_functions();
    
    // Regsiter Template Blocks
    $this->register_blocks();
    
  }
  
  /**
   * register template functions
   */
  public function register_functions() {
    if ('Smarty'==$this->driverClass) {
      $class = new ReflectionClass('Tpl');
      $methods = $class->getMethods(ReflectionMethod::IS_STATIC | ReflectionMethod::IS_PUBLIC);
      if (!empty($methods)) {
        foreach ($methods AS $method) {
          $this->driverObj->register_function($method->name,array('Tpl', $method->name));
        }
      }
    }
  }
  
  /**
   * register template blocks
   */
  public function register_blocks() {
    if ('Smarty'==$this->driverClass) {
      $class = new ReflectionClass('Block');
      $methods = $class->getMethods(ReflectionMethod::IS_STATIC | ReflectionMethod::IS_PUBLIC);
      if (!empty($methods)) {
        foreach ($methods AS $method) {
          $cacheable = 'nocache'==$method->name? false : true;
          $this->driverObj->register_block($method->name,array('Block', $method->name),$cacheable);
        }
      }
    }
  }
  
  /**
   * assign var
   * @param string $tpl_var
   * @param mixed $value
   * @return Template
   */
  public function assign($tpl_var, $value) {
    $driverMethod = $this->driverAssign;
    if (!method_exists($this->driverObj, $driverMethod)) {
      throw new TemplateMethodNotFoundException($driverMethod, $this->driverClass);
    }
    $this->driverObj->$driverMethod($tpl_var,$value);
    return $this;
  }
  
  /**
   * assign var by reference
   * @param string $tpl_var
   * @param mixed $value
   * @return Template
   */
  public function assign_by_ref($tpl_var, &$value) {
    $driverMethod = $this->driverAssignRef;
    if (!method_exists($this->driverObj, $driverMethod)) {
      throw new TemplateMethodNotFoundException($driverMethod, $this->driverClass);
    }
    $this->driverObj->$driverMethod($tpl_var,$value);
    return $this;
  }
  
  /**
   * render a template
   * 
   * @param string $template    the resource handle of the template file or template object
   * @param mixed $cache_id     cache id to be used with this template
   * @param mixed $compile_id   compile id to be used with this template
   * @param bool $display       true: display, false: fetch
   */
  public function render($template = null, $cache_id = null, $compile_id = null, $display = false) {
    $driverMethod = $this->driverRender;
    if (!method_exists($this->driverObj, $driverMethod)) {
      throw new TemplateMethodNotFoundException($driverMethod, $this->driverClass);
    }
    
    try {
      if (is_array($template)) {
        return call_user_func_array(array($this->driverObj, $driverMethod), $template);
      }
      return $this->driverObj->$driverMethod($template,$cache_id,$compile_id,$display);
    }
    catch (Exception $e) {
      trigger_error($e->getMessage(), E_USER_ERROR);
    }
  }
  
  /**
   * magic method '__call', calling other methods of driverClass
   *
   * @param string $method
   * @param array $arguments
   * @throws TemplateMethodNotFoundException
   */
  public function __call($method,$arguments) {
    if (!method_exists($this->driverObj, $method)) {
      throw new TemplateMethodNotFoundException($method, $this->driverClass);
    }
    return call_user_func_array(array($this->driverObj, $method), $arguments);
  }
  
  /**
   * magic method '__get'
   * 
   * @param string $name
   */
  public function __get($name) {
    return array_key_exists($name, $this->_tplConfig) ? $this->_tplConfig[$name] : null;
  }
  
  /**
   * magic method '__set'
   * 
   * @param string $name
   * @param string $value
   */
  public function __set($name, $value) {
    $this->_tplConfig[$name] = $value;
  }
  
}

/**
 * Template Exception
 */
class TemplateException extends SimPHPException {
  public function __construct($message = null, $code = null) {
    parent::__construct($message,$code);
  }
}

class TemplateClassNotFoundException extends TemplateException {
  public function __construct($clsName = '') {
    parent::__construct("Template Driver Class '{$clsName}' Not Found.");
  }  
}

class TemplateMethodNotFoundException extends TemplateException {
  public function __construct($clsMethod = '', $clsName = '') {
    parent::__construct("Template Method '{$clsMethod}' Not Found in Driver Class '{$clsName}'.");
  }
}

/*----- END FILE: class.Template.php -----*/