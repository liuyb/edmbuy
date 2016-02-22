<?php
/**
 * 提现 Controller
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Cash_Controller extends MobileController {
	
	//可提现起始金额(单位: 元)
	const CASH_THRESHOLD = 1;
	
	//默认提现银行代码
	static $default_bank_code = 'WXPAY';
	
	/**
	 * hook init
	 *
	 * @param string $action
	 * @param Request $request
	 * @param Response $response
	 */
	public function init($action, Request $request, Response $response)
	{
		$this->nav_flag1 = 'cash';
		parent::init($action, $request, $response);
	}
	
	/**
	 * hook menu
	 * @see Controller::menu()
	 */
	public function menu()
	{
		return [
			
		];
	}
	
	/**
	 * 提现申请 action
	 * @param Request $request
	 * @param Response $response
	 */
	public function apply(Request $request, Response $response)
	{
		$this->v->set_tplname('mod_cash_apply');
		$this->nav_no    = 0;
		$this->topnav_no = 1;
		$this->backurl   = '/partner';
		
		if ($request->is_hashreq()) {
			
			global $user;
			
			$is_ms = $user->level ? : 0; //是否“米商”标志
			$this->v->assign('is_ms', $is_ms);
			
			$available_ids  = UserCommision::get_active_commision_ids($user->uid);
			$available_cash = UserCommision::get_active_commision($user->uid);
			$true_cash = Fn::money_yuan($available_cash * (1-Wxpay::CASH_FEE_RATE));
			$this->v->assign('available_ids', implode(',', $available_ids));
			$this->v->assign('available_cash', $available_cash);
			$this->v->assign('true_cash', $true_cash);
			$this->v->assign('cash_threshold', self::CASH_THRESHOLD);
			$this->v->assign('fee_rate_percent', (Wxpay::CASH_FEE_RATE*100).'%');
			
			$bank_code   = self::$default_bank_code;
			$is_rel_bank = false;
			$eUBank = UserBank::find_one(new AndQuery(new Query('user_id', $user->uid), new Query('bank_code', $bank_code)));
			if ($eUBank->is_exist()) {
				$is_rel_bank = true;
			}
			$this->v->assign('is_rel_bank', $is_rel_bank);
			
		}
		else {
			
		}
		
		$response->send($this->v);
	}
	
	/**
	 * 保存银行信息 action
	 * @param Request $request
	 * @param Response $response
	 */
	public function savebank(Request $request, Response $response)
	{
		if ($request->is_post()) {
			$bank_code  = $request->post('bank_code','');
			$bank_uname = $request->post('bank_uname','');
			
			$ret = ['flag'=>'FAIL','msg'=>'保存失败'];
			
			global $user;
			if (!$user->uid) {
				$ret['msg'] = '未登录，请先登录';
				$response->sendJSON($ret);
			}
			if (!$bank_uname) {
				$ret['msg'] = 'bank_uname不能为空';
				$response->sendJSON($ret);
			}
			if (!$bank_code) {
				$ret['msg'] = 'bank_code不能为空';
				$response->sendJSON($ret);
			}
			$extCB = CashingBank::find_one(new Query('bank_code', $bank_code));
			if (!$extCB->is_exist()) {
				$ret['msg'] = "bank_code '{$bank_code}' 不支持";
				$response->sendJSON($ret);
			}
			
			$nUB = new UserBank();
			$nUB->user_id    = $user->uid;
			$nUB->bank_code  = $bank_code;
			$nUB->bank_name  = $extCB->bank_name;
			$nUB->bank_uname = $bank_uname;
			$nUB->timeline   = simphp_time();
			if ('WXPAY'==$bank_code) {
				$nUB->bank_branch = C('api.weixin_edmbuy.appId');
				$nUB->bank_no     = $user->openid;
			}
			$nUB->save(Storage::SAVE_INSERT_IGNORE);
			if ($nUB->id) {
				$ret = ['flag'=>'OK','msg'=>'保存成功'];
			}
			
			$response->sendJSON($ret);
		}
	}
	
	/**
	 * 处理提现申请 action
	 * @param Request $request
	 * @param Response $response
	 */
	public function doapply(Request $request, Response $response)
	{
		$step = $request->get('step', 1);
		if ($request->is_post()) {
			$cashing_amount = $request->post('cashing_amount',0);
			$commision_ids  = $request->post('commision_ids','');
			$actual_amount  = Fn::money_yuan($cashing_amount * (1-Wxpay::CASH_FEE_RATE));
			
			$ret = ['flag'=>'FAIL','msg'=>'操作失败', 'detail'=>''];
			
			if (empty($cashing_amount) || $cashing_amount < self::CASH_THRESHOLD) {
				$ret['msg'] = '无法提现';
				$ret['detail'] = '可提现金额须'.self::CASH_THRESHOLD.'元起';
				$response->sendJSON($ret);
			}
			if (empty($commision_ids)) {
				$ret['msg'] = '无法提现';
				$ret['detail'] = '提现记录为空';
				$response->sendJSON($ret);
			}
			
			global $user;
			$bank_code = self::$default_bank_code;
			
			if (1==$step) { //提交提现申请
				
				$exUBank = UserBank::find_one(new AndQuery(new Query('user_id', $user->uid), new Query('bank_code', $bank_code)));
				$exUCash = UserCashing::find_one(new AndQuery(new Query('user_id', $user->uid), new Query('commision_ids', $commision_ids), new Query('state', UserCashing::STATE_FAIL,'<')));
				if (!$exUCash->is_exist()) { //不存在才需要新建记录
					$cashing_no = UserCashing::gen_cashing_no();
					$nUC = new UserCashing();
					$nUC->cashing_no      = $cashing_no;
					$nUC->cashing_amount  = $cashing_amount;
					$nUC->actual_amount   = $actual_amount;
					$nUC->user_id         = $user->uid;
					$nUC->user_nick       = $user->nickname;
					$nUC->user_mobile     = $user->mobilephone;
					$nUC->bank_code       = $bank_code;
					$nUC->bank_name       = $exUBank->bank_name;
					$nUC->bank_province   = $exUBank->bank_province;
					$nUC->bank_city       = $exUBank->bank_city;
					$nUC->bank_distict    = $exUBank->bank_distict;
					$nUC->bank_branch     = $exUBank->bank_branch;
					$nUC->bank_no         = $exUBank->bank_no;
					$nUC->bank_uname      = $exUBank->bank_uname;
					$nUC->apply_time      = simphp_time();
					$nUC->state           = UserCashing::STATE_CHECK_PENDING;
					$nUC->state_time      = simphp_time();
					$nUC->remark          = '提交提现申请';
					$nUC->commision_ids   = $commision_ids;
					$nUC->save(Storage::SAVE_INSERT_IGNORE);
					if ($nUC->id) { //插入成功
						
						$cashing_id = $nUC->id;
						
						//立马更新佣金记录状态为“锁定”
						UserCommision::change_state($commision_ids, UserCommision::STATE_LOCKED);
						
						//设置提现记录状态为“自动审核”
						UserCashing::change_state($cashing_id, UserCashing::STATE_SUBMIT_AUTOCHECK, '提交自动审核');
						
						//: 检查提交提现金额跟实际金额是否一致
						$__cashing_amount = UserCommision::count_commision($commision_ids);
						if ($__cashing_amount != $cashing_amount) {
							UserCashing::change_state($cashing_id, UserCashing::STATE_NOPASS_AUTOCHECK, '提现金额和对应的订单的佣金总额不相等');
							$ret = ['flag'=>'FAIL','msg'=>'无法提现','detail'=>'提现金额和对应的订单佣金总额不相等'];
							$response->sendJSON($ret);
						}
						
						//: 检查提现订单是否有正确的支付交易号
						if (!UserCashing::check_out_trade_no($commision_ids)) {
							//设置提现记录状态为“人工审核”
							UserCashing::change_state($cashing_id, UserCashing::STATE_SUBMIT_MANUALCHECK, '提交人工审核');
							$ret = ['flag'=>'FAIL','msg'=>'提现失败','detail'=>'提现金额和对应的订单佣金总额不相等'];
							$response->sendJSON($ret);
						}
						
						//设置提现记录状态为“提交银行转账中”
						UserCashing::change_state($cashing_id, UserCashing::STATE_SUBMIT_BANK, '提交银行转账');
						
						//对接微信接口
						$wx_ret = Wxpay::enterprisePay($cashing_no, '米商提现');
						if ('SUCC'==$wx_ret['code']) {
							//更新提现记录状态数据
							$cashing_data = [
								'payment_no'   => $wx_ret['payment_no'],
								'payment_time' => $wx_ret['payment_time'],
								'state'        => UserCashing::STATE_SUCC,
								'state_time'   => simphp_time(),
								'remark'       => '提现成功',
							];
							D()->update(UserCashing::table(), $cashing_data, ['cashing_id'=>$cashing_id]);
							
							//设置佣金记录状态为“已提现”
							UserCommision::change_state($commision_ids, UserCommision::STATE_CASHED);
							
							$ret = ['flag'=>'SUCC','msg'=>'提现成功','detail'=>'(微信钱包查看提现金额)'];
						}
						else {
							//设置提现记录状态为“提现失败”
							UserCashing::change_state($cashing_id, UserCashing::STATE_FAIL, $wx_ret['msg']);
							
							//恢复佣金记录状态为“已生效”
							UserCommision::change_state($commision_ids, UserCommision::STATE_ACTIVE);
							
							$ret = ['flag'=>'SUCC','msg'=>'提现失败','detail'=>$wx_ret['msg']];
						}
					}
					else {
						$ret = ['flag'=>'FAIL','msg'=>'提现失败','detail'=>'生成提现记录失败'];
					}
				}
				else { //存在对应的提现记录，则提示
					$ret = ['flag'=>'SUCC','msg'=>'提现已提交公司审核','detail'=>'提现未通过安全检查，已转为人工审核，5个工作日内完成提现，请留意微信通知。'];
				}
				
			}
			
			$response->sendJSON($ret);
		}
	}
	
	/**
	 * 提现明细 action
	 * @param Request $request
	 * @param Response $response
	 */
	public function detail(Request $request, Response $response)
	{
		$this->v->set_tplname('mod_cash_detail');
		$this->nav_no    = 0;
		$this->topnav_no = 1;
		$this->backurl = '/partner';
		
		if ($request->is_hashreq()) {
			
			$cashing_list = UserCashing::cashing_list($GLOBALS['user']->uid);
			$this->v->assign('cashing_list', $cashing_list);
			
		}
		else {
			
		}
		
		$response->send($this->v);
	}
	
}
 
/*----- END FILE: Cash_Controller.php -----*/