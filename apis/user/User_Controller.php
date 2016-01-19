<?php
/**
 * 用户同步接口Controller
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class User_Controller extends Controller {
	
	/**
	 * default action 'sync'
	 *
	 * @param Request $request
	 * @param Response $response
	 */
	public function sync(Request $request, Response $response)
	{
		Api::append_codes([
			'4000' => '\'unionid\' invalid',
			'4001' => '\'parent_id\' invalid',
			'4002' => '\'parent_id\' not exist',
			'4003' => '\'mobile\' invalid',
			'4004' => '\'logo\' invalid',
			'5000' => 'db op fail',
		]);
		
		$unionId      = $request->unionid;
		$parentUnid   = $request->parent_id    ? : ''; //是一个 parent unionid
		$regtime      = $request->regtime      ? : simphp_time();
		$mobile       = $request->mobile       ? : '';
		$nickname     = $request->nickname     ? : '';
		$logo         = $request->logo         ? : '';
		$business_id  = $request->business_id  ? : '';
		$business_time= $request->business_time? : '';
		
		if (empty($unionId)) {
			throw new ApiException(4000);
		}
		if (!empty($mobile) && !preg_match('/^\d{11,15}$/', $mobile)) {
			//throw new ApiException(4003);
			$mobile = ''; //避免干扰主任务，只是不同步该字段，而不抛出返回
		}
		if (!empty($logo) && !preg_match('/^http(s?):\/\//', $logo)) {
			//throw new ApiException(4004);
			$logo = ''; //避免干扰主任务，只是不同步该字段，而不抛出返回
		}
		if (!$business_id) {
			$business_time = '';
		}
		
		$res = ['user_id'=>0, 'act_type'=>'none', 'req_mobile'=>$mobile ,'parent_id'=>''];
		$exUser = Users::load_by_unionid($unionId);
		if (!$exUser->is_exist()) { //未注册
			$upUser = new Users();
			$upUser->unionid  = $unionId;
			$upUser->mobilephone = $mobile;
			$upUser->nickname = $nickname;
			$upUser->logo     = $logo;
			$upUser->regip    = $request->ip();
			$upUser->regtime  = $regtime;
			$upUser->salt     = gen_salt();
			$upUser->parentid = Users::get_userid($parentUnid);
			$upUser->parentunionid = $parentUnid;
			$upUser->businessid    = $business_id;
			$upUser->businesstime  = $business_time;
			$upUser->from     = $request->appid;
			$upUser->save(Storage::SAVE_INSERT);
			
			$res['user_id']  = $upUser->id;
			$res['act_type'] = 'insert';
			$res['parent_id']= $parentUnid;
		}
		else { //已注册
			$res['user_id']  = $exUser->id;
			
			$upUser = new Users($exUser->id);
			$upUser->parentunionid  = $parentUnid; //始终保存接口传来的parent_unionid
			$upUser->businessid     = $business_id;
			$upUser->businesstime   = $business_time;
			$upUser->lasttime       = simphp_dtime();
			$upUser->lastip         = Request::ip();
			
			//mobile, nickname 和 logo 本地如果为空就更新
			if (empty($exUser->mobilephone)) {
				$upUser->mobilephone = $mobile;
			}
			if (empty($exUser->nickname)) {
				$upUser->nickname = $nickname;
			}
			if (empty($exUser->logo)) {
				$upUser->logo     = $logo;
			}
			
			if (empty($exUser->parentid)) { //只要是空，表示“未确定”状态，则给机会变更
				$upUser->parentid = Users::get_userid($parentUnid);
				$res['act_type'] = 'update';
				$res['parent_id']= $upUser->parentid ? $parentUnid : '';
			}
			else {
				$res['parent_id']= Users::get_unionid($exUser->parentid);
			}
			
			$upUser->save(Storage::SAVE_UPDATE);
		}
		
		throw new ApiResponse($res);
	}
	
	/**
	 * default action 'update'
	 *
	 * @param Request $request
	 * @param Response $response
	 */
	public function update(Request $request, Response $response)
	{
		Api::append_codes([
			'4000' => '\'unionid\' invalid',
			'4001' => '\'parent_id\' invalid',
			'4002' => '\'parent_id\' not exist',
			'4003' => '\'mobile\' invalid',
			'4004' => '\'logo\' invalid',
			'5000' => 'db op fail',
		]);
		
		$unionId      = $request->unionid;
		$parentUnid   = $request->parent_id    ? : ''; //是一个 parent unionid
		$regtime      = $request->regtime      ? : simphp_time();
		$mobile       = $request->mobile       ? : '';
		$nickname     = $request->nickname     ? : '';
		$logo         = $request->logo         ? : '';
		$business_id  = $request->business_id  ? : '';
		$business_time= $request->business_time? : '';
		
		if (empty($unionId)) {
			throw new ApiException(4000);
		}
		if (!empty($mobile) && !preg_match('/^\d{11,15}$/', $mobile)) {
			$mobile = '';
		}
		if (!empty($logo) && !preg_match('/^http(s?):\/\//', $logo)) {
			$logo = '';
		}
		if (!$business_id) {
			$business_time = '';
		}
		
		$res = ['user_id'=>0, 'act_type'=>'none', 'req_mobile'=>$mobile ,'parent_id'=>''];
		$exUser = Users::load_by_unionid($unionId);
		if (!$exUser->is_exist()) { //未注册
			$upUser = new Users();
			$upUser->unionid  = $unionId;
			$upUser->mobilephone = $mobile;
			$upUser->nickname = $nickname;
			$upUser->logo     = $logo;
			$upUser->regip    = $request->ip();
			$upUser->regtime  = $regtime;
			$upUser->salt     = gen_salt();
			$upUser->parentid = Users::get_userid($parentUnid);
			$upUser->parentunionid = $parentUnid;
			$upUser->businessid    = $business_id;
			$upUser->businesstime  = $business_time;
			$upUser->from          = $request->appid;
			$upUser->save(Storage::SAVE_INSERT);
			$upUser->update_synctimes('+1');
			
			$res['user_id']  = $upUser->id;
			$res['act_type'] = 'insert';
			$res['parent_id']= $parentUnid;
		}
		else { //已注册
			$res['user_id']  = $exUser->id;
			
			$upUser = new Users($exUser->id);
			$upUser->parentid       = Users::get_userid($parentUnid);
			$upUser->parentunionid  = $parentUnid;
			$upUser->businessid     = $business_id;
			$upUser->businesstime   = $business_time;
			$upUser->mobilephone    = $mobile;
			$upUser->nickname       = $nickname;
			$upUser->logo           = $logo;
			$upUser->lasttime       = simphp_dtime();
			$upUser->lastip         = Request::ip();
			
			$res['act_type'] = 'update';
			$res['parent_id']= $upUser->parentid ? $parentUnid : '';
			
			$upUser->save(Storage::SAVE_UPDATE);
			$upUser->update_synctimes('+1');
		}
		
		throw new ApiResponse($res);
	}
	
	/**
	 * default action 'activate_sync'
	 *
	 * @param Request  $request
	 * @param Response $response
	 */
	public function raw_sync(Request $request, Response $response)
	{
		Api::append_codes([
			'4000' => '\'app_userid\' empty',
			'4003' => '\'mobile\' invalid',
			'4004' => '\'logo\' invalid',
			'5000' => 'db op fail',
		]);
	
		$app_userid           = intval($request->app_userid);
		$app_mobile           = $request->app_mobile;
		$app_regtime          = $request->app_regtime;
		$app_nick             = $request->app_nick             ? : '';
		$app_logo             = $request->app_logo             ? : '';
		$app_business_id      = $request->app_business_id      ? : '';
		$app_business_time    = $request->app_business_time    ? : '';
		$app_openid           = $request->app_openid           ? : '';
		$parent_userid        = $request->parent_userid        ? intval($request->parent_userid) : 0;
		$parent_mobile        = $request->parent_mobile        ? : '';
		$parent_regtime       = $request->parent_regtime       ? : '';
		$parent_nick          = $request->parent_nick          ? : '';
		$parent_logo          = $request->parent_logo          ? : '';
		$parent_business_id   = $request->parent_business_id   ? : '';
		$parent_business_time = $request->parent_business_time ? : '';
		$parent_openid        = $request->parent_openid        ? : '';
		
		if (empty($app_userid)) {
			throw new ApiException(4000);
		}
		
		$ret = User_Model::saveAppUser($this->genAppData($app_userid, $app_mobile, $app_openid, $app_regtime, $app_nick, $app_logo, $app_business_id, $app_business_time, $parent_userid));
		if ($ret && $parent_userid) {
			User_Model::saveAppUser($this->genAppData($parent_userid, $parent_mobile, $parent_openid, $parent_regtime, $parent_nick, $parent_logo, $parent_business_id, $parent_business_time));
		}
		
		$res = ['app_userid'=>$app_userid, 'app_mobile'=>$app_mobile];
		throw new ApiResponse($res);
	}
	
	private function genAppData($userid, $mobile, $openid, $regtime, $nick, $logo, $business_id, $business_time, $parent_userid = 0)
	{
		return [
				'userid'      => $userid,
				'mobile'      => $mobile,
				'openid'      => $openid,
				'regtime'     => $regtime,
				'nick'        => $nick,
				'logo'        => $logo,
				'business_id' => $business_id,
				'business_time' => $business_time,
				'parent_userid' => $parent_userid,
		];
	}
	
}

/*----- END FILE: User_Controller.php -----*/