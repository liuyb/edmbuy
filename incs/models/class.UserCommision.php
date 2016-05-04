<?php
/**
 * UserCommision公用Model
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class UserCommision extends StorageNode {
	
	//佣金状态
	const STATE_INVALID   =-1;  //无效
	const STATE_INACTIVE  = 0;  //未生效
	const STATE_ACTIVE    = 1;  //已生效，但未提现
	const STATE_CASHED    = 2;  //已生效，且已提现
	const STATE_LOCKED    = 3;  //操作锁定中
	
	//佣金类型
	const COMMISSION_TYPE_FX = 0; //商品分销
	const COMMISSION_TYPE_DL = 1; //代理
	const COMMISSION_TYPE_RZ = 2; //入驻
	const COMMISSION_TYPE_JY = 3; //交易
	
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
	
	/**
	 * 获取用户的各种状态的佣金收入
	 * @param integer $uid
	 * @param integer $state
	 * @return mixed(float|array|bool)
	 */
	static function get_commision_income($uid, $state = NULL)
	{
		$table = self::table();
		$sql  = "SELECT ifnull(SUM(`commision`), 0) AS commision,`state` FROM {$table} WHERE `user_id` = %d AND `state`>=0 GROUP BY `state`";
		$list = D()->query($sql, $uid)->fetch_array_all();
		$ret  = [
				self::STATE_INACTIVE => 0.00,
				self::STATE_ACTIVE   => 0.00,
				self::STATE_CASHED   => 0.00,
				self::STATE_LOCKED   => 0.00
		];
		if (!empty($list)) {
			foreach ($list AS $it) {
				$ret[$it['state']] = $it['commision'];
			}
		}
		return isset($state) ? (isset($ret[$state]) ? $ret[$state] : false) : $ret;
	}
	
	/**
	 * 获取用户的各种类型的佣金收入
	 * @param integer $uid
	 * @param integer $type
	 * @return mixed(float|array|bool)
	 */
	static function get_commision_income_bytype($uid, $type = NULL)
	{
	    $table = self::table();
	    $sql  = "SELECT ifnull(SUM(`commision`), 0) AS commision,`type` FROM {$table} WHERE `user_id` = %d AND `state`>=0 GROUP BY `type`";
	    $list = D()->query($sql, $uid)->fetch_array_all();
	    $ret  = [
	        self::COMMISSION_TYPE_FX => 0.00,
	        self::COMMISSION_TYPE_DL   => 0.00,
	        self::COMMISSION_TYPE_RZ   => 0.00,
	        self::COMMISSION_TYPE_JY   => 0.00
	    ];
	    if (!empty($list)) {
	        foreach ($list AS $it) {
	            $ret[$it['type']] = $it['commision'];
	        }
	    }
	    return isset($type) ? (isset($ret[$type]) ? $ret[$type] : false) : $ret;
	}
	
	/**
	 * 获取返利收入
	 * @param unknown $uid
	 */
	static function get_rebate_commision($uid)
	{
	    $table = self::table();
	    $sql  = "SELECT ifnull(SUM(`commision`), 0) AS commision FROM {$table} WHERE `user_id` = `order_uid` and `user_id` = %d AND `state`>=0 ";
	    return D()->query($sql, $uid)->result();
	}
	
	/**
	 * 查询佣金列表
	 * @param PagerPull $pager
	 * @param unknown $options
	 */
	static function get_commision_list(PagerPull $pager, $options){
	    $where = '';
        if(isset($options['state']) && $options['state'] >= 0){
            $where .= ' AND `state` =  '.intval($options['state']);
        }else{
            $where .= ' AND `state` in ('.UserCommision::STATE_ACTIVE.', '.UserCommision::STATE_CASHED.') ';
        }
        if(isset($options['type']) && $options['type'] >= 0){
            $where .= ' AND `type` =  '.intval($options['type']);
        }
        //是否是自己返利
        if(isset($options['rebate']) && $options['rebate'] == 1){
            $where .= ' AND user_id = order_uid ';
        }
	    $table = self::table();
	    $sql = "select * from {$table} where `user_id` = %d $where order by rid desc limit %d,%d";
	    $result = D()->query($sql,$GLOBALS['user']->uid,$pager->start, $pager->realpagesize)->fetch_array_all();
	    foreach ($result AS &$r) {
	        $r['paid_time'] = date("Y-m-d | H:i:s",simphp_gmtime2std($r['paid_time']));
	        $r['paid_time'] = str_replace('|', '<br>', $r['paid_time']);
	    }
	    $pager->setResult($result);
	}
	
	/**
	 * 获取用户可提现记录ID集合
	 * @param integer $uid
	 * @return array
	 */
	static function get_active_commision_ids($uid) {
		$table = self::table();
		$sql  = "SELECT `rid` FROM {$table} WHERE `user_id` = %d AND `state`=%d ORDER BY `rid` ASC"; //加入ORDER BY是为了确保顺序跟值完全一样
		$rids = D()->query($sql, $uid, self::STATE_ACTIVE)->fetch_column('rid');
		return $rids;
	}
	
	/**
	 * 获取用户当前可提现金额数
	 * @param integer $uid
	 * @return double
	 */
	static function get_active_commision($uid) {
		$table = self::table();
		$sql  = "SELECT SUM(`commision`) AS commision FROM {$table} WHERE `user_id` = %d AND `state`=%d";
		$commision = D()->query($sql, $uid, self::STATE_ACTIVE)->result();
		return $commision;
	}
	
	/**
	 * 更改佣金记录状态
	 * @param string  $record_ids  记录ID字符串
	 * @param integer $state_to    要变更的状态
	 * @return boolean
	 */
	static function change_state($record_ids, $state_to) {
		if (empty($record_ids)) return false;
		D()->query("UPDATE ".self::table()." SET `state`=%d,`state_time`=%d WHERE `rid` IN(%s)",
		           $state_to, simphp_time(), $record_ids);
		return D()->affected_rows()==1 ? true : false;
	}
	
	/**
	 * 
	 * @param string $record_ids
	 * @return number
	 */
	static function count_commision($record_ids) {
		if (empty($record_ids)) return 0;
		$count = D()->query("SELECT SUM(`commision`) AS total_commision FROM ".self::table()." WHERE `rid` IN(%s)", $record_ids)->result();
		return $count ? : 0;
	}
	
}

/*----- END FILE: class.UserCommision.php -----*/