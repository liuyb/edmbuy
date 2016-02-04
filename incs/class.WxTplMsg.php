<?php
/**
 * 微信模板消息类
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class WxTplMsg {
	
	/**
	 * Weixin对象
	 * @var Weixin
	 */
	private static $wx;
	
	/**
	 * 是否调试模式
	 * @var boolean
	 */
	public static $is_debug = false;
	
	/**
	 * 发送模板消息内容到微信接口
	 * 
	 * @param string    $openid
	 * @param WxTplData $tplData
	 * @return boolean
	 */
	private static function send($openid, WxTplData $tplData)
	{
		//初始化Weixin对象
		if (!isset(self::$wx) || !(self::$wx instanceof Weixin) ) {
			self::$wx = new Weixin([Weixin::PLUGIN_MSGSEND]);
		}
		
		//组装数据
		$_tpld = [];
		$_dyd  = $tplData->getData();
		$_tpld['first'] = $tplData->first;
		if (!empty($_dyd)) {
			foreach ($_dyd AS $k=>$v) {
				$_tpld[$k] = $v;
			}
		}
		$_tpld['remark']= $tplData->remark;
		
		//发送数据
		$ret = self::$wx->msgsend->sendTplMsg($openid, $tplData->tplid, $tplData->tplurl, $_tpld);
		return empty($ret) ? false : true;
	}

	/**
	 * 打包消息内容数据
	 * @param string $value
	 * @param string $color
	 * @return array ['value'=>'[VALUE]','color'=>'COLOR']
	 */
	private static function packdata($value, $color = '')
	{
		$res = ['value'=>$value];
		if ($color && preg_match('/^#\d{6}$/', $color)) {
			$res['color'] = $color;
		}
		return $res;
	}
	
	/**
	 * 显示中文友好日期时间
	 * @param string $time
	 * @return string
	 */
	static function human_dtime($time = NULL) {
		if (!isset($time)) {
			$time = time();
		}
		return date('Y年n月j日 H:i:s', $time);
	}
	
	/**
	 * 邀请好友注册成功通知
	 * @param string $openid
	 * @param string $first
	 * @param string $remark
	 * @param string $url
	 * @param array  $extra 包括:
	 *   $extra['friendname']  好友名称
	 *   $extra['regtime']     注册时间
	 * @return WxTplMsg
	 */
	static function invite_reg($openid, $first, $remark, $url, Array $extra)
	{
		$tplData = new WxTplData();
		//固定部分
		if (!self::$is_debug) {
			$tplData->tplid = '4J-2s0XK7X_RKBVhXubpJIoW72N0qk8xs_CO7wdNScI'; //"益多米"账号
		}
		else {
			$tplData->tplid = 'IzS4E8v6dnzYxQrSe2jnHNtLZymMTuB8xH1JXYnEPko'; //"多米测"账号
		}
		$tplData->tplurl   = $url;
		$tplData->first    = self::packdata($first."\n", '#173177');
		$tplData->remark   = self::packdata("\n".$remark);
		//额外部分
		$tplData->keyword1 = self::packdata($extra['friendname']);
		$tplData->keyword2 = self::packdata($extra['regtime']);
		
		return self::send($openid, $tplData);
	}
	
	/**
	 * 商品支付成功通知
	 * @param string $openid
	 * @param string $first
	 * @param string $remark
	 * @param string $url
	 * @param array  $extra 包括:
	 *   $extra['paid_money']  付款金额
	 *   $extra['item_desc']   商品详情
	 *   $extra['pay_way']     支付方式
	 *   $extra['order_sn']    交易单号
	 *   $extra['pay_time']    交易时间
	 * @return boolean
	 */
	static function pay_succ($openid, $first, $remark, $url, Array $extra)
	{
		$tplData = new WxTplData();
		//固定部分
		if (!self::$is_debug) {
			$tplData->tplid = 'C6xmqixnkL_cmNcF-ZqOOb5BENOtxxqalxy3JjyADeo'; //"益多米"账号
		}
		else {
			$tplData->tplid = 'n9UVojrgGsXllE2-FMMV3YmmSlwCdlJlklHYHujTdxo'; //"多米测"账号
		}
		$tplData->tplurl   = $url;
		$tplData->first    = self::packdata($first."\n", '#173177');
		$tplData->remark   = self::packdata("\n".$remark);
		//额外部分
		$tplData->keyword1 = self::packdata($extra['paid_money']);
		$tplData->keyword2 = self::packdata($extra['item_desc']);
		$tplData->keyword3 = self::packdata($extra['pay_way']);
		$tplData->keyword4 = self::packdata($extra['order_sn']);
		$tplData->keyword5 = self::packdata($extra['pay_time']);
		
		return self::send($openid, $tplData);
	}
	
	/**
	 * 分销支付成功提醒
	 * @param string $openid
	 * @param string $first
	 * @param string $remark
	 * @param string $url
	 * @param array  $extra 包括:
	 *   $extra['order_sn']     订单单号
	 *   $extra['order_amount'] 订单金额
	 *   $extra['order_state']  订单状态
	 * @return boolean
	 */
	static function sharepay_succ($openid, $first, $remark, $url, Array $extra)
	{
		$tplData = new WxTplData();
		//固定部分
		if (!self::$is_debug) {
			$tplData->tplid = 'TH14OF91TnN3zknUgTvJizkrNd5DWWUBZbv8Iw60jhA'; //"益多米"账号
		}
		else {
			$tplData->tplid = 'WYYQe1TkhQhKGzX1AIf7Bey6ONFL3R_ge0CZugSDjLM'; //"多米测"账号
		}
		$tplData->tplurl   = $url;
		$tplData->first    = self::packdata($first."\n", '#173177');
		$tplData->remark   = self::packdata("\n".$remark);
		//额外部分
		$tplData->keyword1 = self::packdata($extra['order_sn']);
		$tplData->keyword2 = self::packdata($extra['order_amount']);
		$tplData->keyword3 = self::packdata($extra['order_state']);
		
		return self::send($openid, $tplData);
	}
	
	/**
	 * 分销订单提成通知
	 * @param string $openid
	 * @param string $first
	 * @param string $remark
	 * @param string $url
	 * @param array  $extra 包括:
	 *   $extra['order_sn']     订单单号
	 *   $extra['order_amount'] 订单金额
	 *   $extra['share_amount'] 分成金额
	 *   $extra['order_time']   订单时间
	 * @return boolean
	 */
	static function sharecommision_succ($openid, $first, $remark, $url, Array $extra)
	{
		$tplData = new WxTplData();
		//固定部分
		if (!self::$is_debug) {
			$tplData->tplid = 'rR6CjjrrTJRKSHJdi-RIffoylTkhDUts1SvNWr4vaUQ'; //"益多米"账号
		}
		else {
			$tplData->tplid = 'mgonCekQjE4N6rR-liPFhLKhjIEr_F6MQLHIquYJFJg'; //"多米测"账号
		}
		$tplData->tplurl   = $url;
		$tplData->first    = self::packdata($first."\n", '#173177');
		$tplData->remark   = self::packdata("\n".$remark);
		//额外部分
		$tplData->keyword1 = self::packdata($extra['order_sn']);
		$tplData->keyword2 = self::packdata($extra['order_amount']);
		$tplData->keyword3 = self::packdata($extra['share_amount']);
		$tplData->keyword4 = self::packdata($extra['order_time']);
		
		return self::send($openid, $tplData);
	}
	
	/**
	 * 完善资料通知
	 * @param string $openid
	 * @param string $first
	 * @param string $remark
	 * @param string $url
	 * @param array  $extra 包括:
	 *   $extra['org_name']      机构名称
	 *   $extra['info_required'] 需完善的资料
	 *   $extra['info_remark']    备注
	 * @return boolean
	 */
	static function required_info($openid, $first, $remark, $url, Array $extra)
	{
		$tplData = new WxTplData();
		//固定部分
		if (!self::$is_debug) {
			$tplData->tplid = 'WMS_Uvnj8WNmVPID6ZGkcYbnbwH6NEgkFovfjETMOfs'; //"益多米"账号
		}
		else {
			$tplData->tplid = 'ofgPnNH9bPbKNCrJSYreKv9ZFAEYrm1hASk8HKxfQi4'; //"多米测"账号
		}
		$tplData->tplurl   = $url;
		$tplData->first    = self::packdata($first."\n", '#173177');
		$tplData->remark   = self::packdata("\n".$remark);
		//额外部分
		$tplData->keyword1 = self::packdata($extra['org_name']);
		$tplData->keyword2 = self::packdata($extra['info_required']);
		$tplData->keyword3 = self::packdata($extra['info_remark']);
		
		return self::send($openid, $tplData);
	}
	
}

/**
 * 微信模板内容类
 */
class WxTplData extends CBase {
	
	/**
	 * 模板内容必须参数
	 * @var string
	 */
	public $tplid;
	public $tplurl;
	public $first;
	public $remark;
	
	/**
	 * 初始化构造函数
	 * @param string
	 */
	public function __construct()
	{
		$this->tplurl= U('','',true); //默认跳转到首页
	}
	
	/**
	 * 获取动态添加部分数据
	 * @return array
	 */
	public function getData() {
		return $this->__DATA__;
	}
	
}

/*----- END FILE: class.WxTplMsg.php -----*/