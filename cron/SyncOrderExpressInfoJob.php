<?php

/**
 * 同步物流状态信息
 * 
 * @author Jean
 *
 */
class SyncOrderExpressInfoJob extends CronJob {
    
    static $EXPRESS_QUERY_API = "http://api.jisuapi.com/express/query?appkey=416b541d684c9988";
    
    public function main($argc, $argv) {
        $this->sync_express_info();
    }
    
    /**
     * 同步订单物流信息主方法
     */
    private function sync_express_info(){
        $this->log("begin sync order express info...");
        $orders = $this->get_shipped_not_received_order();
        $count = count($orders);
        $this->log("get wait sync express count ".$count);
        $i = 0;
        $already_sync = 0;
        foreach ($orders as $od){
            $order_id = $od['order_id'];
            $expressJson = $this->get_express_from_web($od['shipping_type'], $od['invoice_no']);
            if(!$expressJson){
                continue;
            }
            $expressObj = json_decode($expressJson);
            if(!$expressObj || $expressObj->status != '0'){
                continue;
            }
            $this->insert_or_update_order_express($order_id, $expressJson);
            if($expressObj->result->issign == "1"){
                $this->update_order_received($order_id);
            }
            $i++;
            if($i == 100){
                $already_sync = $already_sync + $i;
                $this->log("already sync express count ".$already_sync." wait sync count : ".($count - $already_sync));
                $i = 0;
                sleep(1);
            }
        }
    }
    
    /**
     * 获取所有已发货但没有收货的订单信息
     * @return mixed
     */
    private function get_shipped_not_received_order() {
        $where  = " AND pay_status=".PS_PAYED;
        $where .= " AND shipping_status IN(".SS_SHIPPED.",".SS_SHIPPED_PART.",".OS_SHIPPED_PART.")";
        $sql = "select o.order_id as order_id, o.invoice_no as invoice_no, s.shipping_type as shipping_type from edmbuy.shp_order_info o 
                inner join shp_shipping s on o.shipping_id =  s.shipping_id 
                where o.is_separate=0 and o.invoice_no <> '' and s.shipping_type <> '' $where";
        $orders = D()->query($sql)->fetch_array_all();
        return $orders;
    }
    
    /**
     * 通过物流API 查询物流状态信息
     * @param unknown $shipping_type
     * @param unknown $invoice_no
     */
    private function get_express_from_web($shipping_type, $invoice_no) {
        $url = self::$EXPRESS_QUERY_API;
        $url .= "&type=$shipping_type&number=$invoice_no";
        $json = file_get_contents($url);
        if(!$json){
            return null;
        }
        return $json;
    }
    
    /**
     * 插入或更新订单物流状态信息
     * @param unknown $order_id
     * @param unknown $trace_info
     */
    private function insert_or_update_order_express($order_id, $trace_info){
        $sql="SELECT count(1) FROM edmbuy.shp_order_express where order_id=$order_id";
        $count = D()->query($sql)->result();
        if($count == 0){
            $sql = "insert into edmbuy.shp_order_express(order_id, express_trace) values(%d, '%s')";
            D()->query($sql, $order_id, $trace_info);
        }else{
            $sql = "update edmbuy.shp_order_express set express_trace='%s' where order_id=%d";
            D()->query($sql, $trace_info, $order_id);
        }
    }
    
    /**
     * 更新订单状态为已收货
     * @param unknown $order_id
     */
    private function update_order_received($order_id){
        $order = new Order();
        $order->order_id = $order_id;
        $order->shipping_status = SS_RECEIVED;
        $order->save();
    }
    
}
