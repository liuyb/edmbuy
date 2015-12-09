<?php
/**
 * 与订单相关方法
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Order {
  
  /**
   * 检查支付付款与原来订单金额是否一致
   * @param string $order_sn 订单号
   * @param integer $money 金钱(以分为单位)
   * @return boolean
   */
  static function check_paid_money($order_sn, $money) {
    
    $ectb = ectable('order_info');
    $order_amount = D()->raw_query("SELECT `order_amount` FROM {$ectb} WHERE `order_sn`='%s'", $order_sn)->result();
    if (empty($order_amount)) {
      return false;
    }
    $order_amount = intval($order_amount*100);
    
    return $order_amount===$money ? true : false;
  }
  
  /**
   * 根据订单号获取订单中跟支付相关的信息
   * @param string $order_sn
   */
  static function get_order_paid_info($order_sn) {
    $ectb = ectable('order_info');
    $row  = D()->raw_query("SELECT `order_id`,`order_sn`,`user_id`,`order_status`,`shipping_status`,`pay_status`,`pay_id`,`order_amount` FROM {$ectb} WHERE `order_sn`='%s'", $order_sn)
               ->get_one();
    return !empty($row) ? $row : [];
  }

  /**
   * 插入订单动作日志
   */
  static function order_action_log($order_id, Array $insert_data) {
    if (empty($order_id)) return false;
    $oinfo = D()->get_one("SELECT `order_id`,`order_status`,`shipping_status`,`pay_status` FROM ".ectable('order_info')." WHERE `order_id`=%d", $order_id);
    $init_data = [
      'order_id'       => $order_id,
      'action_user'    => 'buyer',
      'order_status'   => $oinfo['order_status'],
      'shipping_status'=> $oinfo['shipping_status'],
      'pay_status'     => $oinfo['pay_status'],
      'action_place'   => 0,
      'action_note'    => '',
      'log_time'       => simphp_gmtime(),
    ];
    $insert_data = array_merge($init_data, $insert_data);
     
    $rid = D()->insert(ectable('order_action'), $insert_data, true, true);
    return $rid;
  }
  
  /**
   * 取消订单
   *
   * @param integer $order_id
   * @return boolean
   */
  static function cancel($order_id) {
    if (!$order_id) return false;
    
    D()->update(ectable('order_info'), ['order_status'=>OS_CANCELED], ['order_id'=>$order_id], true);
    
    if (D()->affected_rows()==1) {
  
      //还要将对应的库存加回去
      $order_goods = Goods::getOrderGoods($order_id);
      if (!empty($order_goods)) {
        foreach ($order_goods AS $g) {
          Goods::changeGoodsStock($g['goods_id'],$g['goods_number']);
        }
      }
  
      //写order_action的日志
      self::order_action_log($order_id, ['action_note'=>'用户取消']);
  
      return true;
    }
    return false;
  }
  
  /**
   * 确认订单收货
   *
   * @param integer $order_id
   * @return boolean
   */
  static function confirm_shipping($order_id) {
    if (!$order_id) return false;
    
    D()->update(ectable('order_info'),
                ['shipping_status'=>SS_RECEIVED,'shipping_confirm_time'=>simphp_gmtime()],
                ['order_id'=>$order_id], true);
    
    if (D()->affected_rows()==1) {
  
      //写order_action的日志
      self::order_action_log($order_id, ['action_note'=>'用户确认收货']);
  
      return true;
    }
    return false;
  }
  
  /**
   * 
   * @param integer $order_id
   * @return array
   */
  static function info($order_id) {
    if (empty($order_id)) return false;
    $order_id = intval($order_id);
    $row = D()->from(ectable('order_info'))->where(['order_id'=>$order_id])->select()->get_one();
    return $row;
  }
  
}
 
/*----- END FILE: class.Order.php -----*/