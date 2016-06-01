<?php
/**
 * EQX API Model
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
class ApiEqx extends Model {
	
	const AUTH_KEY_4 = 'jqv8xfaa31my6cpjwuwng455t2q6lt8j';
	const AUTH_KEY_5 = 'q7sh97i27rics3vxo8s4kjokdg93ysee';
	
	public static $argName = 'res';
	
	static function parseEqxRequest() {
	
		$a = $_GET['a'];
		$d = $_GET['d'];
		$d = base64url_decode($d);
		$data = Api::json_decode($d, 3);
		
		if (method_exists(get_called_class(), $a)) {
			self::$a($data);
		}
	
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
		
		$cUser = Users::find_eqx_user($res['guid'], $res['mobile']);
		if ($cUser->is_exist()) { //已存在，更新部分信息
		
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
		}
		return true;
		
	}
	
	static function call_reg() {
		$api = '/user/reg';
		$params_args = [
				'args[mobile]' => '13826516741',
				'args[passwd]' => AuthCode::encrypt('gavin@asdf', $authkey),
				'args[parent_id]' => 636098,
				//'args[parent_id]' => '18610483996',
				'args[gender]' => 2,
				'args[nick]'   => 'Yancy',
				'args[logo]'   => '',
				'args[app_uid]'   => 104,
				'args[app_puid]'  => 345,
		];
	}
	
	static function call_api($api, Array $params_args) {
		$params = [
				'appid'    => 4,
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
		$resturl = 'http://ucapi.edmbuy.com';
		$req = new ApiRequest(['method'=>'post','sendfmt'=>'json']);
		return $req->setUrl($resturl.$api)->setParams($params)->sign($siginfo)->send()->recv(TRUE);
	}
	
}
 
/*----- END FILE: class.ApiEqx.php -----*/