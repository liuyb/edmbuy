<?php
/**
 * Users公用Model
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Users extends StorageNode {
	
	protected static function meta() {
		return array(
				'table' => '`shp_users`',
				'key'   => 'uid',   //该key是应用逻辑的列，当columns为array时，为columns的key，否则，则要设成实际存储字段
				'columns' => array( //columns同时支持'*','实际存储字段列表串',映射数组 三种方式
						'uid'         => 'user_id',
						'unionid'     => 'unionid',
						'openid'      => 'openid',
						'subscribe'   => 'subscribe',
						'subscribetime'=> 'subscribe_time',
						'email'       => 'email',
						'username'    => 'user_name',
						'nickname'    => 'nick_name',
						'password'    => 'password',
						'logo'        => 'logo',
						'question'    => 'question',
						'answer'      => 'answer',
						'sex'         => 'sex',
						'lang'        => 'lang',
						'country'     => 'country',
						'province'    => 'province',
						'city'        => 'city',
						'longitude'   => 'longitude',
						'latitude'    => 'latitude',
						'precision'   => 'precision',
						'birthday'    => 'birthday',
						'usermoney'   => 'user_money',
						'frozenmoney' => 'frozen_money',
						'paypoints'   => 'pay_points',
						'rankpoints'  => 'rank_points',
						'addressid'   => 'address_id',
						'regip'       => 'reg_ip',
						'regtime'     => 'reg_time',
						'lastlogin'   => 'last_login',
						'lasttime'    => 'last_time',
						'lastip'      => 'last_ip',
						'visitcount'  => 'visit_count',
						'userrank'    => 'user_rank',
						'isspecial'   => 'is_special',
						'ecsalt'      => 'ec_salt',
						'salt'        => 'salt',
						'parentid'    => 'parent_id',
						'flag'        => 'flag',
						'alias'       => 'alias',
						'msn'         => 'msn',
						'qq'          => 'qq',
						'officephone' => 'office_phone',
						'homephone'   => 'home_phone',
						'mobilephone' => 'mobile_phone',
						'isvalidated' => 'is_validated',
						'creditline'  => 'credit_line',
						'passwdquestion'=> 'passwd_question',
						'passwdanswer'=> 'passwd_answer',
						'state'       => 'state',
						'from'        => 'from',
						'authmethod'  => 'auth_method',
				));
	}
	
	/**
	 * Load user by UnionID
	 * 
	 * @param string $unionid
	 * @param string $from
	 * @return Users
	 */
	static function load_by_unionid($unionid, $from = 'weixin') {
		$meta   = self::meta();
		$prikey = is_array($meta['columns']) ? $meta['columns'][$meta['key']] : $meta['key'];
		$user_id = D()->from($meta['table'])->where("`unionid`='%s'",$unionid)->select($prikey)->result();
		return self::load($user_id);
	}
	
	/**
	 * Get parent union id by parent_id
	 * @param integer $user_id
	 * @return string
	 */
	static function get_unionid($user_id) {
		if (empty($user_id)) return '';
		$meta = self::meta();
		$id   = D()->from($meta['table'])->where("`user_id`=%d",$user_id)->select("unionid")->result();
		return $id ? $id : '';
	}
	
	static function get_userid($unionid) {
		if (empty($unionid)) return 0;
		$meta = self::meta();
		$id   = D()->from($meta['table'])->where("`unionid`='%s'",$unionid)->select("user_id")->result();
		return $id ? $id : 0;
	}
	
	/**
	 * Set the current user to 'logined' status
	 */
	public function set_logined_status() {
		
		//设置登录session uid
		$GLOBALS['user']->uid = $this->id;
		
		//新起一个对象来编辑，避免过多更新
		$nUser = new Users($this->id);
		$nUser->lastlogin = simphp_time();
		$nUser->lastip    = Request::ip();
		$nUser->save();
	}
	
}
 
/*----- END FILE: class.Users.php -----*/