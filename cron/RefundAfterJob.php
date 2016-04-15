<?php
/**
 * 微信退款后的逻辑处理作业 

能跟微信支付产生交付的几种情况：

1、一个不会产生分单的订单提交到微信支付
   --全额退款：order_status=OS_REFUND     , pay_status=PS_REFUNDING|PS_REFUND, pay_log表is_paid=0,佣金表完全“删除”
   --部分退款：order_status=OS_REFUND_PART, pay_status=PS_PAYED, pay_log表is_paid=1,佣金表重新算更新
   
2、一个会产生分单的主订单成功提交到微信支付
   --全额退款：order_status=OS_REFUND     , pay_status=PS_REFUNDING|PS_REFUND, pay_log表is_paid=0,佣金表完全“删除”
   --部分退款：order_status=OS_REFUND_PART, pay_status=PS_PAYED, pay_log表is_paid=1,佣金表重新算更新
   --同样逻辑递归处理子订单状态(pay_log表和佣金表不用处理，因为一个没影响，一个没数据)
   
3、一个会产生分单的主订单没提交到微信支付，然后各个子订单分别提交到微信支付
   --全额退款：order_status=OS_REFUND     , pay_status=PS_REFUNDING|PS_REFUND, pay_log表is_paid=0,佣金表完全“删除”
   --部分退款：order_status=OS_REFUND_PART, pay_status=PS_PAYED, pay_log表is_paid=1,佣金表重新算更新
   --父订单状态变更：order_status=OS_INVALID,pay_status=PS_CANCEL
   
 * @author Gavin<laigw.vip@gmail.com>
 */
class RefundAfterJob extends CronJob {
	
	private $table           = '`shp_order_refund`';
	private $table_order     = '`shp_order_info`';
	private $table_paylog    = '`shp_pay_log`';
	private $table_commision = '`shp_user_commision`';
	
	public function main($argc, $argv) {
		
		$start = 0;
		$limit = 100;
		
		$done_records = []; //记录已经处理完成的记录ID
		$total_order_refund = []; //用于记录某个订单号总退款金额(因为list记录中有可能一个订单号分成多次退款)
		$total= $this->getTotal();
		$list = $this->getList($start, $limit);
		while (!empty($list)) {
			
			$this->log('current records: '.count($list).'/'.$total);
			foreach ($list AS $row) {
				$order_info    = $this->getOrderInfo($row['order_sn']);
				if (empty($order_info)) continue;
				
				$paid_order_id = $order_info['order_id'];
				
				//~ 先补全可能缺失的订单信息(pay_trade_no等)
				if (empty($order_info['pay_trade_no'])) {
					$this->updateOrderInfo($paid_order_id, $order_info['user_id'], $row);
				}
				
				//~ 退款状态
				$paid_paystatus = PS_REFUNDING;
				if ('退款成功'==$row['refund_status']) {
					$paid_paystatus = PS_REFUND;
				}
				if (!isset($total_order_refund[$row['order_sn']])) {
					$total_order_refund[$row['order_sn']] = 0;
				}
				$total_order_refund[$row['order_sn']] += $row['refund_money'];
				
				//~ 分别针对"全额退款"和"部分退款"逻辑处理
				if ($total_order_refund[$row['order_sn']] < $row['trade_money']) { //部分退款(浮点数不能用=来判断)
					
					//订单状态
					$this->updateOrderStatus($paid_order_id, OS_REFUND_PART, PS_PAYED);
					
					//paylog
					//$this->updatePaylog($paid_order_id, 1);
					
					//佣金表
					/*
					$new_order_amount = $row['trade_money'] - $row['refund_money']; //TODO 还要扣除一些费用？这里$new_order_amount的计算有问题
					$new_order_amount = $new_order_amount < 0 ? 0 : $new_order_amount;
					$this->removeCommision($paid_order_id, false, $new_order_amount);
					*/
					$this->removeCommision($paid_order_id, true); //TODO 先暂时全部去掉，这里要精确化处理
				}
				else { //全额退款
					
					//订单状态
					$this->updateOrderStatus($paid_order_id, OS_REFUND, $paid_paystatus);
						
					//paylog
					$this->updatePaylog($paid_order_id, 0);
						
					//佣金表
					$this->removeCommision($paid_order_id, true);
				}
				
				//~ 检查是否是分单
				if ($order_info['is_separate']) { //这是一个会产生分单的主订单，并且已经成功提交到微信支付
					
					//先更新分单标志
					if (!$row['is_separate']) {
						D()->query("UPDATE ".$this->table." SET `is_separate`=1 WHERE `rec_id`=%d", $row['rec_id']);
					}
					
					//看是否登记了子订单号，如果登记了，就分是否全款退款处理
					if (!empty($row['sub_ordersn'])) { //sub_ordersn 是类似 'E2016012504210627352:1,E2016012417083851569:0' 这样的格式，其中冒号:后面的1或者0表示是(1)否(0)全额退款
						$sub_arr1 = explode(',', $row['sub_ordersn']);
						$sub_arr2 = [];
						foreach ($sub_arr1 AS $oit) {
							$tarr = explode(':', trim($oit));
							$sub_arr2[$tarr[0]] = intval(isset($tarr[1]) ? $tarr[1] : 0);
						}
						foreach ($sub_arr2 AS $subosn => $isfull) { //$sub_arr2 is an array like Array('E2016012504210627352'=>1,'E2016012417083851569'=>0)
							$suborder = $this->getOrderInfo($subosn);
							if (empty($suborder)) continue;
							if ($isfull) { //全额退款
								//订单状态
								$this->updateOrderStatus($suborder['order_id'], OS_REFUND, $paid_paystatus);
							}
							else { //部分退款
								//订单状态
								$this->updateOrderStatus($suborder['order_id'], OS_REFUND_PART, PS_PAYED);
							}
						}
					}
					
				}
				elseif (!empty($order_info['parent_id'])) { //这是一个子订单的单独成功提交
					//更新父订单状态为无效(这时父订单已经没用)
					$this->updateOrderStatus($order_info['parent_id'], OS_INVALID, PS_CANCEL);
				}
				
				//~ 记录is_done的rec_id
				if ('退款成功'==$row['refund_status']) {
					if (!$order_info['is_separate'] || !empty($row['sub_ordersn'])) {
						array_push($done_records, $row['rec_id']);
					}
				}
				
			}//END foreach ($list AS $row)
			
			unset($list);
			$start += $limit;
			$list = $this->getList($start, $limit);
		}
		
		//批量更新is_done标志
		$this->log('done records count: '.count($done_records));
		if (!empty($done_records)) {
			$done_records_str = join(",", $done_records);
			D()->query("UPDATE ".$this->table." SET `is_done`=1 WHERE `rec_id` IN(%s)", $done_records_str);
		}
		
	}
	
	private function getTotal() {
		$count = D()->from($this->table)
								->where('is_done=0')
								->select('COUNT(1)')
		            ->result();
		return $count;
	}
	
	private function getList($start = 0, $limit = 100) {
		
		$list = D()->from($this->table)
		           ->where('is_done=0')
		           ->order_by('refund_time ASC')
		           ->limit($start,$limit)
		           ->select()
		           ->fetch_array_all();
		return $list;
		
	}
	
	private function getOrderInfo($order_sn) {
		$row = D()->from($this->table_order)->where("order_sn='%s'", $order_sn)->select()->get_one();
		return $row;
	}
	
	//更新订单部分信息(如pay_trade_no等)
	//这里的逻辑跟 Wxpay_Controller::notify 要一致，其实是一个"补单"的过程
	private function updateOrderInfo($order_id, $order_uid, Array $paid_order_info) {
		//更新pay_log
		$this->updatePaylog($order_id, 1);
		
		//更新订单状态
		$ec_order = $this->table_order;
		$updata = [
				'pay_trade_no'   => $paid_order_info['pay_trade_no'],
				'order_status'   => OS_CONFIRMED,
				'confirm_time'   => simphp_gmtime(), //跟从ecshop习惯，使用格林威治时间
				'pay_status'     => PS_PAYED,
				'pay_time'       => simphp_gmtime(), //TODO 先用最新的时间
				'money_paid'     => $paid_order_info['trade_money'],
				'order_amount'   => 0, //将order_amount设为0
		];
		D()->update($ec_order, $updata, ['order_id'=>$order_id]);
		
		//更改可能子订单的状态
		if ($order_id) { //出于严格判断
			D()->query("UPDATE {$ec_order} SET money_paid=order_amount,order_amount=0 WHERE `parent_id`=%d", $order_id);
			unset($updata['money_paid'],$updata['order_amount']);
			D()->update($ec_order, $updata, ['parent_id'=>$order_id]);
		}
		
		//设置佣金计算
		UserCommision::generate($order_id);
		
		//记录订单操作记录
		Order::action_log($order_id, ['action_note'=>'用户支付']);
		
		//检查用户资格
		$cUser = Users::load($order_uid);
		if ($cUser->is_exist()) {
			$cUser->check_level();
		}
		
		//更新订单下所有商品的"订单数"
		Items::updateOrderCntByOrderid($order_id);
		
		//更新订单下所有商品卖出的"单品数"
		Items::updatePaidNumByOrderid($order_id);
		
	}
	
	//更新定单状态
	private function updateOrderStatus($order_id, $order_status, $pay_status) {
		$updata = [];
		if (isset($order_status)) {
			$updata['order_status'] = $order_status;
		}
		if (isset($pay_status)) {
			$updata['pay_status'] = $pay_status;
		}
		if (empty($order_id) || empty($updata)) return false;
		
		D()->update($this->table_order, $updata, ['order_id'=>$order_id]);
		return true;
	}
	//更新paylog
	private function updatePaylog($order_id, $is_paid = 0) {
		if (empty($order_id)) return false;
		D()->update($this->table_paylog, ['is_paid'=>$is_paid], ['order_id'=>$order_id]);
		return true;
	}
	//"删除"佣金表`shp_user_commision`数据
	private function removeCommision($order_id, $is_full = TRUE, $new_order_amount = NULL) {
		if (empty($order_id)) return false;
		$table = $this->table_commision;
		if ($is_full) { //该订单全额退款，则全部"删除"
			$sql = "UPDATE {$table} SET `state`=%d,`state_time`=%d WHERE `order_id`=%d AND `state`<%d";
			D()->query($sql, -1, simphp_time(), $order_id, UserCommision::STATE_CASHED);//确保已提现和提现中的订单不能变更
		}
		else { //该订单部分退款，需要用新的订单佣金来重新算
			if (!is_null($new_order_amount)) {
				$canshare = UserCommision::can_share($new_order_amount);
				$sql = "UPDATE {$table} SET `order_amount`=%n,`commision`=`use_ratio`*%n WHERE `order_id`=%d AND `state`<%d";
				D()->query($sql, $new_order_amount, $canshare, $order_id, UserCommision::STATE_CASHED);//确保已提现和提现中的订单不能变更
			}
		}
		return true;
	}
	
}








/*----- END FILE: RefundAfterJob.php -----*/