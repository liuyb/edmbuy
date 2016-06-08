<?php
defined('IN_SIMPHP') or die('Access Denied');

class OrderRefund_Model extends Model{
    
    /**
     * 退款列表显示
     * @param Pager $pager
     * @param array $options
     */
    static function getPagedOrders(Pager $pager, array $options){
        $muid = $GLOBALS['user']->uid;
        $where = "";
        if($options['order_sn']){
            $where .= " and order_sn like '%%".D()->escape_string(trim($options['order_sn']))."%%' ";
        }
        if($options['buyer']){
            $buy = D()->escape_string(trim($options['buyer']));
            $where .= " and (consignee like '%%".$buy."%%' or nick_name like '%%".$buy."%%') ";
        }
        if($options['check_status']){
            if($options['check_status'] == 'wait_check'){
                $where .= " and check_status = 0 ";
            }else{
                $where .= " and check_status > 0 ";
            }
        }
        $sql = "SELECT count(1) FROM shp_order_refund where merchant_id='%s' $where ";
        $count = D()->query($sql, $muid)->result();
        $pager->setTotalNum($count);
        $sql = "SELECT * from shp_order_refund where merchant_id='%s' $where order by rec_id desc limit {$pager->start},{$pager->pagesize}";
        $orders = D()->query($sql, $muid)->fetch_array_all();
        $pager->setResult($orders);
    }
    
    /**
     * 商家退款拒绝
     * @param unknown $rec_id
     * @param unknown $refuse_txt
     */
    static function refuseRefund($rec_id, $refuse_txt){
        $refund = OrderRefund::load($rec_id);
        $muid = $GLOBALS['user']->uid;
        if(!$refund->is_exist() || $muid != $refund->merchant_id){
            return array('result' => 'FAIL', 'msg' => '订单数据不存在');
        }
        D()->beginTransaction();
        try{
            $refund->check_status = OrderRefund::CHECK_STATUS_NO;
            $refund->refuse_txt = $refuse_txt;
            $refund->refuse_time = time();
            $refund->save(Storage::SAVE_UPDATE);
            Order::action_log($refund->order_id, ['action_note'=>'商家退款拒绝', 'action_user'=>$muid]);
            $ret = ['result' => 'SUCC'];
            //修改订单状态 退款拒绝
            D()->update(Order::table(), array('order_status'=>OS_REFUND_REFUSED), "order_id=$refund->order_id");
            
            $extra = ['succ_time' => simphp_dtime(), 'order_sn' => $refund->order_sn,
                      'refund_sn' => $refund->refund_sn, 'refund_money' => $refund->refund_money,
                      'reason' => $refuse_txt
            ];
            $msg = '尊敬的会员，您有一笔退款申请被商家拒绝，点击详情查看被拒绝理由。如需申诉，请及时向益多米官方提交申诉材料。';
            WxTplMsg::refund_msg(self::getUserOpenid($refund->user_id), $msg, U('order/detail','',true), $extra);
        }catch (Exception $e){
            D()->rollback();
            $ret = ['result' => 'FAIL', 'msg' => $e->getMessage()];
        }
        D()->commit();
        return $ret;
    }
    
    /**
     * 同意退款
     * @param unknown $order_id
     */
    static function acceptRefund($rec_id){
        $refund = OrderRefund::load($rec_id);
        $order = Order::load($refund->order_id);
        $muid = $GLOBALS['user']->uid;
        if(!$order->is_exist() || $muid != $order->merchant_ids){
            return array('result' => 'FAIL', 'msg' => '订单数据不存在');
        }
        if(!OrderRefund::isValidAcceptRefundStatus($order->pay_status, $order->shipping_status)){
            return array('result' => 'FAIL', 'msg' => '当前订单状态不支持退款');
        }
        if($refund->check_status == OrderRefund::CHECK_STATUS_YES){
            return array('result' => 'FAIL', 'msg' => '订单已在退款中，请求重复');
        }
        //审核同意
        $refund->check_status = OrderRefund::CHECK_STATUS_YES;
        //微信退款处理
        $params = array('order_no' => $refund->order_sn, 'trans_id' => $refund->pay_trade_no,
                        'refund_no' => $refund->refund_sn, 'refund_fee' => $refund->refund_money, 'total_fee' => $refund->trade_money);
        //首先判断订单是否有父订单，如果有父订单 判断是用的父订单支付还是用的子订单支付。
        if($order->parent_id){
            $porder = Order::load($order->parent_id);
            //如果子订单单独支付 没有交易号
            if($porder->pay_trade_no){
                $params['order_no'] = $porder->order_sn;
                $params['trans_id'] = $porder->pay_trade_no;
                $params['total_fee'] = $porder->money_paid;
            }
        }
        $result = Wxpay::orderRefund($params);
        //退款成功
        if($result && $result['code'] == 'SUCC'){
            OrderRefund::order_refund($order, $refund->refund_money);
            
            $refund->wx_status = OrderRefund::WX_STATUS_SUCC;
            $refund->pay_refund_no = $result['wx_refund_no'];
            $refund->succ_time = $result['succ_time'];
            $refund->is_done = 1;
        }else{
            $refund->wx_status = OrderRefund::WX_STATUS_FAIL;
            //修改订单状态 退款失败
            D()->update(Order::table(), array('order_status'=>OS_REFUND_FAILED), "order_id=$refund->order_id");
            $extra = ['succ_time' => simphp_dtime(), 'order_sn' => $refund->order_sn,
                'refund_sn' => $refund->refund_sn, 'refund_money' => $refund->refund_money,
                'reason' => $result['msg']
            ];
            WxTplMsg::refund_msg(self::getUserOpenid($refund->user_id), '尊敬的会员，您有一笔退款申请在退款时出现失败！请及时重新提交退款申请。', U('order/detail','',true), $extra);
        }
        $refund->wx_response = $result['msg'];
        $refund->save(Storage::SAVE_UPDATE);
        return ['result' => 'SUCC'];
    }
    
    private static function getUserOpenid($user_id){
        return D()->from(Users::table())->where("user_id = '%d'", $user_id)->select('openid')->result();
    }
    
}

?>