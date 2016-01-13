<?php

defined('IN_SIMPHP') or die('Access Denied');

/**
 * 
 * @author Jean
 *
 */
class Order extends StorageNode{
    
    protected static function meta() {
        return array(
            'table' => '`shp_order_info`',
            'key'   => 'uid',   //该key是应用逻辑的列，当columns为array时，为columns的key，否则，则要设成实际存储字段
            'columns' => array( //columns同时支持'*','实际存储字段列表串',映射数组 三种方式
                'orderid'           => 'order_id',
                'ordersn'           => 'order_sn',
                'pay_trade_no'      => 'pay_trade_no',
                'userid'            => 'user_id',
                'order_status'      => 'order_status',
                'shipping_status'   => 'shipping_status',
                'pay_status'        => 'pay_status',
                'consignee'         => 'consignee',
                'country'           => 'country',
                'province'          => 'province',
                'city'              => 'city',
                'address'           => 'address',
                'zipcode'           => 'zipcode',
                'tel'               => 'tel',
                'mobile'            => 'mobile',
                'email'             => 'email',
                'besttime'          => 'best_time',
                'sign_building'     => 'sign_building',
                'postscript'        => 'postscript',
                'shippingid'        => 'shipping_id',
                'shippingname'      => 'shipping_name',
                'payid'             => 'pay_id',
                'payname'           => 'pay_name',
                'howoos'            => 'how_oos',
                'howsurplus'        => 'how_surplus',
                'packname'          => 'pack_name',
                'inv_payee'         => 'inv_payee',
                'inv_content'       => 'inv_content',
                'goods_amount'      => 'goods_amount',
                'shipping_fee'      => 'shipping_fee',
                'insure_fee'        => 'insure_fee',
                'pay_fee'           => 'pay_fee',
                'pack_fee'          => 'pack_fee',
                'card_fee'          => 'card_fee',
                'money_paid'        => 'money_paid',
                'surplus'           => 'surplus',
                'integral'          => 'integral',
                'integral_money'    => 'integral_money',
                'bonus'             => 'bonus',
                'order_amount'      => 'order_amount',
                'from_ad'           => 'from_ad',
                'referer'           => 'referer',
                'add_time'          => 'add_time',
                'confirm_time'      => 'confirm_time',
                'paytime'           => 'pay_time',
                'shipping_time'     => 'shipping_time',
                'shipping_confirm_time'     => 'shipping_confirm_time',
                'packid'            => 'pack_id',
                'cardid'            => 'card_id',
                'bonusid'           => 'bonus_id',
                'invoiceno'         => 'invoice_no',
                'extension_code'    => 'extension_code',
                'extension_id'      => 'extension_id',
                'tobuyer'           => 'to_buyer',
                'paynote'           => 'pay_note',
                'agencyid'          => 'agency_id',
                'invtype'           => 'inv_type',
                'tax'               => 'tax',
                'isseparate'        => 'is_separate',
                'parentid'          => 'parent_id',
                'discount'          => 'discount',
                'paydata1'          => 'pay_data1',
                'paydata2'          => 'pay_data2'
            ));
    }
    
    /**
     * 检查支付付款与原来订单金额是否一致
     * @param string $order_sn 订单号
     * @param integer $money 金钱(以分为单位)
     * @return boolean
     */
    static function check_paid_money($order_sn, $money) {
    
    	$ectb = ectable('order_info');
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
    	$ectb = ectable('order_info');
    	$row  = D()->raw_query("SELECT `order_id`,`order_sn`,`user_id`,`order_status`,`shipping_status`,`pay_status`,`pay_id`,`order_amount` FROM {$ectb} WHERE `order_sn`='%s'", $order_sn)
    	->get_one();
    	return !empty($row) ? $row : [];
    }
    
    /**
     * 插入订单动作日志
     */
    static function order_action_log($order_id, Array $insert_data) {
    	if (empty($order_id)) return false;
    	$oinfo = D()->get_one("SELECT `order_id`,`order_status`,`shipping_status`,`pay_status` FROM ".ectable('order_info')." WHERE `order_id`=%d", $order_id);
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
    	 
    	$rid = D()->insert(ectable('order_action'), $insert_data, true, true);
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
    
    	D()->update(ectable('order_info'), ['order_status'=>OS_CANCELED], ['order_id'=>$order_id], true);
    
    	if (D()->affected_rows()==1) {
    
    		//还要将对应的库存加回去
    		$order_goods = Goods::getOrderGoods($order_id);
    		if (!empty($order_goods)) {
    			foreach ($order_goods AS $g) {
    				Goods::changeGoodsStock($g['goods_id'],$g['goods_number']);
    			}
    		}
    
    		//写order_action的日志
    		self::order_action_log($order_id, ['action_note'=>'用户取消']);
    
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
    
    	D()->update(ectable('order_info'),
    	['shipping_status'=>SS_RECEIVED,'shipping_confirm_time'=>simphp_gmtime()],
    	['order_id'=>$order_id], true);
    
    	if (D()->affected_rows()==1) {
    
    		//写order_action的日志
    		self::order_action_log($order_id, ['action_note'=>'用户确认收货']);
    
    		return true;
    	}
    	return false;
    }
    
}

?>