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
                if($level == 3){
                    $item_id = GOLD_AGENT_GOODS_ID;
                }else if($level == 4){
                    $item_id = SILVER_AGENT_GOODS_ID;
                }
                self::createOrder($user, '', $money, Order::ORDER_FLAG_AGENT, $item_id);
            }else if($level == 5){
                $password = '123456';
                if($user['mobile']){
                    $password = substr($mobile, -6);
                }
                User_Model::saveMerchantInfo($mobile, $user['parent_id'], $password,$user_id);
                $m = Merchant::getMerchantByUserId($user_id);
                self::createOrder($user, $m->uid, $money, Order::ORDER_FLAG_MERCHANT, MECHANT_GOODS_ID);
            }
        }
    }
    
    /**
     * 创建订单
     * @param unknown $user_id
     * @param unknown $merchant_id
     */
    private function createOrder($user, $merchant_id, $amount, $type, $item_id){
        $user_id = $user['user_id'];
        $level = $user['level'];
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
        $newOrder->money_paid = $amount;
        if($level == Users::USER_LEVEL_3){
            $newOrder->commision    = 198;
        }else if($level == Users::USER_LEVEL_4){
            $newOrder->commision    = 98;
        }else if($level == Users::USER_LEVEL_5){
            $newOrder->commision    = 999;
        }
        $newOrder->confirm_time = simphp_gmtime();
        $newOrder->pay_time = simphp_gmtime();
        //...
        $newOrder->referer      = isset($_GET['refer']) && !empty($_GET['refer']) ? $_GET['refer'] : '本站';
        $newOrder->add_time     = simphp_gmtime(); //跟从ecshop习惯，使用格林威治时间
        //...
        $newOrder->merchant_ids = 'mc_570b4e6b540ae';
        
        $newOrder->order_flag = $type;
        
        $newOrder->save(Storage::SAVE_INSERT_IGNORE);
        
        if($newOrder->is_exist()){
            
            $order_id = $newOrder->id;
            $cItem = Items::load($item_id);
            $newOI = new OrderItems();
            $newOI->order_id    = $order_id;
            $newOI->goods_id    = $item_id;
            $newOI->goods_name  = $cItem->item_name;
            $newOI->goods_sn    = $cItem->item_sn;
            $newOI->product_id  = 0;
            $newOI->goods_number= 1;
            $newOI->market_price= $cItem->market_price; //market_price,shop_price,income_price这三个字段使用最新的信息
            $newOI->goods_price = $cItem->shop_price;
            $newOI->income_price= $cItem->income_price;
            $newOI->goods_attr  = '';
            $newOI->send_number = 0;
            $newOI->is_real     = $cItem->is_real;
            $newOI->extension_code = $cItem->extension_code;
            $newOI->parent_id   = 0;
            $newOI->is_gift     = 0;
            $newOI->goods_attr_id = 0;
            $newOI->merchant_ids = $cItem->merchant_id;
            $newOI->save(Storage::SAVE_INSERT_IGNORE);
            
            $order_update = [];
            if ($newOI->id) {
                // 生成表 pay_log 记录
                PayLog::insert($order_id, $newOrder->order_sn, $newOrder->order_amount, PAY_SURPLUS, 1);
            }
            
            
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