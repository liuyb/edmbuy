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
        $merchant = null;
        foreach ($order_goods as $item){
            if($merchant != null){
                array_push($merchant['goods'], $item);
            }else{
                $merchant = $item;
                $merchant['goods'] = array();
            }
        }
        return $merchant;
    }
	
}
 
/*----- END FILE: Partner_Model.php -----*/