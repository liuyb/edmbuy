<?php
/**
 * 用户同步接口Controller
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class User_Controller extends Controller {
	
	/**
	 * hook menu
	 * @see Controller::menu()
	 */
	public function menu() {
		return [
				
		];
	}
	
	/**
	 * hook init
	 *
	 * @param string $action
	 * @param Request $request
	 * @param Response $response
	 */
	public function init($action, Request $request, Response $response)
	{
		
	}
	
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
			'5000' => 'db op fail',
		]);
		
		$unionId    = $request->unionid;
		$parentUnid = $request->parent_id; //是一个 parent unionid
		$mobile     = $request->mobile;
		$regtime    = $request->regtime;
		
		if (empty($unionId)) {
			throw new ApiException(4000);
		}
		if (!empty($mobile) && !preg_match('/^\d{11,15}$/', $mobile)) {
			throw new ApiException(4003);
		}
		
		$aUser = Users::load_by_unionid($unionId);
		$res = ['user_id'=>0, 'act_type'=>'none', 'parent_id'=>0];
		if (empty($aUser)) { //未注册
			$aUser = new Users();
			$aUser->unionid  = $unionId;
			$aUser->mobilephone = $mobile;
			$aUser->regip    = $request->ip();
			$aUser->regtime  = $regtime;
			$aUser->parentid = Users::get_userid($parentUnid);
			$aUser->from     = $request->appid;
			$aUser->save();
			
			$res['user_id']  = $aUser->id;
			$res['act_type'] = 'insert';
			$res['parent_id']= $parentUnid;
		}
		else { //已注册
			$res['user_id']  = $aUser->id;
			$res['parent_id']= Users::get_unionid($aUser->parentid);
			if ($aUser->from==$request->appid) { //表示初始来自于某应用(如甜玉米)
				if (empty($aUser->parentid)) {
					$bUser = new Users($aUser->id);
					$bUser->parentid = Users::get_userid($parentUnid);
					$bUser->save();
					$res['act_type'] = 'update';
					$res['parent_id']= $parentUnid;
				}
			}
		}
		$response->sendAPI($res);
	}
	
}

/*----- END FILE: User_Controller.php -----*/