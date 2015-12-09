<?php
/**
 * 与Member相关常用方法
 *
 * @author afar<afarliu@163.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Member{
  
  /**
   * 本地access token的生命周期(秒)
   * @var integer
   */
  const LOCAL_ACCESS_TOKEN_LIFETIME = 604800; //60*60*24*7
  
	/**
	 * 检测用户名合法性
	 * @param string $name
	 */
	public static function checkUsername($name)
	{
		$rs  = [false,''];
		$msg = '用户名规则为5-15个字母、数字或下划线(首字母不能为数字)';
		$pattern = '/^[a-zA-Z_][a-zA-Z_\d]{4,14}$/';
		if( preg_match($pattern,$name) ) {
			$rs[1] = $msg;
			return $rs;
		}
		
		//敏感用户名检测
		$censorusername = C('stopword.uname');
		foreach($censorusername as $val){
			if(preg_match($val,$name)){
				$msg = '用户名中含有敏感词';
				$rs[1] = $msg;
				return $rs;
			}
		}
		$rs[0] = true;
		
		return $rs;
	}
	
	/**
	 * 检测密码合法性
	 * @param string $pwd
	 */
	public static function checkPwd($pwd)
	{
		$rs  = [false,''];
		$msg = '密码应为6-20个字符';
		$len = strlen($pwd);
		if($len<6||$len>20) {
			$rs[1] = $msg;
			return $rs;
		}
		$rs[0] = true;
		
		return $rs;
	}
	
	/**
	 * 检测Email合法性
	 * @param string $email
	 */
	public static function checkEmail($email)
	{
		$rs = [false,''];
		
		if(strlen($email) > 6 && preg_match("/^[\w\-\.]+@[\w\-]+(\.\w+)+$/", $email)){
			$rs[0] = true;
		}
		else {
			$rs[1] = '邮箱格式不正确：'.$email;
		}
		return $rs;
	}
	
	/**
	 * 检测moblie合法性
	 * @param string $mobile
	 */
	public static function checkMobile($mobile)
	{
		$rs = [false,''];
		
		if(preg_match("/^[0-9]{11}$/", $mobile)){
			$rs[0] = true;
		}
		else{
			$rs[1] = '手机号格式不正确';
		}
		
		return $rs;
	}

	/**
	 * 用户是否登录
	 * @return boolean
	 */
	public static function isLogined()
	{
		if( $GLOBALS['user']->uid ){
			return true;
		}
		else{
		  /*
			$openid = Cookie::get('auth_id');
			if(FALSE===$openid){
				return false;
			}else{
				$openid = zf_authcode($openid, 'DECODE' , Config::get('env.au_key'));
				$userinfo = self::getTinyInfoByOpenid($openid);
				if(empty($userinfo)){
					return false;
				}else{
					$GLOBALS['user']->uid = $userinfo['uid'];
					return true;
				}
			}
			*/
		  return false;
		}
	}
	
	/**
	 * 检查uid是否关注公众号
	 * 
	 * @param $uid
	 * @param $platform
	 * @return boolean
	 */
	public static function isSubscribe($uid, $platform = 'weixin')
	{
	  $b = D()->result("SELECT `subscribe` FROM `{member}` WHERE `uid`=%d AND `from`='%s'",$uid,$platform);
	  return $b ? true : false;
	}
	
	public static function getUser()
	{
		$user = ['uid'=>0];
		if(self::isLogined()){
			$user['uid'] = $GLOBALS['user']->uid;
		}
		return $user;
	}

	public static function autoLogin($openid)
	{
		//$openid = zf_authcode($openid, 'ENCODE' , Config::get('env.au_key'));
		//Cookie::set('auth_id', $openid, PHP_INT_MAX);
	}
	
	/**
	 * 检查对应$openid的用户是否存在
	 *
	 * @param string $openid
	 * @param string $from 用户来源
	 * @return boolean
	 */
	public static function checkExistByOpenid($openid, $from = 'weixin')
	{
	  $uid = 0;
	  if ('weixin'==$from) {
	    $uid = D()->result("SELECT `uid` FROM `{member}` WHERE `openid`='%s' AND `from`='%s'", $openid, $from);
	  }
	  return $uid ? TRUE : FALSE;
	}
	
	public static function checkECUserExist($ec_user_id)
	{
	  $ectb = ectable('users');
	  $user_id = D()->result("SELECT `user_id` FROM {$ectb} WHERE `user_id`=%d", $ec_user_id);
	  return $user_id ? TRUE : FALSE;
	}
	
	/**
	 * 返回最小用户信息字段
	 *
	 * @return string
	 */
	private static function tinyFields($prefix = '')
	{
	  if (''!=$prefix && strrpos($prefix, '.')===false) {
	    $prefix .= '.';
	  }
	  return "{$prefix}`uid`,{$prefix}`openid`,{$prefix}`unionid`,{$prefix}`subscribe`,{$prefix}`subscribe_time`,{$prefix}`username`,{$prefix}`nickname`,{$prefix}`sex`,{$prefix}`logo`,{$prefix}`state`,{$prefix}`from`";
	}
	
	/**
	 * 通过$uid获取最小用户信息
	 *
	 * @param integer $uid
	 * @param boolean $include_ecuser
	 * @return multitype:
	 */
	public static function getTinyInfoByUid($uid, $include_ecuser = false)
	{
	  $uinfo = D()->get_one("SELECT ".self::tinyFields()." FROM `{member}` WHERE `uid`=%d", $uid);
	  if ($include_ecuser && !empty($uinfo)) {
	    $ectable  = ectable('users');
	    $uinfo_ec = D()->get_one("SELECT `user_id` AS ec_user_id FROM {$ectable} WHERE `member_platform`='%s' AND `member_id`=%d", APP_PLATFORM, $uid);
	    $uinfo = array_merge($uinfo, $uinfo_ec);
	  }
	  return $uinfo;
	}
	
	/**
	 * 通过$openid获取最小用户信息
	 * @param string $openid
	 * @param string $from 用户来源
	 * @return multitype:
	 */
	public static function getTinyInfoByOpenid($openid, $from = 'weixin')
	{
	  return D()->get_one("SELECT ".self::tinyFields()." FROM `{member}` WHERE `openid`='%s' AND `from`='%s'", $openid, $from);
	}
	
	/**
	 * 创建一个新用户
	 *
	 * @param array $data
	 * @param string $from 用户来源
	 * @return boolean|number
	 */
	public static function createUser(Array $data, $from = 'weixin')
	{
	  if (empty($data)) return FALSE;
	  
	  $now  = simphp_time();
	  $salt = gen_salt();
	  $data = array_merge($data,['regip'=>Request::ip(), 'regtime'=>$now, 'posttime'=>$now, 'salt'=>$salt, 'state'=>1, 'from'=>$from]);
	  $uid  = D()->insert('member', $data);
	  if($uid>0){
	    
	    if (empty($data['username'])) {
	      $data['username'] = $uid;
	      D()->update('member' , ['username'=>$uid] , ['uid'=>$uid]);
	    }
	    
	    //~ 插入ecshop数据表users
	    $ecdata  = [];
	    $ecdata['member_platform'] = APP_PLATFORM;
	    $ecdata['member_id']       = $uid;
	    $ecdata['user_name']       = $data['username'] . '@' . $from;
	    if (isset($data['nickname'])) {
	      $ecdata['nick_name'] = $data['nickname'];
	    }
	    if (isset($data['email'])) {
	      $ecdata['email'] = $data['email'];
	    }
	    if (isset($data['password'])) {
	      $ecdata['password'] = $data['password'];
	    }
	    if (isset($data['sex'])) {
	      $ecdata['sex'] = $data['sex'];
	    }
	    if (isset($data['city']) || isset($data['province']) || isset($data['country'])) {
	      $ecdata['address_id'] = self::getECRegionId($data['city'],$data['province'],$data['country']);
	    }
	    $ecdata['reg_time'] = simphp_time();
	    $ecdata['ec_salt']  = $salt;
	    if (!empty($ecdata)) {
	      D()->insert(ectable('users'), $ecdata, 1, TRUE);
	    }
	    
	    return $uid;
	  }
	  return FALSE;
	}
	
	/**
	 * 通过openid(或uid,当openid是整形数据时)更新用户信息
	 *
	 * @param array $data
	 * @param string $openid
	 * @param string $from
	 * @return boolean|number
	 */
	public static function updateUser(Array $data, $openid = '', $from = 'weixin')
	{
	  if (empty($data)) return FALSE;
	  $data = array_merge($data,['posttime'=>simphp_time()]);
	  
	  $where= [];
	  $uid  = 0;
	  if (is_numeric($openid) && $openid>0) {
	    $where = ['uid'=>$openid];
	    $uid   = $openid;
	  }
	  else {
	    $where = ['openid'=>$openid, 'from'=>$from];
	    $uid   = D()->result("SELECT `uid` FROM {member} WHERE `openid`='%s' AND `from`='%s'", $openid,$from);
	  }
	
	  $effcnt=0;
	  if (!empty($where)) {
	    $effcnt = D()->update('member', $data, $where);
	    
	    //~ 更新ecshop数据表users
	    $ecdata  = [];
	    $ecwhere = ['member_platform' => APP_PLATFORM, 'member_id' => $uid];
	    if (isset($data['nickname'])) {
	      $ecdata['nick_name'] = $data['nickname'];
	    }
	    if (isset($data['sex'])) {
	      $ecdata['sex'] = $data['sex'];
	    }
	    if (isset($data['city']) || isset($data['province']) || isset($data['country'])) {
	      $ecdata['address_id'] = self::getECRegionId($data['city'],$data['province'],$data['country']);
	    }
	    if (!empty($ecdata)) {
	      $ecdata['last_time'] = simphp_dtime();
	    }
	    if (!empty($ecdata)) {
	      D()->update(ectable('users'), $ecdata, $ecwhere, TRUE);
	    }
	    
	  }
	  return $effcnt ? $effcnt : FALSE;
	}
	
	/**
	 * 获取ecshop数据表的region id
	 * @param string $city
	 * @param string $province
	 * @param string $country
	 * @return number
	 */
	public static function getECRegionId($city, $province = '', $country = '') {
	  $theid = 0;
	  
	  if (!empty($city)) {
	    $ectb= ectable('region');
	    $sql = "SELECT `region_id` FROM {$ectb} WHERE `region_name`='%s' AND ";
	    $row = D()->raw_query($sql."`region_type`=2",$city)->get_one();
	    if (empty($row)) {
	      $row = D()->raw_query($sql."`region_type`=1",$province)->get_one();
	      if (empty($row)) {
	        $row = D()->raw_query($sql."`region_type`=0",$country)->get_one();
	        if (!empty($row)) {
	          $theid = $row['region_id'];
	        }
	      }
	      else {
	        $theid = $row['region_id'];
	      }
	    }
	    else {
	      $theid = $row['region_id'];
	    }
	  }
	  
	  return $theid;
	}
	
	/**
	 * 设置本地登录信息
	 * 
	 * @param integer $uid
	 */
	public static function setLocalLogin($uid)
	{
	  if (!$uid) return;
	  
	  global $user;
	  
	  //设置登录session uid
	  $user->uid = $uid;
	  
	  //更新登录记录
	  self::updateUser(['lastip'=>Request::ip(), 'lasttime'=>simphp_time()], $uid);
	  
	  //更新ecshop数据表users
	  $ecdata  = ['last_login' => simphp_time(), 'last_ip' => Request::ip()];
	  $ecwhere = ['member_platform' => APP_PLATFORM, 'member_id' => $uid];
	  D()->update(ectable('users'), $ecdata, $ecwhere, TRUE);

	}
	
}
?>