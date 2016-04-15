<?php
/**
 * Template Class functions for registering
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Tpl extends TplBase {
  
  public static function getJsPath($params)
  {
    
  	$files = isset($params['file']) ? trim($params['file']) : '';
  	$scope = isset($params['scope']) ? trim($params['scope']) : 'global';  //'module' or 'global'

  	$contextpath = C('env.contextpath','/');
  	$filedir = $contextpath.'misc/js/';
  	$version = defined('STATIC_VERSION') ? STATIC_VERSION : '';
  	if ('global'!=$scope) {
  	  $filedir = $contextpath.SimPHP::$gConfig['modroot'].'/'.current_module().'/js/';
  	}
  	
  	return $filedir.$files.(strrpos($files, '?')===FALSE ? '?' : '&') . $version;;
  }
  
  public static function show_checked($params){
    $result = '';
    $refval = '';	//参考值
    $chkval = ''; //待检查值
    extract($params);
    if (is_array($refval)) {
      if (in_array($chkval, $refval)) {
        $result = 'checked="checked"';
      }
    }
    else {
      if ($chkval==$refval) {
        $result = 'checked="checked"';
      }
    }
    return $result;
  }
  
  public static function show_selected($params){
    $result = '';
    $refval = '';	//参考值
    $chkval = ''; //待检查值
    extract($params);
    if (is_array($refval)) {
      if (in_array($chkval, $refval)) {
        $result = 'selected="selected"';
      }
    }
    else {
      if ($chkval==$refval) {
        $result = 'selected="selected"';
      }
    }
    return $result;
  }
  
  public static function trans_time($params)
  {
    $time 	   = $params['time'];
    if (!$time) return '--';
    $is_real   = isset($params['is_real']) ? $params['is_real'] : false;
    $just_date = isset($params['just_date']) ? $params['just_date'] : false;
  
    $result = date('Y-m-d H:i:s', $time);
  
    if ($just_date) {
      $result = date('n月j日', $time);
    }
    elseif (!$is_real) {
      $differ  = time() - $time;
      if ($differ < 1*60) {		//1分钟前，则显示“刚刚”
        $result = '刚刚';
      }
      elseif ($differ < 3600) {	//1个小时前，则显示“xx分钟前”
        $result = round($differ/60).'分钟前';
      }
      else {
        if ($time < shorttotime('jn')) {			//今年以前的，显示具体年月“yyyy-mm-dd hh:mm”
          $result = date('Y-m-d H:i', $time);
        }
        elseif ($time < shorttotime('qt')) {	// 今年内的，显示“x月y日 hh:mm”
          $result = date('n月j日 H:i', $time);
        }
        elseif ($time < shorttotime('zt')) {	// 前天内的，显示“前日 hh:mm”
          $result = date('前天 H:i', $time);
        }
        elseif ($time < shorttotime('jt')) {	// 昨天内的，显示“昨日 hh:mm”
          $result = date('昨天 H:i', $time);
        }
        else {	//今天内的，显示“今日 hh:mm”
          $result = date('今天 H:i', $time);
        }
      }
    }
  
    return $result;
  }
  
  public static function sortfield($params, $tpl = NULL)
  {
    $field       = isset($params['field']) ? $params['field'] : 'rid';
    $listorderby = $tpl->_tpl_vars['listorderby'];
    $listorder   = $tpl->_tpl_vars['listorder'];
    $result = '';
    if ($field===$listorderby) {
      if ($listorder=='DESC') {
        $result = '<b class="icon icon-list-down" title="降序"></b>';
      }
      else {
        $result = '<b class="icon icon-list-up" title="升序"></b>';
      }
    }
    return $result;
  }
  
  public static function encodeurl($params, $tpl = NULL)
  {
  	$str = isset($params['str']) ? $params['str'] : '';
  	return $str ? rawurlencode($str) : '';
  }
  
  public static function include_pager($params, $tpl = NULL)
  {
    static $eid = 0;
  
    //~ prepare income parametets
    $url      = '';
    $extraurl = '';
    $element  = $eid;
    $callback = '';
    if (isset($params['url'])) {
      $url = $params['url'];
      unset($params['url']);
    }
    if (isset($params['extraurl'])) {
      $extraurl = $params['extraurl'];
      unset($params['extraurl']);
    }
    if (isset($params['element'])) {
      $element = $params['element'];
      unset($params['element']);
    }
    if (isset($params['callback'])) {
      $callback = $params['callback'];
      unset($params['callback']);
    }
  
    $pageparams = array();
    $pageparams['pager_eid'] = $eid++;
    $pageparams['callback']  = $callback!='' ? $callback : '';
  
    $key_arr 	= array();
    $val_arr 	= array();
    foreach ($params as $key => $val) {
      $key_arr[] = "/%{$key}/";
      $val_arr[] = $val;
    }
    if (!empty($url) && !empty($key_arr)) {
      $url = preg_replace($key_arr,$val_arr,$url);
    }
  
    $urlconnter = View::link_connector();
    $pager_curr = $GLOBALS['pager_currpage_arr'][$element];
    $pager_max  = $GLOBALS['pager_totalpage_arr'][$element];
    $pos = strpos($url, '#');
    if ($pos >=0 ) {
      $url = substr($url, $pos);
      if ('?'===$urlconnter) $urlconnter = ',';
    }
    
    $pagerpname  = 'p';
    $extraurl    = empty($extraurl) ? '' : $extraurl.'&';
    $urlprefix   = $url.$urlconnter.$extraurl.$pagerpname.'=';
  
    $pageparams['pager_max'] 	   = $pager_max;
    $pageparams['pager_curr']		 = $pager_curr;
    $pageparams['pager_prefix']  = $urlprefix;
    $pageparams['pager_first']	 = $urlprefix.'1';
    $pageparams['pager_prev'] 	 = $urlprefix.($pager_curr-1);
    $pageparams['pager_next'] 	 = $urlprefix.($pager_curr+1);
    $pageparams['pager_last'] 	 = $urlprefix.$pager_max;
    if ($pager_curr==1) {
      $pageparams['pager_first'] = '';
      $pageparams['pager_prev'] = '';
    }
    if ($pager_curr==$pager_max) {
      $pageparams['pager_next'] = '';
      $pageparams['pager_last'] = '';
    }
  
    $pageparams['pager_links']   = '';
    $selected = '';
    for($i=1; $i<=$pager_max; $i++) {
      $selected = '';
      if ($i == $pager_curr) {
        $selected = ' selected="selected"';
      }
      $pageparams['pager_links'] .= '<option value="'.$i.'"'.$selected.'>'.$i.'/'.$pager_max.'</option>';
    }
  
    $tpl->assign('pageparams', $pageparams);
    $result = $tpl->fetch('inc/pagination.htm');
    return $result;
  }
  
  /**
   * 图片延迟加载函数标签
   * @param array  $params
   * @param string $tpl
   * @return string
   */
  static function imglazyload($params, $tpl = NULL) {
  	$src = isset($params['src']) ? trim($params['src']) : '';
  	$default_src = isset($params['default_src']) ? trim($params['default_src']) : '';
  	return imglazyload($src, $default_src);
  }
  
  /**
   * 图片队列加载函数标签
   * @param array  $params
   * @param string $tpl
   * @return string
   */
  static function imgqueueload($params, $tpl = NULL) {
  	$src = isset($params['src']) ? trim($params['src']) : '';
  	$default_src = isset($params['default_src']) ? trim($params['default_src']) : '';
  	return imgqueueload($src, $default_src);
  }
}
 
/*----- END FILE: class.Tpl.php -----*/