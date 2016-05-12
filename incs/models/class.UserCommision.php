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
	const COMMISSION_TYPE_JY = 3; //交易 - 商家交易给推荐人的佣金
	
	//分成比例设定
	/* static $share_ratio = [
			'0' => PLATFORM_COMMISION,  //平台分成比例
			'1' => 0.35,                //一级上级分成比例
			'2' => 0.35,                //二级上级分成比例
			'3' => 0.30,                //三级上级分成比例
	]; */
	
    //推荐商家 商家交易佣金终身分佣 金牌代理2% 银牌代理1%
    static $share_radio_trade_gold = 0.02;
    
    static $share_radio_trade_silver = 0.01;
	
    //米商及米商以上级别 有自己返利
    static $share_ratio_fx_misha = [
        '0' => 0.25,                //米商及米商以上级别的购物返利
        '1' => 0.30,                //一级上级分成比例
        '2' => 0.25,                //二级上级分成比例
        '3' => 0.20,                //三级上级分成比例
    ];
    
    //米客没有自己返利
    static $share_ratio_fx_mike = [
        '1' => 0.45,                //一级上级分成比例
        '2' => 0.30,                //二级上级分成比例
        '3' => 0.25,                //三级上级分成比例
    ];
    
	//发展代理 各层分佣
	static $share_ratio_agent = [
	    '1' => 0.45,
	    '2' => 0.30,
	    '3' => 0.25
	];
	
	//金牌推荐商家 各层分佣
	static $share_ratio_gold_merchant = [
	    '1' => 0.30,
	    '2' => 0.10,
	    '3' => 0.05
	];
	
	//银牌推荐商家 各层分佣
	static $share_ratio_silver_merchant = [
	    '1' => 0.20,
	    '2' => 0.05,
	    '3' => 0.05
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
						'state_time'   => 'state_time',
				        'type'         => 'type'
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
	/* static function user_share($total_commision, $leader_level = 1) {
		if (!in_array($leader_level, [1,2,3])) return 0;
		$total_commision = self::can_share($total_commision);
		if ($total_commision <= 0) return 0;
		return number_format($total_commision*self::$share_ratio[$leader_level],2);
	} */
	
	static function generate($exOrder) {
	    if (!$exOrder->is_exist()) {
	        return false;
	    }
	    $total_commision = $exOrder->commision;
	    if(!$total_commision){
	        return false;
	    }
	    $cUser  = Users::load($exOrder->user_id, true);
	    //扣除平台的20%
	    $commision = self::can_share($total_commision);
	    //推荐商家终身提点 从平台20%扣除 用总佣金计算
	    self::merchantInviteCommision($cUser, $exOrder, ($total_commision));
	    $share_radio = 0;
	    //米商及米商以上
	    if($cUser->level){
	        $share_radio = self::$share_ratio_fx_misha;
	        self::createCommisionForGoodsFX($cUser, $cUser, $exOrder, $commision, 0, self::COMMISSION_TYPE_FX, $share_radio);
	    }else{
	        $share_radio = self::$share_ratio_fx_mike;
	    }
	    // 1级
	    $parent_level= 1;
	    if ($cUser->parentid) {
	        $parent = Users::load($cUser->parentid);
	        self::createCommisionForGoodsFX($cUser, $parent, $exOrder, $commision, $parent_level, self::COMMISSION_TYPE_FX, $share_radio);
	        //2级
	        $parent_level = 2;
	        if($parent->parentid){
	            $parent2 = Users::load($parent->parentid);
	            self::createCommisionForGoodsFX($cUser, $parent2, $exOrder, $commision, $parent_level, self::COMMISSION_TYPE_FX, $share_radio);
	            //3级
	            $parent_level = 3;
	            if($parent2->parentid){
	                $parent3 = Users::load($parent2->parentid);
	                self::createCommisionForGoodsFX($cUser, $parent3, $exOrder, $commision, $parent_level, self::COMMISSION_TYPE_FX, $share_radio);
	            }
	        }
	         
	    }
	    
	}
	
	//商品交易时给商家推荐人分佣
	static function merchantInviteCommision($cUser, $exOrder, $platf_commision){
	    //查找当前订单商家对应的推荐人
	    $merchant = Merchant::load($exOrder->merchant_ids);
	    if($merchant->invite_code){
	        $invite_user = Users::load($merchant->invite_code);
	        if($invite_user->is_exist()){
	            $radio = 0;
	            if(Users::isGoldAgent($invite_user->level)){
	                $radio = self::$share_radio_trade_gold;
	            }else if(Users::isSilverAgent($invite_user->level)){
	                $radio = self::$share_radio_trade_silver;
	            }
	            $commision = number_format($platf_commision*$radio,2);
	            self::createCommision($cUser, $invite_user, $exOrder, 0, UserCommision::COMMISSION_TYPE_JY, $radio, $commision);
	        }
	    }
	}
	
	/**
	 * 生成佣金记录
	 * @param integer  $order_id
	 * @return boolean
	 */
	/* static function generate($order_id) {
	    
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
	} */
	
	/**
	 * 发展代理生成佣金
	 * @param unknown $order_id
	 * @param unknown $cUser
	 * @param $agent
	 */
	static function generatForAgent($exOrder, $cUser, $agent){
	    if (!$exOrder->is_exist()) {
	        return false;
	    }
	    // 1级
	    $parent_level= 1;
	    if ($cUser->parentid) {
	        $agent_type = $agent['level'];
	        //佣金根据类型写死
	        if($agent_type == Users::USER_LEVEL_3 || $agent_type == Users::USER_LEVEL_5){
	            $exOrder->commision = 198;
	        }else if($agent_type == Users::USER_LEVEL_4){
	            $exOrder->commision = 98;
	        }
	        $commision_dl = UserCommision::COMMISSION_TYPE_DL;
	        $share_radio = UserCommision::$share_ratio_agent;
	        
	        $parent = Users::load($cUser->parentid);
	        self::createCommisionForAgent($cUser, $parent, $exOrder, $parent_level, $commision_dl, $share_radio);
	        //2级
	        $parent_level = 2;
	        if($parent->parentid){
    	        $parent2 = Users::load($parent->parentid);
	            self::createCommisionForAgent($cUser, $parent2, $exOrder, $parent_level, $commision_dl, $share_radio);
	            
	            //3级
	            $parent_level = 3;
	            if($parent2->parentid){
    	            $parent3 = Users::load($parent2->parentid);
	                self::createCommisionForAgent($cUser, $parent3, $exOrder, $parent_level, $commision_dl, $share_radio);
	            }
	        }
	    
	    }
	}
	
	/**
	 * 推荐商家生成佣金
	 * @param unknown $order_id
	 * @param unknown $cUser
	 * @param $agent
	 */
	static function generatForMerchant($exOrder, $cUser){
	    if (!$exOrder->is_exist()) {
	        return false;
	    }
	    // 1级
	    $parent_level= 1;
	    if ($cUser->parentid) {
	        $exOrder->commision = $exOrder->goods_amount;
	        $commision_rz = UserCommision::COMMISSION_TYPE_RZ;
	        $share_gold_radio = UserCommision::$share_ratio_gold_merchant;
	        $share_silver_radio = UserCommision::$share_ratio_silver_merchant;
	         
	        $parent = Users::load($cUser->parentid);
	        self::createCommisionForAgent($cUser, $parent, $exOrder, $parent_level, $commision_rz, $share_gold_radio, $share_silver_radio);
	    
	        //2级
	        $parent_level = 2;
	        if($parent->parentid){
    	        $parent2 = Users::load($parent->parentid);
	            self::createCommisionForAgent($cUser, $parent2, $exOrder, $parent_level, $commision_rz, $share_gold_radio, $share_silver_radio);
	            
	            //3级
	            $parent_level = 3;
	            if($parent2->parentid){
    	            $parent3 = Users::load($parent2->parentid);
	                self::createCommisionForAgent($cUser, $parent3, $exOrder, $parent_level, $commision_rz, $share_gold_radio, $share_silver_radio);
	            }
	        }
	         
	    }
	}
	
	/**
	 * 发展代理推荐店铺 给一二三层分配佣金，只有代理资格才能分佣
	 * @param $buyer 购买人
	 * @param unknown $cUser 当前需要分配佣金用户
	 * @param unknown $exOrder
	 * @param unknown $parent_level
	 * @param unknown $gold_share_radio
	 * @param unknown $silver_share_radio
	 */
	static function createCommisionForAgent($buyer, $cUser, $exOrder, $parent_level, $type, $gold_share_radio, $silver_share_radio = null){
	    if(!Users::isAgent($cUser->level)){
	        //return;
	    }
	    if(!$silver_share_radio || count($silver_share_radio) == 0){
	        $silver_share_radio = $gold_share_radio;
	    }
	    $radio = 0;
	    if($cUser->level == Users::USER_LEVEL_3 || $cUser->level == Users::USER_LEVEL_5){
	        //金牌代理
	        $radio = $gold_share_radio[$parent_level];
	    }else if($cUser->level == Users::USER_LEVEL_4){
	        //银牌代理
	        $radio = $silver_share_radio[$parent_level];
	    }
	    $radio = $radio ? $radio : 0;
	    $commision = number_format($exOrder->commision*$radio,2);
	    return self::createCommision($buyer, $cUser, $exOrder, $parent_level, $type, $radio, $commision);
	}
	
	/**
	 * 商品分销返佣
	 * @param unknown $buyer
	 * @param unknown $cUser
	 * @param unknown $exOrder
	 * @param unknown $commision
	 * @param unknown $parent_level
	 * @param unknown $type
	 * @param unknown $share_radio
	 */
	static function createCommisionForGoodsFX($buyer, $cUser, $exOrder, $commision, $parent_level, $type, $share_radio){
	    $radio = $share_radio[$parent_level];
	    $radio = $radio ? $radio : 0;
	    $commision = number_format($commision*$radio,2);
	    return self::createCommision($buyer, $cUser, $exOrder, $parent_level, $type, $radio, $commision);
	}
	
	/**
	 * 创建佣金数据
	 * @param unknown $buyer
	 * @param unknown $cUser
	 * @param unknown $exOrder
	 * @param unknown $parent_level
	 * @param unknown $type
	 * @param unknown $radio
	 * @param unknown $commision
	 */
	static function createCommision($buyer, $cUser, $exOrder, $parent_level, $type, $radio, $commision){
	    $upUC = new UserCommision();
	    $upUC->user_id      = $cUser->uid;
	    $upUC->parent_level = $parent_level;
	    $upUC->order_uid    = $buyer->uid;
	    $upUC->order_unick  = $buyer->nickname;
	    $upUC->order_id     = $exOrder->order_id;
	    $upUC->order_sn     = $exOrder->order_sn;
	    $upUC->order_amount = $exOrder->money_paid;
	    $upUC->commision    = $commision;
	    $upUC->use_ratio    = $radio;
	    $upUC->paid_time    = $exOrder->pay_time;
	    $upUC->type         = $type;
	    if(UserCommision::COMMISSION_TYPE_DL == $type){
	        $upUC->state = 1;
	    }
	    $upUC->save(Storage::SAVE_INSERT_IGNORE);
	    return $upUC;
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
            $where .= ' AND `state` >= 0 ';
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