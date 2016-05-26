<?php
/**
 * Refund Controller
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Order_Controller extends AdminController {
	
    /**
     * hook menu
     *
     * @return array
     */
    public function menu()
    {
        return [
            'order/refund'=>'refund_list',
            'order/refund/over' => 'refund_overtime_list',
            'order/refund/detail'=>'refund_detail'
        ];
    }
    
	/**
	 * hook init
	 *
	 * @param string $action
	 * @param Request $request
	 * @param Response $response
	 */
	public function init($action, Request $request, Response $response)
	{
		$this->nav = 'order';
		parent::init($action, $request, $response);
	}
	
	/**
	 * default action 'index'
	 * 
	 * @param Request $request
	 * @param Response $response
	 */
	public function index(Request $request, Response $response)
	{
		$this->v->set_tplname('mod_order_index');
		$this->nav_second = 'index';
		
		$response->send($this->v);
		
	}
	
	/**
	 * 退款列表
	 * @param Request $request
	 * @param Response $response
	 */
	public function refund_list(Request $request, Response $response)
	{
	    $this->v->set_tplname('mod_order_refund');
	    $this->nav_second = 'refund';
	    
	    // 查询条件
	    $query_conds = [];
	    $searchinfo = [
	        'order_sn' => '',
	        'buyer' => '',
	        'merchant_id' => '',
	        'from_date' => '',
	        'to_date' => ''
	    ];
	    $searchinfo['order_sn'] = $request->get('order_sn', '');
	    $searchinfo['buyer'] = $request->get('buyer', '');
	    $searchinfo['merchant_id'] = $request->get('merchant_id', '');
	    $searchinfo['from_date'] = $request->get('from_date', '');
	    $searchinfo['to_date'] = $request->get('to_date', '');
	    
	    $this->setQueryDateRange($searchinfo);
	    
	    $searchstr = 'order_sn=' . $searchinfo['order_sn'] . '&buyer=' . $searchinfo['buyer'] .'
	                   &merchant_id='.$searchinfo['merchant_id'].'&from_date='.$searchinfo['from_date'].'&to_date='.$searchinfo['to_date'];
	    $this->v->assign('searchinfo', $searchinfo);
	    $this->v->assign('searchstr', $searchstr);
	    $query_conds = array_merge($query_conds, $searchinfo);
	    
	    // BEGIN list order
	    //$orderinfo = $this->v->set_listorder('created', 'desc');
	    //$extraurl = $searchstr . '&';
	    //$extraurl .= $orderinfo[2];
	    $this->v->assign('extraurl', $searchstr);
	    $this->v->assign('qparturl', '#/order/refund');
	    // END list order
	    
	    // Record List
	    $recordList = Order_Model::getPagedRefunds('refund.rec_id', 'desc', $this->getPagerLimit(), $query_conds);
	    $recordNum = count($recordList);
	    $totalNum = $GLOBALS['pager_totalrecord_arr'][0];
	    
	    $this->v->assign('recordList', $recordList)
	    ->assign('recordNum', $recordNum)
	    ->assign('totalNum', $totalNum);
	    
	    $merchants = Merchant::getMerchantsKeyValue();
	    $this->v->assign('merchants', $merchants);
	    
	    $response->send($this->v);
	}
	
	public function refund_overtime_list(Request $request, Response $response)
	{
	    $this->v->set_tplname('mod_order_refund_overtime');
	    $this->nav_second = 'refund_over';
	     
	    // 查询条件
	    $query_conds = [];
	    $searchinfo = [
	        'state' => ''
	    ];
	    $searchinfo['state'] = $request->get('state', 0);
	     
	    $searchstr = 'state=' . $searchinfo['state'];
	    $this->v->assign('searchinfo', $searchinfo);
	    $this->v->assign('searchstr', $searchstr);
	    $query_conds = array_merge($query_conds, $searchinfo);
	     
	    $this->v->assign('extraurl', $searchstr);
	    $this->v->assign('qparturl', '#/order/refund/over');
	     
	    // Record List
	    $recordList = Order_Model::getPagedRefunds('refund.rec_id', 'desc', $this->getPagerLimit(), $query_conds);
	    $recordNum = count($recordList);
	    $totalNum = $GLOBALS['pager_totalrecord_arr'][0];
	     
	    $this->v->assign('recordList', $recordList)
	    ->assign('recordNum', $recordNum)
	    ->assign('totalNum', $totalNum);
	     
	    $response->send($this->v);
	}
	
	public function refund_detail(Request $request, Response $response){
	    $view = new PageView('mod_order_refund_detail','_page_front');
	    $rec_id = $request->get('rec_id', 0);
	    $refund = OrderRefund::getRefundDetails($rec_id);
	    $goods = Order::getTinyItems($refund['order_id']);
	    $view->assign('goods', $goods);
	    if(!$refund['nick_name']){
	        $refund['nick_name'] = $refund['consignee'];
	    }
	    $refund_status = OrderRefund::getRefundStatus($refund['check_status'], $refund['wx_status']);
	    $refund['status'] = $refund_status;
	    if(OrderRefund::WX_STATUS_FAIL == $refund['wx_status']){
	        $refund['fail_msg'] = $refund['wx_response'];
	    }
	    $view->assign('refund', $refund);
	    $response->send($view);
	}
	
}
 
/*----- END FILE: Refund_Controller.php -----*/