<?php

defined('IN_SIMPHP') or die('Access Denied');

/**
 * 购买代理记录表
 * @author Jean
 *
 */
class AgentPayment extends StorageNode {
	
	protected static function meta() {
		return array(
				'table'   => '`shp_agent_payment`',
				'key'     => 'pid',
				'columns' => array(
						'pid'        => 'pid',
						'user_id'    => 'user_id',
						'order_id'   => 'order_id',
						'level'      => 'level',
						'created'    => 'created',
						'is_paid'    => 'is_paid',
						'premium_id' => 'premium_id'
				)
		);
	}
	
	/**
	 * 根据用户ID查询 当前已支付的代理记录
	 * @param unknown $user_id
	 */
	static function getAgentByUserId($user_id, $level){
	    return self::find_one(new AndQuery(new Query('user_id', $user_id), new Query('level', $level), new Query('is_paid', 1)));
	}
	
	/**
	 * 根据用户ID查询 当前已支付的代理记录
	 * @param unknown $user_id
	 */
	static function getAgentByOrderId($order_id){
	    return self::find_one(new AndQuery(new Query('order_id', $order_id), new InQuery('level', [Users::USER_LEVEL_3,Users::USER_LEVEL_4])));
	}
	
	/**
	 * 购买代理支付成功后的回调
	 * @param unknown $order_id
	 * @param unknown $cUser
	 */
	static function callbackAfterAgentPay($agent, $cUser){
	    if($agent && $agent->pid){
	        $agent->is_paid = 1;
	        $agent->save(Storage::SAVE_UPDATE);
	    
	        $cUser->level = $agent->level;
	        $cUser->save(Storage::SAVE_UPDATE);
	    }
	}
	
	static function createAgentPayment($user_id, $order_id, $level){
	    $agent = new AgentPayment();
	    $agent->user_id = $user_id;
	    $agent->order_id = $order_id;
	    $agent->level = $level;
	    $agent->created = time();
	    $agent->save(Storage::SAVE_INSERT);
	}
	
	static function getAgentNameByLevel($level){
	    return Users::displayUserLevel($level);
	}
	
	static function getAgentPaidMoney($level, $dot = 0){
	    if(!Users::isAgent($level)){
	        return 0;
	    }
	    $money = (Users::USER_LEVEL_3 == $level) ? 698 : 398;
	    if($dot){
	        $money = sprintf("%.".$dot."f", $money);
	    }
	    return $money;
	}
	
	static function getAgentIconByLevel($level){
	    if(!Users::isAgent($level)){
	        return '';
	    }
	    return (Users::USER_LEVEL_3 == $level) ? '/themes/mobiles/img/jinpai.png' : '/themes/mobiles/img/yinpai.png';
	}
}

