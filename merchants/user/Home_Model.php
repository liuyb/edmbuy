<?php
defined('IN_SIMPHP') or die('Access Denied');

/**
 * 首页数据查询模型
 * @author Jean
 *
 */
class Home_Model extends Model{
    
    /**
     * 获取不同状态订单数量
     * @param unknown $status
     * @param unknown $muid
     * @return mixed
     */
    static function getOrderTotalByStatus($status, $muid){
        $where = '';
        $statusSql = Order::build_order_status_sql(intval($status), 'o');
        if($statusSql){
            $where .= $statusSql;
        }
        $sql = "SELECT count(1) FROM shp_order_info o where merchant_ids='%s' and is_separate = 0 and is_delete = 0 $where ";
        return D()->query($sql, $muid)->result();
    }
    
    /**
     * 等待退款订单数量
     * @param unknown $muid
     * @return mixed
     */
    static function getWaitRefundOrderTotal($muid){
        $sql = "SELECT count(1) FROM shp_order_refund where merchant_id='%s' and check_status = 0";
        return D()->query($sql, $muid)->result();
    }
    
    /**
     * 根据商品状态获取商品数量
     * @param unknown $status
     * @param unknown $muid
     * @return mixed
     */
    static function getGoodsTotalByIsSale($status, $muid){
        $where = '';
        if ($status >= 0){
            $where = " and is_on_sale=".intval($status);
        }
        $sql = "select count(1) from shp_goods where merchant_id='%s' and is_delete=0 $where";
        return D()->query($sql, $muid)->result();
    }
    
    /**
     * 商品库存预警数量
     * @param unknown $muid
     * @param unknown $warn_number
     * @return mixed
     */
    static function getGoodsNumberWarning($muid, $warn_number){
        $sql = "select count(1) from shp_goods where merchant_id='%s' and is_delete=0 and goods_number < '%d'";
        return D()->query($sql, $muid, (intval($warn_number) + 1))->result();
    }
}
