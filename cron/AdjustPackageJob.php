<?php

/**
 * 调整用户套餐情况
 * 
 * uid 398 -> 698
 * uid 698 -> 398
 * uid 398 -> 999
 * uid 698 -> 999
 * uid 999 -> [398|698]
 * @author Jean
 *
 */
class AdjustPackageJob extends CronJob {
	
    private $packages = [398,698,999];
    
	public function main($argc, $argv) {
        if(count($argv) != 4){
            exit('incorrect arguments.');
        }
        $uid = $argv[1];
        $old_pack = $argv[2];
        $new_pack = $argv[3];
        
        if(!in_array($old_pack, $this->packages) || !in_array($new_pack, $this->packages)){
            exit('incorrect packages.');
        }
        $user = Users::load($uid);
        if(!$user->is_exist()){
            exit('user not exists.');
        }
        require SIMPHP_ROOT . "/cron/MockCreateMerchantJob.php";
        
        if($old_pack == 398 || $old_pack == 698){
            $agent = $this->getOldAgentPackage($uid, $old_pack);
            if(empty($agent) || !$agent['pid']){
                exit("agent payment doesn't exists");
            }
            if($agent['premium_id']){
                exit("premium id aready exists.");
            }
            D()->beginTransaction();
            $this->removePaymentRecords($agent, Order::ORDER_FLAG_AGENT);
            //用户级别失效
            $user->level = 0;
            $user->save(Storage::SAVE_UPDATE_IGNORE);
            if($new_pack == 999){
                //创建新商家
                $this->createMerchant($user, $new_pack);
            }else{
                //重新创建新代理
                $mock = new MockCreateMerchantJob();
                $mock->createOrder(['user_id' => $uid, 'level' =>  $new_pack == 698 ? Users::USER_LEVEL_4 : Users::USER_LEVEL_3],
                    '', $new_pack, Order::ORDER_FLAG_AGENT, ($new_pack == 698?GOLD_AGENT_GOODS_ID : SILVER_AGENT_GOODS_ID));
            }
            D()->commit();
        }else if($old_pack == 999){
            if($new_pack == 999){
                exit('incorrect new package.');
            }
            $result = $this->removeMerchantRecords($uid);
            $user->level = 0;
            $user->save(Storage::SAVE_UPDATE_IGNORE);
            //重新创建新代理
            $mock = new MockCreateMerchantJob();
            $mock->createOrder(['user_id' => $uid, 'level' => $new_pack == 698 ? Users::USER_LEVEL_4 : Users::USER_LEVEL_3],
                '', $new_pack, Order::ORDER_FLAG_AGENT, ($new_pack == 698?GOLD_AGENT_GOODS_ID : SILVER_AGENT_GOODS_ID));
        }
	}
	
	private function getOldAgentPackage($uid, $pack){
	    $sql = "select * from shp_agent_payment where user_id = %d and is_paid=1 and level = %d";
	    $result = D()->query($sql, $uid, $pack == 698 ? Users::USER_LEVEL_4 : Users::USER_LEVEL_3)->fetch_array();
	    return $result;
	}
	
	/**
	 * 删除商家记录
	 */
	private function removeMerchantRecords($user_id){
	    $time =date('Y-m-d H:i:s' ,time());
	    $sql = "select * from shp_merchant_payment where user_id =%d and start_time <= '{$time}' and end_time >='{$time}' and money_paid > 0 ";
	    $result = D()->query($sql, $user_id)->fetch_array();
	    if(empty($result) || !$result->rid){
	        exit("merchant payment dosen't exists.");
	    }
	    $mid = $result['merchant_id'];
	    $merchant = Merchant::load($mid);
	    //商家取消激活
	    $merchant->activation = 0;
	    
	    $this->removePaymentRecords($result, Order::ORDER_FLAG_MERCHANT);
	    return $result;
	}
	
	/**
	 * 创建一个商家
	 * @param unknown $user
	 * @param unknown $new_pack
	 */
	private function createMerchant($user, $new_pack){
	    $password = '123456';
	    $user_id = $user->uid;
	    $mobile = $user->mobile;
	    $parent_id = $user->parentid;
	    if($mobile){
	        $password = substr($mobile, -6);
	    }
	    User_Model::saveMerchantInfo($mobile, $parent_id, $password,$user_id);
	    $m = Merchant::getMerchantByUserId($user_id);
	    $mock = new MockCreateMerchantJob();
        $mock->createOrder(['user_id' => $user->uid, 'level' => 5 ], $m->uid, $new_pack, Order::ORDER_FLAG_MERCHANT, MECHANT_GOODS_ID);
	}
	
	/**
	 * 清除 代理、入驻 对应的支付记录
	 * @param unknown $agent
	 */
	private function removePaymentRecords($payment, $type){
	     
	    //订单失效
	    $order_id = $payment['order_id'];
	    $order = new Order();
	    $order->order_id = $order_id;
	    $order->money_paid = 0;
	    $order->is_delete = 1;
	    $order->save(Storage::SAVE_UPDATE_IGNORE);
	     
	    //订单支付失效
	    D()->query('update shp_pay_log set is_paid = 0 where order_id = %d', $order_id);
	     
	    //佣金失效
	    D()->query('update shp_user_commision set state = %d where order_id = %d', UserCommision::STATE_INVALID, $order_id);
	     
	    if(Order::ORDER_FLAG_AGENT){
	        //代理支付失效
	        D()->query('update shp_agent_payment set is_paid = 0 where pid=%d', $payment['pid']);
	    }else if(Order::ORDER_FLAG_MERCHANT){
	        //商家支付取消记录
	        D()->query("update shp_merchant_payment set money_paid = 0 where rid = %d",$payment['rid']);
	    }
	     
	}
}
