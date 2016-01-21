<?php
/**
 * TymUser公用Model
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
include_once (SIMPHP_INCS . '/libs/ApiRequest/class.ApiRequest.php');

class TymUser extends StorageNode {
	
	const APP_ID = 'app567528488ae7e'; //甜玉米APPID
	const MIGRATE_DEADLINE = '2016-01-22 20:00:00'; //关闭封闭平移时间点
	const QUERY_URL = 'http://98tym.com/z/m/ydmm!gettymusersById.jsp?userid=%d'; //甜玉米查询
	
	protected static function meta() {
		return array(
				'table' => '{tym_user}',
				'key'   => 'userid',
				'columns' => array(
						'userid'       => 'userid',
						'mobile'       => 'mobile',
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
	 * 
	 * @param integer $userid
	 * @param string $mobile
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
	static function composeData($userid, $mobile, $openid, $regtime, $nick, $logo, $qrcode, $business_id, $business_time, $parent_userid = 0)
	{
		return [
				'userid'      => $userid,
				'mobile'      => $mobile,
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
	 * @return number|Ambigous <boolean, number, unknown>
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
	
}
 
/*----- END FILE: class.TymUser.php -----*/