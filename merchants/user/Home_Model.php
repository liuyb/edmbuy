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
    
    /**
     * 获取首页显示的店铺相关信息
     * @param unknown $muid
     */
    static function getShopInfo($muid){
        $sql = "select m.facename as facename,m.logo as logo,p.end_time as end_time from shp_merchant m left join shp_merchant_payment p on m.merchant_id = p.merchant_id 
                where m.merchant_id='%s' order by p.end_time desc limit 1";
        $result = D()->query($sql, $muid)->fetch_array();
        return $result;
    }
    
    /**
     * 店铺销售总额
     * @param unknown $muid
     * @return mixed
     */
    static function getOrderSalesMoney($muid){
        $sql = "SELECT sum(money_paid) as salesTotal from shp_order_info where merchant_ids='%s' and is_separate=0 and is_delete=0 and pay_status = ".PS_PAYED."";
        return D()->query($sql, $muid)->result();
    }
    
    /**
     * 获取店铺运营数据
     * @param unknown $muid
     * @param unknown $gap
     */
    static function getMerchantDataByTime($muid, $gap){
        $where = '';
        if($gap == 'yesterday'){
            $beginYesterday=mktime(0,0,0,date('m'),date('d')-1,date('Y'));
            $endYesterday=mktime(0,0,0,date('m'),date('d'),date('Y'))-1;
            $where .= " and (add_time >= $beginYesterday and add_time <= $endYesterday) ";
        }else if($gap > 0){
            $gap = mktime(0,0,0,date('m'),date('d')-intval($gap),date('Y'));
            $where .= " and add_time >= $gap ";
        }
        
        //商家新增访问记录
        $sql = "select count(*) from shp_merchant_visiting where merchant_id='%s' $where";
        $visit = D()->query($sql, $muid)->result();
        //所有未删除的订单
        $sql = "SELECT count(1) as totalOrder from shp_order_info where merchant_ids='%s' and is_separate=0 and is_delete=0 $where";
        $totalOrder = D()->query($sql, $muid)->result();
        //所有已支付的订单
        $sql = "SELECT ifnull(sum(money_paid),0) money_paid, ifnull(sum(commision),0) as commision, ifnull(sum(discount),0) as discount 
                from shp_order_info where merchant_ids='%s' and is_separate=0 and is_delete=0 and pay_status = ".PS_PAYED." $where";
        $result = D()->query($sql, $muid)->fetch_array();
        $result['totalOrder'] = $totalOrder;
        $result['income'] = number_format((doubleval($result['money_paid']) - doubleval($result['commision']) - doubleval($result['discount'])), 2);
        $result['visit'] = $visit;
        return $result;
    }
    
    /**
     * 
     * @param unknown $muid
     * @return mixed
     */
    private static function getMerchantVisitingCount($muid){
        
    }
    
}
