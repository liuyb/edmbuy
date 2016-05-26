<?php

/**
 * 退款监听任务
 * 1，用户申请退款 超过一定时间 商家没处理，直接完成退款
 * 2，商家拒绝 超过一定时间 用户没处理 订单状态变成待发货
 * @author Jean
 *
 */
class RefundMonitorJob extends CronJob{
    
    /**
     * 3天时间秒数
     * @var constant
     */
    const TIME_3DAYS = 259200; //=86400*3
    
    /**
     * {@inheritDoc}
     * @see CronJob::main()
     */
    public function main($argc, $argv){
        self::handleUserOvertime();
    }
    
    /**
     * 商家没有处理退款申请超时
     */
    private function handleMerchantOvertime(){
        $daysago = date('Y-m-d H:i:s',(time() - self::TIME_3DAYS));
        $sql = "SELECT * FROM shp_order_refund where check_status = 0 and is_done=0 and refund_time <= '%s'";
        $list = D()->query($sql, $daysago)->fetch_array_all();
        
        foreach ($list as $refund){
            //self::systemRefundAction($refund['rec_id']);
        }
    }
    
    /**
     * 商家拒绝退款申请后 买家没有处理 用refuse_time比较
     */
    public function handleUserOvertime(){
        $daysago = time() - self::TIME_3DAYS;
        $sql = "SELECT * FROM shp_order_refund where check_status = ".OrderRefund::CHECK_STATUS_NO." and is_done=0 and refuse_time <= %d";
        $list = D()->query($sql, $daysago)->fetch_array_all();
        $this->log('count user overtime refund apply those was refused by merchant: '.count($list));
        foreach ($list as $refund){
            self::refundApplyRollback($refund);
        }
    }
    
    /**
     * 系统退款处理
     * @param unknown $rec_id
     */
    private static function systemRefundAction($rec_id){
        $refund = OrderRefund::load($rec_id);
        $order = Order::load($refund->order_id);
        if(!$order->is_exist()){
            return;
        }
        if(!OrderRefund::isValidAcceptRefundStatus($order->pay_status, $order->shipping_status)){
            return;
        }
        if($refund->check_status == OrderRefund::CHECK_STATUS_YES){
            return;
        }
        //审核同意
        $refund->check_status = OrderRefund::CHECK_STATUS_YES;
        $refund->action_type = 1;//系统处理
        //微信退款处理
        $params = array('order_no' => $refund->order_sn, 'trans_id' => $refund->pay_trade_no,
            'refund_no' => $refund->refund_sn, 'refund_fee' => $refund->refund_money, 'total_fee' => $refund->trade_money);
        $result = Wxpay::orderRefund($params);
        //退款成功
        if($result && $result['code'] == 'SUCC'){
            OrderRefund::order_refund($order, $refund->refund_money);
            $refund->wx_status = OrderRefund::WX_STATUS_SUCC;
            $refund->pay_refund_no = $result['wx_refund_no'];
            $refund->succ_time = $result['succ_time'];
        }else{
            $refund->wx_status = OrderRefund::WX_STATUS_FAIL;
            $extra = ['succ_time' => simphp_dtime(), 'order_sn' => $refund->order_sn,
                'refund_sn' => $refund->refund_sn, 'refund_money' => $refund->refund_money,
                'reason' => $result['msg']
            ];
            WxTplMsg::refund_msg(self::getUserOpenid($refund->user_id), '尊敬的会员，您有一笔退款申请在退款时出现失败！请及时重新提交退款申请。', U('order/detail','',true), $extra);
        }
        $refund->wx_response = $result['msg'];
        $refund->save(Storage::SAVE_UPDATE);
    }
    
    /**
     * 退款申请回滚
     * @param unknown $refund
     */
    private static function refundApplyRollback($ret){
        D()->beginTransaction();
        
        $refund = new OrderRefund();
        $refund->rec_id = $ret['rec_id'];
        $refund->is_done = 1;
        $refund->action_type = 1;
        $refund->check_status = OrderRefund::CHECK_STATUS_CANCEL;
        $refund->save(Storage::SAVE_UPDATE_IGNORE);
        
        $order = new Order();
        $order->order_id = $ret['order_id'];
        $order->pay_status = PS_PAYED;
        $order->order_status = OS_CONFIRMED;
        $order->save(Storage::SAVE_UPDATE_IGNORE);
        
        D()->commit();
    }
    
    private static function getUserOpenid($user_id){
        return D()->from(Users::table())->where("user_id = '%d'", $user_id)->select('openid')->result();
    }
}

?>