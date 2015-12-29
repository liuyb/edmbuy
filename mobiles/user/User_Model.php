<?php
/**
 * Node Model
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class User_Model extends Model {
	
	static function checkAccessToken($token, $idfield = 'openid'){
		$record = D()->get_one("SELECT * FROM {access_token} WHERE token='%s'", $token);
		if(empty($record)){
			return FALSE;
		}
		else{
			$now = simphp_time();
			if($now > $record['lifetime']){
				return FALSE;
			}
			else{
				return $record[$idfield];
			}
		}
	}
	
	/**
	 * 检查用户信息完成度，nickname或logo没有的话都重定向请求OAuth2详细认证获取资料
	 * @param array $uinfo
	 * @return boolean
	 */
	static function checkUserInfoCompleteDegree($uinfo, $refer = '/') {
	  if (empty($uinfo['nickname']) || empty($uinfo['logo'])) { //只要两个其中一个为空，都请求OAuth2详细认证
	    if ( !isset($_SESSION['wxoauth_reqcnt']) ) $_SESSION['wxoauth_reqcnt'] = 0;
	    $_SESSION['wxoauth_reqcnt']++;
	    if ($_SESSION['wxoauth_reqcnt'] < 4) { //最多尝试2次，避免死循环
	      (new Weixin())->authorizing('http://'.Request::host().'/user/oauth/weixin?act=&refer='.$refer, 'detail');
	    }
	  }
	  return true;
	}
	
}