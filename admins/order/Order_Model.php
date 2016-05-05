<?php
/**
 * Refund Model
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Order_Model extends Model {
	
    /**
     * 退款列表显示
     * @param Pager $pager
     * @param array $options
     */
    static function getPagedRefunds($orderby='rec_id', $order='DESC', $limit=30, $query_conds=array(), &$statinfo=array()){
        $where = "";
        if($query_conds['order_sn']){
            $where .= " and refund.order_sn like '%%".D()->escape_string(trim($query_conds['order_sn']))."%%' ";
        }
        if($query_conds['buyer']){
            $buyer = D()->escape_string(trim($query_conds['buyer']));
            $where .= " and (refund.consignee like '%%".$buyer."%%' or refund.nick_name like '%%".$buyer."%%') ";
        }
        if($query_conds['merchant_id']){
            $where .= " and refund.merchant_id = '".D()->escape_string($query_conds['merchant_id'])."'";
        }
        if (isset($query_conds['from_date']) && $query_conds['from_date']) {
            $where .= " AND refund.`refund_time`>= '".D()->escape_string($query_conds['from_date'])."'";
        }
        if (isset($query_conds['to_date']) && $query_conds['to_date']) {
            $where .= " AND refund.`refund_time`<= '".D()->escape_string($query_conds['to_date'])."'";
        }
		$table  = OrderRefund::table();
	    $sql    = "select refund.*,sm.facename from {$table} refund left join shp_merchant sm on refund.merchant_id = sm.merchant_id where 1 {$where} ORDER BY %s %s";
		$sqlcnt = "select count(refund.rec_id) from {$table} refund left join shp_merchant sm on refund.merchant_id = sm.merchant_id where 1 {$where}";
	
		$result = D()->pager_query($sql,$limit,$sqlcnt,0,$orderby,$order)->fetch_array_all();
		if (!empty($result)) {
			foreach ($result AS &$it) {
				$it['state_txt'] = OrderRefund::getRefundStatus($it['check_status'], $it['wx_status']);
				$it['goods_name'] = self::getRefundGoodsName($it['order_id']);
			}
		}
		return $result;	
    }
    
    /**
     * 根据订单ID获取商品名称 一对多
     * @param unknown $order_id
     */
    private static function getRefundGoodsName($order_id){
        $goods_name = '';
        $sql = "select goods_name from shp_order_goods where order_id = %d";
        $result = D()->query($sql, $order_id)->fetch_column();
        if(count($result) > 0){
            $goods_name = implode(',', $result);
        }
        return $goods_name;
    }
    
}
 
/*----- END FILE: Refund_Model.php -----*/