<?php
/**
 * 提现 Node Model
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

/**
 * UserCashing
 * 
 * @property cashing_id
 * @property cashing_no
 * @property payment_no
 * @property cashing_amount
 * @property actual_amount
 * @property user_id
 * @property user_nick
 * @property user_mobile
 * @property bank_code
 * @property bank_name
 * @property bank_province
 * @property bank_city
 * @property bank_distict
 * @property bank_branch
 * @property bank_no
 * @property bank_uname
 * @property apply_time
 * @property payment_time
 * @property state
 * @property state_time
 * @property remark
 * @property commision_ids
 */
class UserCashing extends StorageNode {
	
	/**
	 * 状态常量
	 * @var constant
	 */
	const STATE_INVALID            = -1; //无效记录
	const STATE_CHECK_PENDING      =  0; //申请已提交,待审核
	const STATE_SUBMIT_AUTOCHECK   =  1; //提交自动审核
	const STATE_NOPASS_AUTOCHECK   =  2; //没有通过自动审核
	const STATE_PASS_AUTOCHECK     =  3; //通过自动审核
	const STATE_SUBMIT_MANUALCHECK =  4; //提交人工审核
	const STATE_NOPASS_MANUALCHECK =  5; //没有通过人工审核
	const STATE_PASS_MANUALCHECK   =  6; //通过人工审核
	const STATE_SUBMIT_BANK        =  7; //提交到银行转账中
	const STATE_FAIL               =  9; //提现失败
	const STATE_SUCC               = 10; //提现成功
	
	protected static function meta() {
		return array(
				'table' => '`shp_user_cashing`',
				'key'   => 'cashing_id',
				'columns' => array(
						'cashing_id'      => 'cashing_id',
						'cashing_no'      => 'cashing_no',
						'payment_no'      => 'payment_no',
						'cashing_amount'  => 'cashing_amount',
						'actual_amount'   => 'actual_amount',
						'user_id'         => 'user_id',
						'user_nick'       => 'user_nick',
						'user_mobile'     => 'user_mobile',
						'bank_code'       => 'bank_code',
						'bank_name'       => 'bank_name',
						'bank_province'   => 'bank_province',
						'bank_city'       => 'bank_city',
						'bank_distict'    => 'bank_distict',
						'bank_branch'     => 'bank_branch',
						'bank_no'         => 'bank_no',
						'bank_uname'      => 'bank_uname',
						'apply_time'      => 'apply_time',
						'payment_time'    => 'payment_time',
						'state'           => 'state',
						'state_time'      => 'state_time',
						'remark'          => 'remark',
						'commision_ids'   => 'commision_ids',
				)
		);
	}
	
	/**
	 * 生成提现订单号
	 * @return string
	 */
	static function gen_cashing_no() {
		/* 选择一个随机的方案 */
		mt_srand((double) microtime() * 1000000);
		return 'C'.date('YmdHis') . str_pad(mt_rand(1, 999), 5, '0', STR_PAD_LEFT);
	}
	

	/**
	 * 更改提现记录状态
	 * @param string  $record_ids  记录ID字符串
	 * @param integer $state_to    要变更的状态
	 * @param string  $remark      变更状态的说明
	 * @return boolean
	 */
	static function change_state($record_ids, $state_to, $remark = '') {
		if (empty($record_ids)) return false;
		D()->query("UPDATE ".self::table()." SET `state`=%d,`state_time`=%d,`remark`='%s' WHERE `cashing_id` IN(%s)",
		          $state_to, simphp_time(), $remark, $record_ids);
		return D()->affected_rows()==1 ? true : false;
	}
	
	/**
	 * 检查提现的订单记录是否有对应正确的支付交易号
	 * @param string $record_ids 记录ID字符串
	 * @return boolean
	 */
	static function check_out_trade_no($record_ids) {
		if (empty($record_ids)) return false;
		$tb_uc = UserCommision::table();
		$tb_od = Order::table();
		$sql = "SELECT COUNT(uc.`rid`) AS num FROM {$tb_uc} uc INNER JOIN {$tb_od} o ON uc.order_id=o.order_id WHERE uc.`rid` IN(%s) AND o.pay_trade_no<>'' AND o.pay_status=2";
		$num = D()->query($sql, $record_ids)->result();
		return $num==count(explode(',', $record_ids)) ? true : false;
	}
	
	/**
	 * 获取用户提现列表
	 * @param integer $uid
	 * @return array
	 */
	static function cashing_list($uid = NULL) {
		$where = "";
		if (!is_null($uid)) {
			$where = "AND `user_id`=%d";
		}
		$sql = "SELECT * FROM ".self::table()." WHERE 1 {$where} AND `state`>=0 ORDER BY `cashing_id` DESC LIMIT 0,50";
		$list= D()->query($sql, $uid)->fetch_array_all();
		if (!empty($list)) {
			foreach ($list AS &$it) {
				if ($it['state'] == self::STATE_SUCC) {
					$it['state_type']= 3;
					$it['state_txt'] = '提现成功';
				}
				elseif (in_array($it['state'], [self::STATE_FAIL,self::STATE_NOPASS_MANUALCHECK])) {
					$it['state_type']= 2;
					$it['state_txt'] = '提现失败';
				}
				else {
					$it['state_type']= 1;
					$it['state_txt'] = '提现中';
				}
			}
		}
		return $list;
	}
}
 
/*----- END FILE: class.UserCashing.php -----*/