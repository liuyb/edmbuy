<?php
/**
 * 微信支付回调通知
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Wxpay_Controller extends Controller {
  
  /**
   * action 'notify'
   * 微信支付统一下订单回调
   *
   * @param Request $request
   * @param Response $response
   */
  public function notify(Request $request, Response $response)
  {
     
    Wxpay::nofify(function($data, &$msg){
      
      //trace_debug('api_wxpay_notify_data', $data);
      //trace_debug('api_wxpay_notify_msg', $msg);
      
      if ('OK'==$msg) { // 成功合法的订单
        
        // 关注的数据
        $openid         = $data['openid'];         //用户openid
        $is_subscribe   = $data['is_subscribe'];   //是否关注公众账号：'Y' or 'N'
        $total_fee      = $data['total_fee'];      //订单总金额，单位为分
        $bank_type      = $data['bank_type'];      //付款银行
        $transaction_id = $data['transaction_id']; //微信支付订单号
        $order_sn       = $data['out_trade_no'];   //自身订单号
        $attach         = isset($data['attach']) ? $data['attach'] : ''; //商家自定义数据，原样返回
        $time_end       = $data['time_end'];       //支付完成时间，格式为yyyyMMddHHmmss，如：20141030133525
        $time_end       = Fn::to_timestamp($time_end);
        
        //对日志表"写锁定"，避免其他线程进入干扰(这时其他线程对表pay_log的读写都要等待)
        $ec_paylog = PayLog::table();
        $ec_order  = Order::table();
        //D()->lock_tables($ec_paylog, DB::LOCK_WRITE, '', TRUE);
        D()->beginTransaction();
        
        //检查支付日志表，以确定订单是否存在(之所以用日志表而不是主表order_info，是为了在锁表期间不阻塞到前台访问频繁的主表)
        $pay_log = D()->from($ec_paylog) //这里必须用写模式，因为lock表在写进程
                      ->where(['order_sn'=>$order_sn])
                      ->for_update()
                      ->select()
                      ->get_one();
        if (empty($pay_log)) {
          $msg = '订单号不存在';
          //D()->unlock_tables();// 解锁
          D()->commit();
          return false;
        }
        $order_id = $pay_log['order_id'];
        
        //检查支付金额是否正确
        if (Fn::money_fen($pay_log['order_amount']) != $total_fee) {
          $msg = '金额不对';
          //D()->unlock_tables();// 解锁
          D()->commit();
          return false;
        }
        
        //检查订单状态
        if (!$pay_log['is_paid']) { //未付款
          
          //更新pay_log
          D()->update($ec_paylog, ['is_paid'=>1], ['order_id'=>$order_id]);
          
          // 更新完立马解锁
          //D()->unlock_tables();
          D()->commit();
        
          //立马修改订单状态为已付款
          $updata = [
            'pay_trade_no'   => $transaction_id,
            'order_status'   => OS_CONFIRMED,
            'confirm_time'   => simphp_gmtime(), //跟从ecshop习惯，使用格林威治时间
            'pay_status'     => PS_PAYED,
            'pay_time'       => simphp_gmtime($time_end),
            'money_paid'     => $pay_log['order_amount'],
            'order_amount'   => 0, //将order_amount设为0
            'pay_data2'      => json_encode($data) //保存微信支付接口的返回
          ];
          D()->update($ec_order, $updata, ['order_id'=>$order_id]);
          
          //更改可能子订单的状态
          if ($order_id) { //出于严格判断
          	D()->query("UPDATE {$ec_order} SET money_paid=order_amount,order_amount=0 WHERE `parent_id`=%d", $order_id);
          	unset($updata['money_paid'],$updata['order_amount'],$updata['pay_data2']);
          	D()->update($ec_order, $updata, ['parent_id'=>$order_id]);
          }
          
        
          //记录订单操作记录
          Order::action_log($order_id, ['action_note'=>'用户支付']);
          
          //检查用户资格
          $cUser = Users::load_by_openid($openid);
          if ($cUser->is_exist()) {
          	$cUser->check_level();
          }
          
          //更新订单下所有商品的"订单数"
          Items::updateOrderCntByOrderid($order_id);
          
          //更新订单下所有商品卖出的"单品数"
          Items::updatePaidNumByOrderid($order_id);
          
          $order = Order::load($order_id);
          //当前订单是金牌银牌代理更新
          if($order->order_flag == Order::ORDER_FLAG_AGENT){
              $agent = AgentPayment::getAgentByOrderId($order_id);
              if(AgentPayment::callbackAfterAgentPay($agent, $cUser)){
                  Order::setOrderShippingReceived($order_id);
                  UserCommision::generatForAgent($order, $cUser, $agent);
              }
          }else if($order->order_flag == Order::ORDER_FLAG_MERCHANT){
              if(Merchant::setPaymentIfIsMerchantOrder($order_id, $pay_log['order_amount'])){
                  //是购买店铺时 用店铺分佣模式
                  UserCommision::generatForMerchant($order, $cUser);
              }
          }else{
              //普通商品交易设置佣金计算
              UserCommision::generate($order);
          }
          //微信通知
          $cUser->notify_buyer_pay_succ($order);
        }
        else {
          //D()->unlock_tables();// 解锁
          D()->commit();
        }
        
        return true;
      }
      return false;
      
    });
    //trace_debug('wxpay_final', $GLOBALS['HTTP_RAW_POST_DATA']);
    exit; //应该退出，避免上层影响返回
  }
  
}

/*----- END FILE: Wxpay_Controller.php -----*/