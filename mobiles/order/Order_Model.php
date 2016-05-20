<?php
/**
 * Partner Model
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Order_Model extends Model {

    /**
     * 根据从Order里面获取的商品列表及商家信息
     * 组装成  商家/商品列表 集合
     * @param unknown $order_id
     */
    static function getOrderItems($order_id) {
        $order_goods = Order::getOrderItems($order_id);
        return $order_goods;
    }
	
    /**
     * 根据订单里面的区域ID获取区域组合信息
     * @param unknown $order_id
     */
    static function getOrderRegion(array $regionIds) {
        if(!$regionIds || count($regionIds) == 0){
            return '';
        }
        $regionIds = join(',', $regionIds);
        $sql = "select region_name from shp_region where region_id in ($regionIds) order by region_id";
        $arr = D()->query($sql)->fetch_array_all();
        $region = "";
        foreach ($arr as $item){
            $region .= $item['region_name'];
        }
        return $region;
    }
}
 
/*----- END FILE: Partner_Model.php -----*/