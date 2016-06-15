<?php
/**
 * Cash Model
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Cash_Model extends Model {
	
	static function stateHTML($state) {
		//待审核(red),已审核(blue),转账中(yellow),提现成功(green),提现失败(gray)
		$state_txt = '';
		/* if (in_array($state, [UserCashing::STATE_CHECK_PENDING,UserCashing::STATE_SUBMIT_AUTOCHECK,UserCashing::STATE_NOPASS_AUTOCHECK,UserCashing::STATE_SUBMIT_MANUALCHECK])) {
			$state_txt = '<span style="color:red">待审核</span>';
		}
		elseif (in_array($state, [UserCashing::STATE_PASS_AUTOCHECK,UserCashing::STATE_PASS_MANUALCHECK])) {
			$state_txt = '<span style="color:blue">已审核</span>';
		} */
		if (in_array($state, [UserCashing::STATE_SUBMIT_MANUALCHECK])) {
		    $state_txt = '<span style="color:red">待审核</span>';
		}
		elseif (in_array($state, [UserCashing::STATE_NOPASS_AUTOCHECK,UserCashing::STATE_NOPASS_MANUALCHECK])) {
		    $state_txt = '<span style="color:gray">审核未通过</span>';
		}
		elseif (in_array($state, [UserCashing::STATE_PASS_AUTOCHECK,UserCashing::STATE_SUBMIT_BANK])) {
			$state_txt = '<span style="color:#ff6600">转账中</span>';
		}
		elseif ($state==UserCashing::STATE_SUCC) {
			$state_txt = '<span style="color:green">提现成功</span>';
		}
		else {
			$state_txt = '<span style="color:gray">提现失败</span>';
		}
		return $state_txt;
	}
	
	static function stateTxt($state) {
	    //待审核(red),已审核(blue),转账中(yellow),提现成功(green),提现失败(gray)
	    $state_txt = '';
	    if (in_array($state, [UserCashing::STATE_SUBMIT_MANUALCHECK])) {
	        $state_txt = '待审核';
	    }
	    elseif (in_array($state, [UserCashing::STATE_NOPASS_AUTOCHECK,UserCashing::STATE_NOPASS_MANUALCHECK])) {
	        $state_txt = '审核未通过';
	    }
	    /* elseif (in_array($state, [UserCashing::STATE_PASS_AUTOCHECK,UserCashing::STATE_PASS_MANUALCHECK])) {
	        $state_txt = '已审核';
	    } */
	    elseif (in_array($state, [UserCashing::STATE_PASS_AUTOCHECK,UserCashing::STATE_SUBMIT_BANK])) {
	        $state_txt = '转账中';
	    }
	    elseif ($state==UserCashing::STATE_SUCC) {
	        $state_txt = '提现成功';
	    }
	    else {
	        $state_txt = '提现失败';
	    }
	    return $state_txt;
	}
	
	static function getCashingList($orderby='cashing_id', $order='DESC', $limit=30, Array $query_conds=array(), &$statinfo=array())
	{
		
	    $where_cond = self::getCashingQueryCondition($query_conds);
		$where  = $where_cond;
		$table  = UserCashing::table();
		$sql    = "SELECT c.* FROM {$table} c WHERE 1 {$where} ORDER BY `%s` %s";
		$sqlcnt = "SELECT COUNT(1) FROM {$table} c WHERE 1 {$where}";
	
		$result = D()->pager_query($sql,$limit,$sqlcnt,0,$orderby,$order)->fetch_array_all();
		$statinfo = ['total_pay'=>0, 'current_pay'=>0];
		if (!empty($result)) {
			foreach ($result AS &$it) {
				$it['state_txt'] = self::stateHTML($it['state']);
			}
		}
		return $result;	
	}
	
	static function getCashingForExport($orderby='cashing_id', $order='DESC', Array $query_conds=array()){
	    $table  = UserCashing::table();
	    $where  = self::getCashingQueryCondition($query_conds);
	    $sql    = "SELECT c.* FROM {$table} c WHERE 1 {$where} ORDER BY `%s` %s";
	    $result = D()->query($sql,$orderby,$order)->fetch_array_all();
	    if (!empty($result)) {
	        foreach ($result AS &$it) {
	            $it['state_txt'] = self::stateTxt($it['state']);
	        }
	    }
	    return $result;
	}
	
	private static function getCashingQueryCondition(Array $query_conds=array()){
	    $where_cond = '';
	    if (isset($query_conds['from_date']) && $query_conds['from_date']) {
	        $starttime = strtotime($query_conds['from_date'].DAY_BEGIN);
	        $where_cond .= " AND c.`apply_time`>=".$starttime;
	    }
	    if (isset($query_conds['to_date']) && $query_conds['to_date']) {
	        $endtime = strtotime($query_conds['to_date'].DAY_END);
	        $where_cond .= " AND c.`apply_time`<=".$endtime;
	    }
	    
	    if(isset($query_conds['status']) && $query_conds['status']){
	        $status = $query_conds['status'];
	        /* if("wait_check" == $status){
	            $where_cond .= " AND c.`state` in (".implode(',', [UserCashing::STATE_CHECK_PENDING,UserCashing::STATE_SUBMIT_AUTOCHECK,UserCashing::STATE_NOPASS_AUTOCHECK,UserCashing::STATE_SUBMIT_MANUALCHECK]).")";
	        }else if("checked" == $status){
	            $where_cond .= " AND c.`state` in (".implode(',', [UserCashing::STATE_PASS_AUTOCHECK,UserCashing::STATE_PASS_MANUALCHECK]).")";
	        }else  */
	        if("wait_check" == $status){
	            $where_cond .= " AND c.`state` in (".implode(',', [UserCashing::STATE_SUBMIT_MANUALCHECK]).")";
	        }else if("fail_check" == $status){
	            $where_cond .= " AND c.`state` in (".implode(',', [UserCashing::STATE_NOPASS_MANUALCHECK]).")";
	        }else if("transfer" == $status){
	            $where_cond .= " AND c.`state` in (".implode(',', [UserCashing::STATE_PASS_AUTOCHECK,UserCashing::STATE_SUBMIT_BANK]).")";
	        }else if("wdraw_succ" == $status){
	            $where_cond .= " AND c.`state` = ".UserCashing::STATE_SUCC;
	        }else if("wdraw_fail" == $status){
	            $where_cond .= " AND c.`state` = ".UserCashing::STATE_FAIL;
	        }
	    }
	    
	    if(isset($query_conds['searchTxt']) && $query_conds['searchTxt']){
	        $where_cond .= " AND (c.`user_id` like '%".$query_conds['searchTxt']."%' or c.`user_nick` like '%".$query_conds['searchTxt']."%' or c.`user_mobile` like '%".$query_conds['searchTxt']."%') ";
	    }
	    return $where_cond;
	}
	
	static function getCashingOrderList($commision_ids) {
		if (empty($commision_ids)) return [];
		$sql = "SELECT uc.order_sn,uc.order_amount,uc.commision,o.pay_trade_no,o.pay_name,o.pay_status,o.pay_time,o.order_flag
				FROM `shp_user_commision` uc INNER JOIN `shp_order_info` o ON uc.order_id=o.order_id
				WHERE uc.rid IN(%s)
				";
		$list = D()->query($sql,$commision_ids)->fetch_array_all();
		return $list;
	}
	
	//人工审核的佣金 需要提前锁定
	static function hasNoLockedCommisions($item_list){
	    $sql = "select count(1) from shp_user_commision where state <> ".UserCommision::STATE_LOCKED." and ".Fn::db_create_in($item_list,'rid')." ";
	    return D()->query($sql)->result();
	}
	
	//审核通过
	static function checkAccept($extUC, $user){
	    if(!$extUC->cashing_no){
	        return false;
	    }
	    $cashing_id = $extUC->cashing_id;
	    $commision_ids = $extUC->commision_ids;
	    $cashing_no = $extUC->cashing_no;
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
	    
	        //设置成功返回码
	        $ret = ['flag'=>'SUCC','msg'=>'审核通过，提现成功'];
	    
	        //微信模板消息通知提现成功
	        WxTplMsg::cashing_success($user->openid, "您的提现已成功！\n请查看“微信钱包”明细以确认。", '有任何疑问，请联系客服。', U('cash/detail','',true), 
	            ['apply_money'=>strval($extUC->cashing_amount),'actual_money'=>strval($extUC->actual_amount),'apply_time'=>simphp_dtime('std',$extUC->apply_time),
	                'succ_time'=>simphp_dtime('std',$cashing_data['payment_time']),'cashing_no'=>$cashing_no,'payment_no'=>$cashing_data['payment_no']]);
	    }
	    else {
	        $ret = ['flag'=>'SUCC','msg'=>'审核通过，微信提现返回失败'];
	    
	        //设置提现记录状态为“提现失败”
	        UserCashing::change_state($cashing_id, UserCashing::STATE_FAIL, $wx_ret['msg']);
	    
	        //恢复佣金记录状态为“已生效”
	        UserCommision::change_state($commision_ids, UserCommision::STATE_ACTIVE);
	    
	        //微信模板消息通知提现失败
	        WxTplMsg::cashing_fail($user->openid, "您的提现失败！\n\n失败原因: ".$wx_ret['msg'], '有任何疑问，请联系客服。', U('cash/detail','',true), ['money'=>strval($extUC->cashing_amount),'time'=>simphp_dtime('std',$extUC->apply_time),'cashing_no'=>$cashing_no]);
	    }
	    return $ret;
	}
	
}
 
/*----- END FILE: Cash_Model.php -----*/