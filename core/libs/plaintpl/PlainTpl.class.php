<?php
/**
 * Plain php template parser class
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
class PlainTpl {
  
  /**
   * The name of the directory where templates are located.
   *
   * @var string
   */
  public $template_dir    =  'tpl';
  
  /**
   * The directory where compiled templates are located.
   *
   * @var string
   */
  public $compile_dir     =  'compiled';
  
  /**
   * The name of the directory for cache files.
   *
   * @var string
   */
  public $cache_dir       =  'cached';
  
  /**
   * The left delimiter used for the template tags.
   *
   * @var string
   */
  public $left_delimiter  =  '{';
  
  /**
   * The right delimiter used for the template tags.
   *
   * @var string
   */
  public $right_delimiter =  '}';
  
  /**
   * Restricted php statement
   * 
   * Note: the php statement must just one row, no newline charactor
   * 
   * @var string
   */
  public $restrict_php_statement = "defined('IN_SIMPHP') or die('Access Denied');";
  
  /**
   * This tells PlainTpl whether to check for recompiling or not. Recompiling
   * does not need to happen unless a template or config file is changed.
   * Typically you enable this during development, and disable for
   * production.
   *
   * @var boolean
   */
  public $compile_check   =  true;
  
  /**
   * This forces templates to compile every time. Useful for development
   * or debugging.
   *
   * @var boolean
   */
  public $force_compile   =  false;
  
  /**
   * Compile code length, default to 16
   * @var integer
   */
  public $compile_codelen = 16;
  
  /**
   * where assigned template vars are kept
   *
   * @var array
   */
  public $_tpl_vars       = array();
  
  /**
   * Tpl Config Set
   * 
   * @var array
   */
  protected $_tpl_config   = array(
    
  );
  
  /**
   * assigns values to template variables
   *
   * @param array|string $tpl_var the template variable name(s)
   * @param mixed $value the value to assign
   */
  function assign($tpl_var, $value = null) {
    if (is_array($tpl_var)){
      foreach ($tpl_var as $key => $val) {
        if ($key != '') {
          $this->_tpl_vars[$key] = $val;
        }
      }
    } else {
      if ($tpl_var != '')
        $this->_tpl_vars[$tpl_var] = $value;
    }
  }
  
  /**
   * assigns values to template variables by reference
   *
   * @param string $tpl_var the template variable name
   * @param mixed $value the referenced value to assign
   */
  function assign_by_ref($tpl_var, &$value) {
    if ($tpl_var != '')
      $this->_tpl_vars[$tpl_var] = &$value;
  }
  
  /**
   * executes & returns or displays the template results
   *
   * @param string $resource_name
   * @param string $cache_id
   * @param string $compile_id
   * @param boolean $display
   */
  public function fetch($resource_name, $cache_id = null, $compile_id = null, $display = false) {
    
    $____RESOURCE_PATH = $this->compile($resource_name);
    
    // Buffering on
    ob_start();
    
    // Import the template variables to local namespace
    extract($this->_tpl_vars);
    
    // Import php template entry file
    include ($____RESOURCE_PATH);
    
    // Fetch the output and close the buffer
    $output = ob_get_clean();
    if (!$display) {
      return $output;
    }
    echo $output;
  }
  
  /**
   * Compile the template file, and then return the final resource full path
   * 
   * @param string $resource_name
   * @return string
   *   The real template file for including, empty string("") indicating error happen
   */
  public function compile($resource_name) {
    
    if (preg_match('/^file:/i', $resource_name)) {
      $resource_path = str_ireplace('file:', '', $resource_name);
    }
    else {
      $resource_path = $this->template_dir . '/' . $resource_name;
    }
    
    if (!file_exists($resource_path)) {
      trigger_error("PlainTpl error: template file '{$resource_path}' not found.", E_USER_ERROR);
      return '';
    }
    
    if (!preg_match('/\.php$/i', $resource_name)) {
    
      // Check compile dir writable
      if (!is_writable($this->compile_dir)) {
        trigger_error("PlainTpl error: template compile dir '{$this->compile_dir}' not writable.", E_USER_ERROR);
        return '';
      }
    
      // Check compile file
      $cname = '~'.$this->_encode($resource_path, $this->compile_codelen).'_'.basename($resource_path).'.php';
      $cfile = $this->compile_dir . '/' . $cname;
      $regen = FALSE;
      $fmtime= intval(filemtime($resource_path)); // Gets file modification time
      if (file_exists($cfile)) {
        $content = file_get_contents($cfile);
        $contarr = explode("\n", $content);
        $themark = $contarr[0]; // the first row
        $oldtime = 0;
        if (preg_match('!\/\*#(\d+)#\*\/!', $themark, $match)) {
          $oldtime = intval($match[1]);
        }
    
        if ( $this->force_compile || ($this->compile_check && $fmtime > $oldtime) ){
          $regen = TRUE;
        }
        unset($oldtime,$themark,$contarr,$content); //for saving memory
      }
      else {
        $regen = TRUE;
      }
    
      // Do regenerate compile cache php file
      if ($regen) {
        $prepend = "<?php /*#{$fmtime}#*/{$this->restrict_php_statement}?>\n";
        $compile_content = file_get_contents($resource_path);
        /* no need parsing template tag
        $ldq = preg_quote($this->left_delimiter, '~');
        $rdq = preg_quote($this->right_delimiter, '~');
        $search = "~{$ldq}(.+){$rdq}~s";
        $compile_content = preg_replace_callback($search, array($this,'_parser'), $compile_content);
        unset($search,$rdq,$ldq,$compile_content,$prepend);
        */
        file_put_contents($cfile, $prepend.$compile_content, LOCK_EX);
        unset($compile_content,$prepend);
      }
    
      // Change the resource path to compile file path
      $resource_path = $cfile;
    }
    
    return $resource_path;
  }
  
  /**
   * Template parser callback
   * @param array $match
   */
  protected function _parser($match) {
    $match[1] = isset($match[1]) ? trim($match[1]) : '';
    if (''===$match[1]) {
      return '';
    }
    
    $sep = chr(31); //使用一个非打印字符，避免跟正文冲突
    $match[1] = preg_replace('~(?<==)(\s+)|(\s+)(?==)~', '', $match[1]); //去掉'='前后可能存在的空白
    $match[1] = preg_replace('~(?<=[\w\'"])(\s+)(?=\w+=)~', $sep, $match[1]); //将参数每段用$sep连接起来
    $matcharr = explode($sep, $match[1]);
    
    $matchlen = count($matcharr);
    $tpltag   = $matcharr[0];
    $tplparams= array();
    for($i=1; $i<$matchlen; ++$i) {
      $tarr = explode('=', $matcharr[$i]);
      $tplparams[$tarr[0]] = isset($tarr[1])&&''!=$tarr[1] ? trim($tarr[1],'"\'') : '';
    }
    
    switch ($tpltag) {
      case 'include':
        $file = !empty($tplparams['file']) ? $tplparams['file'] : '';
        if (''!==$file) {
          return "<?php include('{$file}') ?>";
        }
        break;
    }
    
    return '';
  }
  
  /**
   * Encode string by md5
   * 
   * @param string $str
   * @param inteter $len
   * @return string
   */
  protected function _encode($str, $len = 32) {
    $result  = $md5code = md5($str);
    if (in_array($len, array(16, 8, 4, 2))) {
      $result = '';
      for($i=0; $i<32; $i++) {
        if ($i%(32/$len)==0) {
          $result .= $md5code[$i];
        }
      }
    }
    return $result;
  }

  /**
   * magic method '__get'
   *
   * @param string $name
   */
  public function __get($name) {
    return array_key_exists($name, $this->_tpl_config) ? $this->_tpl_config[$name] : NULL;
  }
  
  /**
   * magic method '__set'
   *
   * @param string $name
   * @param string $value
   */
  public function __set($name, $value) {
    $this->_tpl_config[$name] = $value;
  }
  
}




/*=============== Some PHP Template Functions ===============*/

/**
 * Plain function version of TplBase::add_css()
 * 
 * @param string $file
 * @param array $params
 * @return void
 * @see TplBase::add_css()
 */
function add_css($file, $params = array()) {
  $content = '';
  $tpl = Template::$_instance;
  if ('PlainTpl' === $tpl->driverClass) {
    $params = array_merge(array('file'=>$file), $params);
    $content = TplBase::add_css($params, $tpl->driverObj);
  }
  echo $content;
}

/**
 * Plain function version of TplBase::add_js()
 *
 * @param string $file
 * @param array $params
 * @return void
 * @see TplBase::add_js()
 */
function add_js($file, $params = array()) {
  $content = '';
  $tpl = Template::$_instance;
  if ('PlainTpl' === $tpl->driverClass) {
    $params = array_merge(array('file'=>$file), $params);
    $content = TplBase::add_js($params, $tpl->driverObj);
  }
  echo $content;
}

/**
 * Plain function version of TplBase::tplholder()
 *
 * @param string $name
 * @return void
 * @see TplBase::tplholder()
 */
function tplholder($name) {
  $params = array('name'=>$name);
  $content = TplBase::tplholder($params);
  echo $content;
}




















 
/*----- END FILE: PlainTpl.class.php -----*/