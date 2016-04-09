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
  	if (preg_match("/^1\d{10,}$/", $mobile)) {
  		return true;
  	}
  	return false;
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
      file_put_contents($cache_file_path, $content, LOCK_EX);
  }
  
  /**
   * get 查询时过滤
   * @param unknown $str
   */
  public static function search_check($str) {
      $str = str_replace ( "%", "\%", $str );
      //把"%"过滤掉
      $str = htmlspecialchars($str, ENT_QUOTES);
      //转换html
      return $str;
  }
  
  /**
   * post 提交请求时过滤
   * @param unknown $text
   * @param unknown $tags
   */
  public static function post_check($text, $tags = null) {
      $text = trim($text);
      $text = preg_replace("/<!--?.*-->/", "", $text);
      $text = preg_replace("/<!--?.*-->/", "", $text);
      $text = preg_replace("/<\\?|\\?>/", "", $text);
      $text = preg_replace("/<script?.*\\/script>/", "", $text);
      $text = preg_replace("/\\r?\\n/", "", $text);
      $text = preg_replace("/<br(\\s\\/)?>/i", "[br]", $text);
      $text = preg_replace("/(\\[br\\]\\s*){10,}/i", "[br]", $text);
      while (preg_match("/(<[^><]+) (lang|on|action|background|codebase|dynsrc|lowsrc)[^><]+/i", $text, $mat)) {
          $text = str_replace($mat[0], $mat[1], $text);
      }
      while (preg_match("/(<[^><]+)(window\\.|javascript:|js:|about:|file:|document\\.|vbs:|cookie)([^><]*)/i", $text, $mat)) {
          $text = str_replace($mat[0], $mat[1] . $mat[3], $text);
      }
      /* if (empty($tags)) {
          $tags = "table|tbody|td|th|tr|i|b|u|strong|img|p|br|div|span|em|ul|ol|li|dl|dd|dt|a|alt|h[1-9]?";
          $tags .= "|object|param|embed";
      }
      $text = preg_replace("/<(\\(?:" . $tags . "))( [^><\\[\\]]*)?>/i", "[\\1\\2]", $text);
      $text = preg_replace("/<\\(html|head|meta|link|base|basefont|body|bgsound|title|style|script|form|iframe|frame|frameset|applet|id|ilayer|layer|name|style|xml)[^><]*>/i", "", $text); */
      while (preg_match("/<([a-z]+)[^><\\[\\]]*>[^><]*<\\/\\1>/i", $text, $mat)) {
          $text = str_replace($mat[0], str_replace(">", "]", str_replace("<", "[", $mat[0])), $text);
      }
      while (preg_match("/(\\[[^\\[\\]]*=\\s*)(\\\"|')([^\\2\\[\\]]+)\\2([^\\[\\]]*\\])/i", $text, $mat)) {
          $text = str_replace($mat[0], $mat[1] . "|" . $mat[3] . "|" . $mat[4], $text);
      }
      $text = htmlspecialchars($text, ENT_QUOTES);
      return $text;
  }
}
