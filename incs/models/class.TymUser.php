<?php
/**
 * TymUser公用Model
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
include_once (SIMPHP_INCS . '/libs/ApiRequest/class.ApiRequest.php');

class TymUser extends StorageNode {
	
	const APP_ID = 'app567528488ae7e'; //甜玉米APPID
	const MIGRATE_DEADLINE = '2016-01-25 00:00:00'; //关闭封闭平移时间点
	const QUERY_URL = 'http://98tym.com/z/m/ydmm!gettymusersById.jsp?userid=%d'; //甜玉米查询
	
	protected static function meta() {
		return array(
				'table' => '{tym_user}',
				'key'   => 'userid',
				'columns' => array(
						'userid'       => 'userid',
						'mobile'       => 'mobile',
						'unionid'      => 'unionid',
						'openid'       => 'openid',
						'regtime'      => 'regtime',
						'nick'         => 'nick',
						'logo'         => 'logo',
						'qrcode'       => 'qrcode',
						'business_id'  => 'business_id',
						'business_time'=> 'business_time',
						'parent_userid'=> 'parent_userid',
						'synctimes'    => 'synctimes',
				)
		);
	}
	
	/**
	 * 根据cid请求甜玉米接口查询用户数据
	 * @param integer $cid
	 */
	static function query($cid) {
		if (empty($cid) || !is_numeric($cid)) return false;
		$tym_url = sprintf(self::QUERY_URL, $cid);
		
		$req = new ApiRequest(['method'=>'get','timeout_connect'=>3,'timeout'=>5]);
		try {
			$ret = $req->setUrl($tym_url)->send()->recv(TRUE);
			return $ret;
		}
		catch(ApiRequestException $e) {
			//Response::dump($e->getMessage());
			return false;
		}
		
	}
	
	/**
	 * 请求甜玉米接口查询用户数据，并且保存到本地库
	 * @param integer $cid
	 * @return TymUser
	 */
	static function queryAndSave($cid) {
		$ret = self::query($cid);
		if (!empty($ret)) { //有可能调用失败
			$parent_userid = isset($ret['reguser']) && isset($ret['reguser']['userid']) ? $ret['reguser']['userid'] : 0;
			$state = self::saveUser(self::composeData($ret['userid'], $ret['mobile'], $ret['unionid'], $ret['openid'], $ret['regtime'], $ret['nick'], $ret['picUrl'], $ret['qrcode'], $ret['business_id'], $ret['business_time'], $parent_userid));
			if ($state && $parent_userid) {
				$regu = $ret['reguser'];
				$state = self::saveUser(self::composeData($parent_userid, $regu['mobile'], $ret['unionid'], $regu['openid'], $regu['regtime'], $regu['nick'], $regu['picUrl'], $regu['qrcode'], $regu['business_id'], $regu['business_time']));
			}
			$appUser = self::load($cid,TRUE); //刷新读取
		}
		else {
			$appUser = new self(); //返回一个空对象
		}
		return $appUser;
	}
	
	/**
	 * 
	 * @param integer $userid
	 * @param string $mobile
	 * @param string $unionid
	 * @param string $openid
	 * @param string $regtime
	 * @param string $nick
	 * @param string $logo
	 * @param string $qrcode
	 * @param string $business_id
	 * @param string $business_time
	 * @param number $parent_userid
	 * @return array
	 */
	static function composeData($userid, $mobile, $unionid, $openid, $regtime, $nick, $logo, $qrcode, $business_id, $business_time, $parent_userid = 0)
	{
		return [
				'userid'      => $userid,
				'mobile'      => $mobile,
				'unionid'     => $unionid,
				'openid'      => $openid,
				'regtime'     => $regtime,
				'nick'        => $nick,
				'logo'        => $logo,
				'qrcode'      => $qrcode,
				'business_id' => $business_id,
				'business_time' => $business_time,
				'parent_userid' => $parent_userid ? : 0,
		];
	}
	
	/**
	 * 保存甜玉米用户数据
	 * @param array $data
	 * @param number $synctimes
	 * @return number|boolean
	 */
	static function saveUser(Array $data, &$synctimes = 0) {
		$userid = isset($data['userid']) ? $data['userid'] : 0;
		if (empty($userid)) return 0;
	
		$table = self::table();
		D()->realtime_query = TRUE;
		$row = D()->query("SELECT * FROM {$table} WHERE `userid`=%d", $userid)->get_one();
		if (empty($row)) { //未存在，插入
			$effrows = D()->insert($table, $data, false);
			$synctimes = 0;
		}
		else { //已存在，更新可能要更新的数据
			if (isset($data['userid'])) unset($data['userid']);
			if (isset($data['regtime'])) unset($data['regtime']);
			if (isset($data['synctimes'])) unset($data['synctimes']);
			if (!empty($row['parent_userid']) && isset($data['parent_userid'])) unset($data['parent_userid']);
			$effrows = D()->update($table, $data, ['userid' => $userid]);
			$synctimes = $row['synctimes'];
		}
		return $effrows ? $userid : false;
	}
	
	public function updateSynctimes($value = 1) {
		D()->query("UPDATE ".self::table()." SET synctimes=%d WHERE userid=%d", $value, $this->id);
	}
	
	public function incSynctimes($inc = 1) {
		D()->query("UPDATE ".self::table()." SET synctimes=synctimes+%d WHERE userid=%d", $inc, $this->id);
	}
	
}
 
/*----- END FILE: class.TymUser.php -----*/