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
        $list = $this->getMerchantPaidUser();
        $count = count($list);
        $this->log("get wait execute count ".$count);
        require SIMPHP_ROOT . "/mobiles/user/User_Model.php";
        foreach ($list as $user){
            $password = '123456';
            if($user->mobile){
                $password = substr($user->mobile, -6);
            }
            User_Model::saveMerchantInfo($user->mobile, $user->parentid, $password,$user->uid);
            $m = Merchant::getMerchantByUserId($user->uid);
            self::createOrder($user->uid, $m->uid);
        }
    }
    
    /**
     * 创建订单
     * @param unknown $user_id
     * @param unknown $merchant_id
     */
    private function createOrder($user_id, $merchant_id){
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
        $newOrder->goods_amount = 999;
        $newOrder->shipping_fee = 0;
        $newOrder->insure_fee   = 0;
        $newOrder->pay_fee      = 0;
        $newOrder->pack_fee     = 0;
        $newOrder->card_fee     = 0;
        $newOrder->tax          = 0;
        $newOrder->discount     = 0;
        $newOrder->order_amount = 999;
        $newOrder->commision    = 0;
        //...
        $newOrder->referer      = isset($_GET['refer']) && !empty($_GET['refer']) ? $_GET['refer'] : '本站';
        $newOrder->add_time     = simphp_gmtime(); //跟从ecshop习惯，使用格林威治时间
        //...
        
        $newOrder->save(Storage::SAVE_INSERT_IGNORE);
        
        if($newOrder->is_exist()){
            $mch = new Merchant();
            $mch->uid = $merchant_id;
            $mch->activation = 1;
            $mch->save(Storage::SAVE_UPDATE);
            Merchant::addMerchantPayment($merchant_id, $user_id, $newOrder);
            Merchant::setPaymentIfIsMerchantOrder($newOrder->order_id, 999);
        }
    }
    
    private static function getMerchantPaidUser(){
        $list = Users::find(new Query('level', Users::USER_LEVEL_5));
        return $list;
    }
}

?>