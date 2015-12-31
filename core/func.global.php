<?php
/**
 * Global functions of SimPHP
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

/**
 * traversal object to array
 * 
 * @param object $arrayOfObjects
 * @param string $key
 * @return array
 */
function object_map($arrayOfObjects, $key)
{
	return array_map(function($o) use ($key) {return $o->$key;}, $arrayOfObjects);
}

/**
 * Returns the remainder (modulo) of the division of the arguments(Support big int)
 * @param integer $bn dividend
 * @param integer $sn divisor
 * @return integer remainder
 */
function kmod($bn, $sn = 10)
{
	return intval(fmod(floatval($bn), $sn));
}

/**
 * Get file mime type
 * @param string $file
 * @return string
 */
function get_mime($file)
{
	if (!file_exists($file)) {
		return '';
	}
	$fi = new finfo(FILEINFO_MIME_TYPE);
	return $fi->file($file);
}

/**
 * create a directory recursively
 * 
 * @param string $dir, the directory path
 * @param string $mode, the mode is 0777 by default, optional
 * @param bool $recursive, whether create directory recursively
 */
function mkdirs($dir, $mode='', $recursive=TRUE)
{
  if (is_dir($dir)) {
  	return TRUE;
  }
  
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
function randstr($len = 6, $prefix = '') {
  static $_charset = array('a','b','c','d','e','f','g',
  		                     'h','i','j','k','l','m','n',
  		                     'o','p','q','r','s','t',
  		                     'u','v','w','x','y','z',
													 '0','1','2','3','4','5','6','7','8','9');
	$rlen = count($_charset) - 1;
	$str = '';
	for ($i = 0; $i < $len; $i++) {
		//$str .= chr(mt_rand(97, 122)); //48~57
		$str .= $_charset[mt_rand(0, $rlen)];
	}
	return $prefix.$str;
}

/**
 * get client ip
 */
function get_clientip()
{
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
function get_client_platform()
{
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
function get_client_browser()
{
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
 * convert ip to detail address
 *
 * possible return:
 * - LAN
 * - Invalid IP Address
 * - Invalid IP data file
 * - System Error
 * - Unknown
 * <空>
 * - 广东省深圳市 - 电信
 */
function ip_convert($ip) {

  $return = '';
  if(preg_match("/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/", $ip)) {
    $iparray = explode('.', $ip);
    if($iparray[0] == 10 || $iparray[0] == 127 || ($iparray[0] == 192 && $iparray[1] == 168) || ($iparray[0] == 172 && ($iparray[1] >= 16 && $iparray[1] <= 31))) {
      $return = '- LAN';
    } elseif($iparray[0] > 255 || $iparray[1] > 255 || $iparray[2] > 255 || $iparray[3] > 255) {
      $return = '- Invalid IP Address';
    } else {
      $tinyipfile = SIMPHP_ROOT.'/misc/ipdata/tinyipdata.dat';
      $fullipfile = SIMPHP_ROOT.'/misc/ipdata/wry.dat';
      if(@file_exists($tinyipfile)) {
        $return = ip_convert_tiny($ip, $tinyipfile);
      } elseif(@file_exists($fullipfile)) {
        $return = ip_convert_full($ip, $fullipfile);
      }
    }
  }

  return $return;

}

/**
 * possible return:
 * - Invalid IP data file
 * - Unknown
 * - 广东省深圳市 - 电信
 */
function ip_convert_tiny($ip, $ipdatafile) {

  static $fp = NULL, $offset = array(), $index = NULL;

  $ipdot = explode('.', $ip);
  $ip    = pack('N', ip2long($ip));

  $ipdot[0] = (int)$ipdot[0];
  $ipdot[1] = (int)$ipdot[1];

  if($fp === NULL && $fp = @fopen($ipdatafile, 'rb')) {
    $offset = unpack('Nlen', fread($fp, 4));
    $index  = fread($fp, $offset['len'] - 4);
  } elseif($fp == FALSE) {
    return  '- Invalid IP data file';
  }

  $length = $offset['len'] - 1028;
  $start  = unpack('Vlen', $index[$ipdot[0] * 4] . $index[$ipdot[0] * 4 + 1] . $index[$ipdot[0] * 4 + 2] . $index[$ipdot[0] * 4 + 3]);

  for ($start = $start['len'] * 8 + 1024; $start < $length; $start += 8) {

    if ($index{$start} . $index{$start + 1} . $index{$start + 2} . $index{$start + 3} >= $ip) {
      $index_offset = unpack('Vlen', $index{$start + 4} . $index{$start + 5} . $index{$start + 6} . "\x0");
      $index_length = unpack('Clen', $index{$start + 7});
      break;
    }
  }

  fseek($fp, $offset['len'] + $index_offset['len'] - 1024);
  if($index_length['len']) {
    return '- '.fread($fp, $index_length['len']);
  } else {
    return '- Unknown';
  }

}

/**
 * possible return:
 * - Invalid IP data file
 * <空>
 * - System Error
 * - Unknown
 * - 广东省深圳市 - 电信
 */
function ip_convert_full($ip, $ipdatafile) {

  if(!$fd = @fopen($ipdatafile, 'rb')) {
    return '- Invalid IP data file';
  }

  $ip = explode('.', $ip);
  $ipNum = $ip[0] * 16777216 + $ip[1] * 65536 + $ip[2] * 256 + $ip[3];

  if(!($DataBegin = fread($fd, 4)) || !($DataEnd = fread($fd, 4)) ) return;
  @$ipbegin = implode('', unpack('L', $DataBegin));
  if($ipbegin < 0) $ipbegin += pow(2, 32);
  @$ipend = implode('', unpack('L', $DataEnd));
  if($ipend < 0) $ipend += pow(2, 32);
  $ipAllNum = ($ipend - $ipbegin) / 7 + 1;

  $BeginNum = $ip2num = $ip1num = 0;
  $ipAddr1 = $ipAddr2 = '';
  $EndNum = $ipAllNum;

  while($ip1num > $ipNum || $ip2num < $ipNum) {
    $Middle= intval(($EndNum + $BeginNum) / 2);

    fseek($fd, $ipbegin + 7 * $Middle);
    $ipData1 = fread($fd, 4);
    if(strlen($ipData1) < 4) {
      fclose($fd);
      return '- System Error';
    }
    $ip1num = implode('', unpack('L', $ipData1));
    if($ip1num < 0) $ip1num += pow(2, 32);

    if($ip1num > $ipNum) {
      $EndNum = $Middle;
      continue;
    }

    $DataSeek = fread($fd, 3);
    if(strlen($DataSeek) < 3) {
      fclose($fd);
      return '- System Error';
    }
    $DataSeek = implode('', unpack('L', $DataSeek.chr(0)));
    fseek($fd, $DataSeek);
    $ipData2 = fread($fd, 4);
    if(strlen($ipData2) < 4) {
      fclose($fd);
      return '- System Error';
    }
    $ip2num = implode('', unpack('L', $ipData2));
    if($ip2num < 0) $ip2num += pow(2, 32);

    if($ip2num < $ipNum) {
      if($Middle == $BeginNum) {
        fclose($fd);
        return '- Unknown';
      }
      $BeginNum = $Middle;
    }
  }

  $ipFlag = fread($fd, 1);
  if($ipFlag == chr(1)) {
    $ipSeek = fread($fd, 3);
    if(strlen($ipSeek) < 3) {
      fclose($fd);
      return '- System Error';
    }
    $ipSeek = implode('', unpack('L', $ipSeek.chr(0)));
    fseek($fd, $ipSeek);
    $ipFlag = fread($fd, 1);
  }

  if($ipFlag == chr(2)) {
    $AddrSeek = fread($fd, 3);
    if(strlen($AddrSeek) < 3) {
      fclose($fd);
      return '- System Error';
    }
    $ipFlag = fread($fd, 1);
    if($ipFlag == chr(2)) {
      $AddrSeek2 = fread($fd, 3);
      if(strlen($AddrSeek2) < 3) {
        fclose($fd);
        return '- System Error';
      }
      $AddrSeek2 = implode('', unpack('L', $AddrSeek2.chr(0)));
      fseek($fd, $AddrSeek2);
    } else {
      fseek($fd, -1, SEEK_CUR);
    }

    while(($char = fread($fd, 1)) != chr(0))
      $ipAddr2 .= $char;

    $AddrSeek = implode('', unpack('L', $AddrSeek.chr(0)));
    fseek($fd, $AddrSeek);

    while(($char = fread($fd, 1)) != chr(0))
      $ipAddr1 .= $char;
  } else {
    fseek($fd, -1, SEEK_CUR);
    while(($char = fread($fd, 1)) != chr(0))
      $ipAddr1 .= $char;

    $ipFlag = fread($fd, 1);
    if($ipFlag == chr(2)) {
      $AddrSeek2 = fread($fd, 3);
      if(strlen($AddrSeek2) < 3) {
        fclose($fd);
        return '- System Error';
      }
      $AddrSeek2 = implode('', unpack('L', $AddrSeek2.chr(0)));
      fseek($fd, $AddrSeek2);
    } else {
      fseek($fd, -1, SEEK_CUR);
    }
    while(($char = fread($fd, 1)) != chr(0))
      $ipAddr2 .= $char;
  }
  fclose($fd);

  if(preg_match('/http/i', $ipAddr2)) {
    $ipAddr2 = '';
  }
  $ipaddr = "$ipAddr1 $ipAddr2";
  $ipaddr = preg_replace('/CZ88\.NET/is', '', $ipaddr);
  $ipaddr = preg_replace('/^\s*/is', '', $ipaddr);
  $ipaddr = preg_replace('/\s*$/is', '', $ipaddr);
  if(preg_match('/http/i', $ipaddr) || $ipaddr == '') {
    $ipaddr = '- Unknown';
  }

  return '- '.$ipaddr;
}

/**
 * save ip info to tb_location_ip
 */
function ip_save($ip, $ipaddr) {

  $result = array('ip'=>'','locaid'=>'', 'location'=>'');
  if(empty($ipaddr)) return $result;

  $ipaddr = substr(trim($ipaddr),2);
  if(in_array($ipaddr, array('LAN','Invalid IP Address','Invalid IP data file','System Error','Unknown'))) {
    return $result;
  }

  $db = D();
  $ipinfo_old = $db->get_one("SELECT ipaddr,locaid,location FROM {location_ip} WHERE ip='%s'", $ip);
  if (!empty($ipinfo_old)) {
    if ($ipaddr != $ipinfo_old['ipaddr']) {
      $locainfo = ip_findlocation($ipaddr);
      $db->query("UPDATE {location_ip} SET ipaddr='%s', locaid=%d, location='%s', timeline=%d WHERE ip='%s'",
                  $ipaddr,$locainfo['locaid'],$locainfo['location'],time(),$ip);
      $result = array('ip'=>$ip,'locaid'=>$locainfo['locaid'], 'location'=>$locainfo['location']);
    }
    else {
      $result = array('ip'=>$ip,'locaid'=>$ipinfo_old['locaid'], 'location'=>$ipinfo_old['location']);
    }
  }
  else {
    $locainfo = ip_findlocation($ipaddr);
    $db->query("INSERT INTO {location_ip}(ip,ipaddr,locaid,location,timeline) VALUES('%s','%s',%d,'%s',%d)",
                $ip,$ipaddr,$locainfo['locaid'],$locainfo['location'],time());
    $result = array('ip'=>$ip,'locaid'=>$locainfo['locaid'], 'location'=>$locainfo['location']);
  }
  return $result;
}

/**
 * find location by ip address
 */
function ip_findlocation($ipaddr) {
  if (empty($ipaddr)) return array('locaid'=>'','location'=>'');
  $result = D()->get_one("SELECT locaid, location FROM {location} WHERE '%s' REGEXP concat('^.+',location,'.*$')", $ipaddr);
  return !empty($result) ? $result : array('locaid'=>'','location'=>'');
}

/**
 * truncate string safely
 * @param string $string
 * @param integer $length optional default 80
 * @param string $etc optional default '...'
 * @param boolean $middle optional default false
 * @return string
 */
function mb_truncate($string, $length = 80, $etc = '...', $middle = false)
{
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
 * gen password salt
 */
function gen_salt() {
  return substr(uniqid(rand()), -6);
}
/**
 * gen encoded password
 * @param string $password_raw
 * @param string $salt
 * @return encoded password
 */
function gen_salt_password($password_raw, $salt=NULL, $len=40) {
  $len = in_array($len,array(32,40)) ? $len : 40;
  $encfunc = $len==40 ? 'sha1' : 'md5';
  $password_enc = preg_match("/^\w{{$len}}$/", $password_raw) ? $password_raw : $encfunc($password_raw);
  if (!isset($salt)) {
    $salt = gen_salt();
  }
  return strtoupper($encfunc($password_enc . $salt));
}

/**
 * 下载文件
 * Enter description here ...
 * @param unknown_type $filename
 */
function download($filename){
	if (file_exists($filename)){
		header('content-description:file transfer');
		header('content-type: interface/octet-stream');
		header('content-disposition:attachment;filename='.basename($filename));
		header('content-transfer-encoding:binary');
		header('expires:0');
		header('cache-control:must-revalidate,post-check=0,pre-check=0');
		header('pragma: public');
		header('content-length:'.filesize($filename));
		ob_clean();
		flush();
		readfile($filename);
		exit;
	}
}
/**
 *  处理上传文件,若上传出错，返回''或空array(),错误信息在$error中返回
 *  @param $upload  上传文件数组
 *  @param $mode	是否是批量模式
 *  @param $ext	文件格式
 *  @param $type	文件类别或用图,如:pic,txt,media,user/logo等， 主要用于为文件分类
 *  @param $error	返回错误信息
 */
function upload($upload, $mode = false, $ext='jpg,jpeg,gif,png', $type="pic", &$error = ''){
	$picsavedir = Config::get('env.picsavedir');
	$root_dir = SIMPHP_ROOT.$picsavedir;
	$relative_dir = $type.'/'.date('Ym')."/"; //相对地址
	$target_dir = $root_dir.$relative_dir;//绝对地址
	if(!file_exists($target_dir)){
	  mkdirs($target_dir);/*
		$mode = 0777;
		mkdir($target_dir,$mode,true);
		chmod($target_dir, $mode);*/
		@fclose(fopen($target_dir.'/index.htm', 'w'));
	}
	//批量上传
	if($mode){
		$array=array();
		foreach ($upload["error"] as $key=>$error){
			$check_type=check_type($upload['tmp_name'][$key], $upload['name'][$key],$ext);
			if(!empty($check_type)){
				if (!empty($upload['name'][$key])&&$upload['size'][$key]<2*1024*1024){
					$get_ext=get_ext($upload['name'][$key]);
					if(check_ext($get_ext,$ext)){
						$name = date('d_His');
						$name.= "_" .randstr();
						$name.= ".".$get_ext;
						if (upload_move_file($upload['tmp_name'][$key],$target_dir.$name)){
							$array[]=$picsavedir.$relative_dir.$name; //记录相对于网站根路径的文件路径
						}
					}
				}
			}
		}
		return $array;
	}else{//单个上传
		$filename='';//图片的相对地址
		$localName = '';//上传文件的本地名称
		$maxAttachSize = 10*1024*1024;//允许上传的文件大小，10M

		$err = "";//错误信息
		$tempName = '';//临时文件名
		$tempName_noExt = '';//不带后缀的文件名
		$tempPath = '';//临时文件绝对路径
		$tempName = date('d_His');
		$tempName .= "_".randstr();
		$tempName_noExt = $tempName;
		$tempName .=".tmp";
		$tempPath = $target_dir.$tempName;

		//HTML5上传
		if(isset($_SERVER['HTTP_CONTENT_DISPOSITION'])&&preg_match('/attachment;\s+name="(.+?)";\s+filename="(.+?)"/i',$_SERVER['HTTP_CONTENT_DISPOSITION'],$info)){
			file_put_contents($tempPath,file_get_contents("php://input"));
			$localName=urldecode($info[2]);
		}else{//普通上传
			/*
			 //检测上传文件的类型
			//$check_type=check_type($upload['tmp_name'],$upload['name'],$ext);
			$check_type=true;
			if(!empty($check_type)){
			//上传的文件不能超过10M
			if (!empty($upload['name'])&&$upload['size']<10*1024*1024){
			$get_ext=get_ext($upload['name']);
			if(check_ext($get_ext,$ext)){
			$name = date('YmdHis');
			$name.="_";
			for ($i = 0; $i < 6; $i++){
			$name .=chr(mt_rand(97, 122));
			}
			$name .=".".$get_ext;
			if (upload_move_file($upload['tmp_name'],$target_dir.$name)){
			$filename=$relative_dir.$name;
			}
			}
			}else{
				
			}
			}*/
				
			if(!isset($upload)){
				$err='文件域的name错误';
			}elseif(!empty($upload['error'])){
				switch($upload['error'])
				{
					case '1':
						$err = '文件大小超过了php.ini定义的upload_max_filesize值';
						break;
					case '2':
						$err = '文件大小超过了HTML定义的MAX_FILE_SIZE值';
						break;
					case '3':
						$err = '文件上传不完全';
						break;
					case '4':
						$err = '无文件上传';
						break;
					case '6':
						$err = '缺少临时文件夹';
						break;
					case '7':
						$err = '写文件失败';
						break;
					case '8':
						$err = '上传被其它扩展中断';
						break;
					case '999':
					default:
						$err = '无有效错误代码';
				}
			}elseif(empty($upload['tmp_name']) || $upload['tmp_name'] == 'none'){
				$err = '无文件上传';
			}else{
				move_uploaded_file($upload['tmp_name'],$tempPath);
				$localName=$upload['name'];
			}
		}

		//文件上传是否出错了
		if($err==''){
			$fileInfo=pathinfo($localName);
			$extension=$fileInfo['extension'];//文件的名缀名
				
			//检测上传文件格式
			if(preg_match('/^('.str_replace(',','|',$ext).')$/i',$extension))
			{
				$bytes=filesize($tempPath);
				//检测上传文件的大小
				if($bytes > $maxAttachSize){
					$err='请不要上传大小超过'.formatBytes($maxAttachSize).'的文件';
				}else{
					$targetPath = $target_dir.$tempName_noExt.'.'.$extension;//文件的最终存放位置
					if(!rename($tempPath,$targetPath)){
						@copy($tempPath,$targetPath);
					}
					@chmod($targetPath,0755);
					$filename = $picsavedir.$relative_dir.$tempName_noExt.'.'.$extension; //记录相对于网站根路径的文件路径
				}
			}else{
				$err='上传文件扩展名必需为：'.$ext;
			}
			//@unlink($tempPath);//删除临时文件
		}
		if($err!=''){
			$error = $err;
		}
		return $filename;
	}
}

/**
 * 移动上传的文件
 */
function upload_move_file($from, $target= ''){
	if (function_exists("move_uploaded_file")){
		if (move_uploaded_file($from, $target)){
			@chmod($target,0755);
			return true;
		}else if (copy($from, $target)){
			@chmod($target,0755);
			return true;
		}
	}elseif (copy($from, $target)){
		@chmod($target,0755);
		return true;
	}
	return false;
}
/**
 * 检测后缀
 * Enter description here ...
 * @param unknown_type $ext	文件后缀
 * @param unknown_type $exts	允许的后缀列表，","做为分隔符
 * @return string|boolean
 */
function check_ext($ext,$exts){
	if(empty($ext)||empty($exts)){
		return '';
	}
	$state=false;
	$explode=explode(",",$exts);
	foreach($explode as $value){
		if(!empty($value)){
			if($value==$ext){
				$state=true;
			}
		}
	}
	return $state;
}
/**
 * 检测上传文件的类型是否与后缀相符
 * Enter description here ...
 * @param unknown_type $filename	临时文件名
 * @param unknown_type $realname	真实文件名
 * @param unknown_type $limit_ext_types
 * @return string
 */
function check_type($filename, $realname = '', $limit_ext_types = ''){
	if ($realname){
		$extname = strtolower(substr($realname, strrpos($realname, '.') + 1));
	}else{
		$extname = strtolower(substr($filename, strrpos($filename, '.') + 1));
	}
	if (@stristr($limit_ext_types,(string)$extname) === false){
		return '';
	}
	if('xlsx'==$extname)return 'xlsx';
	if('pptx'==$extname)return 'pptx';
	if('docx'==$extname)return 'docx';
	$str = $format = '';

	$file = @fopen($filename, 'rb');
	if ($file){
		$str = @fread($file, 0x400);
		@fclose($file);
	}else{
		if (stristr($filename, ROOT_PATH) === false){
			if ($extname == 'jpg' || $extname == 'jpeg' || $extname == 'gif' || $extname == 'png' || $extname == 'doc' ||
					$extname == 'xls' || $extname == 'txt'  || $extname == 'zip' || $extname == 'rar' || $extname == 'ppt' ||
					$extname == 'pdf' || $extname == 'rm'   || $extname == 'mid' || $extname == 'wav' || $extname == 'bmp' ||
					$extname == 'swf' || $extname == 'chm'  || $extname == 'sql' || $extname == 'cert')
			{
				$format = $extname;
			}
		}else{
			return '';
		}
	}

	if ($format == '' && strlen($str) >= 2 ){
		if (substr($str, 0, 4) == 'MThd' && $extname != 'txt'){
			$format = 'mid';
		}elseif (substr($str, 0, 4) == 'RIFF' && $extname == 'wav'){
			$format = 'wav';
		}elseif (substr($str ,0, 3) == "\xFF\xD8\xFF"){
			$format = 'jpg';
		}elseif (substr($str ,0, 4) == 'GIF8' && $extname != 'txt'){
			$format = 'gif';
		}elseif (substr($str ,0, 8) == "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A"){
			$format = 'png';
		}elseif (substr($str ,0, 2) == 'BM' && $extname != 'txt'){
			$format = 'bmp';
		}elseif ((substr($str ,0, 3) == 'CWS' || substr($str ,0, 3) == 'FWS') && $extname != 'txt'){
			$format = 'swf';
		}elseif (substr($str ,0, 4) == "\xD0\xCF\x11\xE0"){   // D0CF11E == DOCFILE == Microsoft Office Document
			if (substr($str,0x200,4) == "\xEC\xA5\xC1\x00" || $extname == 'doc'){
				$format = 'doc';
			}elseif (substr($str,0x200,2) == "\x09\x08" || $extname == 'xls'){
				$format = 'xls';
			} elseif (substr($str,0x200,4) == "\xFD\xFF\xFF\xFF" || $extname == 'ppt'){
				$format = 'ppt';
			}
		} elseif (substr($str ,0, 4) == "PK\x03\x04"){
			$format = 'zip';
		} elseif (substr($str ,0, 4) == 'Rar!' && $extname != 'txt'){
			$format = 'rar';
		} elseif (substr($str ,0, 4) == "\x25PDF"){
			$format = 'pdf';
		} elseif (substr($str ,0, 3) == "\x30\x82\x0A"){
			$format = 'cert';
		} elseif (substr($str ,0, 4) == 'ITSF' && $extname != 'txt'){
			$format = 'chm';
		} elseif (substr($str ,0, 4) == "\x2ERMF"){
			$format = 'rm';
		} elseif ($extname == 'sql'){
			$format = 'sql';
		} elseif ($extname == 'txt'){
			$format = 'txt';
		}
	}
	if (@stristr($limit_ext_types,(string)$format) === false){
		$format = '';
	}
	return $format;
}
/**
 * 生成缩略图
 * Enter description here ...
 * @param unknown_type $image
 * @param unknown_type $toW
 * @param unknown_type $toH
 * @param unknown_type $image_thumb
 */
function make_thumb($image,$toW,$toH,$image_thumb=""){
	if($image_thumb==""){
		$image_thumb=$image;
	}

	//获取原始图片大小
	$info=GetImageSize($image);
	if($info[2]==1) {
		if(function_exists("imagecreatefromgif")){
			$im=ImageCreateFromGIF($image);
		}
	}elseif($info[2]==2){
		if(function_exists("imagecreatefromjpeg")){
			$im=ImageCreateFromJpeg($image);
		}
	}else{
		$im=ImageCreateFromPNG($image);
	}


	$srcW=ImageSX($im);//获取原始图片宽度
	$srcH=ImageSY($im);//获取原始图片高度

	$toWH=$toW/$toH;//获取缩图比例
	$srcWH=$srcW/$srcH;//获取原始图比例

	if($toWH<=$srcWH){
		$ftoW=$toW;
		$ftoH=$ftoW*($srcH/$srcW);
	}else{
		$ftoH=$toH;
		$ftoW=$ftoH*($srcW/$srcH);
	}
	//创建画布并且复制原始图像到画布
	if (function_exists('imagecreatetruecolor')&&(function_exists('imagecopyresampled'))){
		$canvas=ImageCreateTrueColor($ftoW,$ftoH);
		//	imagefilledrectangle($canvas,0,0,$toW,$toH,imagecolorallocate($canvas,255,255,255));
		ImageCopyResampled($canvas,$im,0,0,0,0,$ftoW,$ftoH,$srcW,$srcH);
	}else{
		$canvas=ImageCreate($ftoW,$ftoH);
		ImageCopyResized($canvas,$im,0,0,0,0,$ftoW,$ftoH,$srcW,$srcH);
	}

	//输入图像
	if(function_exists('imagejpeg')){
		ImageJpeg($canvas,$image_thumb,100);
	}else{
		ImagePNG($canvas,$image_thumb,100);
	}
	//回收资源
	ImageDestroy($canvas);
	ImageDestroy($im);
}
/**
 * 为图片加水印
 * Enter description here ...
 * @param unknown_type $groundImage
 * @param unknown_type $waterImage
 * @param unknown_type $waterPos
 * @param unknown_type $xOffset
 * @param unknown_type $yOffset
 */
function make_watermark($groundImage,$waterImage="",$waterPos=0,$xOffset=0,$yOffset=0) {
	if(!empty($waterImage) && file_exists($waterImage)) {
		$water_info = getimagesize($waterImage);
		$water_w     = $water_info[0];//取得水印图片的宽
		$water_h     = $water_info[1];//取得水印图片的高
		switch($water_info[2])   {    //取得水印图片的格式
			case 1:$water_im = imagecreatefromgif($waterImage);break;
			case 2:$water_im = imagecreatefromjpeg($waterImage);break;
			case 3:$water_im = imagecreatefrompng($waterImage);break;
		}
	}
	//读取背景图片
	if(!empty($groundImage) && file_exists($groundImage)) {
		$ground_info = getimagesize($groundImage);
		$ground_w     = $ground_info[0];//取得背景图片的宽
		$ground_h     = $ground_info[1];//取得背景图片的高

		switch($ground_info[2]) {    //取得背景图片的格式
			case 1:$ground_im = imagecreatefromgif($groundImage);break;
			case 2:$ground_im = imagecreatefromjpeg($groundImage);break;
			case 3:$ground_im = imagecreatefrompng($groundImage);break;
		}
	}
	$w = $water_w;
	$h = $water_h;
	//水印位置
	switch($waterPos) {
		case 0://随机
			$posX = rand(0,($ground_w - $w));
			$posY = rand(0,($ground_h - $h));
			break;
		case 1://1为顶端居左
			$posX = 0;
			$posY = 0;
			break;
		case 2://2为顶端居中
			$posX = ($ground_w - $w) / 2;
			$posY = 0;
			break;
		case 3://3为顶端居右
			$posX = $ground_w - $w;
			$posY = 0;
			break;
		case 4://4为中部居左
			$posX = 0;
			$posY = ($ground_h - $h) / 2;
			break;
		case 5://5为中部居中
			$posX = ($ground_w - $w) / 2;
			$posY = ($ground_h - $h) / 2;
			break;
		case 6://6为中部居右
			$posX = $ground_w - $w;
			$posY = ($ground_h - $h) / 2;
			break;
		case 7://7为底端居左
			$posX = 0;
			$posY = $ground_h - $h;
			break;
		case 8://8为底端居中
			$posX = ($ground_w - $w) / 2;
			$posY = $ground_h - $h;
			break;
		case 9://9为底端居右
			$posX = $ground_w - $w;
			$posY = $ground_h - $h;
			break;
		default://随机
			$posX = rand(0,($ground_w - $w));
			$posY = rand(0,($ground_h - $h));
			break;
	}
	//设定图像的混色模式
	imagealphablending($ground_im, true);
	imagecopy($ground_im, $water_im, $posX + $xOffset, $posY + $yOffset, 0, 0, $water_w,$water_h);//拷贝水印到目标文件
	@unlink($groundImage);
	switch($ground_info[2]){
		case 1:imagegif($ground_im,$groundImage,100);break;
		case 2:imagejpeg($ground_im,$groundImage,100);break;
		case 3:imagepng($ground_im,$groundImage,100);break;
	}
	//释放内存
	if(isset($water_info)) unset($water_info);
	if(isset($water_im)) imagedestroy($water_im);
	unset($ground_info);
	imagedestroy($ground_im);
}
/**
 * 获取文件的后缀名
 * Enter description here ...
 * @param unknown_type $filename
 */
function get_ext($filename){
	if(!empty($filename)){
		return end(explode(".",strtolower($filename)));
	}
}
/**
 * 将byte转化为其它单位
 * Enter description here ...
 * @param unknown_type $bytes 字节
 */
function formatBytes($bytes) {
	if($bytes >= 1073741824) {
		$bytes = round($bytes / 1073741824 * 100) / 100 . 'GB';
	} elseif($bytes >= 1048576) {
		$bytes = round($bytes / 1048576 * 100) / 100 . 'MB';
	} elseif($bytes >= 1024) {
		$bytes = round($bytes / 1024 * 100) / 100 . 'KB';
	} else {
		$bytes = $bytes . 'Bytes';
	}
	return $bytes;
}
/*----- END FILE: func.global.php -----*/