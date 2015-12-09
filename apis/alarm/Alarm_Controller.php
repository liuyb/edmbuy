<?php
/**
 * 告警回调通知
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Alarm_Controller extends Controller {
  
  /**
   * action 'wxpay'
   * 微信支付告警通知回调
   *
   * @param Request $request
   * @param Response $response
   */
  public function wxpay(Request $request, Response $response)
  {
    trace_debug('api_alarm_wxpay_get', $_GET);
    trace_debug('api_alarm_wxpay_post', $_POST);
    echo "OK";
  }
  
}
 
/*----- END FILE: Alarm_Controller.php -----*/