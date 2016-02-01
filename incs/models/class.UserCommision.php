<?php
/**
 * UserCommision公用Model
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class UserCommision extends StorageNode {

	//分成比例设定
	static $share_ratio = [
			'0' => PLATFORM_COMMISION,  //平台分成比例
			'1' => 0.35,                //一级上级分成比例
			'2' => 0.35,                //二级上级分成比例
			'3' => 0.30,                //三级上级分成比例
	];
	
	protected static function meta() {
		return array(
				'table' => '`shp_user_commision`',
				'key'   => 'rid',
				'columns' => array(
						'rid'          => 'rid',
						'user_id'      => 'user_id',
						'parent_level' => 'parent_level',
						'order_uid'    => 'order_uid',
						'order_unick'  => 'order_unick',
						'order_id'     => 'order_id',
						'order_sn'     => 'order_sn',
						'order_amount' => 'order_amount',
						'commision'    => 'commision',
						'use_ratio'    => 'use_ratio',
						'paid_time'    => 'paid_time',
						'state'        => 'state',
						'state_time'   => 'state_time'
				)
		);
	}
	
	/**
	 * 返回除去平台部分的总佣金
	 * @param double $total_commision
	 * @return string
	 */
	static function can_share($total_commision) {
		return number_format($total_commision*(1-PLATFORM_COMMISION),2);
	}
	
	/**
	 * 计算上级用户的佣金
	 * @param double  $commision    可以分成的总佣金
	 * @param integer $leader_level 上级分级：1、2、3
	 * @return double
	 */
	static function user_share($total_commision, $leader_level = 1) {
		if (!in_array($leader_level, [1,2,3])) return 0;
		$total_commision = self::can_share($total_commision);
		if ($total_commision <= 0) return 0;
		return number_format($total_commision*self::$share_ratio[$leader_level],2);
	}
	
	/**
	 * 生成佣金记录
	 * @param integer  $order_id
	 * @return boolean
	 */
	static function generate($order_id) {
		$exOrder = Order::load($order_id);
		if ($exOrder->is_exist()) {			
			$oUser  = Users::load($exOrder->user_id);
			
			// 1级
			$parent_level= 1;
			if ($oUser->parentid) {
				$upUC = new UserCommision();
				$upUC->user_id      = $oUser->parentid;
				$upUC->parent_level = $parent_level;
				$upUC->order_uid    = $oUser->uid;
				$upUC->order_unick  = $oUser->nickname;
				$upUC->order_id     = $exOrder->order_id;
				$upUC->order_sn     = $exOrder->order_sn;
				$upUC->order_amount = $exOrder->goods_amount;
				$upUC->commision    = self::user_share($exOrder->commision, $parent_level);
				$upUC->use_ratio    = self::$share_ratio[$parent_level];
				$upUC->paid_time    = $exOrder->pay_time;
				$upUC->save(Storage::SAVE_INSERT_IGNORE);
				
				//2级
				$parent_level = 2;
				$parent_id    = Users::getParentId($upUC->user_id);
				if ($parent_id) {
					$upUC->id = NULL;
					$upUC->user_id      = $parent_id;
					$upUC->parent_level = $parent_level;
					$upUC->commision    = self::user_share($exOrder->commision, $parent_level);
					$upUC->use_ratio    = self::$share_ratio[$parent_level];
					$upUC->save(Storage::SAVE_INSERT_IGNORE);
					
					//3级
					$parent_level = 3;
					$parent_id    = Users::getParentId($upUC->user_id);
					if ($parent_id) {
						$upUC->id = NULL;
						$upUC->user_id      = $parent_id;
						$upUC->parent_level = $parent_level;
						$upUC->commision    = self::user_share($exOrder->commision, $parent_level);
						$upUC->use_ratio    = self::$share_ratio[$parent_level];
						$upUC->save(Storage::SAVE_INSERT_IGNORE);
					}
				}
								
			}
			
			
			return true;
		}
		
		return false;
	}
	
}

/*----- END FILE: class.UserCommision.php -----*/