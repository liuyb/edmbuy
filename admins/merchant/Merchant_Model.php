<?php
/**
 * Model 
 *
 * @author afarliu
 */
defined('IN_SIMPHP') or die('Access Denied');

class Merchant_Model extends Model {

    /**
     * 查询店铺列表
     * @param string $orderby
     * @param string $order
     * @param number $limit
     * @param array $query_conds
     * @param array $statinfo
     */
    static function getMerchantList($orderby='created', $order='DESC', $limit=30, $query_conds=array(), &$statinfo=array()){
        $where  = '';
        if(isset($query_conds['name']) && $query_conds['name']){
            $name = D()->escape_string($query_conds['name']);
            $where .= " AND (c.facename like '%%$name%%' or c.mobile like '%%$name%%')";
        }
        if(isset($query_conds['verify']) && is_numeric($query_conds['verify'])){
            $verify = intval($query_conds['verify']);
            $time =date('Y-m-d H:i:s' ,time());
            if($verify == -1){
                //已过期
                $where .= " AND (p.start_time > '{$time}' or p.end_time < '{$time}') ";
            }else{
                //其他状态查询未过期的
                $where .= " AND c.verify = {$verify} AND (p.start_time <= '{$time}' or p.end_time >= '{$time}') ";
            }
        }
        $table  = Merchant::table();
        $sql    = "SELECT c.*,p.start_time,p.end_time FROM {$table} c join shp_merchant_payment p on c.merchant_id = p.merchant_id and p.money_paid > 0 WHERE 1 {$where} ORDER BY `%s` %s";
        $sqlcnt = "SELECT COUNT(1) FROM {$table} c join shp_merchant_payment p on c.merchant_id = p.merchant_id and p.money_paid > 0 WHERE 1 {$where}";
        
        $result = D()->pager_query($sql,$limit,$sqlcnt,0,$orderby,$order)->fetch_array_all();
        if (!empty($result)) {
            foreach ($result AS &$it) {
                $it['verifyTxt'] = self::verifyTxt($it['verify']);
            }
        }
        return $result;
    }
    
    /**
     * 店铺详情
     * @param unknown $mid
     */
    static function getMerchantDetail($mid){
        $table  = Merchant::table();
        $sql = "select c.*,p.* from {$table} c join shp_merchant_payment p on c.merchant_id = p.merchant_id where c.merchant_id = '%s' order by p.end_time desc";
        $result = D()->query($sql, $mid)->get_one();
        if ($result) {
            $result['verifyTxt'] = self::verifyTxt($result['verify']);
            $business_scope = $result['business_scope'];
            $business_scope = trim($business_scope, ",");
            $where = "cat_id = {$business_scope}";
            if (strpos($business_scope, ",")) {
                $where = "cat_id in ({$business_scope})";
            };
            $sql = "select cat_name from shp_business_category where  {$where} order by cat_id DESC ";
            $list = D()->query($sql)->fetch_column();
            if (count($list) > 0) {
                $result["business_scope"] = implode(",", $list);
            } else {
                $result["business_scope"] = "";
            }
            $regionIds = [$result['province'], $result['city'], $result['district']];
            $adrrstr = Order::getOrderRegion($regionIds);
            $result['address'] = $adrrstr . $result['address'];
        }
        return $result;
    }
    
    /**
     * 显示认证状态
     * @param unknown $state
     * @return string
     */
    static function verifyTxt($state){
        $txt = '';
        switch ($state){
            case Merchant::VERIFY_UNDO:
                $txt = '未认证';
            break;
            case Merchant::VERIFY_CHECKING:
                $txt = '待审核';
            break;
            case Merchant::VERIFY_SUCC:
                $txt = '已认证';
            break;
            case Merchant::VERIFY_FAIL:
                $txt = '被拒绝';
            break;
            default:
                $txt = '未认证';
        }
        return $txt;
    }
}