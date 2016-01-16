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
	
	static function findUserInfoById($uid){
	    $user = Users::find_one(new Query('uid', $uid));
	    return  $user;
	}
	
	static function updateUserInfo(array $args){
	    if(!isset($args) || count($args) == 0){
	        return;
	    }
	    $uid = $GLOBALS['user']->uid;
	    $user = new Users($uid);
	    foreach ($args as $key => $val){
	        $user->$key = $val;
	    }
	    $user->save();
	}
	
	static function displayUserRole($level){
	    switch ($level){
	        case Users::USER_LEVEL_0 == $level : 
	           return "米客";
	           break;
	        case Users::USER_LEVEL_1 == $level :
	            return "米商";
	            break;
	        case Users::USER_LEVEL_2 == $level :
                return "合伙人";
                break;
	    }
	}
	
	/**
	 * 根据传入的订单状态列，统计该状态列数量
	    $sql = "select t1.c as status1,t2.c status2,t3.c status3 from
            (SELECT count(1) c FROM edmbuy.shp_order_info where user_id=%d and pay_status = 0) t1,
            (SELECT count(1) c FROM edmbuy.shp_order_info where user_id=%d and shipping_status = 0) t2,
            (SELECT count(1) c FROM edmbuy.shp_order_info where user_id=%d and shipping_status = 1) t3";
	 * @param unknown $uid
	 * @param array $status 传入需要统计的订单状态列
	 */
	static function findOrderStatusCountByUser($uid, array $status){
	    $sql = '';
	    $field = '';
	    $condition = '';
	    $i = 0;
	    foreach ($status as $statu=>$val){
	        ++$i;
	        if (is_array($val)){
	            foreach ($val as $item){
	                $field.="t$i.c as status$i,";
	                $condition.="(SELECT count(1) c FROM edmbuy.shp_order_info where user_id=$uid and $statu = $item) t$i ,";
	                ++$i;
	            }
	        }else{
    	        $field.="t$i.c as status$i ,";
    	        $condition.="(SELECT count(1) c FROM edmbuy.shp_order_info where user_id=$uid and $statu = $val) t$i ,";
	        }
	    }
	    $field = rtrim($field, ',');
	    $condition = rtrim($condition, ',');
	    $sql = "select $field from $condition";
        $rows = D()->query($sql)->fetch_array_all();
        if(is_array($rows) && count($rows) > 0){
            return $rows[0];
        }
        return array();
	}
}