<?php
defined('IN_SIMPHP') or die('Access Denied');

/**
 * 订单快递状态查询
 * @author Jean
 *
 */
class OrderExpress extends CStatic{
    
    const EXPRESS_QUERY_API = "http://api.jisuapi.com/express/query?appkey=416b541d684c9988";
    
    const EXPRESS_EXPIRE_TIME = 14400;//4*3600 四小时有效
    
    /**
     * 对外暴露获取物流信息
     * @param unknown $order
     * @return mixed|string|string|unknown|string|mixed|unknown
     */
    static function getOrderExpress($order){
        if(!$order || empty($order)){
            return null;
        }
        $order_id = $order['order_id'];
        $express = D()->query("select * from shp_order_express where order_id = $order_id ")->fetch_array();
        $trace = $express['express_trace'];
        if($order['shipping_status'] == SS_RECEIVED){
            return $trace;
        }
        if(!$order['shipping_id']){
            return $trace;
        }
        if(!$express || empty($express) || count($express) == 0){
            $trace = self::pullExpressData($order, true);
            self::insert_or_update_order_express($order_id, $trace, true);
            return $trace;
        }
        
        $is_done = $express['is_done'];
        //已经完成签收
        if($is_done){
            return $trace; 
        }
        $time = time() - self::EXPRESS_EXPIRE_TIME;
        $last_update = $express['last_update_time'];
        //过了有效期需要重新拉取数据
        if($last_update < $time){
            $trace = self::pullExpressData($order);
            self::insert_or_update_order_express($order_id, $trace, false);
            return $trace;
        }
        return $trace;
    }
    
    /**
     * 拉取最新物流信息
     * @param unknown $order
     */
    private static function pullExpressData($order, $is_insert = false){
        $invoice_no = $order['invoice_no'];
        $shipping_type = D()->query("select shipping_type from shp_shipping where shipping_id = $order[shipping_id] ")->result();
        $url = self::EXPRESS_QUERY_API;
        $url .= "&type=$shipping_type&number=$invoice_no";
        $expressJson = file_get_contents($url);
        if(empty($expressJson)){
            return $expressJson;
        }
        if(!$is_insert){
            self::monitorExpressIsDone($order['order_id'], $expressJson);
        }
        return $expressJson;
    }
    
    /**
     * 已签收则设置快递查询状态为已完成。
     * @param unknown $order_id
     * @param unknown $expressJson
     */
    private static function monitorExpressIsDone($order_id, $expressJson){
        $expressObj = json_decode($expressJson);
        //status:0 表示抓取状态成功
        if(empty($expressObj)){
            return;
        }
        if($expressObj->result->issign == "1"){
            D()->query("update shp_order_express set is_done = 1 where order_id = $order_id ");
        }
    }
    
    /**
     * 插入或更新订单物流状态信息
     * @param unknown $order_id
     * @param unknown $trace_info
     */
    private static function insert_or_update_order_express($order_id, $trace_info, $is_insert){
        if(!$trace_info || empty($trace_info)){
            return;
        }
        if($is_insert){
            $sql = "insert ignore into edmbuy.shp_order_express(order_id, express_trace,last_update_time) values(%d, '%s',".time().")";
            D()->query($sql, $order_id, $trace_info);
        }else{
            $sql = "update ignore shp_order_express set express_trace='%s',last_update_time=".time()." where order_id=%d";
            D()->query($sql, $trace_info, $order_id);
        }
    }

}
