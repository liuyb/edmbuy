<?php

/**
 * 修正物流单号任务 
 * 有的物流单号输入了快递公司名称，给予修正。
 * @author Jean
 *
 */
class CorretInvoiceNoJob extends CronJob {
    
    
    public function main($argc, $argv) {
        $this->correct_invoice_job();
    }
    
    /**
     * 同步订单物流信息主方法
     */
    private function correct_invoice_job(){
        $orders = $this->get_all_shippinged_order();
        $this->log("get wait correct invoice no order count ".count($orders));
        foreach ($orders as $od){
            $order_id = $od['order_id'];
            $invoice_no = $od['invoice_no'];
            if(is_numeric($invoice_no)){
                continue;
            }
            preg_match('/\d+/', $invoice_no, $match);
            if(count($match) == 0){
                continue;   
            }
            $invoice_no = $match[0];
            $this->update_order_invoice_no($order_id, $invoice_no);
        }
    }
    
    /**
     * 获取所有快递单号不为空的信息
     * @return mixed
     */
    private function get_all_shippinged_order() {
        $sql = "SELECT order_id as order_id, invoice_no as invoice_no FROM edmbuy.shp_order_info 
                where is_separate=0 and invoice_no <> ''";
        $orders = D()->query($sql)->fetch_array_all();
        return $orders;
    }
    
    private function update_order_invoice_no($order_id, $invoice_no) {
        $sql = "update edmbuy.shp_order_info set invoice_no = '$invoice_no' where order_id = $order_id";
        D()->query($sql);
    }
}
