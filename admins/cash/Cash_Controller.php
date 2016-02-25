<?php
/**
 * Cash Controller
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Cash_Controller extends AdminController {
	
	/**
	 * init hook
	 *
	 * @param string $action
	 * @param Request $request
	 * @param Response $response
	 */
	public function init($action, Request $request, Response $response)
	{
		$this->nav = 'cash';
		parent::init($action, $request, $response);
	}
	
	/**
	 * menu hook
	 * @see Controller::menu()
	 */
	public function menu(){
		return [
				'cash/%d/detail'=>'detail',
	
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
		$this->v->set_tplname('mod_cash_index');
		$this->nav_second = 'cash';
		
		//查询条件
		$query_conds = [];
		$searchinfo = ['from_date'=>'', 'to_date'=>''];
		$searchinfo['from_date']  = $request->get('fdate','');
		$searchinfo['to_date']    = $request->get('tdate','');
		if (strlen($searchinfo['from_date'])!=10) { //format: 'YYYY-MM-DD'
			$searchinfo['from_date'] = '';
		}
		if (strlen($searchinfo['to_date'])!=10) { //format: 'YYYY-MM-DD'
			$searchinfo['to_date'] = '';
		}
		if (!empty($searchinfo['from_date']) && !empty($searchinfo['to_date']) && $searchinfo['from_date'] > $searchinfo['to_date']) { //交换
			$t = $searchinfo['from_date'];
			$searchinfo['from_date'] = $searchinfo['to_date'];
			$searchinfo['to_date'] = $t;
		}
		$searchstr  = 'fdate='.$searchinfo['from_date'].'&tdate='.$searchinfo['to_date'];
		$this->v->assign('searchinfo', $searchinfo);
		$this->v->assign('searchstr', $searchstr);
		$query_conds = array_merge($query_conds, $searchinfo);
		
		//BEGIN list order
		$orderinfo = $this->v->set_listorder('cashing_id', 'desc');
		$extraurl  = $searchstr.'&';
		$extraurl .= $orderinfo[2];
		$this->v->assign('extraurl', $extraurl);
		$this->v->assign('qparturl', '#/cash');
		$this->v->assign('backurl', '/cash,'.$extraurl.'&p='.$_GET['p']);
		//END list order
		
		// Record List
		$limit = 30;
		$recordList = Cash_Model::getCashingList($orderinfo[0],$orderinfo[1],$limit,$query_conds,$statinfo);
		$recordNum  = count($recordList);
		$totalNum   = $GLOBALS['pager_totalrecord_arr'][0];
		
		$this->v->assign('recordList', $recordList)
		        ->assign('recordNum', $recordNum)
		        ->assign('totalNum', $totalNum)
		        ->assign('query_conds', $query_conds)
		        ->assign('statinfo', $statinfo)
		        ->assign('mainsite', C('env.site.mobile'))
		;
		
		$response->send($this->v);
	
	}
	
	/**
	 * default action 'detail'
	 *
	 * @param Request $request
	 * @param Response $response
	 */
	public function detail(Request $request, Response $response)
	{
		$this->v->set_tplname('mod_cash_detail');
		$this->nav_second = 'cash';
		
		$cash_id = $request->arg(1);
		$backurl = $request->get('backurl','');
		$this->v->assign('backurl', $backurl);
		
		$order_list = [];
		$total_commision = 0.00;
		$exUC =  UserCashing::load($cash_id);
		if ($exUC->is_exist()) {
			$exUC->state_txt = Cash_Model::stateTxt($exUC->state);
			$exUC->is_pass_auto = in_array($exUC->state, [UserCashing::STATE_NOPASS_AUTOCHECK,UserCashing::STATE_SUBMIT_MANUALCHECK,UserCashing::STATE_NOPASS_MANUALCHECK,UserCashing::STATE_PASS_MANUALCHECK]) ? false : true;
			
			$order_list = Cash_Model::getCashingOrderList($exUC->commision_ids);
			foreach ($order_list AS &$it) {
				$total_commision += $it['commision'];
				if (!empty($it['pay_trade_no']) && PS_PAYED==$it['pay_status']) {
					$it['order_status_txt'] = '<em style="color:green">有效订单</em>';
				}
				elseif(empty($it['pay_trade_no'])) {
					$it['order_status_txt'] = '<em style="color:red">无效订单</em>';
				}
				else {
					$_status = Fn::pay_status($it['pay_status']);
					$it['order_status_txt'] = '<em style="color:blue">异常订单('.$_status.')</em>';
				}
			}
		}
		$this->v->assign('userCash', $exUC);
		$this->v->assign('order_list', $order_list);
		$this->v->assign('total_commision', $total_commision);
		
		$response->send($this->v);
	}
	
}
 
/*----- END FILE: Cash_Controller.php -----*/