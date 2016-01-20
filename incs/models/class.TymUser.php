<?php
/**
 * TymUser公用Model
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
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
	
}
 
/*----- END FILE: class.TymUser.php -----*/