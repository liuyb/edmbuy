<?php
/**
 * Users公用Model
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Users extends StorageNode {
	
	/**
	 * User level constant
	 * @var constant
	 */
	const USER_LEVEL_0 = 0; //米客
	const USER_LEVEL_1 = 1; //米商
	const USER_LEVEL_2 = 2; //合伙人
	
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
						'wxqr'        => 'wxqr',
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
						'level'       => 'level',
						'state'       => 'state',
						'from'        => 'from',
						'authmethod'  => 'auth_method',
				));
	}
	
	/**
	 * Set the current user to 'logined' status
	 */
	public function set_logined_status() {
	
		//设置登录session uid
		$GLOBALS['user']->uid = $this->id;
	
		//新起一个对象来编辑，避免过多更新
		$nUser = new self($this->id);
		$nUser->lastlogin = simphp_time();
		$nUser->lastip    = Request::ip();
		$nUser->save();
	}
	
	/**
	 * Get user cart num
	 * @return integer
	 */
	public function cart_num() {
		if (!isset($this->cart_num)) {
			$this->cart_num = Cart::getUserCartNum($this->uid);
		}
		return $this->cart_num;
	}
	
	/**
	 * 检查必要用户信息是否为空
	 * @return boolean
	 */
	public function required_uinfo_empty() {
		return empty($this->nickname) || empty($this->logo) ? true : false;
	}
	
	/**
	 * Load user by UnionID
	 * 
	 * @param string $unionid
	 * @param string $from
	 * @return Users
	 */
	static function load_by_unionid($unionid, $from = 'weixin') {
		return self::find_one(new Query('unionid', $unionid));
	}
	
	/**
	 * Load user by OpenID
	 * @param unknown $openid
	 * @param string $from
	 * @return Users
	 */
	static function load_by_openid($openid, $from = 'weixin') {
		return self::find_one(new Query('openid', $openid));
	}
	
	/**
	 * Get parent union id by parent_id
	 * @param integer $uid
	 * @return string
	 */
	static function get_unionid($uid) {
		if (empty($uid)) return '';
		$u = self::find_one(new Query('uid', $uid));
		return $u->is_exist() ? $u->unionid : '';
	}
	
	/**
	 * Get user id by union id
	 * @param string $unionid
	 * @return integer
	 */
	static function get_userid($unionid) {
		if (empty($unionid)) return 0;
		$u = self::find_one(new Query('unionid', $unionid));
		return $u->is_exist() ? $u->uid : 0;
	}
	
	/**
	 * Check whether user logined
	 * @return boolean
	 */
	static function is_logined() {
		return $GLOBALS['user']->uid ? true : false;
	}
	
	/**
	 * Check user info complete degreee, or else redirect to weixin detail oauth
	 * @param string  $refer redirect url
	 */
	static function check_detail_info($refer='') {
		if ($GLOBALS['user']->uid && $GLOBALS['user']->required_uinfo_empty()) { //必要信息为空，则请求OAuth2详细认证
			if ( !isset($_SESSION['wxoauth_reqcnt']) ) $_SESSION['wxoauth_reqcnt'] = 0;
			$_SESSION['wxoauth_reqcnt']++;
			if ($_SESSION['wxoauth_reqcnt'] < 4) { //最多尝试3次，避免死循环
				if (!$refer) $refer = Request::url();
				(new Weixin())->authorizing('http://'.Request::host().'/user/oauth/weixin?act=&refer='.rawurlencode($refer), 'detail');
			}
		}
		return true;
	}
	
	/**
	 * Object version of static method 'wx_qrcode'
	 * @param integer $scene_type Weixin::QR_SCENE 或 Weixin::QR_LIMIT_SCENE
	 * @param boolean $return_text 如果$return_text为真，则返回二维码内容，否则返回二维码图片地址
	 * @return string
	 *   返回二维码图片地址或二维码内容
	 */
	public function wx_qr($scene_type = Weixin::QR_SCENE, $return_text = false) {
		return self::qrcode($this->uid,$scene_type,$return_text);
	}
	
	/**
	 * Get user qrcode
	 * 算法：
	 *  1、无论临时二维码还是永久二维码，一个用户仅对应一个“有效的” 
	 *  2、如果永久二维码数已经达到50000(此参数可调)，则无论$scene_type输入什么，都只生产临时二维码(微信对永久二维码有10w个的限制)
	 * @param integer $scene_type Weixin::QR_SCENE 或 Weixin::QR_LIMIT_SCENE
	 * @param boolean $return_text 如果$return_text为真，则返回二维码内容，否则返回二维码图片地址
	 * @return string
	 *   返回二维码图片地址或二维码内容
	 */
	static function wx_qrcode($user_id, $scene_type = Weixin::QR_SCENE, $return_text = false) {
		if (!in_array($scene_type, [Weixin::QR_SCENE, Weixin::QR_LIMIT_SCENE])) {
			$scene_type = Weixin::QR_LIMIT_SCENE;//有可能输入了 Weixin::QR_LIMIT_STR_SCENE
		}
		if ($scene_type==Weixin::QR_LIMIT_SCENE) {
			$persistent_nums = Wxqrcode::total_count(new Query('scene_type', Weixin::QR_SCENE, '<>'));
			if ($persistent_nums > (Weixin::QR_MAX_PERSISTENT*3/4)) { //TODO 此值可根据情况适当调整，但不能超过 Weixin::QR_MAX_PERSISTENT
				$scene_type = Weixin::QR_SCENE; //超过阀值，则只生成临时二维码
			}
		}
		
		$now = simphp_time();
		$where_extra = '';
		if ($scene_type==Weixin::QR_SCENE) {
			$where_extra = "AND wq.created > {$now}-wq.expire_seconds";
		}
		$sql = "SELECT uq.user_id, wq.*
		        FROM `shp_user_qrcode` AS uq INNER JOIN `{weixin_qrcode}` AS wq ON uq.scene_id=wq.scene_id
				    WHERE uq.user_id=%d AND wq.scene_type=%d {$where_extra}";
		$row = D()->query($sql, $user_id, $scene_type)->get_one();
		if (empty($row)) { //不存在则要创建
			$wxqr = new Wxqrcode();
			$wxqr->scene_type = $scene_type;
			$wxqr->created    = $now;
			$wxqr->user_id    = $user_id; //用于 UserQrcode 对象保存
			$wxqr->save(Storage::SAVE_INSERT);
			if ($wxqr->id) {
				$wx = new Weixin([Weixin::PLUGIN_QRCODE]);
				$wx->qrcode->getQRCode($wxqr->id, $scene_type);
				$wxqr = Wxqrcode::load($wxqr->id,true);
				if ($wxqr->is_exist()) {
					$row  = ['url'=>$wxqr->url,'img'=>$wxqr->img];
				}
			}
		}
		
		return $return_text ? $row['url'] : $row['img'];
	}
	
}
 
/*----- END FILE: class.Users.php -----*/