<?php
/**
 * 测试作业
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
class TestJob extends CronJob {
  
  
  public function main($argc, $argv) {
    
      $order = Order::load(1840);
      $cUser = Users::load_by_openid('odyFWsxLsxs9U2P6Z3sV4S7O7D5I');
      $cUser->notify_buyer_pay_succ($order);
  }
  
}
 
/*----- END FILE: TestJob.php -----*/