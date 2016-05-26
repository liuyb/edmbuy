<?php
/**
 * Partner Controller
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Order_Controller extends MobileController {
	
	/**
	 * hook init
	 *
	 * @param string $action
	 * @param Request $request
	 * @param Response $response
	 */
	public function init($action, Request $request, Response $response)
	{
		$this->nav_flag1 = 'order';
		parent::init($action, $request, $response);
	}
	
	/**
	 * hook menu
	 * @see Controller::menu()
	 */
	public function menu()
	{
		return [
			'order' => 'index',
		    'order/%d/detail' => 'order_detail',
		    'order/%d/express' => 'order_express',
		    'order/refund'   => 'order_refund',
		    'order/refund/info' => 'order_refund_info',
		    'order/refund/cancel' => 'order_refund_cancel',
		    'order/refund/again'  => 'order_refund_modify'
		];
	}
	
	/**
	 * default action 'index'
	 *
	 * @param Request $request
	 * @param Response $response
	 */
	public function index(Request $request, Response $response)
	{
		$this->setPageView($request, $response, '_page_mpa');
		$this->v->set_tplname('mod_order_index');
		$this->topnav_no = 1;
		$this->nav_no    = 0;
        
		if ($request->is_hashreq()) {
			
		}
		else {
			
		}
		throw new ViewResponse($this->v);
	}
	
	/**
	 * 订单详情
	 * @param Request $request
	 * @param Response $response
	 * @throws ViewResponse
	 */
	public function order_detail(Request $request, Response $response)
	{
		$this->setPageView($request, $response, '_page_mpa');
	    $this->v->set_tplname('mod_order_detail');
	    $this->nav_no    = 0;
	    $this->topnav_no = 1;
	
	    if (1||$request->is_hashreq()) {
	       $order_id  = $request->arg(1);
	       $order = Order::load($order_id);
	       //$order_detail = Order::getOrderDetail($order_id);
	       $regionIds = [$order->province, $order->city, $order->district];
	       $order_region = Order_Model::getOrderRegion($regionIds);
	       $order->order_region = $order_region;
	       $merchant_goods = Order_Model::getOrderItems($order_id);
	       //$this->v->assign("item", $order_detail);
	       $this->v->assign("order", $order);
	       $this->v->assign("order_id", $order_id);
	       $this->v->assign("merchant_goods", $merchant_goods);
	       $merchant = Merchant::load($order->merchant_ids);
	       $this->v->assign('merchant', $merchant);
	       //商家客服
			$ent_id = Merchant::getMerchantKefu($order->merchant_ids);
			$this->v->assign('ent_id', $ent_id);
	    }
	
	    throw new ViewResponse($this->v);
	}
	
	/**
	 * 订单的物流信息
	 * @param Request $request
	 * @param Response $response
	 * @throws ViewResponse
	 */
	public function order_express(Request $request, Response $response)
	{
		$this->setPageView($request, $response, '_page_mpa');
	    $this->v->set_tplname('mod_order_express');
	    $this->nav_no    = 0;
	    $this->topnav_no = 1;
	
	    if (1||$request->is_hashreq()) {
	        $order_id  = $request->arg(1);
	        $express = Order::getOrderExpress($order_id);
	        $this->v->assign("express", $express);
	    }
	
	    throw new ViewResponse($this->v);
	}
	
	/**
	 * 退款申请
	 * @param Request $request
	 * @param Response $response
	 */
	public function order_refund(Request $request, Response $response)
	{
	    if ($request->is_post()) {
	        $ret = self::order_refund_common($request, false);
	        $response->sendJSON($ret);
	    }
	}
	
	/**
	 * 订单退款详情
	 * @param Request $request
	 * @param Response $response
	 * @throws ViewResponse
	 */
	public function order_refund_info(Request $request, Response $response){
	    $this->setPageView($request, $response, '_page_mpa');
	    $this->v->set_tplname('mod_order_refundinfo');
	    $this->nav_no    = 0;
	    $this->topnav_no = 1;
	    $order_id  = $request->get('order_id');
	    $refund = Order_Model::getOrderRefundDetail($order_id);
	    $this->v->assign("refund", $refund);
	    throw new ViewResponse($this->v);
	}
	
	/**
	 * 退款申请撤销
	 * @param Request $request
	 * @param Response $response
	 */
	public function order_refund_cancel(Request $request, Response $response){
	    $ret = ['flag'=>'FAIL'];
	    if($request->is_post()){
	        $rec_id = $request->post('rec_id');
	        $refund = OrderRefund::load($rec_id);
	        if(!$refund->is_exist()){
	            $ret['msg'] = '退款申请不存在！';
	            $response->sendJSON($ret);
	        }
	        if($refund->is_done){
	            $ret['msg'] = '退款申请已完成，不能撤销！';
	            $response->sendJSON($ret);
	        }
	        Order_Model::OrderRefundCancel($refund);
	         
	        $ret['flag'] = 'SUCC';
	    }
	    $response->sendJSON($ret);
	}
	
	/**
	 * 修改退款申请
	 * @param Request $request
	 * @param Response $response
	 */
	public function order_refund_modify(Request $request, Response $response){
	    if ($request->is_post()) {
	        $ret = self::order_refund_common($request, true);
	        $response->sendJSON($ret);
	    }
	}
	
	/**
	 * 
	 * @param Request $request
	 * @param string $ismodify true修改退款申请，false 发起退款申请
	 */
	private function order_refund_common(Request $request, $ismodify = false){
	    $ret = ['flag'=>'FAIL','msg'=>'退款失败'];
	    $user_id = $GLOBALS['user']->uid;
	    if (!$user_id) {
	        $ret['msg'] = '未登录, 请登录';
	        return $ret;
	    }
	    
	    $order_id = $request->post('order_id', 0);
	    if (!$order_id) {
	        $ret['msg'] = '订单id为空';
	        return $ret;
	    }
	    
	    $order = Order::load($order_id);
	    if(!$order->is_exist() || $user_id != $order->user_id){
	        $ret['msg'] = '订单不存在';
	        return $ret;
	    }
	    if($ismodify){ //修改退款申请
	        //待发货状态时才能处理
	        $valid_status = Fn::get_order_status(CS_AWAIT_SHIP);
	        if(!OrderRefund::isValidAcceptRefundStatus($order->pay_status, $order->shipping_status)){
	            $ret['msg'] = '当前订单状态不支持退款';
	            return $ret;
	        }
	        //修改原退款申请为已处理
	        $rec_id = $request->post('rec_id', 0);
	        D()->query('update shp_order_refund set is_done = 1 where rec_id = %d', $rec_id);
	    }else{
	        //待发货状态时才能处理
	        $valid_status = Fn::get_order_status(CS_AWAIT_SHIP);
	        if(!OrderRefund::isValidRefundStatus($order->pay_status, $order->shipping_status)){
	            $ret['msg'] = '当前订单状态不支持退款';
	            return $ret;
	        }
	    }
	    //不能重复退款
	    $has_refund = D()->from(OrderRefund::table())->where("order_id='%d' and is_done = 0", $order->order_id)
	    ->select('count(1)')->result();
	    if($has_refund){
	        $ret['msg'] = '当前订单已经申请退款，不能重复提交';
	        return $ret;
	    }
	    $refund_reason = $request->post('refund_reason', '');
	    $refund_desc = $request->post('refund_desc', '');
	    
	    $refund = new OrderRefund();
	    $refund->order_sn = $order->order_sn;
	    $refund->order_id = $order_id;
	    $refund->pay_trade_no = $order->pay_trade_no;
	    $refund->refund_sn = Fn::gen_unique_code('R');
	    $refund->trade_money = $order->money_paid;
	    $refund->refund_money = $order->money_paid;
	    $refund->refund_time = date('Y-m-d H:i:s', time());
	    $refund->user_id = $order->user_id;
	    $refund->refund_reason = $refund_reason;
	    $refund->refund_desc = $refund_desc;
	    $refund->is_done = 0;
	    $refund->consignee = $order->consignee;
	    $refund->nick_name = $GLOBALS['user']->nickname;
	    $refund->merchant_id = $order->merchant_ids;
	    $refund->save(Storage::SAVE_INSERT);
	    if (D()->insert_id()) {
	        //订单状态修改成退款中
	        $order->pay_status = PS_REFUNDING;
	        $order->order_status = OS_REFUNDING;
	        $order->save(Storage::SAVE_UPDATE);
	        $ret = ['flag'=>'SUC'];
	    }
	    return $ret;
	}
}

 
/*----- END FILE: Partner_Controller.php -----*/