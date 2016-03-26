<?php
/**
 * Template functions Base Class for registering
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class TplBase {
  
  /**
   * Special Template Place Holder
   */
  public static function HEAD_CSS($params, $tpl = NULL) {
    return self::tplholder(array('name'=>__FUNCTION__), $tpl);
  }
  public static function HEAD_JS($params, $tpl = NULL) {
    return self::tplholder(array('name'=>__FUNCTION__), $tpl);
  }
  public static function FOOT_JS($params, $tpl = NULL) {
    return self::tplholder(array('name'=>__FUNCTION__), $tpl);
  }
  public static function FOOT_CSS($params, $tpl = NULL) {
    return self::tplholder(array('name'=>__FUNCTION__), $tpl);
  }
  
  /**
   * 'tplholder' tpl func
   */
  public static function tplholder($params, $tpl = NULL) {
    $name = isset($params['name']) ? trim($params['name']) : '';
    if (''==$name) return '';
    return '<!--#'.$name.'#-->';
  }
  
  /**
   * 'include_file' tpl func
   */
  public static function include_file($params, $tpl = NULL) {
    $file = $params['name'];
    $zip  = isset($params['zip']) ? $params['zip'] : 0;
    
    $file = View::tpl_realpath($file);
    if (!$tpl->is_cached($file)) {
  
    }
    $result = $tpl->fetch($file);
    if ($zip) {
      $result = simphp_ziphtml($result);
    }
    return $result;
  }
  
  /**
   * 'include_box' tpl func
   *
   * @param array $params
   *   parameters array, including:
   *   required parameters:
   *     $params['name']: box name
   *   control parameters:
   *     $params['at']  : 'mod:user' means that the box is defined in user module; 'app:fpic' means that the box is defined in fpic app
   *     $params['ctrl']: control parameters, for example: ctrl="hide:0,notpl:1", means show the box, and the box no need read smarty template file.
   *     detail control parameter:
   *        $pctrl['hide']: whether hide the box, default 0
   *        $pctrl['notpl']: whether no need read template file, default 0
   *        $pctrl['fpipe']: whether use FPipe render page html, default 0
   *        $pctrl['phase']: if $pctrl['fpipe'] is true, this parameter make effect, indicating the html render order.
   *        $pctrl['id']: if $pctrl['fpipe'] is true, this parameter make effect, indicating box wrapper id.
   *   callback function parameters:
   *     ...
   * @param object $tpl
   *   smarty object
   *   
   * @param array $params_parsed
   *
   * @return string
   *   generated string by box
   *
   */
  public static function include_box($params, $tpl = NULL, $params_parsed = array()) {
  
    if (empty($params_parsed)) {
  
      //~ parse ctrl parameters
      $pctrl  = array('hide'=>0, 'notpl'=>0, 'fpipe'=>is_fpipe(), 'phase'=>0, 'id'=>'', 'css'=>'', 'js'=>'');
      if (isset($params['ctrl'])) {
        $params['ctrl'] = trim($params['ctrl'], '{} ');
        $arr1 = explode(',', $params['ctrl']);
        if (count($arr1)) {
          $chkctrl = array_keys($pctrl);
          foreach ($arr1 AS &$it1) {
            $arr2 = explode(':', $it1);
            $arr2[0] = trim($arr2[0]);
            $arr2[1] = isset($arr2[1])?trim($arr2[1]):'';
            if (in_array($arr2[0], $chkctrl)) {
              $pctrl[$arr2[0]] = $arr2[1];
            }
          }
        }
        unset($params['ctrl']);
      }
  
      //~ if hide, then return empty string
      if ($pctrl['hide']) {
        return '';
      }
  
      //~ check boxname and box in where
      $boxname = $params['name'];
      $boxat   = '';
      $boxapp  = '';
      $arr1 = explode('@', $boxname);
      if (count($arr1)>1) { //like name='abox@app:fpic'
        $boxname = trim($arr1[0]);
        $arr2    = explode(':', trim($arr1[1]));
        $boxat   = trim($arr2[0]);
        $boxapp  = isset($arr2[1]) ? trim($arr2[1]) : '';
        if (!in_array($boxat, array('mod','app')) || ''==$boxapp) {
          $boxat = '';
          $boxapp= '';
        }
      }
      elseif (isset($params['at'])) {
        $at = trim($params['at']);
        if ('' != $at) {
          $arr2    = explode(':', $at);
          $boxat   = trim($arr2[0]);
          $boxapp  = isset($arr2[1]) ? trim($arr2[1]) : '';
          if (!in_array($boxat, array('mod','app')) || ''==$boxapp) {
            $boxat = '';
            $boxapp= '';
          }
        }
        unset($params['at']);
      }
      unset($params['name']);
      
      $boxfunc = $boxname;
  
      //~ if use pipe stream method to render html, just store box info first
      if ($pctrl['fpipe']) {
        global $_BOXDATA;
        $_BOXDATA[] = array(
          'boxname'=> $boxname,
          'boxfunc'=> $boxfunc,
          'boxat'  => $boxat,
          'boxapp' => $boxapp,
          'params' => $params,
          'notpl'  => $pctrl['notpl'],
          'phase'  => $pctrl['phase'],
          'id' 		 => $pctrl['id'],
          'css' 	 => $pctrl['css'],
          'js' 		 => $pctrl['js'],
        );
        return '';	//return directly, no do business logic
      }
    }
    else {
      $pctrl  = array('notpl'=>$params_parsed['notpl']);
      $boxfunc= $params_parsed['boxfunc'];
      $params = $params_parsed['params'];
      $boxat  = $params_parsed['boxat'];
      $boxapp = $params_parsed['boxapp'];
    }
  
    //~ adjust box tpl file path
    $file = 'box/'.$boxname;
    if ($boxat=='mod') {
      $file = 'file:'.SIMPHP_ROOT . 'modules/'.$boxapp.'/tpl/'.$boxname;
    }
    elseif ($boxat=='app') {
      $file = 'file:'.SIMPHP_ROOT . 'apps/'.$boxapp.'/tpl/'.$boxname;
    }
  
    //~ if no corresponding box function, then return empty string
    $boxclass = 'Box';
    if (!method_exists($boxclass,$boxfunc)) {
      return '';
    }
  
    $func_ajax_exist = method_exists($boxclass,'_'.$boxfunc); //like Box::_aboxfunc(correspondig to Box::aboxfunc)
    $return = call_user_func_array(array($boxclass,$boxfunc), array_merge(array($params), array($tpl)));	//append $tpl to last parameter
    $extra  = '';
    if (!$pctrl['notpl']) {
      if ($func_ajax_exist) {
        $tpl->assign('isajaxload', 1);
      }
      $return = $tpl->fetch($file . C('env.tplpostfix'));
      if ($func_ajax_exist) {
        $atpart = ''==$boxapp ? '' : "@{$boxat}:{$boxapp}";
        $extra  = '<script type="text/javascript">';
        $extra .= 'F.'.$boxfunc.'={ajaxurl:"'.A('ajax/loadbox').AC().'name='.$boxfunc.$atpart.'&param='.implode(',', $params).'"};';
        $extra .= '</script>';
        $return = preg_replace_callback('/<!--\[ajaxload(\s+[^\[\]]*)?\]-->(.*)<!--\[\/ajaxload\]-->/sU', function($matches){return str_replace($matches[2], '', $matches[0]);}, $return);
      }
    }
  
    return $extra . (!empty($return) ? $return : '');
  }
  
  /**
   * 'add_css' tpl func
   */
  public static function add_css($params, $tpl = NULL) {
    
    $files   = isset($params['file']) ? trim($params['file']) : '';
    $scope   = isset($params['scope']) ? trim($params['scope']) : 'global';  //'module' or 'global'
    $mod     = isset($params['mod']) ? trim($params['mod']) : '';            //when scope='module', indicating module name
    $pos     = isset($params['pos']) ? trim($params['pos']) : 'head';        //'head' or 'foot' or 'current'
    $media   = isset($params['media']) ? trim($params['media']) : 'screen';  //css media property
    $ismin   = isset($params['ismin']) ? intval($params['ismin']) : 0;       //whether min zip
    $disable = isset($params['disable']) ? intval($params['disable']) : 0;   //whether disable it
    $ver     = isset($params['ver']) ? trim($params['ver']) : 'rand';        //whether with version controlling, optional value: 'rand','none','','VERSION'
    if ($disable) return '';
    
    $content = '';
    $version = defined('STATIC_VERSION') && $ver=='rand' ? STATIC_VERSION : $ver;
    if (''!=$files) {
      $contextpath = C('env.contextpath','/');
      $tpldir = C('env.sitetheme','default');
      if (SimPHP::$gConfig['modroot'] != 'modules') {
        $tpldir = SimPHP::$gConfig['modroot'];
      }
      $filedir  = $contextpath.'themes/'.$tpldir.'/css/';
      if ('global'!=$scope) {
        $mod = empty($mod) ? current_module() : $mod;
        $filedir = $contextpath.SimPHP::$gConfig['modroot'].'/'.$mod.'/css/';
      }
            
      if (!isset($GLOBALS['_CSSPATHS'])) $GLOBALS['_CSSPATHS'] = array('head'=>array(),'foot'=>array());
      
      $filesarr = explode(',',$files);
      foreach ($filesarr AS $filename) {
        $filepath= $filedir.$filename;
        if (preg_match("!^(http|https):\/\/!i", $filename) || preg_match("!^\/\/!", $filename)) {
          $filepath = $filename;
        }
        elseif (0 ===strpos($filename, '/')) { // absolute path
          $filepath = $contextpath.substr($filename, 1);
        }
        if ($ver && $ver!='0' && $ver!='none') {
          $filepath .= (strrpos($filepath, '?')===FALSE ? '?' : '&') . 'ver=' .$version;
        }
        $html = "<link type=\"text/css\" rel=\"stylesheet\" media=\"{$media}\" href=\"{$filepath}\" />";
        if ('current'==$pos) {
          $content .= $html;
        }
        else {
          $GLOBALS['_CSSPATHS'][$pos][] = $html;
        }
      }
    }
    
    return $content;
  }
  
  /**
   * 'add_js' tpl func
   */
  public static function add_js($params, $tpl = NULL) {
    
    $files = isset($params['file']) ? trim($params['file']) : '';
    $scope = isset($params['scope']) ? trim($params['scope']) : 'global';  //'module' or 'global'
    $mod   = isset($params['mod']) ? trim($params['mod']) : '';            //when scope='module', indicating module name
    $pos   = isset($params['pos']) ? trim($params['pos']) : 'foot';        //'head' or 'foot' or 'current'
    $ismin = isset($params['ismin']) ? intval($params['ismin']) : 0;       //whether min zip
    $disable = isset($params['disable']) ? intval($params['disable']) : 0;       //whether disable it
    $ver     = isset($params['ver']) ? trim($params['ver']) : 'rand';        //whether with version controlling, optional value: 'rand','none','','VERSION'
    if ($disable) return '';
    
    $content = '';
    $version = defined('STATIC_VERSION') && $ver=='rand' ? STATIC_VERSION : $ver;
    if (''!=$files) {
      $contextpath = C('env.contextpath','/');
      $filedir  = $contextpath.'misc/js/';
      if ('global'!=$scope) {
        $mod = empty($mod) ? current_module() : $mod;
        $filedir = $contextpath.SimPHP::$gConfig['modroot'].'/'.$mod.'/js/';
      }
    
      if (!isset($GLOBALS['_JSPATHS'])) $GLOBALS['_JSPATHS'] = array('head'=>array(),'foot'=>array());
    
      $filesarr = explode(',',$files);
      foreach ($filesarr AS $filename) {
        $filepath= $filedir.$filename;
        if (preg_match("!^(http|https):\/\/!i", $filename) || preg_match("!^\/\/!", $filename)) {
          $filepath = $filename;
        }
        elseif (0 ===strpos($filename, '/')) { // absolute path
          $filepath = $contextpath.substr($filename, 1);
        }
        if ($ver && $ver!='0' && $ver!='none') {
          $filepath .= (strrpos($filepath, '?')===FALSE ? '?' : '&') . 'ver=' .$version;
        }
        $html = "<script type=\"text/javascript\" src=\"{$filepath}\"></script>";
        if ('current'==$pos) {
          $content .= $html;
        }
        else {
          $GLOBALS['_JSPATHS'][$pos][] = $html;
        }
      }
    }
    
    return $content;
  }
  
  public static function genurl($params, $tpl = NULL) {
  	$uri = isset($params['uri']) ? trim($params['uri']) : '';
  	$vars= isset($params['var']) ? trim($params['var']) : '';
  	$doms= isset($params['domain']) ? $params['domain'] : false;
  	return U($uri, $vars, $doms);
  }
  
}



/*----- END FILE: class.TplBase.php -----*/