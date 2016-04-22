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
        $sql = "SELECT * from shp_order_refund where merchant_id='%s' $where limit {$pager->start},{$pager->pagesize}";
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
            $refund->save(Storage::SAVE_UPDATE);
            Order::action_log($refund->order_id, ['action_note'=>'商家退款拒绝', 'action_user'=>$muid]);
            $ret = ['result' => 'SUCC'];
            
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
        return ['result' => 'SUCC'];
    }
    
    private static function getUserOpenid($user_id){
        return D()->from(Users::table())->where("user_id = '%d'", $user_id)->select('openid')->result();
    }
    
    /**
     * 返回退款状态
     * @param unknown $check_status 审核状态
     * @param unknown $wx_status 微信返回状态
     * @return string
     */
    static function getRefundStatus($check_status, $wx_status){
        $check_status = intval($check_status);
        $wx_status = intval($wx_status);
        $display = '';
        switch($check_status){
            case 1 :
                $display = ($wx_status == 1 ? '退款成功' : ($wx_status == 2 ? '退款失败' : '退款中'));
                break;
            case 2 :
                $display = '已拒绝';
                break;
            default :
                $display = '待审核';
        }
        return $display;
    }
    
    static function getRefundDetails($rec_id){
        $sql = "SELECT refund.*,u.mobile_phone as mobilephone
                FROM shp_order_refund refund left join shp_users u on refund.user_id = u.user_id where refund.rec_id='%d'";
        $result = D()->query($sql, $rec_id)->fetch_array();
        return $result;
    }
}

?>