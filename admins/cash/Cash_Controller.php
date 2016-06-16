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
    
    const CONFIG_CASH_MONEY_LIMIT = 'config_cash_money_limit';
    
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
	            'cash/export/excel'=>'export_excel',
		        'cash/check' => 'check',
		        'cash/config/info' => 'cash_config',
		        'cash/config' => 'cash_config_save'
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
					if ($it['order_flag']>0 && $it['pay_time']>0) {
						$it['order_status_txt'] = '<em style="color:green">有效订单</em>';
					}
					else {
						$it['order_status_txt'] = '<em style="color:red">无效订单</em>';
					}
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
	
	/**
	 * 审核 state -1 拒绝 1 通过
	 * @param Request $request
	 * @param Response $response
	 */
	public function check(Request $request, Response $response){
	    $ret = ['flag' => 'FAIL', 'msg' => '数据不存在，操作失败'];
	    if($request->is_post()){
	        $cashing_id = $request->post('cash_id');
	        $state = $request->post('state');
	        $remark = $request->post('remark');
	        $exUC = UserCashing::load($cashing_id);
	        if(!$exUC->is_exist()){
	            $response->sendJSON($ret);
	        }
	        if($exUC->state != UserCashing::STATE_SUBMIT_MANUALCHECK){
	            $ret = ['flag' => 'FAIL', 'msg' => '当前状态还不是待审核状态'];
	            $response->sendJSON($ret);
	        }
	        $commision_ids = $exUC->commision_ids;
	        if(Cash_Model::hasNoLockedCommisions($commision_ids)){
	            $ret = ['flag' => 'FAIL', 'msg' => '提现订单存在非锁定状态的佣金数据，操作失败'];
	            $response->sendJSON($ret);
	        }
	        $user = Users::load($exUC->user_id);
	        //拒绝
	        if($state == -1){
	            //设置提现记录状态为“人工审核拒绝”
	            UserCashing::change_state($cashing_id, UserCashing::STATE_FAIL, $remark);
	            //拒绝后还原佣金状态为激活状态
	            UserCommision::change_state($commision_ids, UserCommision::STATE_ACTIVE);
	            //微信模板消息通知提现失败
	            WxTplMsg::cashing_fail($user->openid, "您的提现申请审核失败！\n\n失败原因: ".$remark, '有任何疑问，请联系客服。', U('cash/detail','',true), ['money'=>strval($exUC->cashing_amount),'time'=>simphp_dtime('std',$exUC->apply_time),'cashing_no'=>$exUC->cashing_no]);
	            $ret = ['flag' => 'SUCC', 'msg' => '操作成功'];
	        }else if($state == 1){
	            $result = Cash_Model::checkAccept($exUC, $user);
	            if($result){
	                $ret = $result;
	            }
	        }
	        $response->sendJSON($ret);
	    }
	}
	
	public function cash_config(Request $request, Response $response){
	    $this->v->set_tplname('mod_cash_config');
	    $this->nav_second = 'config';
	    
	    $limit = config_get(self::CONFIG_CASH_MONEY_LIMIT);
	    $this->v->assign('limitMoney', $limit);
	    
	    $response->send($this->v);
	}
	
	/**
	 * 保存提现配置
	 * @param Request $request
	 * @param Response $response
	 */
	public function cash_config_save(Request $request, Response $response){
	    
	    $limit = $request->post('limit', 500);
	    
	    $affect = config_set(self::CONFIG_CASH_MONEY_LIMIT, $limit);
	    
	    $ret = ['flag' => 'FAIL', 'msg' => '保存失败！'];
	    if($affect){
	        $ret['flag'] = 'SUCC';
	    }
	     
	    $response->sendJSON($ret);
	}
}
 
/*----- END FILE: Cash_Controller.php -----*/