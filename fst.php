<?php
/**
 * FST server side entry
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
//~ require init.php
require (__DIR__.'/core/init.php');

ignore_user_abort(TRUE);
set_time_limit(60);

if (!function_exists('show_jsonmsg')) {
  function show_jsonmsg($content) {
    Response::sendJSON($content);
    exit;
  }
}

if (!function_exists('show_emptyimg')) {
  function show_emptyimg($format = 'gif') {
    header('Content-Type: image/'.$format);
    $width  = 1;
    $height = 1;
    $img    = imageCreate($width, $height);
    //imageFilledRectangle($img, 0, 0, $width, $height, imagecolorallocate($img, 255, 255, 255));
    ImageColorTransparent($img,imagecolorallocate($img, 255, 255, 255));
    imagegif($img);
    imagedestroy($img);
    exit;
  }
}

SimPHP::I()->boot(RC_SESSION);

if (isset($_GET['act'])) {
  $_action = trim($_GET['act']);
  switch ($_action) {
    case 'onload_stat':
      $vid    = isset($_GET['vid']) ? intval($_GET['vid']) : 0;
      $onload_time = isset($_GET['t']) ? intval($_GET['t']) : 0;
  
      $params = array('onloadTime'=>$onload_time);
      $vid = V($vid, $params,$_action);
      //show_jsonmsg(array('msg'=>'OK', 't'=>$onload_time));
      show_emptyimg();
      break;
    case 'retention_stat':
      $vid    = isset($_GET['vid']) ? intval($_GET['vid']) : 0;
      $retention_time = isset($_GET['rt']) ? intval($_GET['rt']) : 0;
  
      $params = array('retentionTime'=>$retention_time);
      $vid = V($vid, $params,$_action);
      //show_jsonmsg(array('msg'=>'OK', 'rt'=>$retention_time));
      show_emptyimg();
      break;
    case 'poststatus':
      $vid    = isset($_GET['vid']) ? intval($_GET['vid']) : 0;
      $uid    = isset($_GET['uid']) ? intval($_GET['uid']) : 0;
      $status = isset($_GET['status']) ? trim($_GET['status']) : 'R';
      $params = array('uid'=>$uid, 'status'=>$status);
      $vid = V($vid, $params,$_action);
      show_jsonmsg(array('vid'=>$vid, 'uid'=>$uid, 'status'=>$status));
      break;
  }
  show_jsonmsg(array('msg'=>'act function \''.$_GET['act'].'\' not found.'));
}

$url    = isset($_GET['lo']) ? rawurldecode($_GET['lo']) : '';
$params = array();
$params['browserName']     = isset($_GET['bn']) ? rawurldecode($_GET['bn']) : '';
$params['browserVersion']  = isset($_GET['bv']) ? rawurldecode($_GET['bv']) : '';
$params['browserCodeName'] = isset($_GET['bc']) ? rawurldecode($_GET['bc']) : '';
$params['browserLanguage'] = isset($_GET['bl']) ? rawurldecode($_GET['bl']) : '';
$params['userAgent']       = isset($_GET['ua']) ? rawurldecode($_GET['ua']) : '';
$params['osPlatform']      = isset($_GET['op']) ? rawurldecode($_GET['op']) : '';
$params['cookieEnabled']   = isset($_GET['ck']) ? intval($_GET['ck']) : 0;
$params['javaEnabled']     = isset($_GET['jv']) ? intval($_GET['jv']) : 0;
$params['screenWxH']       = isset($_GET['sw']) ? rawurldecode($_GET['sw']) : '';
$params['screenColor']     = isset($_GET['sc']) ? intval($_GET['sc']) : '';
$params['screenPixelRatio']= isset($_GET['sp']) ? floatval($_GET['sp']) : 0;  //0:undefined,1,1.5,2,2.5,3...
$params['screenOrientation']= isset($_GET['so']) ? intval($_GET['so']) : 0;  //1:portrait,2:landscape
$params['winOrientation']  = isset($_GET['wo']) ? intval($_GET['wo']) : 0;  //0,90,-90,180...
$params['flashVersion']    = isset($_GET['fl']) ? rawurldecode($_GET['fl']) : '';
$params['referrer']        = isset($_GET['rf']) ? rawurldecode($_GET['rf']) : '';
$params['uvid']            = isset($_GET['uv']) ? rawurldecode($_GET['uv']) : '';
$params['uid']             = isset($_GET['ud']) ? intval($_GET['ud']) : 0;
$params['cflag1']          = isset($_GET['c1']) ? rawurldecode($_GET['c1']) : '';
$params['cflag2']          = isset($_GET['c2']) ? rawurldecode($_GET['c2']) : '';
$params['cflag3']          = isset($_GET['c3']) ? rawurldecode($_GET['c3']) : '';

if ($url) {
  $vid = V($url, $params);
  show_jsonmsg(array('vid'=>$vid));
}
show_jsonmsg(array('vid'=>0));

/**
 * new insert or update `{visiting}` table
 *
 * if $url is a numeric, then for editing;
 * else for new insert, while $url is a string.
 *
 * @param mixed $url
 *   may be a url or a numeric id
 * @param array $params
 *   containing browser extra info, for example:
 *   $params['browserName']
 *   $params['browserVersion']
 *   $params['browserCodeName']
 *   $params['browserLanguage']
 *   $params['userAgent']
 *   $params['osPlatform']
 *   $params['cookieEnabled']
 *   $params['javaEnabled']
 *   $params['screenWxH']
 *   $params['screenColor']
 *   $params['screenPixelRatio']
 *   $params['screenOrientation']
 *   $params['winOrientation']
 *   $params['flashVersion']
 *   $params['referrer']
 *   $params['uvid']
 *   $params['uid']
 *   $params['cflag1']
 *   $params['cflag2']
 *   $params['cflag3']
 *   $params['status']
 *   $params['retentionTime']
 *  @param string $action
 */
function V($url='', $params = array(), $action = 'poststatus') {
  $vid = 0;
  if (is_numeric($url)) {	// for editing
    $vid = $url;
    if (!!$vid) {
      switch ($action) {
        case 'onload_stat':
          $onload_time= isset($params['onloadTime']) ? intval($params['onloadTime']) : 0;
          $vinfo = array('onload_time'=>$onload_time, 'changed'=>time());
          D()->update('visiting', $vinfo, array('vid'=>$vid));
          break;
        case 'retention_stat':
          $retention_time= isset($params['retentionTime']) ? intval($params['retentionTime']) : 0;
          $vinfo = array('retention_time'=>$retention_time, 'changed'=>time());
          D()->update('visiting', $vinfo, array('vid'=>$vid));
          break;
        case 'poststatus':
          $uid   = isset($params['uid']) ? intval($params['uid']) : 0;
          $status= isset($params['status']) ? trim($params['status']) : 'R';
          $vinfo = array('uid'=>$uid, 'changed'=>time(), 'status'=>$status);
          D()->update('visiting', $vinfo, array('vid'=>$vid));
          break;
      }
    }
  }
  else {	// for new insert
    $ip       = get_clientip();
    $locaid   = 0;
    $locacity = '';
    
    //try to find location by ip
    //$ipinfo   = ip_save($ip, ip_convert($ip));
    if (!empty($ipinfo['location']) && !empty($ipinfo['locaid'])) {
      $locaid = $ipinfo['locaid'];
      $locacity = $ipinfo['location'];
    }

    $uid             = isset($params['uid']) ? $params['uid'] : 0;
    $browserName     = isset($params['browserName']) ? $params['browserName'] : '';
    $browserVersion  = isset($params['browserVersion']) ? $params['browserVersion'] : '';
    $browserCodeName = isset($params['browserCodeName']) ? $params['browserCodeName'] : '';
    $cookieEnabled   = isset($params['cookieEnabled']) ? $params['cookieEnabled'] : 0;
    $javaEnabled     = isset($params['javaEnabled']) ? $params['javaEnabled'] : 0;
    $userAgent       = isset($params['userAgent']) ? $params['userAgent'] : $_SERVER['HTTP_USER_AGENT'];
    $osPlatform      = isset($params['osPlatform']) ? $params['osPlatform'] : '';
    $screenWxH       = isset($params['screenWxH']) ? $params['screenWxH'] : '';
    $screenColor     = isset($params['screenColor']) ? $params['screenColor'] : 0;
    
    $flashVersion    = isset($params['flashVersion']) ? $params['flashVersion'] : '';
    $referrer        = isset($params['referrer']) ? $params['referrer'] : '';
    $cflag1          = isset($params['cflag1']) ? $params['cflag1'] : '';
    $cflag2          = isset($params['cflag2']) ? $params['cflag2'] : '';
    $cflag3          = isset($params['cflag3']) ? $params['cflag3'] : '';

    $uv    = !empty($params['uvid']) ? $params['uvid'] : md5($ip.$userAgent);	//uv = md5(ip + userAgent)
    $now   = time();
    $vinfo = array(
      'uid'      => $uid,
      'targeturl'=> $url,
      'referurl' => $referrer,
      'uv'       => $uv,
      'ip'       => $ip,
      'locaid'   => $locaid,
      'locacity' => $locacity,
       
      'user_agent'   => $userAgent,
      'browser_name' => $browserName,
      'browser_ver'  => $browserVersion,
      'browser_core' => $browserCodeName,
      'browser_lang' => $params['browserLanguage'],
      'os_platform'  => $osPlatform,
      'is_cookie'    => $cookieEnabled,
      'is_java'      => $javaEnabled,
      'screen_color' => $screenColor,
      'screen_wxh'   => $screenWxH,
      'screen_pxratio'=> $params['screenPixelRatio'],
      'screen_orientation'=> $params['screenOrientation'],
      'window_orientation'=> $params['winOrientation'],
      'flash_ver'    => $flashVersion,
       
      'cflag1'   => $cflag1,
      'cflag2'   => $cflag2,
      'cflag3'   => $cflag3,
      'created'  => $now,
      'changed'  => $now,
      'status'   => 'N'
    );
    $vid = D()->insert('visiting', $vinfo);
  }

  return $vid;
}

/*----- END FILE: fst.php -----*/