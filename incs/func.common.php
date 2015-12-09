<?php
/**
 * Common functions entry point of Current Project
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

/**
 * get db config info
 *
 * @param string $key: if $key is empty, then return all config info in table 'tb_config'
 * @return mixed
 */
function config_get($key='')
{
  $return = '';
  if (empty($key)) {	// get all config info
    $result = D()->query("SELECT * FROM {config} WHERE 1")->fetch_array_all();
    $return = array();
    foreach($result AS $obj) {
      if ($obj['encode']=='S') {
        $return[] = unserialize($obj['val']);
      }
      elseif ($obj['encode']=='J') {
        $return[] = json_decode($obj['val'], TRUE);
      }
      else {
        $return[] = $obj['val'];
      }
    }
  }
  else {	// get the only $key config info
    $result = D()->query("SELECT * FROM {config} WHERE `key`='%s'", $key)->fetch_array();
    $return = $result['val'];
    if (!empty($result)) {
      if ($result['encode']=='S') {
        $return = unserialize($return);
      }
      elseif ($result['encode']=='J') {
        $return = json_decode($return, TRUE);
      }
    }
  }
  return $return;
}

/**
 * set db config info
 *
 * @param $key
 *   configure key
 * @param $val
 *   configure value, if $val has '+' prefix, then db field +1, same as '-' prefix
 * @param $encode
 *   optional value: 'R' -> raw plain text, 'S' -> Serialized, 'J' -> JSON
 * @return integer
 */
function config_set($key, $val = '', $encode = 'R')
{
  if (!in_array($encode, array('R','S','J'))) {
    $encode = 'R';
  }
  
  $metas = $key;
  if (!is_array($key)) {
    $metas = array(array('key'=>$key, 'val'=>$val, 'encode'=>$encode));
  }

  $effcnt = 0;
  if (count($metas)) {
    foreach ($metas AS $meta) {
      $op = '';
      if (is_string($meta['val'])) {
        if ($meta['val'][0]=='+' || $meta['val'][0]=='-') {
          $op = $meta['val'][0];
          $meta['val'] = substr($meta['val'],1);
        }
      }

      $db  = D();
      $val = $meta['val'];
      if ($meta['encode']=='S') {
        $val = serialize($meta['val']);
      }
      elseif ($meta['encode']=='J') {
        $val = json_encode($meta['val'], JSON_UNESCAPED_UNICODE);
      }
      $exist = $db->result("SELECT `cid` FROM {config} WHERE `key`='%s'", $meta['key']);
      if ($exist) {
        if ($op=='') {
          $db->query("UPDATE {config} SET `val`='%s', `encode`='%s' WHERE `key`='%s'", $val, $meta['encode'], $meta['key']);
        }
        else {	// $op='+' or $op='-'
          if (!empty($val)) {
            $db->query("UPDATE {config} SET `val`=val{$op}%s, `encode`='%s' WHERE `key`='%s'", $val, $meta['encode'], $meta['key']);
          }
        }
      }
      else {
        $db->query("INSERT INTO {config}(`key`,`val`,`encode`) VALUES('%s','%s','%s')", $meta['key'], $val, $meta['encode']);
      }
      
      if ($db->affected_rows()) {
        ++$effcnt;
      }
    }
  }
  return $effcnt;
}

/**
 * 返回一个空图片路径(用于占位)
 * @return string
 */
function emptyimg()
{
  return C('env.usecdn') ? 'http://fcdn.qiniudn.com/img/b.gif'
                         : C('env.contextpath').'misc/images/b.gif';
}

function ploadingimg()
{
  return C('env.usecdn') ? 'http://fcdn.qiniudn.com/img/bloading.gif' 
                         : C('env.contextpath').'misc/images/bloading.gif';
}

/**
 * 跟踪调试，结果保存在数据库表: tb_debug
 * @param string $key
 * @param mixed $val
 */
function trace_debug($key, $val)
{
  if (!empty($key)) {
    $val = is_string($val) ? $val : print_r($val, TRUE);
    $dtime = simphp_dtime();
    D()->query("INSERT INTO `{debug}`(`key`,`val`,`dtime`) VALUES('{$key}', '{$val}', '{$dtime}')");
  }
  return;
}

/**
 * 处理用户输入
 * 
 * @param string $str
 * @param boolean $is_strip_tags	是否处理html,php标签
 * @return string
 */
function treat_input_str($str, $is_strip_tags=true)
{
	if(is_array($str)) {
		$a = array();
		foreach($str as $key=>$val) {
			$a[$key] = trim($val);
			if($is_strip_tags) {
				$a[$key] = strip_tags($a[$key]);
			}
		}
		$str = $a;
	}
	else {
		$str = trim($str);
		if($is_strip_tags) {
			$str = strip_tags($str);
		}
	}
	return $str;
}

/**
 * 生成指定长度的随机密码
 * 
 * @param unknown_type $len
 * @param char $type 类型	both(混合),number(数字)
 */
function create_randcode($len=10,$type='both')
{
	switch ($type) {
		case 'number': $chars = '0123456789';break;
		case 'both': $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';break;
	}

	mt_srand((double)microtime()*1000000*getmypid());
	$code="";
	while(strlen($code)<$len) {
		$code.=substr($chars,(mt_rand()%strlen($chars)),1);
	}
	return $code;
}

/**
 * 默认头像
 */
function default_logo()
{
	return C('env.contextpath').'misc/images/avatar/default_ulogo.png';
}

/**
 * 检测用户是否登录
 */
function checkLogin_ajax()
{
	$res = ['flag'=>'FAIL','msg'=>'请先登录'];
	if(!Member::isLogined()) {
		Response::sendJSON($res);
	}
}

/**
 * 解析html字串的媒体(图片、视频等)元素个数
 * @param string $html
 * @return array('picnum'=>xx,'videonum'=>yy)
 */
function parse_html_media_count($html)
{
  if (!class_exists('simple_html_dom')) {
    include (SIMPHP_ROOT . '/core/libs/htmlparser/simple_html_dom.php');
  }
  $doms = str_get_html($html);
  $result = array(
    'picnum' => count($doms->find('.edui-img')),
    'videonum' => count($doms->find('.edui-faked-video')),
  );
  return $result;
}

/**
 * 解析html字符串中的媒体元素(图片、视频等)
 * @param string $html
 * @return string
 */
function parse_html_media($html)
{
  if (!class_exists('simple_html_dom')) {
    include (SIMPHP_ROOT . '/core/libs/htmlparser/simple_html_dom.php');
  }
  $doms = str_get_html($html);
  foreach ($doms->find('.edui-img,.edui-faked-video') AS $dom) {
    if ($dom->tag == 'embed') {
      $dom->outertext = parse_video_code($dom->getAllAttributes());
    }
    elseif ($dom->tag == 'img') {
      $attrs_to = array('width'=>'data-width','height'=>'data-height');
      foreach ($attrs_to AS $k => $v) {
        if ($dom->hasAttribute($k)) {
          $dom->setAttribute($v,$dom->getAttribute($k));
          $dom->removeAttribute($k);
        }        
      }
    }
  }
  return $doms;
}

/**
 * 解析html字串的视频元素
 * @param string $html
 * @return string
 */
function parse_html_video($html)
{
  if (!class_exists('simple_html_dom')) {
    include (SIMPHP_ROOT . '/core/libs/htmlparser/simple_html_dom.php');
  }
  $doms = str_get_html($html);
  foreach ($doms->find('.edui-faked-video') AS $dom) {
    if ($dom->tag == 'embed') {
      $dom->outertext = parse_video_code($dom->getAllAttributes());
    }
  }
  return $doms;
}

/**
 * 解析视频代码
 * @param array $attrs
 * @return string
 */
function parse_video_code($attrs)
{
  if (empty($attrs) || empty($attrs['src'])) return '';

  $src    = $attrs['src'];
  $width  = isset($attrs['width']) ? $attrs['width'] : '';
  $height = isset($attrs['height']) ? $attrs['height'] : '';
  $play   = isset($attrs['play']) ? ' play="'.$attrs['play'].'"' : '';
  $loop   = isset($attrs['loop']) ? ' loop="'.$attrs['loop'].'"' : '';
  $allowfullscreen = isset($attrs['allowfullscreen'])&&$attrs['allowfullscreen']!='false' ? ' allowfullscreen' : '';

  $src_lc = strtolower($attrs['src']);
  $vcode  = '';
  if (strpos($src_lc, 'letv.com')) { //乐视云视频
    $src = str_replace('bcloud.swf', 'bcloud.html', $src);
    if ($width) {
      $src .= '&width='.$width;
    }
    if ($height) {
      $src .= '&height='.$height;
    }
    $vcode = '<iframe'
           . ($width ? ' width="'.$width.'"' : '')
           . ($height ? ' height="'.$height.'"' : '')
           . ' src="'.$src.'" frameborder="0"'.$allowfullscreen.'></iframe>';
  }
  elseif (strpos($src_lc, 'youku.com')) { //优酷
    if (preg_match('/^http:\/\/player\.youku\.com\/player\.php\/sid\/([a-z0-9]+)/i', $src, $matches)) {
      $vcode = '<iframe'
             . ($width ? ' width="'.$width.'"' : '')
             . ($height ? ' height="'.$height.'"' : '')
             . ' src="http://player.youku.com/embed/'.$matches[1].'" frameborder="0"'.$allowfullscreen.'></iframe>';
    }
  }
  elseif (strpos($src_lc, 'tudou.com')) { //土豆

  }
  elseif (strpos($src_lc, '56.com')) { //56
    if (preg_match('/^http:\/\/player\.56\.com\/v_([a-z0-9]+)\.swf/i', $src, $matches)) {
      $vcode = '<iframe'
             . ($width ? ' width='.$width : '')
             . ($height ? ' height='.$height : '')
             . ' src="http://www.56.com/iframe/'.$matches[1].'" frameborder=0'.$allowfullscreen.'></iframe>';
    }
  }
  elseif (strpos($src_lc, 'ku6.com')) { //酷6
    if (preg_match('/^http:\/\/player\.ku6\.com\/refer\/([^\/]+)\/v.swf/i', $src, $matches)) {
      $vcode = '<script data-vid="'.$matches[1].'" src="//player.ku6.com/out/v.js"'
             . ($width ? ' data-width="'.$width.'"' : '')
             . ($height ? ' data-height="'.$height.'"' : '')
             . '></script>';
    }
  }
  elseif (strpos($src_lc, 'youtube.com')) { //youtube
    if (preg_match('/^http:\/\/www\.youtube\.com\/v\/([a-z0-9]+)/i', $src, $matches)) {
      $vcode = '<iframe'
             . ($width ? ' width="'.$width.'"' : '')
             . ($height ? ' height="'.$height.'"' : '')
             . ' src="http://www.youtube.com/embed/'.$matches[1].'" frameborder="0"'.$allowfullscreen.'></iframe>';
    }
  }

  return $vcode;
}

function gen_signtail()
{
  $html =<<<SHTML
<div class="signtail">
<p><span><br></span></p>
<hr/>
<p><span>【8558游戏平台】是8558游戏平台旗下的微信公众帐号，每天为您带来游戏新开服信息，游戏相关资讯和文章评论，欢迎关注！</span></p>
<p><span><br></span></p>
<p><span>微信帐号：Game8558</span></p>
<p><span>平台地址：m.8558.com; www.8558.com</span></p>
<p><span>新浪微博：http://e.weibo.com/yx8558</span></p>
<p><span>腾讯微博：http://t.qq.com/yx8558</span></p>
<p><span>客服QQ&nbsp; ：1255818558 或 1255828558</span></p>
<p><span><br></span></p>
<p><span>微信收听方式如下：</span></p>
<p><span>&nbsp; 1.&nbsp;在微信添加朋友，搜索微信公众号，查找：Game8558</span></p>
<p><span>&nbsp; 2.&nbsp;用微信扫描下面二维码，进行关注：</span></p>
<p><span><img src="http://m.8558.com/a/content/201401/15_095647_sxruas.png" alt=""></span></p>
<p><br></p>
</div>
SHTML;
  return $html;
}

/**
 * 生成指定类型的id
 * 
 * @param string $idname
 */
function idmaker($idname)
{
	if(!in_array($idname, ['serial_no','order_no'])){
		return '';
	}
	$db = D();
	$date		= date('Ymd');
	$lock		= "LOCK TABLE {idmaker} WRITE";
	//日初始
	$select_init = "SELECT * FROM {idmaker} WHERE name='%s' AND date='%s' ";
	$update_init = "UPDATE {idmaker} SET cur=start,date='%s' WHERE name='%s' ";
	//生成id
	$update = "UPDATE {idmaker} SET cur=cur+step WHERE name='%s'";
	$select = "SELECT cur FROM {idmaker} WHERE name='%s'";
	
	$unlock	= "UNLOCK TABLE";
	
	$db->query($lock);
	$record = $db->get_one($select_init, $idname, $date);
	if(empty($record)){
		//初始化当天值
		$db->query($update_init, $date,$idname);
		if ( $db->affected_rows()==1 ) {
			$result = $db->result($select, $idname);
			$db->query($unlock);
			return $result;
		}
	}else{
		$db->query($update, $idname);
		if ( $db->affected_rows()==1 ) {
			$result = $db->result($select, $idname);
			$db->query($unlock);
			return $result;
		}
	}
	$db->query($unlock);
	return '';
}

/**
 * 生成站内流水号
 */
function gen_serial_no()
{
	//站内流水号为16位数字,年月日(8)+8位自增长
	$pre = date('Ymd');
	$id = idmaker('serial_no');
	if($id==''){
		return '';
	}
	$id = str_pad($id, 8, '0', STR_PAD_LEFT);
	return $pre.$id;
}

/**
 * 生成订单号
 */
function gen_order_no()
{
	//站内流水号为15位数字,年月日(8)+7位自增长
	$pre = date('Ymd');
	$id = idmaker('order_no');
	if($id==''){
		return '';
	}
	$id = str_pad($id, 7, '0', STR_PAD_LEFT);
	return $pre.$id;
}

/**
 * 对称加解密
 * 
 * @param string $string
 * @param string $operation
 * @param string $key
 * @param integer $expiry
 * @return string
 */
function zf_authcode($string, $operation = 'DECODE', $key = '', $expiry = 0)
{
  $ckey_length = 4;

  $key = md5($key ? $key : ZF_KEY);
  $keya = md5(substr($key, 0, 16));
  $keyb = md5(substr($key, 16, 16));
  $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

  $cryptkey = $keya.md5($keya.$keyc);
  $key_length = strlen($cryptkey);

  $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
  $string_length = strlen($string);

  $result = '';
  $box = range(0, 255);

  $rndkey = array();
  for($i = 0; $i <= 255; $i++) {
    $rndkey[$i] = ord($cryptkey[$i % $key_length]);
  }

  for($j = $i = 0; $i < 256; $i++) {
    $j = ($j + $box[$i] + $rndkey[$i]) % 256;
    $tmp = $box[$i];
    $box[$i] = $box[$j];
    $box[$j] = $tmp;
  }

  for($a = $j = $i = 0; $i < $string_length; $i++) {
    $a = ($a + 1) % 256;
    $j = ($j + $box[$a]) % 256;
    $tmp = $box[$a];
    $box[$a] = $box[$j];
    $box[$j] = $tmp;
    $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
  }

  if($operation == 'DECODE') {
    if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
      return substr($result, 26);
    } else {
      return '';
    }
  } else {
    return $keyc.str_replace('=', '', base64_encode($result));
  }
}

/**
 * 订单状态
 * 
 * @param integer $state
 * @return Ambigous <string>|multitype:string
 */
function getOrderState($state='')
{
  //0:已下单，-1:表示发货失败,-2:已取消，1：已扣款，2:已发货,3:交易完成
  $state_array = [
    '0' => '已下单',
    '1' => '已扣款',
    '2' => '已发货',
    '3' => '交易完成',
    '-1'=> '发货失败',
    '-2'=> '已取消'
  ];
  
  if(isset($state_array[$state])) {
    return $state_array[$state];
  }
  else {
    return $state_array;
  }
}

/**
 * 发货状态
 * @param integer $state
 * @return Ambigous <string>|multitype:string
 */
function getSendState($state='')
{
  //发货状态,0:不发货,1:待发货，2:已发货,3:已收货
  $state_array = [
    '0' => '不发货',
    '1' => '待发货',
    '3' => '已发货'
  ];
  if(isset($state_array[$state])) {
    return $state_array[$state];
  }
  else {
    return $state_array;
  }
}

/**
 * 获取发货方式
 * 
 * @param string $type
 */
function get_send_type($type = NULL)
{
  //发货发式,0:未知,1:EMS,2:顺丰,3:圆通,4:申通,5:汇通,6:中通,7:韵达,8:天天
  $send_type = array(
    '0'=>array('name'=>'未知','site'=>''),
    '1'=>array('name'=>'EMS','site'=>'http://www.ems.com.cn/'),
    '2'=>array('name'=>'顺丰','site'=>'http://www.sf-express.com/cn/sc/'),
    '3'=>array('name'=>'圆通','site'=>'http://www.yto.net.cn'),
    '4'=>array('name'=>'申通','site'=>'http://www.sto.cn/'),
    '5'=>array('name'=>'汇通','site'=>'http://www.800bestex.com/'),
    '6'=>array('name'=>'中通','site'=>'http://www.zto.cn/'),
    '7'=>array('name'=>'韵达','site'=>'http://www.yundaex.com/'),
    '8'=>array('name'=>'天天','site'=>'http://www.ttkdex.com/')   
  );
  
  if($type==NULL) {
    return $send_type;
  }
  elseif($send_type[$type]) {
    return $send_type[$type];
  }
  else {
    return $send_type[0];
  }
}

/**
 * 字符串的截取
 * 
 * @param unknown_type $string
 * @param unknown_type $length
 * @param unknown_type $append
 * @return string
 */
function truncate($string,$length,$append = true)
{
    $string = trim($string);
    $strlength = strlen($string);
    if ($length == 0 || $length >= $strlength){
        return $string;
    }elseif ($length < 0){
        $length = $strlength + $length;
        if ($length < 0)
        {
            $length = $strlength;
        }
    }
    if (function_exists('mb_substr')){
        $newstr = mb_substr($string, 0, $length,"UTF-8");
    }elseif (function_exists('iconv_substr')){
        $newstr = iconv_substr($string, 0, $length,"UTF-8");
    }else{
    for($i=0;$i<$length;$i++){
        $tempstring=substr($string,0,1);
        if(ord($tempstring)>127){
          $i++;
          if($i<$length){
            $newstring[]=substr($string,0,3);
            $string=substr($string,3);
          }
        }else{
          $newstring[]=substr($string,0,1);
          $string=substr($string,1);
        }
      }
    $newstr =join($newstring);
    }
    if ($append && $string != $newstr){
        $newstr .= '...';
    }
    return $newstr;
}

/**
 * 返回ecshop相对于本程序的数据表前缀
 * 
 * @param string $tbname
 * @return string
 */
function ectable( $tbname )
{
  return ECDB . '.`' . ECDB_PRE . $tbname . '`';
}

/**
 * 显示在html head 里的全局js
 */
function headscript()
{
  global $user;
  
  // Global data
  $wxVer   = Weixin::browserVer();
  $wxVer   = $wxVer ? "'".$wxVer."'" : 0;
  $isWxBro = $wxVer ? 'true' : 'false';
  $wxConf  = C('api.weixin_fxmgou');
  $wxAppId = $wxConf['appId'];
  $appName = L('appname');
  $currUri = Request::uri();
  $ctxpath = C('env.contextpath');
  
  $script  = '<script type="text/javascript">';
  $script .= "var wxData={isWxBrowser:{$isWxBro},browserVer:{$wxVer},isReady:false,appId:'{$wxAppId}'},gData={appName:'{$appName}',currURI:'{$currUri}',referURI:'',contextpath:'{$ctxpath}'},gUser={};";
  foreach (((array)$user) AS $k => $v) {
    if (in_array($k, ['uid','openid','unionid','subscribe','username','nickname','sex','logo','ec_user_id'])) {
      $v = (is_numeric($v)&&$k!='username') ? $v : "'".$v."'";
      $script .= 'gUser.'.$k."={$v};";
    }
  }
  $script .= '</script>';
  
  echo $script;
}

/**
 * 显示在html foot后的js，如微信JS-SDK
 */
function footscript()
{
  $q = Request::q();
  if (!$q) $q = '/';
  
  $resjs = '';
  $wx = new Weixin([Weixin::PLUGIN_JSSDK]);
  $base_url = C('env.site.mobile').'/';
  $shop_url = C('env.site.shop').'/';
  
  $wxjs_init = '';
  $wxjs_api  = '';
  $wxjs_api_extra = '';
  $wxapi_init= ['onMenuShareTimeline','onMenuShareAppMessage','onMenuShareQQ','onMenuShareWeibo'];
  
  if (preg_match('!item/(\d+)!i', $q, $match)) { //分享商品详情页
    $goods_id = $match[1];
    $goods_info = Goods::getGoodsInfo($goods_id, array('is_on_sale'=>0,'goods_img'=>1));
    if (!empty($goods_info)) {
      
      $goods_name = str_replace("'", "\\'", $goods_info['goods_name']);
      
      $cat_chain= Goods::getParentCatesChain($goods_info['cat_id']);
      $cat_pre  = '';
      foreach ($cat_chain AS $c) {
        $cat_pre .= $c['cat_name'] . '//';
      }
      if (''!=$cat_pre) {
        $cat_pre  = rtrim($cat_pre,'/');
        $cat_pre  = '['.$cat_pre.']';
      }
      
      //$fx_desc  = str_replace("'", "\\'", $goods_info['goods_name']);
      $fx_title = $cat_pre. $goods_name;
      $fx_desc  = $goods_name;
      $fx_link  = $base_url.'item/'.$goods_info['goods_id'];
      $fx_pic   = $shop_url.$goods_info['goods_thumb'];
      
      $goods_gallery = Goods::getGoodsGallery($goods_id);
      $fx_picset= [];
      if (!empty($goods_gallery)) {
        foreach ($goods_gallery AS $ga) {
          $fx_picset[] = $shop_url.$ga['img_url'];
        }
        $fx_picset_str = implode("','", $fx_picset);
        $wxjs_api_extra =<<<HREDOC_0
$('.gpic img').click(function(){
  wx.previewImage({
    current: '{$fx_picset[0]}',
    urls: ['{$fx_picset_str}']
  });
});
HREDOC_0;
      }
      
      $wxjs_init = $wx->jssdk->js($wxapi_init+['previewImage']);
      
    }

  }
  else { //其他页都只分享首页
    
    $fx_title = "福小蜜";
    $fx_desc  = '福小蜜海外购，专注于海外商品的代购，让你足不出户即可享受来自澳洲、新西兰、加拿大等海外的放心商品。';
    $fx_link  = $base_url;
    $fx_pic   = $base_url.'misc/images/napp/touch-icon-144.png';
    
    $wxjs_init = $wx->jssdk->js($wxapi_init);
  }
  
  //API接口
  $wxjs_api  =<<<HEREDOC_1
wx.ready(function(){
  //分享给微信好友
  wx.onMenuShareAppMessage({
    title: '{$fx_title}',
    desc: '{$fx_desc}',
    link: '{$fx_link}',
    imgUrl: '{$fx_pic}',
    success: function (res) {
        alert('谢谢分享！');
    },
    cancel: function (res) {
        //alert('已取消');
    }
  });
  //分享到朋友圈
  wx.onMenuShareTimeline({
    title: '{$fx_title}',
    link: '{$fx_link}',
    imgUrl: '{$fx_pic}',
    success: function (res) {
      alert('谢谢分享！');
    },
    cancel: function (res) {
      //alert('已取消');
    }
  });
  //分享到QQ
  wx.onMenuShareQQ({
    title: '{$fx_title}',
    desc: '{$fx_desc}',
    link: '{$fx_link}',
    imgUrl: '{$fx_pic}',
    success: function (res) {
      alert('谢谢分享！');
    },
    cancel: function (res) {
      //alert('已取消');
    }
  });
  //分享到微博
  wx.onMenuShareWeibo({
    title: '{$fx_title}',
    desc: '{$fx_desc}',
    link: '{$fx_link}',
    imgUrl: '{$fx_pic}',
    success: function (res) {
      alert('谢谢分享！');
    },
    cancel: function (res) {
      //alert('已取消');
    }
  });
  {$wxjs_api_extra}
});
HEREDOC_1;
  
  if (''!=$wxjs_init) {
    $resjs = "{$wxjs_init}\n<script>if (typeof(wx)=='object') {\n{$wxjs_api}\n}</script>";
  }
  
  echo $resjs;
}

function form_topay_script($back_url) {
  $frm_action = U('trade/order/topay');
  $html =<<<HEREDOC
<div class="hide">
  <form id="frm-topay" action="{$frm_action}" method="post">
    <input type="hidden" name="pay_mode" value="wxpay" id="frm-paymode" />
    <input type="hidden" name="order_id" value="0"  id="frm-orderid"/>
    <input type="hidden" name="back_url" value="{$back_url}" />
  </form>
</div>
<script type="text/javascript">
function form_topay_submit(order_id, pay_mode) {
  var _self = form_topay_submit;
  if (typeof(_self._frm) !='object') {
    _self._frm = $('#frm-topay');
  }
  if (typeof(_self._frm_orid) !='object') {
    _self._frm_orid = $('#frm-orderid');
  }
  if (typeof(_self._frm_mode) !='object') {
    _self._frm_mode = $('#frm-paymode');
  }
  if (typeof(pay_mode)=='undefined') {
    pay_mode = 'wxpay';
  }
  _self._frm_orid.val(order_id);
  _self._frm_mode.val(pay_mode);
  _self._frm.submit();
}
</script>
HEREDOC;
  echo $html;
}

function require_scroll2old() {
  $html =<<<HEREDOC
<script>
F.scroll2old = true;
F.onScrollEnd(function(){
  Cookies.set(F.scroll_cookie_key(),this.y,{path:'/'});
});
</script>
HEREDOC;

  echo $html;
}

/**
 * 获取预定时间列表(跟ecshop中的一致)
 * 
 * @param integer $day
 * @return array 预定天数 => name
 */
function get_booking_days_list( $day = NULL )
{
  static $booking_day_list = array(
    '0'    =>  '现货',
    '7'    =>  '1周内',
    '14'   =>  '2周内',
    '21'   =>  '3周内',
    '28'   =>  '4周内',
    '30'   =>  '1个月内',
    '45'   =>  '1个半月',
    '60'   =>  '2个月内',
  );

  return isset($day) ? $booking_day_list[$day] :  $booking_day_list;
}










/*----- END FILE: func.common.php -----*/