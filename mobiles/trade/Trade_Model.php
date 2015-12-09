<?php
/**
 * Trade Model
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Trade_Model extends Model {
  

  /**
   * 将支付LOG插入数据表
   *
   * @access  public
   * @param   integer     $order_id   订单编号
   * @param   string      $order_sn   订单号
   * @param   float       $amount     订单金额
   * @param   integer     $type       支付类型
   * @param   integer     $is_paid    是否已支付
   *
   * @return  int
   */
  public static function insertPayLog($order_id, $order_sn, $amount, $type = PAY_ORDER, $is_paid = 0) {
    $insert = [
      'order_id'    => $order_id,
      'order_sn'    => $order_sn,
      'order_amount'=> $amount,
      'order_type'  => $type,
      'is_paid'     => $is_paid,
    ];
    $log_id = D()->insert(ectable('pay_log'), $insert, true, true);
    return $log_id;
  }
  
}
 
/*----- END FILE: Trade_Model.php -----*/