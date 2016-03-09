<?php
/**
 * Cash Controller
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Cash_Controller extends AdminController {
	
    const CSV_SEP = ',';
    const CSV_LN = "\n";
    
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
	            'cash/export/excel'=>'export_excel'
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
		$searchinfo['period']     = $request->get('period', '');
		$searchinfo['status']     = $request->get('status', '');
		$searchinfo['searchTxt']  = $request->get('searchTxt', '');
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
		$searchstr  = 'fdate='.$searchinfo['from_date'].'&tdate='.$searchinfo['to_date'].'&period='.$searchinfo['period'].'&status='.$searchinfo['status'];
		$searchstr  .= '&searchTxt='.$searchinfo['searchTxt'];
		$this->v->assign('searchinfo', $searchinfo);
		$this->v->assign('searchstr', $searchstr);
		$query_conds = array_merge($query_conds, $searchinfo);
		
		//BEGIN list order
		$orderinfo = $this->v->set_listorder('cashing_id', 'desc');
		$extraurl  = $searchstr.'&';
		$extraurl .= $orderinfo[2];
		$this->v->assign('extraurl', $extraurl);
		$this->v->assign('qparturl', '#/cash');
		$this->v->assign('backurl', '/cash,'.$extraurl.'&p='.(isset($_GET['p']) ? $_GET['p'] : ''));
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
	
	public function export_excel(Request $request, Response $response){
	    //查询条件
	    $query_conds = [];
	    $searchinfo = ['from_date'=>'', 'to_date'=>''];
	    $searchinfo['from_date']  = $request->get('fdate','');
	    $searchinfo['to_date']    = $request->get('tdate','');
	    $searchinfo['status']     = $request->get('status', '');
	    $searchinfo['searchTxt']  = $request->get('searchTxt', '');
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
	    $query_conds = array_merge($query_conds, $searchinfo);
	    //BEGIN list order
	    $orderinfo = $this->v->set_listorder('cashing_id', 'desc');
	    $recordList = Cash_Model::getCashingForExport($orderinfo[0],$orderinfo[1],$query_conds);
	    
	    $filename  = SIMPHP_ROOT . '/var/tmp/CASH_LIST_%s.csv';
	    $format_str = "";
	    if(!$searchinfo['from_date'] && !$searchinfo['to_date']){
	        $format_str = "ALL";
	    }else if($searchinfo['from_date'] && !$searchinfo['to_date']){
	        $format_str = "FROM ".$searchinfo['from_date'];
	    }else if(!$searchinfo['from_date'] && $searchinfo['to_date']){
	        $format_str = "TO ".$searchinfo['to_date'];
	    }else if($searchinfo['from_date'] && $searchinfo['to_date']){
	        $format_str = "FROM ".$searchinfo['from_date']." TO ". $searchinfo['to_date'];
	    }
	    $filename  = sprintf($filename, $format_str);
	    
	    
    	$csv = "提现订单号,支付订单号,姓名,手机号,持卡人,提现账号,	提现金额,实际到账,提交时间,提交时间,提现状态".self::CSV_LN;
    	$CSV_SEP = self::CSV_SEP;
    	//获取商家收入订单详情
    	if (!empty($recordList)) {
    		foreach ($recordList AS $it) {
    			$csv .= '"'.$it['cashing_no'].'"'.$CSV_SEP.'"'.$it['payment_no'].'"'.$CSV_SEP.'"'.$it['user_nick'].'"'.$CSV_SEP.'"'.$it['user_mobile'].'"';
    			$csv .= $CSV_SEP.'"'.$it['bank_uname'].$it['bank_no'].'"'.$CSV_SEP.'"'.$it['bank_name'].'"'.$CSV_SEP.$it['cashing_amount'].$CSV_SEP.$it['actual_amount'];
    			$csv .= $CSV_SEP.'"'.date('Y-m-d H:i:s', simphp_gmtime2std($it['apply_time'])).'"'.$CSV_SEP.'"'.date('Y-m-d H:i:s', simphp_gmtime2std($it['payment_time'])).'"';
    			$csv .= $CSV_SEP.'"'.$it['state_txt'].'"'.self::CSV_LN;
    		}
    	}
    	if (''!=$csv) {
    	    file_put_contents($filename, $csv);
    	    download($filename);
    	}
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