<?php
/**
 * 一起享消息队列Controller
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Eqxmq_Controller extends Controller {

	/**
	 * 用于记录log数据
	 * @var array
	 */
	private $logData = [];
	
	private $argName = 'res';
	
	/**
	 * hook init
	 * @param string $action
	 * @param Request $request
	 * @param Response $response
	 */
	public function init($action, Request $request, Response $response) {
		
	}
	
	/**
	 * hook index
	 * @param Request $request
	 * @param Response $response
	 */
	public function index(Request $request, Response $response) {
		$this->logData = [
				'msgId'     => '',
				'msgAction' => '',
				'msgData'   => '',
				'reqTime'   => simphp_msec(),
				'dealTime'  => 0,
				'execResult'=> 0,
				'respCode'  => '',
				'respMsg'   => '',
		];
		
		/*
		 // 1. get the headers and check the signature
		 if (!Eqxmq_Model::check_mq_sign()) {
		 $this->response_mq(400, "Verify Signature Fail");
		 }
		*/
		
		// 2.now parse the content
		$headers = Eqxmq_Model::get_all_headers();
		$content = file_get_contents("php://input");
		$this->logData['msgId'] = isset($headers['X-Mns-Message-Id']) ? $headers['X-Mns-Message-Id'] : '';
		$this->logData['msgData'] = $content;
		if (empty($content)) {
			$this->response_mq(404, 'Message Not Exist');
		}
		$content = base64_decode($content);
		$params  = array();
		$this->logData['msgData'] = $content;
		parse_str($content, $params);
		if (count($params) < 1 || !isset($params['a'])) {
			$this->response_mq(400, 'Invalid Argument');
		}
		$mq_action = 'mq_'.$params['a'];
		$mq_data   = isset($params['d']) ? base64url_decode($params['d']) : '';
		$this->logData['msgAction'] = $params['a'];
		$this->logData['msgData']   = $mq_data;
		if (!$this->action_exists($mq_action)) {
			$this->response_mq(400, 'Invalid Action');
		}
		if (FALSE===$mq_data) {
			$this->response_mq(400, 'Data base64 decode(url safe)');
		}
		$mq_data = Api::json_decode($mq_data, 3);
		if (is_null($mq_data)) {
			$this->response_mq(400, 'Data json data fail');
		}
		
		// 检查消息数据签名
		if (!Eqxmq_Model::check_data_sign($mq_data)) {
			$this->response_mq(400, "Verify Data Signature Fail");
		}
		
		// 检查时间戳是否有效时间内
		if (isset($mq_data['ts']) && simphp_msec() > ($mq_data['ts'] + 5*60*1000)) { //5分钟内有效(ts是毫秒)
			$this->response_mq(400, 'ts expired');
		}
		
		// 检查接口参数字段是否存在
		if (!isset($data[$this->argName])) {
			$this->response_mq(400, 'args no exist');
		}
		
		// 调用消息aciton
		$ret = $this->$mq_action($mq_data);
		if ($ret) {
			$this->logData['execResult'] = 1;
		}
		else {
			$this->logData['execResult'] = -1;
		}
		
		// 调用完消息aciton后回显204
		$this->response_mq();
	}
	
	/**
	 * 测试消息接口
	 * @param array $data
	 * @return boolean
	 */
	public function mq_test_mq($data)
	{
		return true;
	}
	
	/**
	 * 同步登录和同步注册逻辑是一样的
	 * @param array $data
	 * @return boolean
	 */
	public function mq_sync_login($data)
	{
		return $this->mq_sync_reg($data);
	}
	
	/**
	 * 同步注册逻辑
	 * @param array $data
	 * @return boolean
	 */
	public function mq_sync_reg($data)
	{
		$res = $data[$this->argName];
		
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
				$nPUser->ecsalt   = gen_salt();
				$nPUser->salt     = $nPUser->ecsalt;
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
		}
		
		//~ 处理当前用户
		$passwd_raw = '';
		if (!empty($res['passwd'])) {
			$passwd_raw = AuthCode::decrypt($res['passwd'], Eqxmq_Model::AUTH_KEY_5);
		}
		$cUser = Users::find_eqx_user($res['guid'], $res['mobile']);
		if ($cUser->is_exist()) { //已存在，更新部分信息
			
			if (!$cUser->parentid || !$cUser->password) { //只有上级为空，或者密码为空时才会去更新
				$nUser = new Users($cUser->id);
				if (''!=$passwd_raw) {
					$passwd_enc = gen_salt_password($passwd_raw, $cUser->salt);
					$nUser->password = $passwd_enc;
				}
				if (!$cUser->parentid) {
					$nUser->parentid    = $parent_id;
					$nUser->parentnick  = $parent_nick;
					$nUser->guid        = $res['guid'];
					$nUser->parent_guid = $res['parent_guid'];
				}
				$nUser->save(Storage::SAVE_UPDATE);
			}

		}
		else { //不存在，新增用户
			$salt = gen_salt();
			$passwd_enc = !empty($passwd_raw) ? gen_salt_password($passwd_raw, $salt) : '';
			
			$nUser = new Users();
			$nUser->mobile   = $res['mobile'];
			$nUser->nickname = base64url_decode($res['nick']);
			$nUser->password = $passwd_enc; //这时密码"可能"为空
			$nUser->logo     = $res['logo'];
			$nUser->sex      = $res['gender'];
			$nUser->regip    = Request::ip();
			$nUser->regtime  = simphp_time();
			$nUser->ecsalt   = $salt;
			$nUser->salt     = $nUser->ecsalt;
			$nUser->parentid   = $nUser->parentid;
			$nUser->parentnick = $nUser->parentnick;
			$nUser->guid     = $res['guid'];
			$nUser->parent_guid = $res['parent_guid'];
			$nUser->from_sync= 1;
			$nUser->from     = 'reg_sync';
			$nUser->randver  = randstr(6);
			$nUser->save(Storage::SAVE_INSERT_IGNORE);
		}
		
		return true;
	}
	
	/**
	 * 同步修改上级
	 * @param array $data
	 * @return boolean
	 */
	public function mq_sync_parent($data)
	{
		$res = $data[$this->argName];
		
		$cUser = Users::find_eqx_user($res['guid'], $res['mobile']);
		if (!$cUser->is_exist()) { //用户不存在，则忽略该消息请求
			return false;
		}
		
		$pUser = Users::find_eqx_user($res['parent_guid'], $res['parent_mobile']);
		if (!$pUser->is_exist()) { //如果提供的上级用户也不存在，也忽略该消息请求
			return false;
		}
		
		// 到这里可以强制覆盖上级了
		$pinfo = Users::get_parent_ids($pUser->uid, TRUE);
		D()->update(Users::table(), $pinfo, ['user_id'=>$cUser->uid]);
		
		return true;
	}
	
	/**
	 * 同步修改密码
	 * @param array $data
	 * @return boolean
	 */
	public function mq_sync_passwd($data)
	{
		$res = $data[$this->argName];
		
		$cUser = Users::find_eqx_user($res['guid'], $res['mobile']);
		if (!$cUser->is_exist()) { //用户不存在，则忽略该消息请求
			return false;
		}
		
		// 到这里可以强制覆盖密码了
		$passwd_raw = AuthCode::decrypt($res['passwd'],Eqxmq_Model::AUTH_KEY_5);
		$passwd_enc = gen_salt_password($passwd_raw, $cUser->salt);
		D()->update(Users::table(), ['password'=>$passwd_enc,'salt'=>$salt], ['user_id'=>$cUser->uid]);
		
		return true;
	}
	
	/**
	 * 响应mq消息
	 * @param number $http_code
	 * @param string $http_msg
	 */
	public function response_mq($http_code = 204, $http_msg = '') {
		http_response_code($http_code);
		echo $http_msg;
		//保存日志
		$this->logData['respCode'] = $http_code;
		$this->logData['respMsg']  = $http_msg;
		$this->logData['dealTime'] = simphp_msec()-$this->logData['reqTime'];
		D()->insert('{eqxmq_log}', $this->logData);
		exit;
	}

}

 
/*----- END FILE: Eqxmq_Controller.php -----*/