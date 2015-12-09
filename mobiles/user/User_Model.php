<?php
/**
 * Node Model
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class User_Model extends Model {

	static function saveFeedback($data){
	  $user_id = $GLOBALS['user']->ec_user_id;
	  if ($user_id) { //对于登录用户，获取用户信息
	    $uinfo = D()->from(ectable('users'))->where(['user_id'=>$user_id])
	                ->select('user_id,email,user_name,nick_name')->get_one();
	    if (!empty($uinfo)) {
	      $uinfo_append = [
	        'user_id'   => $uinfo['user_id'],
	        'user_name' => $uinfo['user_name'],
	        'nick_name' => $uinfo['nick_name']
	      ];
	      if (''==$data['user_email'] && ''!=$uinfo['email']) {
	        $data['user_email'] = $uinfo['email'];
	      }
	      $data = array_merge($data, $uinfo_append);
	    }
	  }
	  
		$fid = D()->insert(ectable('feedback'), $data, true, true);
		return $fid;
	}
	
	static function checkAccessToken($token){
		$record = D()->get_one("SELECT * FROM {access_token} WHERE token='%s'", $token);
		if(empty($record)){
			return FALSE;
		}
		else{
			$timestamp = time();
			if($timestamp>$record['lifetime']){
				return FALSE;
			}
			else{
				//D()->update('access_token', ['lifetime'=>0], ['openid'=>$record['openid']]);
				return $record['openid'];
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