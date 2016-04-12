<?php
/**
 * Storage Node Model
 *
 * @author Jean
 */
defined('IN_SIMPHP') or die('Access Denied');

class Order extends StorageNode{
    
    protected static function meta() {
        return array(
            'table' => '`shp_order_info`',
            'key'   => 'order_id',
            'columns' => array(
                'order_id'           => 'order_id',
                'order_sn'           => 'order_sn',
                'pay_trade_no'       => 'pay_trade_no',
                'user_id'            => 'user_id',
                'order_status'       => 'order_status',
                'shipping_status'    => 'shipping_status',
                'pay_status'         => 'pay_status',
                'consignee'          => 'consignee',
                'country'            => 'country',
                'province'           => 'province',
                'city'               => 'city',
                'district'           => 'district',
                'address'            => 'address',
                'zipcode'            => 'zipcode',
                'tel'                => 'tel',
                'mobile'             => 'mobile',
                'email'              => 'email',
                'best_time'          => 'best_time',
                'sign_building'      => 'sign_building',
                'postscript'         => 'postscript',
                'shipping_id'        => 'shipping_id',
                'shipping_name'      => 'shipping_name',
                'pay_id'             => 'pay_id',
                'pay_name'           => 'pay_name',
                'how_oos'            => 'how_oos',
                'how_surplus'        => 'how_surplus',
                'pack_name'          => 'pack_name',
                'card_name'          => 'card_name',
                'card_message'       => 'card_message',
                'inv_payee'          => 'inv_payee',
                'inv_content'        => 'inv_content',
                'goods_amount'       => 'goods_amount',
                'shipping_fee'       => 'shipping_fee',
                'insure_fee'         => 'insure_fee',
                'pay_fee'            => 'pay_fee',
                'pack_fee'           => 'pack_fee',
                'card_fee'           => 'card_fee',
                'money_paid'         => 'money_paid',
                'surplus'            => 'surplus',
                'integral'           => 'integral',
                'integral_money'     => 'integral_money',
                'bonus'              => 'bonus',
                'order_amount'       => 'order_amount',
                'commision'          => 'commision',
                'from_ad'            => 'from_ad',
                'referer'            => 'referer',
                'add_time'           => 'add_time',
                'confirm_time'       => 'confirm_time',
                'pay_time'           => 'pay_time',
                'shipping_time'      => 'shipping_time',
                'shipping_confirm_time' => 'shipping_confirm_time',
                'pack_id'            => 'pack_id',
                'card_id'            => 'card_id',
                'bonus_id'           => 'bonus_id',
                'invoice_no'         => 'invoice_no',
                'extension_code'     => 'extension_code',
                'extension_id'       => 'extension_id',
                'to_buyer'           => 'to_buyer',
                'pay_note'           => 'pay_note',
                'agency_id'          => 'agency_id',
                'inv_type'           => 'inv_type',
                'tax'                => 'tax',
                'is_separate'        => 'is_separate',
                'parent_id'          => 'parent_id',
                'discount'           => 'discount',
                'merchant_ids'       => 'merchant_ids',
                'pay_data1'          => 'pay_data1',
                'pay_data2'          => 'pay_data2'
            ));
    }
    
    /**
     * 检查支付付款与原来订单金额是否一致
     * @param string $order_sn 订单号
     * @param integer $money 金钱(以分为单位)
     * @return boolean
     */
    static function check_paid_money($order_sn, $money) {
    
    	$ectb = self::table();
    	$order_amount = D()->raw_query("SELECT `order_amount` FROM {$ectb} WHERE `order_sn`='%s'", $order_sn)->result();
    	if (empty($order_amount)) {
    		return false;
    	}
    	$order_amount = intval($order_amount*100);
    
    	return $order_amount===$money ? true : false;
    }
    
    /**
     * 根据订单号获取订单中跟支付相关的信息
     * @param string $order_sn
     */
    static function get_order_paid_info($order_sn) {
    	$ectb = self::table();
    	$row  = D()->raw_query("SELECT `order_id`,`order_sn`,`user_id`,`order_status`,`shipping_status`,`pay_status`,`pay_id`,`order_amount` FROM {$ectb} WHERE `order_sn`='%s'", $order_sn)
    	           ->get_one();
    	return !empty($row) ? $row : [];
    }
    
    /**
     * 插入订单动作日志
     */
    static function action_log($order_id, Array $insert_data) {
    	if (empty($order_id)) return false;
    	$oinfo = D()->get_one("SELECT `order_id`,`order_status`,`shipping_status`,`pay_status` FROM ".self::table()." WHERE `order_id`=%d", $order_id);
    	$init_data = [
    			'order_id'       => $order_id,
    			'action_user'    => 'buyer',
    			'order_status'   => $oinfo['order_status'],
    			'shipping_status'=> $oinfo['shipping_status'],
    			'pay_status'     => $oinfo['pay_status'],
    			'action_place'   => 0,
    			'action_note'    => '',
    			'log_time'       => simphp_gmtime(),
    	];
    	$insert_data = array_merge($init_data, $insert_data);
    	 
    	$rid = D()->insert(OrderAction::table(), $insert_data);
    	return $rid;
    }
    
    /**
     * 取消订单
     *
     * @param integer $order_id
     * @return boolean
     */
    static function cancel($order_id, $action_note = '用户取消') {
    	if (!$order_id) return false;
        
    	D()->update(self::table(), ['order_status'=>OS_CANCELED,'pay_status'=>PS_CANCEL], ['order_id'=>$order_id]);
    
    	if (D()->affected_rows()==1) {
    
    		//还要将对应的库存加回去
    		$order_goods = Order::getItems($order_id);
    		if (!empty($order_goods)) {
    			foreach ($order_goods AS $g) {
    				Items::changeStock($g['goods_id'],$g['goods_number']);
    			}
    		}
    
    		//写order_action的日志
    		self::action_log($order_id, ['action_note'=>$action_note]);
    
    		return true;
    	}
    	return false;
    }
    
    /**
     * 变更订单状态
     *
     * @param integer $order_id
     * @param integer $status_to
     * @param integer $user_id
     * @param integer $no_equal_status 不等的状态
     * @return boolean
     */
    static function change_paystatus($order_id, $status_to, $user_id = NULL, $no_equal_status = -1) {
    	if (!$order_id) return false;
    
    	$updata = ['pay_status'=>$status_to];
    	$where  = 'order_id='.intval($order_id);
    	if ($status_to==PS_PAYED) {
    		$updata['pay_time'] = simphp_gmtime();
    	}
    	if (!empty($user_id)) {
    		$where  .= ' AND user_id='.intval($user_id);
    	}
    	if ($no_equal_status >=0 ) {
    		$where  .= ' AND pay_status<>'.intval($no_equal_status);
    	}
    	D()->update(self::table(), $updata, $where);
    
    	if (D()->affected_rows()==1) {
    		return true;
    	}
    	return false;
    }
    
    /**
     * 确认订单收货
     *
     * @param integer $order_id
     * @param integer $user_id
     * @return boolean
     */
    static function confirm_shipping($order_id, $user_id = NULL) {
    	if (!$order_id) return false;
    
    	$where = ['order_id'=>$order_id];
    	if (!empty($user_id)) {
    		$where['user_id'] = $user_id;
    	}
    	D()->update(self::table(),
    	            ['order_status'=>OS_CONFIRMED,'shipping_status'=>SS_RECEIVED,'shipping_confirm_time'=>simphp_gmtime()],
    	            $where);
    
    	if (D()->affected_rows()==1) {
    
    		//写order_action的日志
    		self::action_log($order_id, ['action_note'=>$user_id ? "用户确认收货(UID={$user_id})" : "系统确认收货"]);
    
    		return true;
    	}
    	return false;
    }
    
    /**
     * 获取一个订单下的商品列表
     *
     * @param integer $order_id
     * @param integer $merchant_uid
     * @return array
     */
    static function getItems($order_id, $merchant_uid = 0) {
    	if (empty($order_id)) return [];
    
    	$ectb_goods = Items::table();
    	$ectb_order_goods = OrderItems::table();
    
    	$where = '';
    	if ($merchant_uid) {
    		$where = " AND g.merchant_uid=%d";
    	}
    	$sql = "SELECT og.*,g.`goods_thumb`,g.`commision` FROM {$ectb_order_goods} og INNER JOIN {$ectb_goods} g ON og.`goods_id`=g.`goods_id` WHERE og.`order_id`=%d {$where} ORDER BY og.`rec_id` DESC";
    	$order_goods = D()->raw_query($sql, $order_id, $merchant_uid)->fetch_array_all();
    	if (!empty($order_goods)) {
    		foreach ($order_goods AS &$g) {
    			$g['goods_url']   = Items::itemurl($g['goods_id']);
    			$g['goods_thumb'] = Items::imgurl($g['goods_thumb']);
    		}
    	}
    	else {
    		$order_goods = [];
    	}
    
    	return $order_goods;
    }
    
    /**
     * 获取一个订单下的商品列表(仅获取shp_order_goods表中的数据)
     *
     * @param integer $order_id
     * @return array
     */
    static function getTinyItems($order_id) {
    	if (empty($order_id)) return [];
    
    	$ectb_order_goods = OrderItems::table();
    	$sql = "SELECT og.* FROM {$ectb_order_goods} og WHERE og.`order_id`=%d ORDER BY og.`rec_id` DESC";
    	$order_goods = D()->raw_query($sql, $order_id)->fetch_array_all();
    	if (!empty($order_goods)) {
    		foreach ($order_goods AS &$g) {
    			$g['goods_url']   = Items::itemurl($g['goods_id']);
    		}
    	}
    	else {
    		$order_goods = [];
    	}
    
    	return $order_goods;
    }
    
    /**
     * 关联订单和商家
     * @param integer $order_id
     * @param integer $merchant_uid
     * @param string  $merchant_id
     * @return number
     */
    static function relateMerchant($order_id, $merchant_uid, $merchant_id = '') {
    	$order  = Order::load($order_id);
    	$admUsr = AdminUser::load($merchant_uid);
    	if ( $order->is_exist() && $admUsr->is_exist() ) {
    		if (!$merchant_id) $merchant_id = $admUsr->merchant_id;
    		D()->query("INSERT IGNORE INTO `shp_order_merchant`(`order_id`,`merchant_uid`,`merchant_id`) VALUES(%d, %d, '%s')", $order_id, $merchant_uid, $merchant_id);
    		
    		$old_merchant_ids = $order->merchant_ids;//$merchant_uid
    		$new_merchant_ids = $old_merchant_ids;
    		if (empty($old_merchant_ids)) {
    			$new_merchant_ids = $admUsr->merchant_id;
    		}
    		elseif(strpos($old_merchant_ids, $admUsr->merchant_id)===false) {
    			$new_merchant_ids = $old_merchant_ids.','.$admUsr->merchant_id;
    		}
    		if ($new_merchant_ids != $old_merchant_ids) {
    			$upOrder = new self($order_id);
    			$upOrder->merchant_ids = $new_merchant_ids;
    			$upOrder->save(Storage::SAVE_UPDATE);
    		}
    	}
    	return false;
    }
    
    /**
     * 根据主订单生成多个子订单，如果关联多个商家的话
     * @param integer $master_order_id
     * @param array $merchant_uids
     */
    static function genSubOrder($master_order_id, Array $merchant_uids) {
    	if (count($merchant_uids) < 2) { //一个订单只有一个商家uid不需要分单
    		return false;
    	}
    	$master_order = self::load($master_order_id);
    	if ($master_order->is_exist()) {
    		foreach ($merchant_uids AS $m_uid) {
    			$subOrder = $master_order->clone_one();
    			$subOrder->order_sn = Fn::gen_order_no();
    			$subOrder->goods_amount = 0;
    			$subOrder->money_paid   = 0;
    			$subOrder->order_amount = 0;
    			$subOrder->commision    = 0;
    			$subOrder->is_separate  = 0;
    			$subOrder->parent_id    = $master_order_id;
    			$subOrder->merchant_ids = Merchant::getMidByAdminUid($m_uid);
    			$subOrder->save(Storage::SAVE_INSERT); //先生成一个克隆子订单
    			
    			if ($subOrder->id) {
    				$orderIts = self::getItems($master_order_id, $m_uid);
    				$goods_amount = 0;
    				$order_amount = 0;
    				$commision    = 0;
    				foreach ($orderIts AS $oit) {
    					
    					$OI = OrderItems::find_one(new AndQuery(new Query('order_id', $master_order_id),new Query('goods_id', $oit['goods_id'])));
    					if (!$OI->is_exist()) continue;
    					
    					$newOI = $OI->clone_one();
    					$newOI->order_id    = $subOrder->id;
    					$newOI->parent_id   = $master_order_id;
    					$newOI->save(Storage::SAVE_INSERT); //循环生成“订单-商品”关联记录
    					
    					$goods_amount += $oit['goods_price'] * $oit['goods_number'];
    					$commision    += $oit['commision'] * $oit['goods_number'];
    				}

    				$order_amount = $goods_amount + $master_order->shipping_fee; //TODO: 邮费这里以后要处理的
    				$order_update = [];
    				$order_update['goods_amount'] = $goods_amount;
    				$order_update['order_amount'] = $order_amount;
    				$order_update['commision']    = $commision;
    				if (!empty($order_update)) {
    					D()->update(self::table(), $order_update, ['order_id'=>$subOrder->id]);
    				}
    				
    				//paylog也要生成(因为客户可能后期单独去付款)
    				PayLog::insert($subOrder->id, $subOrder->order_sn, $order_amount, PAY_ORDER);
    				
    				//将上级is_separate设为1(已分单)
    				D()->update(self::table(), ['is_separate'=>1], ['order_id'=>$master_order_id]);
    				
    				//关联订单和商家ID
    				self::relateMerchant($subOrder->id, $m_uid);
    				
    			} //END if ($subOrder->id)
    		} //END foreach ($merchant_uids AS $m_uid)
    	} //END if ($master_order->is_exist())
    	return false;
    }
    
    /**
     * 获取一个订单下的商品列表 并附加商家信息
     * @param unknown $order_id
     */
    static function getOrderItems($order_id, $merchant_uid = 0) {
        if (empty($order_id)) return [];
    
        $ectb_goods = Items::table();
        $ectb_order_goods = OrderItems::table();
        $ectb_merchant = Merchant::table();
        $where = '';
        if ($merchant_uid) {
        	$where = " AND g.merchant_uid=%d";
        }

        $sql = "SELECT og.*,g.`goods_thumb`,g.`commision`,m.facename,m.merchant_id,m.mobile,m.telphone,m.kefu
                FROM {$ectb_order_goods} og INNER JOIN {$ectb_goods} g ON og.`goods_id`=g.`goods_id` 
                LEFT JOIN {$ectb_merchant} m on g.merchant_uid = m.admin_uid 
                WHERE og.`order_id`=%d {$where}
                ORDER BY og.`rec_id` DESC";
        $order_goods = D()->raw_query($sql, $order_id, $merchant_uid)->fetch_array_all();
        if (!empty($order_goods)) {
            foreach ($order_goods AS &$g) {
                $g['goods_url']   = Items::itemurl($g['goods_id']);
                $g['goods_thumb'] = Items::imgurl($g['goods_thumb']);
            }
        }
        else {
            $order_goods = [];
        }
    
        return $order_goods;
    }
    
    /**
     * 获取订单详情
     * @param unknown $order_id
     */
    /* static function getOrderDetail($order_id) {
       $sql = "select consignee,mobile,(
                	select region_name from shp_region where region_id = o.province
                ) as province,
                (
                	select region_name from shp_region where region_id = o.city
                )  as city,
                (
                	select region_name from shp_region where region_id = o.district
                )  as district,
               address,how_oos,pay_trade_no,
               add_time,pay_time,shipping_name,invoice_no from shp_order_info o where order_id = %d";
       $rows = D()->query($sql, $order_id)->fetch_array();
       return $rows;
    } */
    
    static function getOrderExpress($order_id) {
        $sql = "select od.shipping_name as shipping_name,od.invoice_no as invoice_no,
                express.express_trace as express_trace from edmbuy.shp_order_info od 
                left join edmbuy.shp_order_express express  
                on od.order_id = express.order_id 
                where od.order_id = %d";
        $rows = D()->query($sql, $order_id)->fetch_array();
        return $rows;
    }
    
    /**
     * 获取订单列表
     *
     * @param integer $user_id
     * @param string  $status
     * @return array
     */
    static function getList($user_id, $status='') {
    	if (empty($user_id)) return [];
    
    	$start = 0;
    	$limit = 100;
    
    	$ectb_order = self::table();
    	$ectb_goods = Items::table();
    	$ectb_order_goods = OrderItems::table();
    	$where = "";
    	if ('wait_pay'==$status) {
    		$where .= " AND pay_status IN(".PS_UNPAYED.", ".PS_PAYING.")";
    	}
    	elseif ('wait_ship'==$status) {
    		$where .= " AND pay_status=".PS_PAYED;
    		$where .= " AND shipping_status IN(".SS_UNSHIPPED.",".SS_PREPARING.",".SS_SHIPPED_ING.")";
    	}
    	elseif ('wait_recv'==$status) {
    		$where .= " AND pay_status=".PS_PAYED;
    		$where .= " AND shipping_status IN(".SS_SHIPPED.",".SS_SHIPPED_PART.",".OS_SHIPPED_PART.")";
    	}
    	elseif ('finished'==$status) {
    		$where .= " AND shipping_status=".SS_RECEIVED;
    	}
    	
    	$sql = "SELECT * FROM {$ectb_order} WHERE `user_id`=%d and is_separate = 0 $where ORDER BY `order_id` DESC LIMIT %d,%d";
    	$orders = D()->raw_query($sql, $user_id, $start, $limit)->fetch_array_all();
    	if (!empty($orders)) {
    		foreach ($orders AS &$ord) {
    			$ord['show_status_html'] = self::genStatusHtml($ord);
    			$ord['order_goods'] = [];
    			$sql = "SELECT og.*,g.`goods_thumb` FROM {$ectb_order_goods} og INNER JOIN {$ectb_goods} g ON og.`goods_id`=g.`goods_id` WHERE og.`order_id`=%d ORDER BY og.`rec_id` DESC";
    			$order_goods = D()->raw_query($sql, $ord['order_id'])->fetch_array_all();
    			if (!empty($order_goods)) {
    				foreach ($order_goods AS &$g) {
    					$g['goods_url']   = Items::itemurl($g['goods_id']);
    					$g['goods_thumb'] = Items::imgurl($g['goods_thumb']);
    				}
    				$ord['order_goods'] = $order_goods;
    			}
    		}
    	}
    	else {
    		$orders = [];
    	}
    	return $orders;
    }
    
    /**
     * 生成订单各种状态显示html
     *
     * @param array $order
     * @return string
     */
    static function genStatusHtml(Array &$order) {
    
    	$html = '';
    
    	$order['active_order'] = 0; //便于区分订单显示样式
    	$br = '<br/>';
    	if (!in_array($order['order_status'], [OS_CANCELED,OS_INVALID,OS_RETURNED])) { //订单“活动中”
    		$order['active_order'] = 1;
    		if (in_array($order['pay_status'], [PS_UNPAYED, PS_PAYING, PS_CANCEL, PS_FAIL])) { //未支付和支付中
    			$html .= '<p class="order-status-txt">'.Fn::pay_status($order['pay_status']).'</p>';
    			$html .= '<p class="order-status-op"><a href="javascript:;" class="btn btn-orange btn-order-topay" data-order_id="'.$order['order_id'].'">立即付款</a></p>';
    			$html .= '<p class="order-status-op last"><a href="javascript:;" class="btn-order-cancel" data-order_id="'.$order['order_id'].'">取消订单</a></p>';
    		}
    		elseif ($order['pay_status']==PS_PAYED) { //已支付
    			$html .= '<p class="order-status-txt">'.Fn::pay_status($order['pay_status']).$br.Fn::shipping_status($order['shipping_status']);
    			if ($order['shipping_status']==SS_RECEIVED) {
    				$html.= $br.'<span style="color:green">'.Fn::zonghe_status(CS_FINISHED).'</span>'; //订单完成
    				$html.= '</p>';
    				$order['active_order'] = 0;
    			}
    			elseif ($order['shipping_status']==SS_SHIPPED) {
    				$html .= '</p><p class="order-status-op"><a href="javascript:;" class="btn btn-orange btn-ship-confirm" data-order_id="'.$order['order_id'].'">确认收货</a></p>';
    			}
    			else {
    				$html.= '</p>';
    			}
    		}
    		else { //支付中
    			$html .= '<p class="order-status-txt">'.Fn::order_status(OS_CONFIRMED).$br.Fn::pay_status($order['pay_status']).'</p>';
    		}
    	}
    	else {
    		$html .= '<p>'.Fn::order_status($order['order_status']).'</p>';
    	}
    
    	return $html;
    }
    
    /**
     * 根据传入的综合状态返回 查询订单的SQL
     * @param unknown $composite_status
     * @param $prefix 表前缀
     */
    static function build_order_status_sql($composite_status, $prefix = ''){
        $status = Fn::get_order_status($composite_status);
        if(!$status || count($status) == 0){
            return '';
        }
        //关闭订单处理
        if(CS_CLOSED == $composite_status){
            return " AND ($prefix.pay_status ".Func::db_create_in($status['pay_status'])." or 
                         $prefix.order_status ".Func::db_create_in($status['order_status']).")";
        }else if(CS_AWAIT_PAY == $composite_status){//待付款
            return " AND $prefix.pay_status ".Func::db_create_in($status['pay_status'])." 
                      AND order_status NOT ".Func::db_create_in(array(OS_CANCELED, OS_INVALID));
        }
        $sql = "";
        foreach ($status as $field => $arr){
            if(is_array($arr)){
                $sql .= " AND $prefix.$field ".Func::db_create_in($arr);
            }else{
                $sql .= " AND $prefix.$field = $arr ";
            }
        }
        return $sql;
    }
    
    /**
     * 查询地址选择列表
     * @param number $type  省市县type[1,2,3]
     * @param number $parent 父ID
     */
    static function get_regions($type = 0, $parent = 0){
        $sql = "SELECT region_id, region_name FROM shp_region WHERE region_type = $type AND parent_id = $parent ";
        return D()->query($sql)->fetch_array_all();
    }
    
    /**
     * 应付订单总额=订单总额+各种费用-折扣
     * @param unknown $order
     * @return number
     */
    static function get_actual_order_amount($order){
        $goods_amount = $order['goods_amount'];
        $shipping_fee = $order['shipping_fee'];
        $insure_fee = $order['insure_fee'];
        $pay_fee = $order['pay_fee'];
        $pack_fee = $order['pack_fee'];
        $card_fee = $order['card_fee'];
        $discount = $order['discount'];
        $acutal_amount = doubleval($goods_amount) + doubleval($shipping_fee) + doubleval($insure_fee) 
                        + doubleval($pack_fee) + doubleval($pack_fee) + doubleval($card_fee) - doubleval($discount);
        return $acutal_amount;
    }
    /**
     * std 对象格式的订单对象
     * @param unknown $order
     */
    static function get_actual_orderobj_amount($order, $discount = 0){
        $goods_amount = $order->goods_amount;
        $shipping_fee = $order->shipping_fee;
        $insure_fee = $order->insure_fee;
        $pay_fee = $order->pay_fee;
        $pack_fee = $order->pack_fee;
        $card_fee = $order->card_fee;
        if(!$discount){
            $discount = $order->discount;
        }
        $acutal_amount = doubleval($goods_amount) + doubleval($shipping_fee) + doubleval($insure_fee)
        + doubleval($pack_fee) + doubleval($pack_fee) + doubleval($card_fee) - doubleval($discount);
        return $acutal_amount;
    }
    
    /**
     * 物流信息json串解析成一个List对象返回
     * @param unknown $express
     * @return NULL
     */
    static function get_express_list($express){
        $ret = [];
        if(!$express || !$express['express_trace']){
            return $ret;
        }
        $json = json_decode($express['express_trace']);
        $list = $json->result ? $json->result->list :null;
        if($list && count($list) > 0){
            foreach ($list as &$ex){
                if($ex->time && strlen($ex->time) > 19){
                    $ex->time = substr($ex->time, 0, 19);
                }
            }
            $ret = $list;
        }
        return $ret;
    }
    
    /**
     * 获取物流公司列表
     */
    static function get_shipping_list(){
        $sql = "select shipping_id,shipping_name from shp_shipping where enabled=1 order by shipping_order desc ";
        $result = D()->query($sql)->fetch_array_all();
        return $result;
    }
    
    /**
     * 订单金额计算公式
     * @param double $goods_amount   商品总金额
     * @param double $discount       折扣
     * @param double $shipping_fee   配送费用
     * @param double $pay_fee        支付费用
     * @param double $insure_fee     保价费用
     * @param double $pack_fee       包装费用
     * @param double $card_fee       贺卡费用
     * @param double $tax            发票税额
     * @return double
     */
    static function calc_order_amount($goods_amount, $discount = 0, $shipping_fee = 0, $pay_fee = 0, $insure_fee = 0, $pack_fee = 0, $card_fee = 0, $tax = 0)
    {
    	return $goods_amount - $discount + $shipping_fee + $pay_fee + $insure_fee + $pack_fee + $card_fee + $tax;
    }
    
}

/*----- END FILE: class.Order.php -----*/