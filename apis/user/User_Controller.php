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
		
		$unionId    = $request->unionid;
		$parentUnid = $request->parent_id ? : ''; //是一个 parent unionid
		$regtime    = $request->regtime   ? : simphp_time();
		$mobile     = $request->mobile    ? : '';
		$nickname   = $request->nickname  ? : '';
		$logo       = $request->logo      ? : '';
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
		$aUser = Users::load_by_unionid($unionId);
		if (!$aUser->is_exist()) { //未注册
			$aUser = new Users();
			$aUser->unionid  = $unionId;
			$aUser->mobilephone = $mobile;
			$aUser->nickname = $nickname;
			$aUser->logo     = $logo;
			$aUser->regip    = $request->ip();
			$aUser->regtime  = $regtime;
			$aUser->parentid = Users::get_userid($parentUnid);
			$aUser->parentunionid = $parentUnid;
			$aUser->businessid    = $business_id;
			$aUser->businesstime  = $business_time;
			$aUser->from     = $request->appid;
			$aUser->save(Storage::SAVE_INSERT);
			
			$res['user_id']  = $aUser->id;
			$res['act_type'] = 'insert';
			$res['parent_id']= $parentUnid;
		}
		else { //已注册
			$res['user_id']  = $aUser->id;
			
			$bUser = new Users($aUser->id);
			$bUser->parentunionid  = $parentUnid; //始终保存接口传来的parent_unionid
			$bUser->businessid     = $business_id;
			$bUser->businesstime   = $business_time;
			
			//mobile, nickname 和 logo 本地如果为空就更新
			if (empty($aUser->mobilephone)) {
				$bUser->mobilephone = $mobile;
			}
			if (empty($aUser->nickname)) {
				$bUser->nickname = $nickname;
			}
			if (empty($aUser->logo)) {
				$bUser->logo     = $logo;
			}
			
			if (empty($aUser->parentid)) { //只要是空，表示“未确定”状态，则给机会变更
				$bUser->parentid = Users::get_userid($parentUnid);
				$res['act_type'] = 'update';
				$res['parent_id']= $bUser->parentid ? $parentUnid : '';
			}
			else {
				$res['parent_id']= Users::get_unionid($aUser->parentid);
			}
			
			$bUser->save(Storage::SAVE_UPDATE);
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
		
		$unionId    = $request->unionid;
		$parentUnid = $request->parent_id ? : ''; //是一个 parent unionid
		$regtime    = $request->regtime   ? : simphp_time();
		$mobile     = $request->mobile    ? : '';
		$nickname   = $request->nickname  ? : '';
		$logo       = $request->logo      ? : '';
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
		$aUser = Users::load_by_unionid($unionId);
		if (!$aUser->is_exist()) { //未注册
			$aUser = new Users();
			$aUser->unionid  = $unionId;
			$aUser->mobilephone = $mobile;
			$aUser->nickname = $nickname;
			$aUser->logo     = $logo;
			$aUser->regip    = $request->ip();
			$aUser->regtime  = $regtime;
			$aUser->parentid = Users::get_userid($parentUnid);
			$aUser->parentunionid = $parentUnid;
			$aUser->businessid    = $business_id;
			$aUser->businesstime  = $business_time;
			$aUser->from     = $request->appid;
			$aUser->save(Storage::SAVE_INSERT);
			$aUser->update_synctimes('+1');
			
			$res['user_id']  = $aUser->id;
			$res['act_type'] = 'insert';
			$res['parent_id']= $parentUnid;
		}
		else { //已注册
			$res['user_id']  = $aUser->id;
			
			$bUser = new Users($aUser->id);
			$bUser->parentid       = Users::get_userid($parentUnid);
			$bUser->parentunionid  = $parentUnid;
			$bUser->businessid     = $business_id;
			$bUser->businesstime   = $business_time;
			$bUser->mobilephone    = $mobile;
			$bUser->nickname       = $nickname;
			$bUser->logo           = $logo;
			
			$res['act_type'] = 'update';
			$res['parent_id']= $bUser->parentid ? $parentUnid : '';
			
			$bUser->save(Storage::SAVE_UPDATE);
			$bUser->update_synctimes('+1');
		}
		throw new ApiResponse($res);
	}
	
}

/*----- END FILE: User_Controller.php -----*/