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
	    $sql = "select pid,level from shp_agent_payment where order_id = %d and level ".Fn::db_create_in([Users::USER_LEVEL_3,Users::USER_LEVEL_4])."";
	    $result = D()->query($sql, $order_id)->get_one();
	    return $result;
	}
	
	/**
	 * 代理 赠品礼包
	 * @param unknown $type
	 */
	static function getAgentPackage($type, $pid = 0){
	    $where = '';
	    if($pid){
	        $where = ' and pid = '.$pid;
	    }
	    $sql = "select * from shp_premium_package where enabled = 1 and type = '%d' $where order by created desc ";
	    $result = D()->query($sql, $type)->fetch_array_all();
	    foreach ($result as &$rs){
	        $goods_ids = $rs['goods_ids'];
	        if(!$goods_ids){
	            continue;
	        }
	        $rs['goodslist'] = self::getGoods(explode(',', $goods_ids));
	    }
	    return $result;
	}
	
	static function getGoods($goods_ids, &$total_price = NULL) {
	    if (!$goods_ids || count($goods_ids) == 0) {
	        return [];
	    }
	    $ret = D()->query("select * from shp_goods where goods_id ".Fn::db_create_in($goods_ids)." and is_delete=0 ")->fetch_array_all();
	    if (!empty($ret)) {
	        $total_price = 0;
	        foreach ($ret As &$g) {
	            $g['goods_url']   = Items::itemurl($g['goods_id']);
	            $g['goods_thumb'] = Items::imgurl($g['goods_thumb']);
	            $g['goods_img']   = Items::imgurl($g['goods_img']);
	            $total_price     += $g['shop_price'];
	        }
	    }
	    return empty($ret) ? [] : $ret;
	}
	
	/**
	 * 购买代理支付成功后的回调
	 * @param unknown $order_id
	 * @param unknown $cUser
	 */
	static function callbackAfterAgentPay($agent, $cUser){
	    if($agent && $agent['pid']){
	        $newAgent = new AgentPayment();
	        $newAgent->pid = $agent['pid'];
	        $newAgent->is_paid = 1;
	        $newAgent->save(Storage::SAVE_UPDATE_IGNORE);
	    
	        $newUser = new Users();
	        $newUser->uid = $cUser->uid;
	        $newUser->level = $agent['level'];
	        $newUser->save(Storage::SAVE_UPDATE_IGNORE);
	        return true;
	    }
	    return false;
	}
	
	static function createAgentPayment($user_id, $order_id, $level){
	    $agent = new AgentPayment();
	    $agent->user_id = $user_id;
	    $agent->order_id = $order_id;
	    $agent->level = $level;
	    $agent->created = time();
	    $agent->save(Storage::SAVE_INSERT_IGNORE);
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
	
	/**
	 * 根据用户level显示用户级别图标
	 * @param unknown $level
	 */
	static function getAgentIconByLevel($level){
	    switch ($level){
	        case Users::USER_LEVEL_1 :
	            $icon = '/themes/mobiles/img/sha.png';
	        break;
	        case Users::USER_LEVEL_2 :
	            $icon = '/themes/mobiles/img/he.png';
	        break;
	        case Users::USER_LEVEL_3 :
	            $icon = '/themes/mobiles/img/jinpai.png';
	        break;
	        case Users::USER_LEVEL_5 :
	            $icon = '/themes/mobiles/img/qiye.png';
	        break;
	        case Users::USER_LEVEL_4 :
	            $icon = '/themes/mobiles/img/yinpai.png';
	        break;
	        default :
	            $icon = '/themes/mobiles/img/ke.png';
	    }
	    return $icon;
	}
}

