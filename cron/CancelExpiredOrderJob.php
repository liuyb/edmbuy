<?php
/**
 * 查找系统中所有没有支付的过期订单，取消并归还库存。
 * @author Jean
 *
 */
class CancelExpiredOrderJob extends CronJob {
    
    //订单未支付时间超过2小时过期
    private static $ORDER_EXPIRED_TIME = 2;    
    
    public function main($argc, $argv) {
        $this->handleExpiredOrders();
    }
    
    private function handleExpiredOrders(){
        $this->log("begin execute job for cancel expired orders...");
        $orders = $this->findAllExpiredOrders();
        $count = count($orders);
        $this->log("get wait expired orders count ".$count);
        foreach ($orders as $item){
            Order::cancel($item['order_id'], "系统取消");
        }
    }
    
    private function findAllExpiredOrders(){
        $sql = 'select order_id from shp_order_info where is_separate = 0 and pay_status = 0 
                and timediff(utc_timestamp(), from_unixtime(add_time)) > %d';
        $reult = D()->query($sql, self::$ORDER_EXPIRED_TIME)->fetch_array_all();
        return $reult;
    } 
}