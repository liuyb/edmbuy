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
				    'user_id'     => 'user_id',
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
				    'is_completed' => 'is_completed',
				    'shop_qcode' => 'shop_qcode'
				)
		);
	}
	
	static function getMerchantByUserId($user_id){
	    return Merchant::find_one(new Query('user_id', $user_id));
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
	 * 获取店铺不同状态订单数量
	 * @param unknown $status
	 * @param unknown $muid
	 * @return mixed
	 */
	static function getOrderTotalByStatus($status, $muid){
	    $where = '';
	    $statusSql = Order::build_order_status_sql(intval($status), 'o');
	    if($statusSql){
	        $where .= $statusSql;
	    }
	    $sql = "SELECT count(1) FROM shp_order_info o where merchant_ids='%s' and is_separate = 0 and is_delete = 0 $where ";
	    return D()->query($sql, $muid)->result();
	}
	
	/**
	 * 等待退款订单数量
	 * @param unknown $muid
	 * @return mixed
	 */
	static function getWaitRefundOrderTotal($muid){
	    $sql = "SELECT count(1) FROM shp_order_refund where merchant_id='%s' and check_status = 0";
	    return D()->query($sql, $muid)->result();
	}
	
	/**
	 * 根据店铺商品状态获取商品数量
	 * @param unknown $status
	 * @param unknown $muid
	 * @return mixed
	 */
	static function getGoodsTotalByIsSale($status, $muid){
	    $where = '';
	    if ($status >= 0){
	        $where = " and is_on_sale=".intval($status);
	    }
	    $sql = "select count(1) from shp_goods where merchant_id='%s' and is_delete=0 $where";
	    return D()->query($sql, $muid)->result();
	}
	
	/**
	 * 商品店铺库存预警数量
	 * @param unknown $muid
	 * @param unknown $warn_number
	 * @return mixed
	 */
	static function getGoodsNumberWarning($muid, $warn_number){
	    $sql = "select count(1) from shp_goods where merchant_id='%s' and is_delete=0 and goods_number < '%d'";
	    return D()->query($sql, $muid, (intval($warn_number) + 1))->result();
	}
	
	/**
	 * 获取首页显示的店铺相关信息
	 * @param unknown $muid
	 */
	static function getShopInfo($muid){
	    $sql = "select m.facename as facename,m.logo as logo,p.end_time as end_time from shp_merchant m left join shp_merchant_payment p on m.merchant_id = p.merchant_id
                where m.merchant_id='%s' order by p.end_time desc limit 1";
	    $result = D()->query($sql, $muid)->fetch_array();
	    return $result;
	}
	
	/**
	 * 店铺销售总额
	 * @param unknown $muid
	 * @return mixed
	 */
	static function getOrderSalesMoney($muid){
	    $sql = "SELECT ifnull(sum(money_paid), 0) as salesTotal from shp_order_info where merchant_ids='%s' and is_separate=0 and is_delete=0 and pay_status = ".PS_PAYED."";
	    return D()->query($sql, $muid)->result();
	}
	
	/**
	 * 获取店铺收藏次数
	 * @param unknown $muid
	 */
	static function getShopCollects($muid){
	    $sql = "select count(1) from shp_collect_shop where merchant_id = '%s'";
	    return D()->query($sql, $muid)->result();
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