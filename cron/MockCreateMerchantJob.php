<?php
/**
 * 模拟线上创建商家
 * @author Jean
 *
 */
class MockCreateMerchantJob extends CronJob{
    
    /**
     * {@inheritDoc}
     * @see CronJob::main()
     */
    public function main($argc, $argv){
        $this->log("begin execute job for mock create merchant...");
        $list = $this->getPrePaidUser();
        $count = count($list);
        $this->log("get wait execute count ".$count);
        require SIMPHP_ROOT . "/mobiles/user/User_Model.php";
        foreach ($list as $user){
            $level = $user['level'];
            $money = $user['money'];
            $mobile = $user['mobile'];
            $user_id = $user['user_id'];
            if(in_array($level, [3,4])){
                self::createOrder($user, '', $money, Order::ORDER_FLAG_AGENT);
            }else if($level == 5){
                $password = '123456';
                if($user['mobile']){
                    $password = substr($mobile, -6);
                }
                User_Model::saveMerchantInfo($mobile, $user['parent_id'], $password,$user_id);
                $m = Merchant::getMerchantByUserId($user_id);
                self::createOrder($user, $m->uid, $money, Order::ORDER_FLAG_MERCHANT);
            }
        }
    }
    
    /**
     * 创建订单
     * @param unknown $user_id
     * @param unknown $merchant_id
     */
    private function createOrder($user, $merchant_id, $amount, $type){
        $user_id = $user['user_id'];
        // 生成订单信息
        $newOrder = new Order();
        $newOrder->order_sn     = Fn::gen_order_no();
        $newOrder->pay_trade_no = '';
        $newOrder->user_id      = $user_id;
        $newOrder->order_status = OS_CONFIRMED;
        $newOrder->shipping_status = SS_RECEIVED;
        $newOrder->pay_status   = PS_PAYED;
        
        $newOrder->how_oos      = Fn::oos_status(OOS_CONSULT);
        
        $newOrder->pay_id       = 1;
        $newOrder->pay_name     = '';
        $newOrder->postscript   = '';
        $newOrder->how_surplus  = '';
        //...
        $newOrder->goods_amount = $amount;
        $newOrder->shipping_fee = 0;
        $newOrder->insure_fee   = 0;
        $newOrder->pay_fee      = 0;
        $newOrder->pack_fee     = 0;
        $newOrder->card_fee     = 0;
        $newOrder->tax          = 0;
        $newOrder->discount     = 0;
        $newOrder->order_amount = $amount;
        $newOrder->commision    = 0;
        //...
        $newOrder->referer      = isset($_GET['refer']) && !empty($_GET['refer']) ? $_GET['refer'] : '本站';
        $newOrder->add_time     = simphp_gmtime(); //跟从ecshop习惯，使用格林威治时间
        //...
        $newOrder->merchant_ids = 'mc_570b4e6b540ae';
        
        $newOrder->order_flag = $type;
        
        $newOrder->save(Storage::SAVE_INSERT_IGNORE);
        
        if($newOrder->is_exist()){
            $cUser = Users::load($user_id);
            if($type == Order::ORDER_FLAG_MERCHANT){
                
                Merchant::addMerchantPayment($merchant_id, $user_id, $newOrder);
                
                if(Merchant::setPaymentIfIsMerchantOrder($newOrder->order_id, $amount)){
                    //是购买店铺时 用店铺分佣模式
                    UserCommision::generatForMerchant($newOrder, $cUser);
                }
            }else if($type == Order::ORDER_FLAG_AGENT){
                $level = $user['level'];
                $order_id = $newOrder->order_id;
                AgentPayment::createAgentPayment($user_id, $order_id, $level);
                $agent = AgentPayment::getAgentByOrderId($order_id);
                if(AgentPayment::callbackAfterAgentPay($agent, $cUser)){
                    Order::setOrderShippingReceived($order_id);
                    UserCommision::generatForAgent($newOrder, $cUser, $agent);
                }
            }
            
            D()->query("update tb_ini_user set sync_flag = 1 where user_id = $user_id");
        }
    }
    
    private static function getPrePaidUser(){
        $sql = "select * from tb_ini_user where sync_flag = 0 and level in (3,4,5)";
        $list = D()->query($sql)->fetch_array_all();
        return $list;
    }
}

?>