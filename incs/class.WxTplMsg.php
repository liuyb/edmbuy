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
	
	/**
	 * 提现成功通知
	 * @param string $openid
	 * @param string $first
	 * @param string $remark
	 * @param string $url
	 * @param array  $extra 包括:
	 *   $extra['apply_money']   申请提现金额
	 *   $extra['actual_money']  实际到账金额
	 *   $extra['apply_time']    申请提现时间
	 *   $extra['succ_time']     成功提现时间
	 *   $extra['cashing_no']    提现单号
	 *   $extra['payment_no']    交易单号
	 * @return boolean
	 */
	static function cashing_success($openid, $first, $remark, $url, Array $extra)
	{
		$tplData = new WxTplData();
		//固定部分
		if (!self::$is_debug) {
			$tplData->tplid = 'PBMVeWrFNew8fTIlRjNVqBglz25__k_xY8YmmLn4it0'; //"益多米"账号
		}
		else {
			$tplData->tplid = 'HoxiCaqwp1u0mkzjK1rp2nHFXIM4fjW6fVGrnday4dQ'; //"多米测"账号
		}
		$tplData->tplurl   = $url;
		$tplData->first    = self::packdata($first, '#173177');
		$tplData->remark   = self::packdata($remark ? "\n".$remark : '');
		//额外部分
		$tplData->money    = self::packdata($extra['apply_money']."元\n到账金额:".$extra['actual_money']."元");
		$tplData->timet    = self::packdata($extra['apply_time']."\n到账时间:".$extra['succ_time']."\n提现单号:".$extra['cashing_no']."\n交易单号:\n".$extra['payment_no']);
		
		return self::send($openid, $tplData);
	}
	
	/**
	 * 提现失败通知
	 * @param string $openid
	 * @param string $first
	 * @param string $remark
	 * @param string $url
	 * @param array  $extra 包括:
	 *   $extra['money']   提现金额
	 *   $extra['time']    提现时间
	 *   $extra['cashing_no'] 提现单号
	 * @return boolean
	 */
	static function cashing_fail($openid, $first, $remark, $url, Array $extra)
	{
		$tplData = new WxTplData();
		//固定部分
		if (!self::$is_debug) {
			$tplData->tplid = 'UZnY-Sc8xB44RE8ghcu11VJ1Lmb695xQAfVeozwuSu4'; //"益多米"账号
		}
		else {
			$tplData->tplid = 'PINAk6TDPSgniNh4Xa1duERLtY0Kmu6OWWhrjvEPRds'; //"多米测"账号
		}
		$tplData->tplurl   = $url;
		$tplData->first    = self::packdata($first, '#173177');
		$tplData->remark   = self::packdata($remark ? "\n".$remark : '');
		//额外部分
		$tplData->money    = self::packdata($extra['money']."元");
		$tplData->time     = self::packdata($extra['time']."\n提现单号:".$extra['cashing_no']);
		
		return self::send($openid, $tplData);
	}
	
	/**
	 * 提交成功提醒
	 * @param string $openid
	 * @param string $first
	 * @param string $remark
	 * @param string $url
	 * @param array  $extra 包括:
	 *   $extra['keyword1'] 微信昵称
	 *   $extra['keyword2'] 时间
	 * @return boolean
	 */
	static function submit_ok($openid, $first, $remark, $url, Array $extra)
	{
		$tplData = new WxTplData();
		//固定部分
		if (!self::$is_debug) {
			$tplData->tplid = '2CkRWh5dlnSzxbLFD7A7LJnZ3D_TN2UflSKATpOYY2g'; //"益多米"账号
		}
		else {
			$tplData->tplid = 'ggBRvqhrp2jFtb8hMguUHxmn8Wi8XoV5Z0bq2cecmSQ'; //"多米测"账号
		}
		$tplData->tplurl   = $url;
		$tplData->first    = self::packdata($first."\n", '#173177');
		$tplData->remark   = self::packdata($remark ? "\n".$remark : '');
		//额外部分
		$tplData->keyword1 = self::packdata($extra['keyword1']);
		$tplData->keyword2 = self::packdata($extra['keyword2']);
		
		return self::send($openid, $tplData);
	}
	
	/**
	 * 退款模板消息
	 * @param unknown $openid
	 * @param unknown $first
	 * @param unknown $url
	 * @param array $extra
	 */
	static function refund_msg($openid, $first, $url, Array $extra)
	{
	    $tplData = new WxTplData();
	    //固定部分
	    if (!self::$is_debug) {
	        $tplData->tplid = '0jEzX6Umm-A2ajrlDJFsta3Vr8egzGDGrzOPQbxiLEs'; //"益多米"账号
	    }
	    else {
	        $tplData->tplid = '0jEzX6Umm-A2ajrlDJFsta3Vr8egzGDGrzOPQbxiLEs'; //"多米测"账号
	    }
	    $tplData->tplurl   = $url;
	    $tplData->first    = self::packdata($first, '#173177');
	    $tplData->keyword1   = self::packdata($extra['succ_time']);
	    $tplData->keyword2   = self::packdata($extra['order_sn']);
	    $tplData->keyword3   = self::packdata($extra['refund_sn']);
	    $tplData->keyword4   = self::packdata($extra['refund_money']);
	    $tplData->keyword5   = self::packdata($extra['reason']);
	    return self::send($openid, $tplData);
	}
	
	/**
	 * 成为会员提醒
	 * @param unknown $openid
	 * @param unknown $first
	 * @param unknown $remark
	 * @param unknown $url
	 * @param array $extra
	 * keyword1 会员编号
	 * keyword2 有效期
	 */
	static function be_member($openid, $first, $remark, $url, Array $extra)
	{
	    $tplData = new WxTplData();
	    //固定部分
	    if (!self::$is_debug) {
	        $tplData->tplid = 'GqaGlGGfO4E3mLk9y8YGBVvpf_65Zs__cgxUW_GdE4E'; //"益多米"账号
	    }
	    else {
	        $tplData->tplid = 'j3nZmDeHEP7SztIN8fC-jfeiM3sb4LX7KGDl5peYE4Y'; //"多米测"账号
	    }
	    $tplData->tplurl   = $url;
	    $tplData->first    = self::packdata($first."\n", '#173177');
	    $tplData->remark   = self::packdata("\n".$remark);
	    //额外部分
	    $tplData->keyword1 = self::packdata($extra['uid']);
	    $tplData->keyword2 = self::packdata($extra['valid_date']);
	    return self::send($openid, $tplData);
	}
	
	/**
	 * 返利提醒
	 * @param unknown $openid
	 * @param unknown $first
	 * @param unknown $remark
	 * @param unknown $url
	 * @param array $extra
	 * keyword1 商品名称
	 * keyword2 返利金额
	 * keyword3 支付金额
	 * keyword4 支付时间
	 */
	static function fanli($openid, $first, $remark, $url, Array $extra)
	{
	    $tplData = new WxTplData();
	    //固定部分
	    if (!self::$is_debug) {
	        $tplData->tplid = 'I2VsT3BgZjfpr70RDN-Yc7ESHbtB_ZmJLBqTUxKu0W4'; //"益多米"账号
	    }
	    else {
	        $tplData->tplid = 'hD9Qr4JNfKMr7R93D808zw-_hAkZ6b8vpAIMXYOsHqw'; //"多米测"账号
	    }
	    $tplData->tplurl   = $url;
	    $tplData->first    = self::packdata($first."\n", '#173177');
	    $tplData->remark   = self::packdata("\n".$remark);
	    //额外部分
	    $tplData->keyword1 = self::packdata($extra['goods_name']);
	    $tplData->keyword2 = self::packdata($extra['fanli']);
	    $tplData->keyword3 = self::packdata($extra['paid_money']);
	    $tplData->keyword4 = self::packdata($extra['pay_time']);
	    return self::send($openid, $tplData);
	}
	
	/**
	 * 会员升级提醒
	 * @param unknown $openid
	 * @param unknown $first
	 * @param unknown $remark
	 * @param unknown $url
	 * @param array $extra
	 *  姓名：{{keyword1.DATA}}
                现在等级：{{keyword2.DATA}}
                升级等级：{{keyword3.DATA}}
                通过时间：{{keyword4.DATA}}
	 */
	static function memberUpgrade($openid, $first, $remark, $url, Array $extra)
	{
	    $tplData = new WxTplData();
	    //固定部分
	    if (!self::$is_debug) {
	        $tplData->tplid = 'Y4ohSj0KMTr0bMq4bSHVbKDGfRxOfsD6ptEZKLf4HVs'; //"益多米"账号
	    }
	    else {
	        $tplData->tplid = '_PDdLZeUGcKsbYIA8VhIzB-ofW93urWVaA4_z3kUmVQ'; //"多米测"账号
	    }
	    $tplData->tplurl   = $url;
	    $tplData->first    = self::packdata($first."\n", '#173177');
	    $tplData->remark   = self::packdata("\n".$remark);
	    //额外部分
	    $tplData->keyword1 = self::packdata($extra['nickname']);
	    $tplData->keyword2 = self::packdata($extra['oldLevel']);
	    $tplData->keyword3 = self::packdata($extra['newLevel']);
	    $tplData->keyword4 = self::packdata($extra['time']);
	
	    return self::send($openid, $tplData);
	}
	
	/**
	 * 商家入驻提醒
	 * @param unknown $openid
	 * @param unknown $first
	 * @param unknown $remark
	 * @param unknown $url
	 * @param array $extra
	 *  入驻商家：{{keyword1.DATA}}
                审核：{{keyword2.DATA}}
	 */
	static function settleMsg($openid, $first, $remark, $url, Array $extra)
	{
	    $tplData = new WxTplData();
	    //固定部分
	    if (!self::$is_debug) {
	        $tplData->tplid = 'WWfmkrCy0vqnklvdQj7afx-Ob8HiUC3nBEGq21DZfL4'; //"益多米"账号
	    }
	    else {
	        $tplData->tplid = 'ZE1a-OhpLL003uiE4ujB8BjJCuxRPoTyBqKfekdto-I'; //"多米测"账号
	    }
	    $tplData->tplurl   = $url;
	    $tplData->first    = self::packdata($first."\n", '#173177');
	    $tplData->remark   = self::packdata("\n".$remark);
	    //额外部分
	    $tplData->keyword1 = self::packdata($extra['facename']);
	    $tplData->keyword2 = self::packdata($extra['check_state']);
	
	    return self::send($openid, $tplData);
	}
	
	/**
	 * 新增订单提醒
	 * @param unknown $openid
	 * @param unknown $first
	 * @param unknown $remark
	 * @param unknown $url
	 * @param array $extra
	 * 订单时间：{{keyword1.DATA}}
              订单类型：{{keyword2.DATA}}
	 */
	static function new_order($openid, $first, $remark, $url, Array $extra)
	{
	    $tplData = new WxTplData();
	    //固定部分
	    if (!self::$is_debug) {
	        $tplData->tplid = '3Rt18CIfigH8JtcEFSIbKiVcyZezcDqTIRStWGCcsWo'; //"益多米"账号
	    }
	    else {
	        $tplData->tplid = 'c5JQgMcByRBJMB4uBC3jI_q2W3sT7_glXdJe8_s-z1Y'; //"多米测"账号
	    }
	    $tplData->tplurl   = $url;
	    $tplData->first    = self::packdata($first."\n", '#173177');
	    $tplData->remark   = self::packdata("\n".$remark);
	    //额外部分
	    $tplData->keyword1 = self::packdata($extra['time']);
	    $tplData->keyword2 = self::packdata($extra['type']);
	
	    return self::send($openid, $tplData);
	}
	
	/**
	 * 订单发货提醒
	 * @param unknown $openid
	 * @param unknown $first
	 * @param unknown $remark
	 * @param unknown $url
	 * @param array $extra
	 *  订单编号：{{keyword1.DATA}}
                卖家：{{keyword2.DATA}}
                快递公司：{{keyword3.DATA}}
                快递单号：{{keyword4.DATA}}
                发货时间：{{keyword5.DATA}}  
	 */
	static function order_shipping($openid, $first, $remark, $url, Array $extra){
	    $tplData = new WxTplData();
	    //固定部分
	    if (!self::$is_debug) {
	        $tplData->tplid = 'hRwNoONfC_lHiyQhDQ7ARZeLZEYPH9ZgFXlPRGBeDE8'; //"益多米"账号
	    }
	    else {
	        $tplData->tplid = 'LffKNZ-zey4hHoddWt98OyLAlqZLqbHVWbKxfOGzIBE'; //"多米测"账号
	    }
	    $tplData->tplurl   = $url;
	    $tplData->first    = self::packdata($first."\n", '#173177');
	    $tplData->remark   = self::packdata("\n".$remark);
	    //额外部分
	    $tplData->keyword1 = self::packdata($extra['order_sn']);
	    $tplData->keyword2 = self::packdata($extra['seller']);
	    $tplData->keyword3 = self::packdata($extra['shipping_name']);
	    $tplData->keyword4 = self::packdata($extra['shipping_no']);
	    $tplData->keyword5 = self::packdata($extra['sihpping_time']);
	    
	    return self::send($openid, $tplData);
	}
	
	/**
	 * 订单自动确认收货
	 * @param unknown $openid
	 * @param unknown $first
	 * @param unknown $remark
	 * @param unknown $url
	 * @param array $extra
	 *  订单编号：{{keyword1.DATA}}
                订单商品：{{keyword2.DATA}}
                发货时间：{{keyword3.DATA}}
                自动确认收货时间：{{keyword4.DATA}}
	 */
	static function order_auto_receive($openid, $first, $remark, $url, Array $extra){
	    $tplData = new WxTplData();
	    //固定部分
	    if (!self::$is_debug) {
	        $tplData->tplid = 'QYwawLj8P8SFvE1g_Fw0CpcBW_BaRaQVyU-TNiw-S8o'; //"益多米"账号
	    }
	    else {
	        $tplData->tplid = 'u7DK16nP_drqUs_kWCMNFSuwKB2ID5Gvut3QGSlJbzQ'; //"多米测"账号
	    }
	    $tplData->tplurl   = $url;
	    $tplData->first    = self::packdata($first."\n", '#173177');
	    $tplData->remark   = self::packdata("\n".$remark);
	    //额外部分
	    $tplData->keyword1 = self::packdata($extra['order_sn']);
	    $tplData->keyword2 = self::packdata($extra['item_desc']);
	    $tplData->keyword3 = self::packdata($extra['sihpping_time']);
	    $tplData->keyword4 = self::packdata($extra['receive_time']);
	     
	    return self::send($openid, $tplData);
	}
	
	/**
	 * 账号(预)锁定提醒
	 * @param string $openid
	 * @param string $first
	 * @param string $remark
	 * @param string $url
	 * @param array  $extra 包括:
	 *   $extra['locked_account']  锁定账号
	 *   $extra['locked_time']     锁定时间
	 * @return boolean
	 */
	static function account_locked($openid, $first, $remark, $url, Array $extra)
	{
		$tplData = new WxTplData();
		//固定部分
		if (!self::$is_debug) {
			$tplData->tplid = 'VTU1FGk5rkDiwWB6UZGgULt_msdhUTjpd4My6dkDyE0'; //"益多米"账号
		}
		else {
			$tplData->tplid = 'm46gGkGoJRdfpjNG4Q2hPgJKp3Qwav0O7htheG0EJCo'; //"多米测"账号
		}
		$tplData->tplurl   = $url;
		$tplData->first    = self::packdata($first."\n", '#173177');
		$tplData->remark   = self::packdata("\n".$remark);
		//额外部分
		$tplData->keyword1 = self::packdata($extra['locked_account']);
		$tplData->keyword2 = self::packdata($extra['locked_time']);
	
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