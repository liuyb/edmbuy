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
	
	static $level_amount = [
			0 => 0,             //米客消费金额
			1 => 98,            //米商消费金额
			2 => 100000000,     //合伙人消费金额
	];
	
	static $allowed_leader_level = [1,2,3]; //运行的上级级别
	
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
						'wxqrimg'     => 'wxqr_img',
						'wxqrpromote' => 'wxqr_promote',
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
						'parentnick'  => 'parent_nick',
						'parentunionid' => 'parent_unionid',
						'appuserid'   => 'app_userid',
						'businessid'  => 'business_id',
						'businesstime'=> 'business_time',
						'childnum1'   => 'childnum_1',
						'childnum2'   => 'childnum_2',
						'childnum3'   => 'childnum_3',
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
						'synctimes'   => 'synctimes',
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
		return empty($this->nickname) && empty($this->logo) ? true : false;
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
	 * Load user by mobile
	 * @param string $mobile
	 * @param string $from
	 * @return Users
	 */
	static function load_by_mobile($mobile) {
		return self::find_one(new Query('mobilephone', $mobile));
	}
	
	/**
	 * Load user by app_userid
	 * @param string $appuid
	 * @return Users
	 */
	static function load_by_appuid($appuid) {
		return self::find_one(new Query('appuserid', $appuid));
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
	 * Get user qrcode
	 * 算法：
	 *  1、无论临时二维码还是永久二维码，一个用户仅对应一个“有效的” 
	 *  2、如果永久二维码数已经达到允许值的3/4(此参数可调)，则无论$scene_type输入什么，都只产生有最大时效的临时二维码(微信对永久二维码有10w个的限制)
	 * @param integer $scene_type  Weixin::QR_SCENE 或 Weixin::QR_LIMIT_SCENE
	 * @param boolean $return_text 如果$return_text为真，则返回二维码内容，否则返回二维码图片地址
	 * @param integer $scene_id    用于返回记录id
	 * @return string
	 *   返回二维码图片地址或二维码内容
	 */
	static function wx_qrcode($user_id, $scene_type = Weixin::QR_SCENE, $return_text = false, &$scene_id = 0) {
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
		$sql = "SELECT wq.*
		        FROM `{weixin_qrcode}` AS wq
				    WHERE wq.user_id=%d AND wq.scene_type=%d {$where_extra}";
		$row = D()->query($sql, $user_id, $scene_type)->get_one();
		if (empty($row)) { //不存在则要创建
			$wxqr = new Wxqrcode();
			$wxqr->user_id    = $user_id;
			$wxqr->scene_type = $scene_type;
			$wxqr->created    = $now;
			$wxqr->save(Storage::SAVE_INSERT);
			if ($wxqr->id) {
				$scene_id = $wxqr->id;
				$wx = new Weixin([Weixin::PLUGIN_QRCODE]);
				$wx->qrcode->getQRCode($wxqr->id, $scene_type);
				$wxqr = Wxqrcode::load($wxqr->id, true);
				if ($wxqr->is_exist()) {
					$row  = ['url'=>$wxqr->url,'img'=>$wxqr->img];
				}
			}
		}
		else {
			$scene_id = $row['scene_id'];
		}
		
		return !empty($row) ? ($return_text ? $row['url'] : $row['img']) : '';
	}
	
	/**
	 * 将个人logo添加到个人二维码上去
	 * @param string   $local_path
	 * @return string
	 */
	public function add_logo_to_qrcode($local_path) {
		if (empty($this->logo)) {
			return $local_path;
		}
		$source_file = SIMPHP_ROOT . $local_path;
		$target_file = preg_replace('/\.jpg$/i', '', $source_file).'_wl'.File::ext($source_file);
		
		$ulogo = $this->logo;
		if (preg_match('/^http(s?):\/\//i', $ulogo)) {
			$ulogo = File::get_remote($ulogo);
		}
		if (Media::is_app_file($ulogo)) {
			$ulogo = SIMPHP_ROOT . $ulogo;
		}
		if (empty($ulogo)) {
			return $local_path;
		}
		$ulogo = realpath($ulogo);
		$ret = File::add_watermark($source_file, $target_file, $ulogo, ['pos'=>'center','w'=>90,'h'=>90], 100,'#FFFFFF');
		return $ret;
	}
	
	/**
	 * 返回用户的微信推广二维码
	 * @return string
	 */
	public function wx_qrimg() {
		if (!empty($this->wxqrimg)) { //存在就直接返回
			return $this->wxqrimg;
		}
		$qr = self::wx_qrcode($this->uid, Weixin::QR_LIMIT_SCENE, false, $scene_id);
		if (preg_match('/^'.preg_quote('https://mp.weixin.qq.com','/').'/', $qr))
		{ //表示还是使用微信的二维码地址，则要保存到本地，并且将个人logo添加上去
			
			$dir = File::gen_unique_dir('id', $this->uid, '/a/wx/qrimg/');
			$localpath = $dir . $this->uid . '.jpg'; //微信二维码返回的是jpg格式
			$localpath = File::get_remote($qr, $localpath);
			if ($localpath) { //成功创建到本地，则在此基础上添加个人logo
				$localpath = $this->add_logo_to_qrcode($localpath);
				if ($localpath) { //成功添加个人logo到二维码，则保存
					//保存用户表
					$upUser = new self($this->uid);
					$upUser->wxqrimg = $localpath;
					$upUser->save(Storage::SAVE_UPDATE);
					return $localpath;
				}
			}
			
		}
		return $qr;
	}
	
	/**
	 * 返回用户带微信二维码的推广图片地址
	 * @return string
	 */
	public function wx_qrpromote() {
		if (!empty($this->wxqrpromote)) { //存在就直接返回
			return Media::path($this->wxqrpromote, true);
		}
		
		$sourcefile     = SIMPHP_ROOT . '/misc/images/wx/promote_base.jpg';
		$targetdir      = SIMPHP_ROOT . File::gen_unique_dir('id', $this->uid, '/a/wx/promote/');
		$targetfile     = $targetdir . $this->uid . '.jpg';
		$qrimg_default  = SIMPHP_ROOT . '/misc/images/wx/qrcode_430.jpg';
		$qrimg          = SIMPHP_ROOT . $this->wx_qrimg();
		if (!file_exists($qrimg)) {
			$qrimg = $qrimg_default;
		}
		if (!is_dir($targetdir)) {
			mkdirs($targetdir);
		}
		
		$ulogo   = $this->logo;
		if (preg_match('/^http(s?):\/\//i', $ulogo)) {
			$ulogo = File::get_remote($ulogo);
		}
		if (Media::is_app_file($ulogo)) {
			$ulogo = SIMPHP_ROOT . $ulogo;
		}
		if (empty($ulogo)) {
			$ulogo  = SIMPHP_ROOT . '/misc/images/wx/edm_logo_r.png';
		}
		$ulogo = realpath($ulogo);

		$unick = ($this->nickname ? $this->nickname : '无名氏').'('.$this->uid.')';
		$txtinfo = array(['text'=>$unick,'x'=>120,'y'=>43,'color'=>'#556993','fontsize'=>20],
				             ['text'=>'长按图片识别二维码','x'=>115,'y'=>850,'color'=>'#000000','fontsize'=>14]);
		
		// 添加二维码
		$ret = File::add_watermark($sourcefile, $targetfile, $qrimg, ['x'=>95,'y'=>480,'w'=>344,'h'=>344], 100);
		// 添加个人头像
		$ret = File::add_watermark($targetfile, '', $ulogo, ['x'=>20,'y'=>25,'w'=>75,'h'=>75], 100, '#FFFFFF');
		// 添加文字
		$ret = File::add_text($targetfile, '', $txtinfo);
		if ($ret) { //创建水印成功
			$upUser = new Users($this->uid);
			$upUser->wxqrpromote = $ret;
			$upUser->save(Storage::SAVE_UPDATE);
			return Media::path($ret, true);
		}
		return '';
	}
	
	/**
	 * 更新同步次数
	 * 支持inc是整形、'+N','-N'的方式
	 */
	public function update_synctimes($inc = 1) {
		if (is_string($inc) && ($inc{0}=='+'||$inc{0}=='-')) {
			$setpart = "synctimes".$inc;
		}
		else {
			$setpart = intval($inc);
		}
		D()->query("UPDATE ".self::table(). " SET synctimes={$setpart} WHERE user_id=%d", $this->uid);
		return true;
	}

	/**
	 * 获取用户收货地址列表
	 *
	 * @param integer $user_id
	 * @return array
	 */
	static function getAddress($user_id) {
		$ectb = UserAddress::table();
		$sql  = "SELECT * FROM {$ectb} WHERE `user_id`=%d ORDER BY `address_id` DESC";
		$ret  = D()->raw_query($sql,$user_id)->fetch_array_all();
		if (!empty($ret)) {
			foreach ($ret AS &$addr) {
				$contact_phone = !empty($addr['tel']) ? $addr['tel'] : $addr['mobile']; //遵循ecshop习惯，优先选择tel作为联系电话
	
				//填充地区名称
				if (empty($addr['country_name']) && !empty($addr['country'])) {
					$addr['country_name'] = Region::getName($addr['country']);
				}
				if (empty($addr['province_name']) && !empty($addr['province'])) {
					$addr['province_name'] = Region::getName($addr['province']);
					$addr['province_name'].= '省';
				}
				if (empty($addr['city_name']) && !empty($addr['city'])) {
					$addr['city_name'] = Region::getName($addr['city']);
					if (!preg_match('/(市$)/u', $addr['city_name'])) {
						$addr['city_name'].= '市';
					}
				}
				if (empty($addr['district_name']) && !empty($addr['district'])) {
					$addr['district_name'] = Region::getName($addr['district']);
					if (!preg_match('/(区$)/u', $addr['district_name'])) {
						$addr['district_name'].= '区';
					}
				}
	
				//添加额外属性，便于前端显示
				$addr['contact_phone']  = $contact_phone;
				$addr['show_consignee'] = $addr['consignee']."（{$contact_phone}）";
				$addr['show_address']   = $addr['province_name'].$addr['city_name'].$addr['district_name'].$addr['address'];
			}
		}
		return empty($ret) ? [] : $ret;
	}
	
	/**
	 * 获取上级User ID
	 * @param integer $user_id
	 * @return integer
	 */
	static function getParentId($user_id) {
		$parent_uid = D()->from(self::table())->where("user_id=%d",$user_id)->select("parent_id")->result();
		return $parent_uid;
	}
	
	/**
	 * 获取用户nickname
	 * @param integer $user_id
	 * @return mixed
	 */
	static function getNick($user_id) {
		$nick = D()->from(self::table())->where("user_id=%d",$user_id)->select("nick_name")->result();
		return $nick;
	}
	
	/**
	 * 查询当前用户总购买金额
	 * @return double
	 */
	public function total_paid() {
		$total = D()->query("SELECT SUM( money_paid ) AS totalpaid FROM ".Order::table()." WHERE user_id =%d AND `pay_status`=%d", $this->id, PS_PAYED)->result();
		return $total;
	}
	
	/**
	 * 检查米商、米客
	 * @return number
	 */
	public function check_level() {
		if ($this->level > 0) return $this->level;
		$total_paid = $this->total_paid();
		if ($total_paid >= self::$level_amount[self::USER_LEVEL_1]) { //成为米商
			$upUser = new self($this->id);
			$upUser->level = self::USER_LEVEL_1;
			$upUser->save(Storage::SAVE_UPDATE);
		}
		return 0;
	}
	
	/**
	 * 获取用户最新一个订单
	 * @return Order
	 */
	public function latest_order() {
		$order = Order::find_one(new AndQuery(new Query('user_id', $this->id),new Query('pay_status', PS_PAYED)),
				                    ['from'=>0,'size'=>1,'sort'=>['order_id'=>'DESC']]);
		return $order;
	}
	
}
 
/*----- END FILE: class.Users.php -----*/