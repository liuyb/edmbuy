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
    	  $sec = self::$ORDER_EXPIRED_TIME * 3600;
        $sql = 'SELECT order_id FROM shp_order_info WHERE pay_status IN(0,1,6) AND pay_time=0 AND is_separate = 0
                AND add_time < (UNIX_TIMESTAMP(UTC_TIMESTAMP())-%d)';
        $reult = D()->query($sql, $sec)->fetch_array_all();
        return $reult;
    }
}