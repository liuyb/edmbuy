<?php
defined('IN_SIMPHP') or die('Access Denied');

/**
 * 订单退款实体
 * @author Jean
 *
 */
class OrderRefund extends StorageNode {
	
    //商家审核通过
    const CHECK_STATUS_YES = 1;
    
    //商家审核拒绝
    const CHECK_STATUS_NO = 2;
    
    //微信退款成功
    const WX_STATUS_SUCC = 1;
    
    //微信退款失败
    const WX_STATUS_FAIL = 2;
    
	protected static function meta() {
		return array(
				'table' => '`shp_order_refund`',
				'key'   => 'rec_id',
				'columns' => array(
    					'rec_id'         => 'rec_id',
    					'order_sn'       => 'order_sn',
				        'order_id'       => 'order_id',
				        'user_id'        => 'user_id',
    					'pay_trade_no'       => 'pay_trade_no',
    					'refund_sn'     => 'refund_sn',
    					'pay_refund_no'       => 'pay_refund_no',
    					'trade_money'     => 'trade_money',
    					'refund_money'   => 'refund_money',
    					'refund_status'   => 'refund_status',
    					'refund_time'    => 'refund_time',
    					'succ_time'   => 'succ_time',
    					'refund_reason'     => 'refund_reason',
    					'refund_desc'    => 'refund_desc',
    					'check_status'        => 'check_status',
    					'refuse_txt' => 'refuse_txt',
    					'wx_status'   => 'wx_status',
    					'wx_response' => 'wx_response',
    			        'consignee'   => 'consignee',
				        'nick_name'   => 'nick_name',
				        'merchant_id' => 'merchant_id'
				)
		);
	}
	
	/**
	 * 已付款未发货的状态才能发起退款申请
	 * @param unknown $pay_status
	 * @param unknown $shipping_status
	 */
	static function isValidRefundStatus($pay_status, $shipping_status){
	    $valid_status = Fn::get_order_status(CS_AWAIT_SHIP);
	    if($pay_status != $valid_status['pay_status'] || !in_array($shipping_status, $valid_status['shipping_status'])){
	        return false;
	    }
	    return true;
	}
	
	/**
	 * 退款中未发货的状态商家才能同意退款处理
	 * @param unknown $pay_status
	 * @param unknown $shipping_status
	 */
	static function isValidAcceptRefundStatus($pay_status, $shipping_status){
	    $valid_status = Fn::get_order_status(CS_AWAIT_SHIP);
	    if($pay_status != PS_REFUNDING || !in_array($shipping_status, $valid_status['shipping_status'])){
	        return false;
	    }
	    return true;
	}
	
	/**
	 * 退款成功后订单相关联处理
	 */
	static function order_refund($order, $refundMoney){
	    $order_id = $order->order_id;
	    //退款订单状态处理
	    self::refund($order_id);
	    self::updatePaylog($order_id);
	    //订单存在父订单
	    if($order->parent_id){
	        $p_order_id = $order->parent_id;
	        $subOrdersCount = D()->from(Order::table())->where("parent_id = '%d' and pay_status <> %d ", $p_order_id, PS_REFUND)->select('count(1)')->result();
	        if($subOrdersCount && $subOrdersCount > 0){
	            //部分子订单退款
	            D()->update(Order::table(), ['order_status'=>OS_REFUND_PART], ['order_id'=>$p_order_id]);
	            //删除佣金记录 insert时因为唯一索引原因，这里需要删除  
	            D()->raw_query("DELETE FROM shp_user_commision WHERE `order_id`=%d AND `state`<%d", $p_order_id, UserCommision::STATE_CASHED);
	            //父订单佣金需要减去退款订单的佣金，并重新生成佣金
	            D()->raw_query("UPDATE shp_order_info SET `commision`=`commision`-%d WHERE `order_id`=%d", $order->commision, $p_order_id);
	            $porder = Order::load($p_order_id);
	            UserCommision::generate($porder);
	            
	            //写order_action的日志
	            $action_note = sprintf("子订单%d退款，删除原佣金记录，从shp_order_info的父订单%d的commision扣减%d，并重新生成佣金。",
	                $order_id, $order->parent_id, $refundMoney, $order->commision);
	            Order::action_log($order_id, ['action_note'=>$action_note, 'action_user' => $GLOBALS['user']->uid]);
	        }else{
	            //全部子订单退款
	            D()->update(Order::table(), ['order_status'=>OS_REFUND,'pay_status'=>PS_REFUND], ['order_id'=>$p_order_id]);
	            //父订单修改
	            self::removeCommision($p_order_id);
	            self::updatePaylog($p_order_id);
	            //写order_action的日志
	            $action_note = sprintf("父订单%d下子订单全部退款，佣金失效处理。",$p_order_id);
	            Order::action_log($order_id, ['action_note'=>$action_note, 'action_user' => $GLOBALS['user']->uid]);
	        }
	    }else{
	        self::removeCommision($order_id);
	    }
	}
	
	/**
	 * 退款成功对应的订单相关处理
	 *
	 * @param integer $order_id
	 * @return boolean
	 */
	static function refund($order_id, $action_note = '退款成功') {
	    if (!$order_id) return false;
	
	    D()->update(Order::table(), ['order_status'=>OS_REFUND,'pay_status'=>PS_REFUND], ['order_id'=>$order_id]);
	
	    if (D()->affected_rows()==1) {
	
	        //还要将对应的库存加回去
	        $order_goods = Order::getItems($order_id);
	        if (!empty($order_goods)) {
	            foreach ($order_goods AS $g) {
	                Items::changeStock($g['goods_id'],$g['goods_number'],$g['goods_attr_id']);
	            }
	        }
	
	        //写order_action的日志
	        Order::action_log($order_id, ['action_note'=>$action_note, 'action_user' => $GLOBALS['user']->uid]);
	
	        return true;
	    }
	    return false;
	}
	
	
	//退款成功后支付状态修改成未支付 
	private static function updatePaylog($order_id, $is_paid = 0) {
	    if (empty($order_id)) return false;
	    D()->update('`shp_pay_log`', ['is_paid'=>$is_paid], ['order_id'=>$order_id]);
	    return true;
	}
	
	//"删除"佣金表`shp_user_commision`数据
	private static function removeCommision($order_id) {
	    if (empty($order_id)) return false;
	    $sql = "UPDATE shp_user_commision SET `state`=%d,`state_time`=%d WHERE `order_id`=%d AND `state`<%d";
	        D()->query($sql, -1, simphp_time(), $order_id, UserCommision::STATE_CASHED);//确保已提现和提现中的订单不能变更
	    return true;
	}
	
	/**
	 * 返回退款状态
	 * @param unknown $check_status 审核状态
	 * @param unknown $wx_status 微信返回状态
	 * @return string
	 */
	static function getRefundStatus($check_status, $wx_status){
	    $check_status = intval($check_status);
	    $wx_status = intval($wx_status);
	    $display = '';
	    switch($check_status){
	        case 1 :
	            $display = ($wx_status == 1 ? '退款成功' : ($wx_status == 2 ? '退款失败' : '退款中'));
	            break;
	        case 2 :
	            $display = '已拒绝';
	            break;
	        default :
	            $display = '待审核';
	    }
	    return $display;
	}
	
	static function getRefundDetails($rec_id){
	    $sql = "SELECT refund.*,u.mobile_phone as mobilephone
                FROM shp_order_refund refund left join shp_users u on refund.user_id = u.user_id where refund.rec_id='%d'";
	    $result = D()->query($sql, $rec_id)->fetch_array();
	    return $result;
	}
}

