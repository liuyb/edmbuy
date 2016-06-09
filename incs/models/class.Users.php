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
	 * 规则：数值越大，权限越大
	 * @var constant
	 */
	const USER_LEVEL_0 = 0; //米客
	const USER_LEVEL_1 = 1; //米商
	const USER_LEVEL_2 = 2; //(备用，如铜牌代理)
	const USER_LEVEL_3 = 3; //银牌代理
	const USER_LEVEL_4 = 4; //金牌代理
	const USER_LEVEL_5 = 5; //入驻商家
	const USER_LEVEL_6 = 6; //分站商家
	const USER_LEVEL_10= 10;//合伙人
	
	/**
	 * 会员登录状态session key
	 * @var constant
	 */
	const AC_LOGINED_KEY = 'AC_LOGINED';
	const AC_WXAUTH_KEY  = 'AC_WXAUTH';
	
	static $level_amount = [
			0 => 0,             //米客消费金额
			1 => 98,            //米商消费金额
			10=> 100000000,     //合伙人消费金额
	];
	
	static $allowed_leader_level = [1,2,3]; //允许的上级级别
	
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
						'mobile'      => 'mobile',
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
						'appuserid'   => 'app_userid',
						'businessid'  => 'business_id',
						'businesstime'=> 'business_time',
						'parentid0'   => 'parent_id0',
						'parentid2'   => 'parent_id2',
						'parentid3'   => 'parent_id3',
						'childnum1'   => 'childnum_1',
						'childnum2'   => 'childnum_2',
						'childnum3'   => 'childnum_3',
						'guid'        => 'guid',
						'parent_guid' => 'parent_guid',
						'from_sync'   => 'from_sync',
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
						'randver'     => 'randver',
						'synctimes'   => 'synctimes'
				));
	}
	
	/**
	 * Set the current user to 'logined' status
	 */
	public function set_logined_status() {
	
		//设置登录session uid
		$GLOBALS['user']->uid = $this->id;

		$_SESSION[self::AC_LOGINED_KEY] = simphp_time();
		
		//重新变更session id
		SimPHP::$session->regenerate_id();
		
		//更新登录信息
		D()->query("UPDATE ".self::table()." SET last_login=%d,last_ip='%s',visit_count=visit_count+1 WHERE user_id=%d",
		          simphp_time(), Request::ip(), $this->id);
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
	 * Load user by mobile
	 * @param string $mobile
	 * @param string $from
	 * @return Users
	 */
	static function load_by_mobile($mobile) {
		return self::find_one(new Query('mobile', $mobile));
	}

	/**
	 * Load user by user
	 * @param int $guid
	 * @return Users
	 */
	static function load_by_guid($guid) {
		return self::find_one(new Query('guid', $guid));
	}
	
	/**
	 * Load user by nick name
	 * @param string $nick
	 * @return StorageNode
	 */
	static function load_by_nickname($nick) {
		return self::find_one(new Query('nickname', $nick));
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
	 * Find eqx user object
	 * @param integer $guid
	 * @param string $mobile
	 * @return Users
	 */
	static function find_eqx_user($guid, $mobile) {
		$cUser = self::load_by_guid($guid);
		if (!$cUser->is_exist()) {
			$cUser = self::load_by_mobile($mobile);
		}
		return $cUser;
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
	 * Check whether user account logined
	 * @return boolean
	 */
	static function is_account_logined() {
		return isset($_SESSION[self::AC_LOGINED_KEY]) && $_SESSION[self::AC_LOGINED_KEY] ? TRUE : FALSE;
	}
	
	/**
	 * 设置账号登录状态
	 */
	static function set_account_logined() {
		$_SESSION[self::AC_LOGINED_KEY] = simphp_time();
	}
	
	/**
	 * 检查是否微信授权登录
	 * @return boolean
	 */
	static function is_weixin_auth() {
		return isset($_SESSION[self::AC_WXAUTH_KEY]) && strlen($_SESSION[self::AC_WXAUTH_KEY])>20 ? TRUE : FALSE;
	}
	
	/**
	 * 设置微信授权openid
	 * @param string $openid
	 */
	static function set_weixin_auth($openid) {
		$_SESSION[self::AC_WXAUTH_KEY] = $openid;
	}
	
	/**
	 * 请求帐号登录
	 */
	static function required_account_logined() {
		if (!self::is_account_logined()) {
			//Response::redirect('user/login_account');
			Response::redirect('eqx/reg');
		}
		return true;
	}
	
	/**
	 * 登录帐号
	 * @param string $mobile
	 * @param string $passwd
	 * @return int
	 *   >0: LOGIN OK 
	 *   -1: 手机号非法
	 *   -2: 密码不能为空
	 *   -3: 手机帐号不存在
	 *   -4: 密码不对
	 *   -5: 当前手机号没有绑定该微信
	 */
	static function login_account($mobile, $passwd, $theuid = NULL) {
		if (empty($mobile) || !Fn::check_mobile($mobile)) {
			return -1;
		}
		if (empty($passwd)) {
			return -2;
		}
		$exUser = self::load_by_mobile($mobile);
		if (!$exUser->is_exist()) {
			return -3;
		}
		$passwd_enc  = gen_salt_password($passwd, $exUser->salt);
		if ($passwd_enc != $exUser->password) {
			return -4;
		}
		if (!isset($theuid)) {
			$theuid = $GLOBALS['user']->uid;
		}
		if ($theuid != $exUser->uid) {
			return -5;
		}
		
		//set status
		self::set_account_logined();
		return $exUser->uid;
	}
	
	/**
	 * 注册帐号
	 * @param string $mobile
	 * @param string $passwd
	 * @param integer $parent_uid
	 * @param integer $uid
	 * @return boolean|integer
	 */
	static function reg_account($mobile, $passwd, $parent_uid = 0, $uid = NULL) {
		if (!isset($uid)) {
			$uid = $GLOBALS['user']->uid;
		}
		if (!$uid) {
			return false;
		}
		$salt = gen_salt();
		$passwd_enc  = gen_salt_password($passwd, $salt);
		$cUser       = self::load($uid);
		$upUser      = new self($uid);
		$upUser->mobile   = $mobile;
		$upUser->password = $passwd_enc;
		$upUser->salt     = $salt;
		if (!$cUser->parentid) {
			$parents_info = self::get_parent_ids($parent_uid, true);
			$upUser->parentid   = $parents_info['parent_id'];
			$upUser->parentnick = $parents_info['parent_nick'];
			$upUser->parentid2  = $parents_info['parent_id2'];
			$upUser->parentid3  = $parents_info['parent_id3'];
		}
		$upUser->regip     = Request::ip();
		$upUser->regtime   = simphp_time();
		$upUser->from      = 'reg';
		$upUser->save(Storage::SAVE_UPDATE);
		return true;
	}
	
	/**
	 * 获取上三层用户ID
	 * @param integer $layer1_puid 同时支持传入puid和mobile查询
	 * @param boolean $strict_mode 严格模式，当为true时根据一级上级严格查询，当为false时直接读取puid,puid2,puid3的值
	 * @return array
	 */
	static function get_parent_ids($layer1_puid, $strict_mode = FALSE) {
		$ret = ['parent_id'=>0,'parent_nick'=>'','parent_id2'=>0, 'parent_id3'=>0];
		if ($layer1_puid) {
			if (11==strlen($layer1_puid)) {
				$puser = self::load_by_mobile($layer1_puid);
			}
			else {
				$puser = self::load($layer1_puid);
			}
			if ($puser->is_exist()) {
				$ret['parent_id']   = $puser->uid;
				$ret['parent_nick'] = $puser->nickname;
				if ($strict_mode) { //严格模式需要根据puid往上查
					if ($puser->parentid) {
						$ret['parent_id2'] = $puser->parentid;
						$puser2 = self::load($puser->parentid);
						if ($puser2->is_exist() && $puser2->parentid) {
							$ret['parent_id3'] = $puser2->parentid;
						}
					}
				}
				else {
					$ret['parent_id2'] = $puser->parentid;
					$ret['parent_id3'] = $puser->parentid2;
				}
			}
		}
		return $ret;
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
		
		$tmpfile = '';
		$ulogo = $this->logo;
		if (preg_match('/^http(s?):\/\//i', $ulogo)) {
			$ulogo = $tmpfile = File::get_remote($ulogo);
		}
		if (Media::is_app_file($ulogo)) {
			$ulogo = SIMPHP_ROOT . $ulogo;
			if (''!=$tmpfile) {
				$tmpfile = SIMPHP_ROOT . $tmpfile;
			}
		}
		if (empty($ulogo)) {
			return $local_path;
		}
		$ulogo = realpath($ulogo);
		$ret = File::add_watermark($source_file, $target_file, $ulogo, ['pos'=>'center','w'=>90,'h'=>90], 100,'#FFFFFF');
		// 删除可能存在的临时文件
		if (''!=$tmpfile) {
			@unlink($tmpfile);
		}
		return $ret;
	}
	
	/**
	 * 返回用户的微信推广二维码
	 * @param  $regen boolean 是否重新生成
	 * @return string
	 */
	public function wx_qrimg($regen = FALSE) {
		if ($regen) {
			$this->wxqrimg = '';
		}
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
	 * 
	 * @param  $regen boolean 是否重新生成
	 * @return string
	 */
	public function wx_qrpromote($regen = FALSE) {
		if ($regen) {
			$this->wxqrpromote = '';
		}
		if (!empty($this->wxqrpromote) && $_p=Media::path($this->wxqrpromote, true)) { //存在就直接返回
			return $_p;
		}
		
		$sourcefile     = SIMPHP_ROOT . '/misc/images/wx/promote_base.jpg';
		$targetdir      = SIMPHP_ROOT . File::gen_unique_dir('id', $this->uid, '/a/wx/promote/');
		$targetfile     = $targetdir . $this->uid . '.jpg';
		$qrimg_default  = SIMPHP_ROOT . '/misc/images/wx/qrcode_430.jpg';
		$qrimg          = SIMPHP_ROOT . $this->wx_qrimg($regen);
		if (!file_exists($qrimg)) {
			$qrimg = $qrimg_default;
		}
		if (!is_dir($targetdir)) {
			mkdirs($targetdir);
		}
		
		$tmpfile = '';
		$ulogo   = $this->logo;
		if (preg_match('/^http(s?):\/\//i', $ulogo)) {
			$ulogo = $tmpfile = File::get_remote($ulogo);
		}
		if (Media::is_app_file($ulogo)) {
			$ulogo = SIMPHP_ROOT . $ulogo;
			if (''!=$tmpfile) {
				$tmpfile = SIMPHP_ROOT . $tmpfile;
			}
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
		// 删除可能存在的临时文件
		if (''!=$tmpfile) {
			@unlink($tmpfile);
		}
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
	 * 重新生成randver
	 */
	public function regen_randver() {
		D()->query("UPDATE ".self::table(). " SET randver='%s' WHERE user_id=%d", rand(100000, 999999),$this->uid);
	}
	
	/**
	 * 返回当前用户在“本机”的推广二维码相对路径
	 * 
	 * @param $uid   integer  用户ID，不提供时用当前登录用户
	 * @param $ulogo string   用户logo地址，不提供时用当前登录用户
	 * @param $regen boolean  是否重新生成
	 * @return boolean
	 */
	static function my_tgqr($uid = NULL, $ulogo = '', $regen = FALSE) {
		if (!isset($uid)) {
			$uid   = $GLOBALS['user']->uid;
			$ulogo = $GLOBALS['user']->logo;
		}
		$dir = Fn::gen_qrcode_dir($uid, 'utg', true);
		$locfile = $dir . $uid . '.png';
		$locfile_jpg = $locfile. '.jpg';
		if ($regen) {
			if (file_exists($locfile_jpg)) {
				@unlink($locfile_jpg);
			}
			if (file_exists($locfile)) {
				@unlink($locfile);
			}
		}
		
		$qrcode = '';
		if (!file_exists($locfile_jpg)) {
			if (mkdirs($dir)) {
				$qrinfo = U('t/'.$uid,'',true);
				include_once SIMPHP_INCS . '/libs/phpqrcode/qrlib.php';
				QRcode::png($qrinfo, $locfile, QR_ECLEVEL_M, 15, 2);
				if (file_exists($locfile)) {
					File::make_thumb($locfile, 435, 435);
					$qrcode = str_replace(SIMPHP_ROOT, '', $locfile);
					$tmpfile= '';
					if (preg_match('/^http(s?):\/\//i', $ulogo)) {
						$ulogo = $tmpfile = File::get_remote($ulogo);
					}
					if (Media::is_app_file($ulogo)) {
						$ulogo = SIMPHP_ROOT . $ulogo;
						if (''!=$tmpfile) {
							$tmpfile = SIMPHP_ROOT . $tmpfile;
						}
					}
					if (!file_exists($ulogo)) {
						$ulogo = '';
					}
					if ($ulogo) {
						$ulogo = realpath($ulogo);
						$wtm_succ = File::add_watermark($locfile, '', $ulogo, ['x'=>180,'y'=>180,'w'=>75,'h'=>75], 100, '#FFFFFF', ['png2jpg'=>true]);
						if ($wtm_succ) {
							$qrcode = $wtm_succ;
						}
					}
					if (''!=$tmpfile) {
						@unlink($tmpfile);
					}
				}
			}
		} else {
			$qrcode = str_replace(SIMPHP_ROOT, '', $locfile_jpg);
		}
		return $qrcode;
	}
	
	/**
	 * 返回当前用户在“本机”的推广一起享二维码相对路径
	 * 
	 * @param $uid   integer  用户ID，不提供时用当前登录用户
	 * @param $ulogo string   用户logo地址，不提供时用当前登录用户
	 * @param $regen boolean  是否重新生成
	 * @return boolean
	 */
	static function eqx_tuiqr($uid = NULL, $ulogo = '', $regen = FALSE) {
		if (!isset($uid)) {
			$uid   = $GLOBALS['user']->uid;
			$ulogo = $GLOBALS['user']->logo;
		}
		$dir = Fn::gen_qrcode_dir($uid, 'uteqx', true);
		$locfile = $dir . $uid . '.png';
		$locfile_jpg = $locfile. '.jpg';
		if ($regen) {
			if (file_exists($locfile_jpg)) {
				@unlink($locfile_jpg);
			}
			if (file_exists($locfile)) {
				@unlink($locfile);
			}
		}
		
		$qrcode = '';
		if (!file_exists($locfile_jpg)) {
			if (mkdirs($dir)) {
				$qrinfo = U('t/'.$uid.'/eqx','',true);
				include_once SIMPHP_INCS . '/libs/phpqrcode/qrlib.php';
				QRcode::png($qrinfo, $locfile, QR_ECLEVEL_M, 15, 2);
				if (file_exists($locfile)) {
					File::make_thumb($locfile, 435, 435);
					$qrcode = str_replace(SIMPHP_ROOT, '', $locfile);
					$tmpfile= '';
					if (preg_match('/^http(s?):\/\//i', $ulogo)) {
						$ulogo = $tmpfile = File::get_remote($ulogo);
					}
					if (Media::is_app_file($ulogo)) {
						$ulogo = SIMPHP_ROOT . $ulogo;
						if (''!=$tmpfile) {
							$tmpfile = SIMPHP_ROOT . $tmpfile;
						}
					}
					if (!file_exists($ulogo)) {
						$ulogo = '';
					}
					if ($ulogo) {
						$ulogo = realpath($ulogo);
						$wtm_succ = File::add_watermark($locfile, '', $ulogo, ['x'=>180,'y'=>180,'w'=>75,'h'=>75], 100, '#FFFFFF', ['png2jpg'=>true]);
						if ($wtm_succ) {
							$qrcode = $wtm_succ;
						}
					}
					if (''!=$tmpfile) {
						@unlink($tmpfile);
					}
				}
			}
		} else {
			$qrcode = str_replace(SIMPHP_ROOT, '', $locfile_jpg);
		}
		return $qrcode;
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
	 * 根据性别值获取中文性别"TA"
	 */
	static function TA($sex = 0) {
		return 1==$sex ? '他' : (2==$sex ? '她' : 'TA');
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
			
			WxTplMsg::be_member($this->openid, "尊敬的{$this->nickname}, 恭喜你成功升级为米商", "点击查看米商计划", U("riceplan",'',true), ["uid"=>$this->uid,"valid_date"=>'永久有效']);
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

	/**
	 * 微信通知上级注册成功
	 */
	public function notify_reg_succ() {
		if (empty($this->parentid)) return false;
	
		$uParent = self::load($this->parentid);
		if ($uParent->is_exist()) {
			$extra = ['friendname'=>$this->nickname,'regtime'=>WxTplMsg::human_dtime($this->regtime)];
			$ta    = self::TA($this->sex);
			$url   = U('partner/my/child','',true); //TODO url 还需要精细化
			$utitle= Users::displayUserLevel($this->level);
				
			//通知一级上级
			if (!empty($uParent->openid)) {
				WxTplMsg::invite_reg($uParent->openid, "你邀请的好友已注册成为{$utitle}了", "{$this->nickname}({$this->uid})是你的一级米客，{$ta}需要你的指导", $url, $extra);
			}
				
			//通知二级上级
			if ($uParent->parentid) {
				$uParent2 = self::load($uParent->parentid);
				if ($uParent2->is_exist()) {
					if (!empty($uParent2->openid)) {
						WxTplMsg::invite_reg($uParent2->openid, "你的下级邀请的好友已注册成为{$utitle}了", "{$this->nickname}是你的二级米客，推荐人：{$uParent->nickname}({$uParent->uid})", $url, $extra);
					}
					
					//通知三级上级
					$uParent3 = self::load($uParent2->parentid);
					if ($uParent3->is_exist()) {
						if (!empty($uParent3->openid)) {
							WxTplMsg::invite_reg($uParent3->openid, "你的下级邀请的好友已注册成为{$utitle}了", "{$this->nickname}是你的三级米客，推荐人：{$uParent->nickname}({$uParent->uid})", $url, $extra);
						}
					}
				}
			}
			
		}
	
		return true;
	}
	
	/**
	 * 微信通知支付成功
	 */
	public function notify_buyer_pay_succ($order) {
	    if ($order->is_exist() && $order->pay_status==PS_PAYED)
	    {
	        $order_id = $order->order_id;
	        //数据准备
	        $orderItems = Order::getTinyItems($order_id);
	        $itemDesc = '';
	        if (!empty($orderItems)) {
	            foreach ($orderItems AS $it) {
	                $itemDesc .= $it['goods_name'].',';
	            }
	            $itemDesc = substr($itemDesc, 0, -1);
	        }
	        	
	        //通知提醒自己
	        $extra = [
	            'paid_money' => $order->money_paid.'元',
	            'item_desc'  => $itemDesc,
	            'pay_way'    => $order->pay_name,
	            'order_sn'   => $order->order_sn,
	            'order_id'   => $order_id,
	            'pay_time'   => WxTplMsg::human_dtime(simphp_gmtime2std($order->pay_time))
	        ];
	        if (!empty($this->openid)) {
	            WxTplMsg::pay_succ($this->openid, '你好，你的商品已支付成功!', '你可以到交易记录查看更多信息', U("order/{$order_id}/detail",'',true), $extra);
	        }
	    }
	}
	
	public function notify_parent_pay_succ(){
	    /* $extra = [
	        'order_sn'     => $order->order_sn,
	        'order_id'     => $order_id,
	        'order_amount' => $order->money_paid.'元',
	        'order_state'  => '支付成功，你可以获得%.2f元佣金'
	    ];
	    $order_upart = $cUser->uid . ',' . $cUser->nickname;
	    $order_state = '支付成功，你可以获得%.2f元佣金';
	    $first  = '你的%s级米客('.$order_upart.')购买商品支付成功!';
	    $remark = '米客确认收货7天后，你将可申请提现，点击查询详情';
	    //一级上级
	    if (!empty($uParent1->openid)) {
	        $commision = UserCommision::user_share($order->commision, 1);
	        $extra['order_state'] = sprintf($order_state, $commision);
	        $url    = U('user/commission',['user_id'=>$cUser->parentid, 'order_id'=>$order_id, 'level'=>1],true);
	        WxTplMsg::sharepay_succ($uParent1->openid, sprintf($first, '一'), $remark, $url, $extra);
	    } */
	}
	
	/**
	 * 微信通知支付成功
	 */
	public function notify_pay_succ($order_id) {
		$order = Order::load($order_id);
		if ($order->is_exist() && $order->pay_status==PS_PAYED)
		{
			//数据准备
			$orderItems = Order::getTinyItems($order_id);
			$itemDesc = '';
			if (!empty($orderItems)) {
				foreach ($orderItems AS $it) {
					$itemDesc .= $it['goods_name'].',';
				}
				$itemDesc = substr($itemDesc, 0, -1);
			}
			
			//通知提醒自己
			$cUser = Users::load($order->user_id);
			$extra = [
				'paid_money' => $order->money_paid.'元',
				'item_desc'  => $itemDesc,
				'pay_way'    => $order->pay_name,
				'order_sn'   => $order->order_sn,
			    'order_id'   => $order_id,
				'pay_time'   => WxTplMsg::human_dtime(simphp_gmtime2std($order->pay_time))
			];
			if (!empty($cUser->openid)) {
				WxTplMsg::pay_succ($cUser->openid, '你好，你的商品已支付成功!', '你可以到交易记录查看更多信息', U("order/{$order_id}/detail",'',true), $extra);
			}
			
			//提醒上三级获得未生效的佣金
			if ($cUser->parentid) {
				$uParent1 = Users::load($cUser->parentid);
				if ($uParent1->is_exist()) {
					$extra = [
						'order_sn'     => $order->order_sn,
					  'order_id'     => $order_id,
						'order_amount' => $order->money_paid.'元',
						'order_state'  => '支付成功，你可以获得%.2f元佣金'
					];
					$order_upart = $cUser->uid . ',' . $cUser->nickname;
					$order_state = '支付成功，你可以获得%.2f元佣金';
					$first  = '你的%s级米客('.$order_upart.')购买商品支付成功!';
					$remark = '米客确认收货7天后，你将可申请提现，点击查询详情';
					//一级上级
					if (!empty($uParent1->openid)) {
						$commision = UserCommision::user_share($order->commision, 1);
						$extra['order_state'] = sprintf($order_state, $commision);
						$url    = U('user/commission',['user_id'=>$cUser->parentid, 'order_id'=>$order_id, 'level'=>1],true);
						WxTplMsg::sharepay_succ($uParent1->openid, sprintf($first, '一'), $remark, $url, $extra);
					}
					
					//二级上级
					if ($uParent1->parentid) {
						$uParent2 = Users::load($uParent1->parentid);
						if ($uParent2->is_exist()) {
							if (!empty($uParent2->openid)) {
								$commision = UserCommision::user_share($order->commision, 2);
								$extra['order_state'] = sprintf($order_state, $commision);
								$url    = U('user/commission',['user_id'=>$uParent1->parentid, 'order_id'=>$order_id, 'level'=>2],true);
								WxTplMsg::sharepay_succ($uParent2->openid, sprintf($first, '二'), $remark, $url, $extra);
							}
							
							//三级上级
							if ($uParent2->parentid) {
								$uParent3 = Users::load($uParent2->parentid);
								if ($uParent3->is_exist()) {
									if (!empty($uParent3->openid)) {
										$commision = UserCommision::user_share($order->commision, 3);
										$extra['order_state'] = sprintf($order_state, $commision);
										$url    = U('user/commission',['user_id'=>$uParent2->parentid, 'order_id'=>$order_id, 'level'=>3],true);
										WxTplMsg::sharepay_succ($uParent3->openid, sprintf($first, '三'), $remark, $url, $extra);
									}
								}
							}
							
						}
					}
					
				}
			}
			
		}
	}
	
	/**
	 * 通知账号“预锁定”
	 * 
	 * @param UsersPending $oUP
	 */
	public static function notify_locked_account(UsersPending $oUP) {
		if ($oUP->parent_id) {
			$pUser = self::load($oUP->parent_id);
			if ($pUser->is_exist() && strlen($pUser->openid)>20) {
				$ta     = 'TA';
				$gender = '';
				if (1==$oUP->gender) {
					$gender = '(男)';
					$ta = '他';
				}
				elseif (2==$oUP->gender) {
					$gender = '(女)';
					$ta = '她';
				}
				WxTplMsg::account_locked($pUser->openid,
				                         '恭喜您，'.$pUser->nickname.'，您刚刚成功预锁定了一个伙伴：',
				                         '“预锁定”不表示“真锁定”，请提示'.$ta.'尽快注册以最终确定关系。',
				                         U('user/prelocked_account','theuid='.$pUser->uid, true),
				                         ['locked_account'=>$oUP->nick.$gender,
				                          'locked_time'=>WxTplMsg::human_dtime(strtotime($oUP->touch_time))]);
			}
		}
	}
	
	/**
	 * 查找预锁定用户
	 * @param integer $theuid
	 * @param integer $start
	 * @param integer $limit
	 * @param integer $totalnum output
	 * @param integer $maxpage output
	 * @return array
	 */
	public static function find_locked_accounts($theuid, $start = 0, $limit = 20, &$totalnum = 0, &$maxpage = 0) {
		$list = [];
		if ($theuid) {
			$sql_cnt  = "SELECT COUNT(unionid) FROM `shp_users_pending` WHERE `parent_id`=%d";
			$totalnum = D()->query($sql_cnt, $theuid)->result();
			$maxpage  = ceil($totalnum / ($limit?:20));
			$sql = "SELECT up.unionid,up.openid,up.parent_id,up.nick,up.logo,up.gender,up.touch_time,up.update_time,IF(u.user_id is NULL,0,1) AS is_reg FROM `shp_users_pending` AS up LEFT JOIN `shp_users` AS u ON u.mobile<>'' AND up.openid=u.openid WHERE up.parent_id=%d ORDER BY up.rid DESC LIMIT %d,%d";
			$list = D()->query($sql,$theuid,$start,$limit)->fetch_array_all();
		}
		return $list;
	}
	
	/**
	 * 是不是代理
	 */
	public static function isAgent($level){
	    return in_array($level, self::getAgentArray());
	}
	
	//金牌代理
	public static function isGoldAgent($level){
	    return ($level == Users::USER_LEVEL_4 || $level == Users::USER_LEVEL_5);
	}
	
	//银牌代理
	public static function isSilverAgent($level){
	    return ($level == Users::USER_LEVEL_3);
	}
	
	public static function displayUserLevel($level){
	    switch($level){
	        case Users::USER_LEVEL_1 : 
	            return '米商';
	        break;
	        case Users::USER_LEVEL_3 :
	            return '银牌代理';
	        break;
	        case Users::USER_LEVEL_4 :
	            return '金牌代理';
	        break;
	        case Users::USER_LEVEL_5 :
	            return '入驻商家';
	        break;
	        default : 
	           return '米客';
	    }
	}
	
	/**
	 * 返回代理数组
	 */
	static function getAgentArray(){
	    return [Users::USER_LEVEL_3, Users::USER_LEVEL_4, Users::USER_LEVEL_5];
	}
	
	/**
	 * 检查是否已存在唯一手机号
	 * @param string $mobile
	 * @return boolean
	 */
	static function check_mobile_exist($mobile) {
		$extu = D()->from(self::table())->where(['mobile'=>$mobile])->select('user_id')->result();
		return $extu ? : false;
	}
	
	/**
	 * 统计用户总数(只包括用手机注册部分)
	 */
	static function user_total() {
		$total = D()->query("SELECT COUNT(user_id) AS utotal FROM ".self::table()." WHERE `mobile`<>''")->result();
		return $total;
	}
	
}
 
/*----- END FILE: class.Users.php -----*/