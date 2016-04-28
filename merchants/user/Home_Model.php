<?php
defined('IN_SIMPHP') or die('Access Denied');

/**
 * 首页数据查询模型
 * @author Jean
 *
 */
class Home_Model extends Model{
    
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
     
}
