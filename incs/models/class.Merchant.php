<?php
/**
 * Merchant Model
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Merchant extends StorageNode {
	
    const VERIFY_UNDO = 0; //未认证
    
    const VERIFY_CHECKING = 1; //待审核
    
    const VERIFY_SUCC = 2; //认证成功
    
    const VERIFY_FAIL = 3; //认证失败
    
    const MERCHANT_TYPE_PERSON = 1; //个人商家
    
    const MERCHANT_TYPE_EMPLOY = 2; //企业商家
    
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
				    'shop_qcode' => 'shop_qcode',
				    'verify_fail_msg' => 'verify_fail_msg',
				    'merchant_type' => 'merchant_type',
				    'activation' => 'activation',
				    'invite_code' => 'invite_code',
				    'recommed_flag' => 'recommed_flag'
				)
		);
	}
	
	static function getMerchantByUserId($user_id){
	    return Merchant::find_one(new Query('user_id', $user_id));
	}
	
	/**
	 * 用户是否已经有激活的店铺
	 * @param unknown $user_id
	 */
	static function userHasOpendShop($user_id){
	    $sql = "select count(1) from shp_merchant where user_id = %d and activation = 1 ";
	    $result = D()->query($sql, $user_id)->result();
	    return $result;
	}
	
	static function getNameByAdminUid($admin_uid) {
		$ret = D()->from(self::table())->where("admin_uid=%d", $admin_uid)->select("facename")->result();
		return $ret;
	}
	
	static function getNameByMerchantId($user_id) {
	    $ret = D()->from(self::table())->where("user_id='%d'", $user_id)->select("facename")->result();
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
	    $sql = "SELECT count(1) FROM shp_order_info o where merchant_ids='%s' and is_separate = 0 and is_removed = 0 $where ";
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
	    $sql = "select m.facename as facename,m.logo as logo,p.end_time as end_time,m.verify from shp_merchant m 
	            left join shp_merchant_payment p on m.merchant_id = p.merchant_id
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
	    $sql = "SELECT ifnull(sum(money_paid), 0)+ifnull(sum(order_amount), 0) as salesTotal from shp_order_info where merchant_ids='%s' and is_separate=0 and pay_status = ".PS_PAYED."";
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
	 * 商家列表 键值对
	 */
	static function getMerchantsKeyValue(){
	    $sql = "SELECT merchant_id, facename FROM shp_merchant where activation = 1 and facename <> '' order by created desc";
	    return D()->query($sql)->fetch_array_all();
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
	
	/**
	 * 生成订单的时候生成一条商家支付信息，money_paid 为 0
	 * @param unknown $merchant_id
	 * @param unknown $order_id
	 * @param unknown $order_sn
	 */
	static function addMerchantPayment($merchant_id, $user_id, $newOrder)
	{
	    $tablename = "`shp_merchant_payment`";
	    $setarr['order_id'] = $newOrder->order_id;
	    $setarr['order_sn'] = $newOrder->order_sn;
	    $setarr['money_paid'] = 0;
	    $setarr['merchant_id'] = $merchant_id;
	    $setarr['start_time'] = date("Y-m-d H:i:s", time()).'';
	    $endDate = date("Y-m-d", strtotime("+1 year", time()))." 23:59:59";
	    $setarr['end_time'] = $endDate;
	    $setarr['term_time'] = '1y';
	    $setarr['discount'] = $newOrder->discount;
	    $setarr['goods_amount'] = $newOrder->goods_amount;
	    $setarr['order_amount'] = $newOrder->order_amount;
	    $setarr['user_id'] = $user_id;
	    D()->insert($tablename, $setarr, true, 'IGNORE');
	}
	
	/**
	 * 当当前订单为购买店铺订单时，更新店铺流水 
	 * @param unknown $order_id
	 * @param unknown $money_paid
	 */
	static function setPaymentIfIsMerchantOrder($order_id, $money_paid){
	    $sql = "select rid,merchant_id,user_id from shp_merchant_payment where order_id = %d";
	    $result = D()->query($sql, $order_id)->get_one();
	    if($result && $result['rid']){
	        $tablename = "`shp_merchant_payment`";
	        $setarr['rid'] = $result['rid'];
	        $setarr['money_paid'] = $money_paid;
	        $setarr['paid_time'] = time();
	        D()->update($tablename, $setarr, array('order_id' => $order_id));
	        
	        //激活商家
	        $mid = $result['merchant_id'];
	        $mch = new Merchant();
	        $mch->uid = $mid;
	        $mch->activation = 1;
	        $mch->save(Storage::SAVE_UPDATE);
	        
	        //用户级别更新为商家
	        $user = new Users();
	        $user->uid = $result['user_id'];
	        $user->level = Users::USER_LEVEL_5;
	        $user->save(Storage::SAVE_UPDATE_IGNORE);
	        
	        //如果是购买店铺，直接设置成已收货
	        Order::setOrderShippingReceived($order_id);
	        return true;
	    }
	    return false;
	}
	
	/**
	 * 检验当前商家是否已经支付
	 */
	static function checkIsPaySuc($is_merchant = false){
	    $user_id = $GLOBALS['user']->uid;
	    $where = '';
	    if($is_merchant){
	        $where .= " and merchant_id = '%s' ";
	    }else{
	        $where .= ' and user_id = %d ';
	    }
	    $time =date('Y-m-d H:i:s' ,time());
	    $sql = "select count(1) from shp_merchant_payment where 1 $where and start_time <= '{$time}' and end_time >='{$time}' and money_paid > 0";
	    return D()->query($sql,$user_id)->result();
	}
	
	/**
	 * 拿商家客服ID，如果不存在，返回益多米的客服ID
	 * @param unknown $merchant_id
	 */
	static function getMerchantKefu($merchant_id){
	    $ent_id = self::getMerchantKefuEntId($merchant_id);
	    if(!$ent_id){
	        $ent_id = C('api.meiqia_edmbuy.edm_ent_id');
	    }
	    return $ent_id;
	}
	
	/**
	 * 获取商家客服ID
	 * @param unknown $merchant_id
	 */
	static function getMerchantKefuEntId($merchant_id){
	    $sql = "select ent_id from shp_merchant_kefu where merchant_id = '$merchant_id' ";
	    return D()->query($sql)->result();
	}
	
	/**
	 * 根据订单返回对应的商家，父订单有多个商家
	 * @param unknown $order
	 */
	static function getMerchantByOrder($order){
	    if(!$order->is_separate){
	        return array($order->merchant_ids => self::load($order->merchant_ids));
	    }
	    $ids = explode(',', $order->merchant_ids);
	    $ms = [];
	    foreach ($ids as $id){
	        $ms[$id] = self::load($id);
	    }
	    return $ms;
	}
	
	/**
	 * 店铺收藏/取消收藏
	 * @param unknown $merchant_id
	 * @param unknown $action
	 */
	static function collectShop($merchant_id, $action, $user_id = 0){
	    if(!$action){
	        return;
	    }
	    if(!$user_id){
    	    $user_id =$GLOBALS['user']->uid;
	    }
	    if($action < 0){
	        $sql = "delete from shp_collect_shop where user_id=%d and merchant_id = '%s'";
	        D()->query($sql,$user_id,$merchant_id);
	    }else{
	        $sql="select count(1) from shp_collect_shop where user_id = %d AND merchant_id ='%s'";
	        $result = D()->query($sql,$user_id,$merchant_id)->result();
	        if($result && $result > 0){
	            return;
	        }
	        $tablename = "`shp_collect_shop`";
	        $insertarr['merchant_id'] = $merchant_id;
	        $insertarr['user_id'] = $user_id;
	        $insertarr['add_time'] = time();
	        D()->insert($tablename,$insertarr,false,'IGNORE');
	    }
	}
}
 
/*----- END FILE: class.Merchant.php -----*/