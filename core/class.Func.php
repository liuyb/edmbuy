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
   * @param $prefix
   * @return string
   */
  public static function randstr($len = 6, $prefix = '') {
    return randstr($len, $prefix);
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
  
  /**
   * Check email address correct
   * 
   * @param  string $email
   * @return boolean
   */
  public static function check_email_address($email) {
  	// First, we check that there's one @ symbol, and that the lengths are right
  	if (!preg_match("/[^@]{1,64}@[^@]{1,255}/", $email)) {
  		// Email invalid because wrong number of characters in one section, or wrong number of @ symbols.
  		return false;
  	}
  	// Split it into sections to make life easier
  	$email_array = explode("@", $email);
  	$local_array = explode(".", $email_array[0]);
  	for ($i = 0; $i < sizeof($local_array); $i++) {
  		if (!preg_match("/^(([A-Za-z0-9!#$%&'*+\/=?^_`{|}~-][A-Za-z0-9!#$%&'*+\/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$/", $local_array[$i])) {
  			return false;
  		}
  	}
  	if (!preg_match("/^\[?[0-9\.]+\]?$/", $email_array[1])) { // Check if domain is IP. If not, it should be valid domain name
  		$domain_array = explode(".", $email_array[1]);
  		if(sizeof($domain_array) < 2) {
  			return false; // Not enough parts to domain
  		}
  		for($i = 0; $i < sizeof($domain_array); $i++) {
  			if(!preg_match("/^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$/", $domain_array[$i])) {
  				return false;
  			}
  		}
  	}
  	return true;
  }

  /**
   * Check mobile format
   *
   * @param  string $mobile
   * @return boolean
   */
  public static function check_mobile($mobile) {
  	if (preg_match("/^1\d{10}$/", $mobile)) {
  		return true;
  	}
  	return false;
  }
  
  /**
   * Check ip format
   * @param string $ip
   * @return boolean
   */
  public static function check_ip($ip) {
  	return preg_match('/^\d{1,3}(\.\d{1,3}){3}$/', $ip) ? TRUE : FALSE;
  }
  
  /**
   * 创建像这样的查询: "IN('a','b')";
   *
   * @access   public
   * @param    mix      $item_list      列表数组或字符串
   * @param    string   $field_name     字段名称
   *
   * @return   void
   */
  public static function db_create_in($item_list, $field_name = '')
  {
      if (empty($item_list))
      {
          return $field_name . " IN ('') ";
      }
      else
      {
          if (!is_array($item_list))
          {
              $item_list = explode(',', $item_list);
          }
          $item_list = array_unique($item_list);
          $item_list_tmp = '';
          foreach ($item_list AS $item)
          {
              if ($item !== '')
              {
                  $item = D()->escape_string($item);
                  $item_list_tmp .= $item_list_tmp ? ",'$item'" : "'$item'";
              }
          }
          if (empty($item_list_tmp))
          {
              return $field_name . " IN ('') ";
          }
          else
          {
              return $field_name . ' IN (' . $item_list_tmp . ') ';
          }
      }
  }
  
  /**
   * 读结果缓存文件
   *
   * @params  string  $cache_name
   *
   * @return  array   $data
   */
  public static function read_static_cache($cache_name)
  {
      static $result = array();
      if (!empty($result[$cache_name]))
      {
          return $result[$cache_name];
      }
      $cache_file_path = SIMPHP_ROOT . '/var/static_caches/' . $cache_name . '.php';
      if (file_exists($cache_file_path))
      {
          include_once($cache_file_path);
          if(isset($data)){
              $result[$cache_name] = $data;
              return $result[$cache_name];
          }
          return false;
      }
      else
      {
          return false;
      }
  }
  
  /**
   * 写结果缓存文件
   *
   * @params  string  $cache_name
   * @params  string  $caches
   *
   * @return
   */
  public static function write_static_cache($cache_name, $caches)
  {
      $cache_file_path = SIMPHP_ROOT . '/var/static_caches/' . $cache_name . '.php';
      $content = "<?php\r\n";
      $content .= "\$data = " . var_export($caches, true) . ";\r\n";
      $content .= "?>";
      $dir_file = dirname($cache_file_path);
      if(!is_dir($dir_file)){
          mkdirs($dir_file, 0777, TRUE);
      }
      file_put_contents($cache_file_path, $content, LOCK_EX);
  }
  
  /**
   * 返回区域选择列表数据
   * @param unknown $objView
   * @param unknown $province
   * @param unknown $city
   */
  public static function assign_regions($objView, $province, $city){
      /* 取得省份 */
      $objView->assign('province_list', Order::get_regions(1, 1));//$order->country 这里默认是中国 不动态取
      if ($province > 0)
      {
          /* 取得城市 */
          $objView->assign('city_list', Order::get_regions(2, $province));
          if ($city > 0)
          {
              /* 取得区域 */
              $objView->assign('district_list', Order::get_regions(3, $city));
          }
      }
  }
  
  /**
   * @from extend.php
   * 过滤xss攻击
   * @param str $val
   * @return mixed
   */
  public static function remove_xss($val) {
      // remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed
      // this prevents some character re-spacing such as <java\0script>
      // note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs
      //$val = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val);
      $val = preg_replace('/([\x00-\x08])/', '', $val);//不能过滤逗号
      // straight replacements, the user should never need these since they're normal characters
      // this prevents like <IMG SRC=@avascript:alert('XSS')>
      $search = 'abcdefghijklmnopqrstuvwxyz';
      $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
      $search .= '1234567890!@#$%^&*()';
      $search .= '~`";:?+/={}[]-_|\'\\';
      for ($i = 0; $i < strlen($search); $i++) {
          // ;? matches the ;, which is optional
          // 0{0,7} matches any padded zeros, which are optional and go up to 8 chars
  
          // @ @ search for the hex values
          $val = preg_replace('/(&#[xX]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $val); // with a ;
          // @ @ 0{0,7} matches '0' zero to seven times
          $val = preg_replace('/(&#0{0,8}'.ord($search[$i]).';?)/', $search[$i], $val); // with a ;
      }
      // now the only remaining whitespace attacks are \t, \n, and \r
      $ra1 = array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script',
          'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
      $ra2 = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut',
          'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate',
          'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut',
          'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend',
          'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange',
          'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete',
          'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover',
          'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange','onreadystatechange',
          'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted',
          'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
      $ra = array_merge($ra1, $ra2);
  
      $found = true; // keep replacing as long as the previous round replaced something
      while ($found == true) {
          $val_before = $val;
          for ($i = 0; $i < sizeof($ra); $i++) {
              $pattern = '/';
              for ($j = 0; $j < strlen($ra[$i]); $j++) {
                  if ($j > 0) {
                      $pattern .= '(';
                      $pattern .= '(&#[xX]0{0,8}([9ab]);)';
                      $pattern .= '|';
                      $pattern .= '|(&#0{0,8}([9|10|13]);)';
                      $pattern .= ')*';
                  }
                  $pattern .= $ra[$i][$j];
              }
              $pattern .= '/i';
              $replacement = substr($ra[$i], 0, 2).'<x>'.substr($ra[$i], 2); // add in <> to nerf the tag
              $val = preg_replace($pattern, $replacement, $val); // filter out the hex tags
              if ($val_before == $val) {
                  // no replacements were made, so exit the loop
                  $found = false;
              }
          }
      }
      return $val;
  }
}
