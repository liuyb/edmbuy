<?php
/**
 * Merchant Model
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Merchant extends StorageNode {
	
	protected static function meta() {
		return array(
				'table' => '`shp_merchant`',
				'key'   => 'uid',
				'columns' => array(
					'uid'         => 'merchant_id',
					'idname'      => 'idname',
					'facename'    => 'facename',
					'password'    => 'password',
					'salt'        => 'salt',
					'email'       => 'email',
					'mobile'      => 'mobile',
					'telphone'    => 'telphone',
					'logo'        => 'logo',
					'wxqr'        => 'wxqr',
					'kefu'        => 'kefu',
					'slogan'      => 'slogan',
					'country'     => 'country',
					'province'    => 'province',
					'city'        => 'city',
					'district'    => 'district',
					'address'     => 'address',
					'mainbody'    => 'mainbody',
					'role_id'     => 'role_id',
					'verify'      => 'verify',
					'lastlogin'   => 'last_login',
					'lastip'      => 'last_ip',
					'created'     => 'created',
					'changed'     => 'changed',
					'admin_uid'   => 'admin_uid',
				    'shop_desc' => 'shop_desc',
				    'business_scope' => 'business_scope',
				    'shop_template' => 'shop_template',
				    'is_completed' => 'is_completed'
				)
		);
	}
	
	static function getNameByAdminUid($admin_uid) {
		$ret = D()->from(self::table())->where("admin_uid=%d", $admin_uid)->select("facename")->result();
		return $ret;
	}
	
	static function getMidByAdminUid($admin_uid) {
		$ret = D()->from(self::table())->where("admin_uid=%d", $admin_uid)->select("merchant_id")->result();
		return $ret;
	}
	
	/**
	 * 经营范围列表 固定数据，采用静态缓存
	 */
	static function getBusinessScope(){
	
	    $data = Fn::read_static_cache('business_category_data');
	    if ($data === false){
	        $sql = "select cat_id,cat_name from shp_business_category order by sort_order desc ";
	        $res = D()->query($sql)->fetch_array_all();
	        //如果数组过大，不采用静态缓存方式
	        if (count($res) <= 1000){
	            Fn::write_static_cache('business_category_data', $res);
	        }
	    }else{
	        $res = $data;
	    }
	    return $res;
	}
	
	/**
	 * Check whether user logined
	 * @return boolean
	 */
	static function is_logined() {
		return $GLOBALS['user']->uid ? true : false;
	}
	
	/**
	 * Set the current user to 'logined' status
	 */
	public function set_logined_status() {
	
		//设置登录session uid
		$GLOBALS['user']->uid = $this->uid;
		
		//重新变更session id
		SimPHP::$session->regenerate_id();
		
		//新起一个对象来编辑，避免过多更新
		$nUser = new self($this->uid);
		$nUser->lastlogin = simphp_time();
		$nUser->lastip    = Request::ip();
		$nUser->save();
		
	}
	
}
 
/*----- END FILE: class.Merchant.php -----*/