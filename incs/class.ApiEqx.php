<?php
/**
 * EQX API Model
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
class ApiEqx extends Model {
	
	const API_BASE_URL = 'http://ucapi.edmbuy.com';  //production environment
	//const API_BASE_URL = 'http://yqxapi.fxmapp.com'; //develop environment
	
	const PAGEAPI_BASE_URL = 'http://eqx.edmbuy.com'; //production environment
	//const PAGEAPI_BASE_URL = 'http://eqxtest.edmbuy.com'; //test environment
	
	const APPID_EDM  = 4;
	const APPID_EQX  = 5;
	const AUTH_KEY_4 = 'jqv8xfaa31my6cpjwuwng455t2q6lt8j';
	const AUTH_KEY_5 = 'q7sh97i27rics3vxo8s4kjokdg93ysee';
	
	public static $argName = 'res';
	
	static function parseEqxRequest() {
	
		$a = isset($_GET['a']) ? $_GET['a'] : '';
		$d = isset($_GET['d']) ? $_GET['d'] : '';
		if (empty($a) || empty($d)) {
			return false;
		}
		
		$d = base64url_decode($d);
		$data = Api::json_decode($d, 3);
		
		if (method_exists(get_called_class(), $a)) {
			return self::$a($data);
		}
	
		return false;
	}
	
	static function sync_login($data) {
		
		$res  = $data[self::$argName];
		
		//~ 检查上级
		$parent_id   = 0;
		$parent_nick = '';
		if (!empty($res['parent_guid'])) { //上级不为空，则需要检查上级是否已在本地
			$pUser = Users::find_eqx_user($res['parent_guid'], $res['parent_mobile']);
			if (!$pUser->is_exist()) { //先创建上级占位
				$nPUser = new Users();
				$nPUser->mobile   = $res['parent_mobile'];
				$nPUser->nickname = base64url_decode($res['parent_nick']);
				$nPUser->password = ''; //这时密码为空
				$nPUser->logo     = $res['parent_logo'];
				$nPUser->sex      = $res['parent_gender'];
				$nPUser->regip    = Request::ip();
				$nPUser->regtime  = simphp_time();
				$nPUser->ecsalt   = '';
				$nPUser->salt     = gen_salt();
				$nPUser->guid     = $res['parent_guid'];
				$nPUser->parent_guid = 0;
				$nPUser->from_sync= 1;
				$nPUser->from     = 'reg_sync';
				$nPUser->randver  = randstr(6);
				$nPUser->save(Storage::SAVE_INSERT_IGNORE);
				$parent_id   = $nPUser->id;
				$parent_nick = $nPUser->nickname;
				unset($nPUser);
			}
			else {
				$parent_id   = $pUser->uid;
				$parent_nick = $pUser->nickname;
			}
		}
		
		//~ 处理当前用户
		$passwd_raw = '';
		if (!empty($res['passwd'])) {
			$passwd_raw = AuthCode::decrypt($res['passwd'], self::AUTH_KEY_5);
		}
		$cUid  = 0;
		$cUser = Users::find_eqx_user($res['guid'], $res['mobile']);
		if ($cUser->is_exist()) { //已存在，更新部分信息
			
			$cUid = $cUid->id;
			if (!$cUser->parentid || !$cUser->password) { //只有上级为空，或者密码为空时才会去更新
				$nUser = new Users($cUser->id);
				if (''!=$passwd_raw) {
					$passwd_enc = gen_salt_password($passwd_raw, $cUser->salt);
					$nUser->password = $passwd_enc;
				}
				if (!$cUser->parentid && $cUser->from_sync) { //只有在from_sync==1 并且 上级不存在时才修改
					$p2layers = Users::get_parent_ids($parent_id, TRUE);
					$nUser->parentid    = $parent_id;
					$nUser->parentnick  = $parent_nick;
					$nUser->parentid2   = $p2layers['parent_id2'];
					$nUser->parentid3   = $p2layers['parent_id3'];
					$nUser->guid        = $res['guid'];
					$nUser->parent_guid = $res['parent_guid'];
				}
				$nUser->save(Storage::SAVE_UPDATE);
			}
		
		}
		else { //不存在，新增用户
			$salt = gen_salt();
			$passwd_enc = !empty($passwd_raw) ? gen_salt_password($passwd_raw, $salt) : '';
			$p2layers = Users::get_parent_ids($parent_id, TRUE);
		
			$nUser = new Users();
			$nUser->mobile   = $res['mobile'];
			$nUser->nickname = base64url_decode($res['nick']);
			$nUser->password = $passwd_enc; //这时密码"可能"为空
			$nUser->logo     = $res['logo'];
			$nUser->sex      = $res['gender'];
			$nUser->regip    = Request::ip();
			$nUser->regtime  = simphp_time();
			$nUser->ecsalt   = '';
			$nUser->salt     = $salt;
			$nUser->parentid   = $p2layers['parent_id'];
			$nUser->parentnick = $p2layers['parent_nick'];
			$nUser->parentid2  = $p2layers['parent_id2'];
			$nUser->parentid3  = $p2layers['parent_id3'];
			$nUser->guid     = $res['guid'];
			$nUser->parent_guid = $res['parent_guid'];
			$nUser->from_sync= 1;
			$nUser->from     = 'reg_sync';
			$nUser->randver  = randstr(6);
			$nUser->save(Storage::SAVE_INSERT_IGNORE);
			$cUid = $nUser->id;
		}
		return $cUid;
		
	}
	
	static function doreg($mobile, $passwd, $parent_uid = 0, Array $extra = array()) {
		$api = '/user/reg';
		$parent_id = 0;
		if ($parent_uid) {
			$pUser = Users::load($parent_uid);
			if ($pUser->is_exist()) {
				if ($pUser->guid) {
					$parent_id = $pUser->guid;
				}
				else {
					$parent_id = $pUser->mobile;
				}
			}
			else {
				$parent_uid = 0;
			}
		}
		
		$params_args = [
			'args' => [
				'mobile'   => $mobile,
				'passwd'   => AuthCode::encrypt($passwd, self::AUTH_KEY_4),
				'parent_id'=> $parent_id,
				'gender'   => isset($extra['gender']) ? $extra['gender'] : 0,
				'nick'     => isset($extra['nick']) ? $extra['nick'] : '',
				'logo'     => isset($extra['logo']) ? $extra['logo'] : '',
				'qrcode'   => isset($extra['qrcode']) ? $extra['qrcode'] : '',
				'app_uid'  => 0,
				'app_puid' => $parent_uid,
			]
		];
		
		// 先注册占位
		$salt            = gen_salt();
		$passwd_enc      = gen_salt_password($passwd, $salt);
		$nUser           = new Users();
		$nUser->mobile   = $mobile;
		$nUser->password = $passwd_enc;
		$nUser->salt     = $salt;
		$nUser->from_sync= 1; //初始化后的所有新增用户的from_sync都是1
		if (isset($extra['unionid'])) {
			$nUser->unionid = $extra['unionid'];
		}
		if (isset($extra['openid'])) {
			$nUser->openid = $extra['openid'];
		}
		if (isset($extra['subscribe'])) {
			$nUser->subscribe = $extra['subscribe'];
		}
		if (isset($extra['subscribe_time'])) {
			$nUser->subscribetime = $extra['subscribe_time'];
		}
		if (isset($extra['gender'])) {
			$nUser->sex = $extra['gender'];
		}
		if (isset($extra['nick'])) {
			$nUser->nickname = $extra['nick'];
		}
		if (isset($extra['logo'])) {
			$nUser->logo = $extra['logo'];
		}
		if (isset($extra['qrcode'])) {
			$nUser->wxqr = $extra['qrcode'];
		}
		if ($parent_uid) {
			$parents_info = Users::get_parent_ids($parent_uid, true);
			$nUser->parentid   = $parents_info['parent_id'];
			$nUser->parentnick = $parents_info['parent_nick'];
			$nUser->parentid2  = $parents_info['parent_id2'];
			$nUser->parentid3  = $parents_info['parent_id3'];
		}
		$nUser->regip     = Request::ip();
		$nUser->regtime   = simphp_time();
		$nUser->level     = 0;
		$nUser->from      = 'reg';
		$nUser->randver   = randnum(6);
		$nUser->save(Storage::SAVE_INSERT_IGNORE);
		
		if ($nUser->id) { //表示本地新建记录成功
			$params_args['args']['app_uid'] = $nUser->id;
			$ret = self::call_api($api, $params_args); //调用一起享注册
			$upUser = new Users($nUser->id);
			if (0==$ret['code'] || 5001==$ret['code']) { //注册成功
				$res = $ret['res'];
				$upUser->guid = $res['guid'];
				$upUser->parent_guid = $res['parent_guid'];
				if (5001==$ret['code']) { //表示UC那边该用户已存在，并且已有了固定的人网，这是要同步成UC那边的上级
					$upUser->from = 'reg_sync';
					if (!empty($res['parent_guid']) || !empty($res['parent_mobile'])) {
						$newParent = Users::find_eqx_user($res['parent_guid'], $res['parent_mobile']);
						if ($newParent->is_exist()) { //找到新上级，则用UC的上级覆盖本地的
							$parents_info = Users::get_parent_ids($newParent->id, true);
							$upUser->parentid   = $parents_info['parent_id'];
							$upUser->parentnick = $parents_info['parent_nick'];
							$upUser->parentid2  = $parents_info['parent_id2'];
							$upUser->parentid3  = $parents_info['parent_id3'];
						}
					}
				}
				$upUser->save(Storage::SAVE_UPDATE);
				return TRUE;
			}
			else { //注册失败，需要清理现场
				$upUser->remove();
			}
		}
		
		return FALSE;
		
	}
	
	static function dologin($mobile, $passwd) {
		$api = '/user/login';
		$params_args = [
			'args' => [
				'mobile'   => $mobile,
				'passwd'   => AuthCode::encrypt($passwd, self::AUTH_KEY_4)
			]
		];
		
		return self::call_api($api, $params_args); //调用一起享登录
	}
	
	static function gen_eqx_loginurl($guid, $mobile) {
		$api = '/synclogin.jsp';
		$params_args = [
				'args' => [
						'guid'   => $guid,
						'mobile' => $mobile,
				]
		];
		$params = [
				'appid'    => self::APPID_EDM,
				//'appid'    => self::APPID_EQX,
				'format'   => 'json',
				'ts'       => simphp_msec(),
				'ip'       => Request::ip(),
				'v'        => '1.0.0'
		];
		$params  = array_merge($params_args,$params);
		$siginfo = [
				'name'   => 'sig',
				//'skey'   => self::AUTH_KEY_4,
				'skey'   => self::AUTH_KEY_5,
				'sep'    => '|',
				'encfunc'=> 'sha1',
				'urlencode_level' => 0,
				'debug'=>1
		];
		
		include_once (SIMPHP_INCS . '/libs/ApiRequest/class.ApiRequest.php');
		ksort($params,SORT_STRING);
		$qstr = ApiRequest::makeQueryString($params, 0);
		$qstr.= '|'.$siginfo['skey'];
		$sig  = sha1($qstr);
		$params['sig'] = $sig;
		$d = base64url_encode(json_encode($params,JSON_UNESCAPED_SLASHES));
		return self::PAGEAPI_BASE_URL.$api.'?jsoncb=__sync_login_cb&d='.$d;
	}
	
	private static function call_api($api, Array $params_args) {
		$params = [
				'appid'    => self::APPID_EDM,
				'format'   => 'json',
				'ts'       => simphp_msec(),
				'ip'       => Request::ip(),
				'v'        => '1.0.0'
		];
		$params  = array_merge($params_args,$params);
		$siginfo = [
				'name'   => 'sig',
				'skey'   => self::AUTH_KEY_4,
				'sep'    => '|',
				'encfunc'=> 'sha1',
				'urlencode_level' => 0,
				'debug'=>0
		];
		
		include_once (SIMPHP_INCS . '/libs/ApiRequest/class.ApiRequest.php');
		$req = new ApiRequest(['method'=>'post','sendfmt'=>'json']);
		return $req->setUrl(self::API_BASE_URL.$api)->setParams($params)->sign($siginfo)->send()->recv(TRUE);
	}
	
}
 
/*----- END FILE: class.ApiEqx.php -----*/