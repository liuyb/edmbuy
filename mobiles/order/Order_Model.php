<?php
/**
 * Partner Model
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Order_Model extends Model {

    /**
     * 根据从Order里面获取的商品列表及商家信息
     * 组装成  商家/商品列表 集合
     * @param unknown $order_id
     */
    static function getOrderItems($order_id) {
        $order_goods = Order::getOrderItems($order_id);
        return $order_goods;
    }
	
    /**
     * 根据订单里面的区域ID获取区域组合信息
     * @param unknown $order_id
     */
    static function getOrderRegion(array $regionIds) {
        if(!$regionIds || count($regionIds) == 0){
            return '';
        }
        $regionIds = join(',', $regionIds);
        $sql = "select region_name from shp_region where region_id in ($regionIds) order by region_id";
        $arr = D()->query($sql)->fetch_array_all();
        $region = "";
        foreach ($arr as $item){
            $region .= $item['region_name'];
        }
        return $region;
    }
    
    /**
     * 订单退款详情
     * @param unknown $order_id
     */
    static function getOrderRefundDetail($order_id){
        $sql = "select * from shp_order_refund where order_id = %d and is_done = 0 order by rec_id desc";
        $refund = D()->get_one($sql, $order_id);
        return $refund;
    }
    
    /**
     * 取消订单退款申请
     * @param OrderRefund $refund
     */
    static function OrderRefundCancel(OrderRefund $refund){
        D()->beginTransaction();
        
        $refund->is_done = 1;
        $refund->check_status = OrderRefund::CHECK_STATUS_CANCEL;
        $refund->save(Storage::SAVE_UPDATE_IGNORE);
        
        $order = new Order();
        $order->order_id = $refund->order_id;
        $order->pay_status = PS_PAYED;
        $order->order_status = OS_CONFIRMED;
        $order->save(Storage::SAVE_UPDATE_IGNORE);
        
        D()->commit();
    }
}
 
/*----- END FILE: Partner_Model.php -----*/