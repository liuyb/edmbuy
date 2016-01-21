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
    static function cancel($order_id) {
    	if (!$order_id) return false;
    
    	D()->update(self::table(), ['order_status'=>OS_CANCELED], ['order_id'=>$order_id]);
    
    	if (D()->affected_rows()==1) {
    
    		//还要将对应的库存加回去
    		$order_goods = Order::getItems($order_id);
    		if (!empty($order_goods)) {
    			foreach ($order_goods AS $g) {
    				Items::changeStock($g['goods_id'],$g['goods_number']);
    			}
    		}
    
    		//写order_action的日志
    		self::action_log($order_id, ['action_note'=>'用户取消']);
    
    		return true;
    	}
    	return false;
    }
    
    /**
     * 确认订单收货
     *
     * @param integer $order_id
     * @return boolean
     */
    static function confirm_shipping($order_id) {
    	if (!$order_id) return false;
    
    	D()->update(self::table(),
    	            ['shipping_status'=>SS_RECEIVED,'shipping_confirm_time'=>simphp_gmtime()],
    	            ['order_id'=>$order_id]);
    
    	if (D()->affected_rows()==1) {
    
    		//写order_action的日志
    		self::action_log($order_id, ['action_note'=>'用户确认收货']);
    
    		return true;
    	}
    	return false;
    }
    
    /**
     * 获取一个订单下的商品列表
     *
     * @param integer $order_id
     * @return array
     */
    static function getItems($order_id) {
    	if (empty($order_id)) return [];
    
    	$ectb_goods = Items::table();
    	$ectb_order_goods = OrderItems::table();
    
    	$sql = "SELECT og.*,g.`goods_thumb` FROM {$ectb_order_goods} og INNER JOIN {$ectb_goods} g ON og.`goods_id`=g.`goods_id` WHERE og.`order_id`=%d ORDER BY og.`rec_id` DESC";
    	$order_goods = D()->raw_query($sql, $order_id)->fetch_array_all();
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
     * 关联订单和商家
     * @param integer $order_id
     * @param integer $merchant_id
     * @return number
     */
    static function relateMerchant($order_id, $merchant_uid) {
    	D()->query("INSERT IGNORE INTO `shp_order_merchant`(`order_id`,`merchant_uid`) VALUES(%d, %d)", $order_id, $merchant_uid);
    	return D()->affected_rows();
    }
}

?>