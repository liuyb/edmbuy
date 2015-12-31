<?php
/**
 * SimPHP View Base Class
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class View extends CBase {
  
	/**
	 * Page render mode enum values
	 *
	 * @var constant
	 */
	const RENDER_MODE_GENERAL = 1;
	const RENDER_MODE_HASH    = 2;
	const RENDER_MODE_DEFAULT = 2;
	
	/**
	 * Page render mode, option value: 1:'general',2:'hash'
	 * @var enum
	 */
	protected $page_render_mode = self::RENDER_MODE_DEFAULT;
	
  /**
   * Template Object
   * @var Template
   */
  protected $tpl = null;
  
  /**
   * Template Driver Class Name
   * @var string
   */
  protected $tpl_driver_class = '';
  
  /**
   * Template File Postfix
   * @var string
   */
  protected $tpl_postfix = '';
  
  /**
   * Name of Template for rendering
   * @var string
   */
  protected $tpl_name = '';
  
  /**
   * Save filter function hooks
   * @var array
   */
  protected $filter_hooks = array();
  
  /**
   * Filter tag constants
   * @var constant
   */
  const FILTER_TAG_RENDER = 'render';
  const FILTER_TAG_OUTPUT = 'output';
  
  /**
   * Constructor
   * 
   * @param string $tpl_name, Name of Template for rendering
   */
  public function __construct($tpl_name) {
    
    $this->tpl_driver_class = Config::get('env.tplclass','Smarty');
    $this->tpl_postfix = Config::get('env.tplpostfix','.htm');
    $this->tpl_name = $this->tpl_realpath($tpl_name);
    $this->modroot = SimPHP::$gConfig['modroot'];
    
    $tpl_config = array('caching'       => Config::get('env.tplcache',0),
                        'cache_lifetime'=> Config::get('env.tplcache_expires',300),
                        'compile_check' => Config::get('env.tplcompile_check',1),
                        'force_compile' => Config::get('env.tplforce_compile',0),
                        'debugging'     => Config::get('env.tpldebug',0));
    
    $tpldir = Config::get('env.sitetheme','default');
    if ($this->modroot != 'modules') {
      $tpldir = $this->modroot;
    }
    $template_dir = SIMPHP_ROOT . "/themes/{$tpldir}";
    $compiled_dir = SIMPHP_ROOT . Config::get('env.tplcachedir') . "/{$tpldir}/compiled";
    $cached_dir   = SIMPHP_ROOT . Config::get('env.tplcachedir') . "/{$tpldir}/cached";
    $tpl_config['template_dir'] = $template_dir;
    $tpl_config['compile_dir']  = $compiled_dir;
    $tpl_config['cache_dir']    = $cached_dir;
    
    try {
      if (!is_dir($compiled_dir) && !mkdirs($compiled_dir) && !is_writable($compiled_dir)) {
        throw new DirWritableException($compiled_dir);
      }
      if (Config::get('env.tplcache') && !is_dir($cached_dir) && !mkdirs($cached_dir) && !is_writable($cached_dir)) {
        throw new DirWritableException($cached_dir);
      }
    }
    catch (DirWritableException $e) {
      trigger_error($e->getMessage(), E_USER_ERROR);
    }
    
    $this->tpl = Template::I(array('driverClass'  => $this->tpl_driver_class,
                                   'driverConfig' => $tpl_config,
                            ));
    $this->assign('contextpath', Config::get('env.contextpath','/'));
    $this->assign_by_ref('user', $GLOBALS['user']);
    
    //add default output filter
    $this->add_output_filter(array($this,'filter_output'));
  }
  
  /**
   * Set $tpl_name
   * @param string $tpl_name
   * @return View
   */
  public function set_tplname($tpl_name) {
    $this->tpl_name = $this->tpl_realpath($tpl_name);
    return $this;
  }
  
  /**
   * Get template real file path
   * @param string $tpl_name
   * @param bool $is_abs_path when true, then return the true template file absolute path
   */
  public static function tpl_realpath($tpl_name, $is_abs_path = false) {
    if (preg_match("/^mod_([a-z]+)_/", $tpl_name, $matchs)) {	// module template
      $tpl_name = ($is_abs_path ? '' : 'file:').SIMPHP_ROOT."/".SimPHP::$gConfig['modroot']."/{$matchs[1]}/tpl/{$tpl_name}";
    }
    elseif ($is_abs_path) {
      $tpldir = Config::get('env.sitetheme','default');
      if (SimPHP::$gConfig['modroot'] != 'modules') {
        $tpldir = SimPHP::$gConfig['modroot'];
      }
      $tpl_name = SIMPHP_ROOT . "/themes/{$tpldir}/{$tpl_name}";
    }
    $tplpostfix = Config::get('env.tplpostfix','.htm');
    return $tpl_name . (false===strrpos($tpl_name, $tplpostfix) ? $tplpostfix : '');
  }
  
  /**
   * Assign var
   * @param string $tpl_var
   * @param mixed $value
   * @return View
   */
  public function assign($tpl_var, $value) {
    $this->tpl->assign($tpl_var, $value);
    return $this;
  }
  
  /**
   * Assign var by reference
   * @param string $tpl_var
   * @param mixed $value
   * @return View
   */
  public function assign_by_ref($tpl_var, &$value) {
    $this->tpl->assign_by_ref($tpl_var, $value);
    return $this;
  }
  
  /**
   * Render a template
   * 
   */
  public function render($tpl_name = null) {
    return $this->tpl->render((isset($tpl_name) ? $tpl_name: $this->tpl_name),null,null,false);
  }
  
  /**
   * Filter render output
   * @param string $content
   * @return string
   */
  public function filter_output($content) {
    return $content;
  }
  
  /**
   * Add render filter function
   * 
   * @param callback $func_to_add The name of the function to be called when the filter is applied.
   * @return View
   */
  public function add_render_filter($func_to_add) {
    $this->add_filter(self::FILTER_TAG_RENDER, $func_to_add);
    return $this;
  }
  
  /**
   * Add output filter function
   * 
   * @param callback $func_to_add The name of the function to be called when the filter is applied.
   * @return View
   */
  public function add_output_filter($func_to_add) {
    $this->add_filter(self::FILTER_TAG_OUTPUT, $func_to_add);
    return $this;
  }
  
  /**
   * add append filter
   * @param callback $func_to_add
   * @param string $position all values are ['head','foot']
   * @param string $type all values are ['js','css']
   * @return PageView
   */
  public function add_append_filter($func_to_add) {
    //do nothing here, leave to PageView implement
    return $this;
  }
  
  /**
   * Add filter function hook
   *
   * @param string $tag The name of the filter to hook the $func_to_add to.
   * @param callback $func_to_add The name of the function to be called when the filter is applied.
   * @return View
   */
  protected function add_filter($tag, $func_to_add) {
    if (!isset($this->filter_hooks[$tag])) $this->filter_hooks[$tag] = array();
    array_push($this->filter_hooks[$tag], array('func_name'=>$func_to_add));
    return $this;
  }
  
  /**
   * Call the functions added to the filter hook(by add_filter).
   * 
   * @param string $tag
   * @param mixed $value The value on which the filters hooked to <tt>$tag</tt> are applied on.
   * @param mixed $var,... Additional variables passed to the functions hooked to <tt>$tag</tt>.
   * @return mixed The filtered value after all hooked functions are applied to it.
   */
  protected function apply_filter($tag, $value = null) {
    $args = func_get_args();
    array_shift($args);
    $argnum = count($args);
    
    if (isset($this->filter_hooks[$tag]) && !empty($this->filter_hooks[$tag])) {
      $hooks = $this->filter_hooks[$tag];
      $this->filter_hooks[$tag] = array(); //clear
      foreach ($hooks as $hk) {
        if (is_callable($hk['func_name'])) {
          $value = call_user_func_array($hk['func_name'], array_merge($args,[$this])); //append the current 'View' object to the end
          if ($argnum>0) { //indicating has at least one input argument for the hook function, then set the returning value for the first argument
            $args[0] = $value;
          }
        }
      }
    }
    
    return $value;
  }
  
  /**
   * Magically converts view object to string.
   *
   * @return string
   */
  public function __toString() {
    try {
      $this->apply_filter(self::FILTER_TAG_RENDER);
      $render_string = $this->render();
      return $this->apply_filter(self::FILTER_TAG_OUTPUT, $render_string);
    }
    catch (Exception $e) {
      trigger_error($e->getMessage(), E_USER_WARNING);
    }
  }
  
  /**
   * set page render mode
   * @param enum $mode View::RENDER_MODE_GENERAL, View::RENDER_MODE_HASH
   * @return View
   */
  public function set_page_render_mode($mode = self::RENDER_MODE_HASH) {
  	$this->page_render_mode = $mode;
  	$this->assign('page_render_mode', $this->page_render_mode);
  	return $this;
  }
  
  /**
   * Set list order parameters
   *
   * @param String $default_field
   *   default sort field
   * @param String $default_order
   *   default sort field
   * @return Array
   *   containing 'three' elements: 
   *   array(
   *     '0' => 'orderby',
   *     '1' => 'order',
   *     '2' => 'order part of url',
   *     'orderby' => 'orderby',
   *     'order'   => 'order',
   *     'orderurl'=> 'order part of url',
   *   )
   */
  public function set_listorder($default_field = 'rid', $default_order = 'DESC') {
    $orderby = isset($_GET['orderby']) && ''!=$_GET['orderby'] ? $_GET['orderby'] : $default_field;  
    $order   = isset($_GET['order']) ? strtoupper($_GET['order']) : strtoupper($default_order);
    if (!in_array($order, array('DESC','ASC'))) {
      $order = 'DESC';
    }
    
    $orderurl = 'orderby='.$orderby.'&order='.strtolower($order);
    $this->assign('listorderby', $orderby);
    $this->assign('listorder', $order);
  
    return array(0=>$orderby, 1=>$order, 2=>$orderurl,'orderby'=>$orderby,'order'=>$order,'orderurl'=>$orderurl);
  }
  
  /**
   * Generate SimPHP page links
   * 
   * @param string $q
   */
  public static function link($q='') {
    $cleanurl = Config::get('env.cleanurl',0);
    $url = Config::get('env.contextpath','/');
    
    if ($cleanurl) {
      $url .= $q;
    }
    else {
      $url .= '?q='.$q;
    }
    
    return $url;
  }
  
  /**
   * Url link connector
   * @return string
   */
  public static function link_connector() {
    return Config::get('env.cleanurl',0) ? '?' : '&';
  }
  
}

/**
 * View Exception
 */
class ViewException extends SimPHPException {
	protected $view;
	
	public function __construct(View $view, $message = null, $code = null) {
		parent::__construct($message, $code);
		$this->view = $view;
	}
	
	public function getView() {
		return $this->view;
	}
}

/**
 * View Exception
 */
class ViewResponse extends ViewException {
	public function __construct(View $view, $message = '', $code = 0) {
		parent::__construct($view, $message, $code);
	}
}
 
/*----- END FILE: class.View.php -----*/