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
			'order' => 'item',
		    'order/detail/%d' => 'order_detail'
		];
	}
	
	/**
	 * default action 'index'
	 *
	 * @param Request $request
	 * @param Response $response
	 */
	public function item(Request $request, Response $response)
	{
		$this->v->set_tplname('mod_order_index');
        $this->topnav_no = 0;
        $this->nav_no    = 1;
        
        if ($request->is_hashreq()) {
          
        }
        else {
        	
        }
    
        throw new ViewResponse($this->v);
	}
	
	public function order_detail(Request $request, Response $response)
	{
	    $this->v->set_tplname('mod_order_detail');
	    $this->nav_no    = 0;
	    $this->topnav_no = 1;
	
	    if ($request->is_hashreq()) {
	       $order_id  = $request->arg(2);
	       $order_detail = Order::getOrderDetail($order_id);
	       $merchant_goods = Order_Model::getOrderItems($order_id);
	       $this->v->assign("item", $order_detail);
	       $this->v->assign("merchant_goods", $merchant_goods);
	    }
	
	    throw new ViewResponse($this->v);
	}
	
}

 
/*----- END FILE: Partner_Controller.php -----*/