<?php
/**
 * 与充值相关的方法
 *
 * @author afar<afarliu@163.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Pay{
	/**
	 * 支付网关
	 * @param  [type] $pay_type  支付方式
	 * @param  [type] $order_no  [订单号]
	 * @param  [type] $pay_money [订单金额(元)]
	 * @return [type]            [description]
	 */
	public static function pay_gateway($pay_type, $order_no, $pay_money, $desc){
		$html_data = '充值方式不存在';
		//支付接口
		switch ($pay_type)
		{
			case 'alipay'://支付宝
			 	$html_data = self::alipay($order_no, $pay_money, $desc);
				break;
		}
		
		return $html_data;
	}
	/**
	 * 支付宝
	 * @param unknown_type $order_no
	 * @param unknown_type $money	单位（元）
	 * @param unknown_type $typeId	一级支付方式
	 * @param unknown_type $typeCode	二级支付方式NO
	 */ 
	public static function alipay($order_no,$money,$desc,$typeId='',$typeCode=''){
		$base_path = SIMPHP_ROOT.'/incs/libs/alipay/';
		require_once($base_path."alipay.config.php");
		require_once($base_path."lib/alipay_submit.class.php");
		
		/**************************调用授权接口alipay.wap.trade.create.direct获取授权码token**************************/
		
		//返回格式
		$format = "xml";
		//必填，不需要修改
		
		//返回格式
		$v = "2.0";
		//必填，不需要修改
		
		//请求号
		$req_id = $order_no;
		//必填，须保证每次请求都是唯一
		
		//**req_data详细信息**
		
		//服务器异步通知页面路径
		$notify_url = $alipay_config['notify_url'];
		//需http://格式的完整路径，不允许加?id=123这类自定义参数
		
		//页面跳转同步通知页面路径
		$call_back_url = $alipay_config['return_url'];
		//需http://格式的完整路径，不允许加?id=123这类自定义参数
		
		//操作中断返回地址
		$merchant_url = $alipay_config['merchant_url'];
		//用户付款中途退出返回商户的地址。需http://格式的完整路径，不允许加?id=123这类自定义参数
		
		//卖家支付宝帐户
		$seller_email = $alipay_config['seller_email'];
		//必填
		
		//商户订单号
		$out_trade_no = $order_no;
		//商户网站订单系统中唯一订单号，必填
		
		//订单名称
		$subject = $desc;
		//必填
		
		//付款金额
		$total_fee = $money;//单位（元）
		//必填
		
		//请求业务参数详细
		$req_data = '<direct_trade_create_req><notify_url>' . $notify_url . '</notify_url><call_back_url>' . $call_back_url . '</call_back_url><seller_account_name>' . $seller_email . '</seller_account_name><out_trade_no>' . $out_trade_no . '</out_trade_no><subject>' . $subject . '</subject><total_fee>' . $total_fee . '</total_fee><merchant_url>' . $merchant_url . '</merchant_url></direct_trade_create_req>';
		//必填
		
		/************************************************************/
		
		//构造要请求的参数数组，无需改动
		$para_token = array(
				"service" => "alipay.wap.trade.create.direct",
				"partner" => trim($alipay_config['partner']),
				"sec_id" => trim($alipay_config['sign_type']),
				"format"	=> $format,
				"v"	=> $v,
				"req_id"	=> $req_id,
				"req_data"	=> $req_data,
				"_input_charset"	=> trim(strtolower($alipay_config['input_charset']))
		);
		
		//建立请求
		$alipaySubmit = new AlipaySubmit($alipay_config);
		$html_text = $alipaySubmit->buildRequestHttp($para_token);
		
		//URLDECODE返回的信息
		$html_text = urldecode($html_text);
		
		//解析远程模拟提交后返回的信息
		$para_html_text = $alipaySubmit->parseResponse($html_text);
		
		//获取request_token
		$request_token = $para_html_text['request_token'];
		
		
		/**************************根据授权码token调用交易接口alipay.wap.auth.authAndExecute**************************/
		
		//业务详细
		$req_data = '<auth_and_execute_req><request_token>' . $request_token . '</request_token></auth_and_execute_req>';
		//必填
		
		//构造要请求的参数数组，无需改动
		$parameter = array(
				"service" => "alipay.wap.auth.authAndExecute",
				"partner" => trim($alipay_config['partner']),
				"sec_id" => trim($alipay_config['sign_type']),
				"format"	=> $format,
				"v"	=> $v,
				"req_id"	=> $req_id,
				"req_data"	=> $req_data,
				"_input_charset"	=> trim(strtolower($alipay_config['input_charset']))
		);
		
		//建立请求
		$alipaySubmit = new AlipaySubmit($alipay_config);
		$html_text = $alipaySubmit->buildRequestForm($parameter, 'get', '跳转中...');
		//header('Content-type: text/html; charset=utf-8');
		return $html_text;
	}
	
	public static function alipayCallback(){
		$base_path = SIMPHP_ROOT.'/incs/libs/alipay/';
		require_once($base_path."alipay.config.php");
		require_once($base_path."lib/alipay_notify.class.php");
		
		$data = print_r($_GET,TRUE);
		$data .= '\r\n'.print_r($_POST,TRUE);
		self::writePayLog($data,'callback');
		//计算得出通知验证结果
		$alipayNotify = new AlipayNotify($alipay_config);
		$verify_result = $alipayNotify->verifyNotify();
		
		//1.验证数据是否正常
		if($verify_result) {//验证成功
			/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			//请在这里加上商户的业务逻辑程序代
		
		
			//——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
			
			//2.提取数据
			//解密（如果是RSA签名需要解密，如果是MD5签名则下面一行清注释掉）
			//$notify_data = decrypt($_POST['notify_data']);
			$notify_data = $_POST['notify_data'];
		
			//获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
		
			//解析notify_data
			//注意：该功能PHP5环境及以上支持，需开通curl、SSL等PHP配置环境。建议本地调试时使用PHP开发软件
			$doc = new DOMDocument();
			$doc->loadXML($notify_data);
		
			if( ! empty($doc->getElementsByTagName( "notify" )->item(0)->nodeValue) ) {
				//商户订单号
				$out_trade_no = $doc->getElementsByTagName( "out_trade_no" )->item(0)->nodeValue;
				//支付宝交易号
				$trade_no = $doc->getElementsByTagName( "trade_no" )->item(0)->nodeValue;
				//交易状态
				$trade_status = $doc->getElementsByTagName( "trade_status" )->item(0)->nodeValue;
				
				$status = 0;//自定义的支付状态，用于跟踪接站内的订单处理，1:订单状态更新成功，2:给用户加平台币成功，3：扣除用户平台币成功，4：虚拟物品在线发货成功
				//$total_fee		= $_POST['total_fee'];//获取金额,单位元
				$total_fee		= $doc->getElementsByTagName( "total_fee" )->item(0)->nodeValue;//获取金额,单位元
				//$pay_date = $_POST['notify_time'];//支付成功的日期
				$pay_date = $doc->getElementsByTagName( "notify_time" )->item(0)->nodeValue;;//支付成功的日期
				
				$time = time();
				
				//3.根据付款状态做处理
				if($trade_status == 'TRADE_FINISHED') {
					//3.1.判断该笔订单是否在商户网站中已经做过处理
					
					$lock = 'LOCK TABLE {order} WRITE';
					D()->query($lock);
					$sql = "SELECT goods_total,state FROM {order} WHERE order_no='%s' ";
					$order = D()->get_one($sql,$out_trade_no);
					//订单存在，金额相同，订单状态为未支付
					if(!empty($order)&&$order['goods_total']==$total_fee&&$order['state']<1){
						//3.2.更新订单的状态为用户已付款
						D()->update_table('order', ['pay_timeline'=>$time,'state'=>1, 'out_order_no'=>$trade_no], ['order_no'=>$out_trade_no]);
						$affected = D()->affected_rows();
						if($affected>0){
							$status = 1;
							$unlock = 'UNLOCK TABLES';
							D()->query($unlock);
						}else{
							exit();
						}
					}
					$unlock = 'UNLOCK TABLES';
					D()->query($unlock);
					
					//如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
					//如果有做过处理，不执行商户的业务程序
					
						
					//注意：
					//该种交易状态只在两种情况下出现
					//1、开通了普通即时到账，买家付款成功后。
					//2、开通了高级即时到账，从该笔交易成功时间算起，过了签约时的可退款时限（如：三个月以内可退款、一年以内可退款等）后。
		
					//调试用，写文本函数记录程序运行情况是否正常
					//logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
					
					//更新本地支付状态成功
					if($status>0){
						echo "success";		//请不要修改或删除
					}	
				}
				else if ($trade_status == 'TRADE_SUCCESS') {
					//判断该笔订单是否在商户网站中已经做过处理
					//如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
					//如果有做过处理，不执行商户的业务程序
						
					//注意：
					//该种交易状态只在一种情况下出现——开通了高级即时到账，买家付款成功后。
		
					//调试用，写文本函数记录程序运行情况是否正常
					//logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
						
					echo "success";		//请不要修改或删除
				}else{
					//其它情况
					
				}
			}
		
			//——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
		
			/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		}
		else {
			//验证失败
			echo "fail";
		
			//调试用，写文本函数记录程序运行情况是否正常
			//logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
		}
	}
	
	/**
	 * 写充值日志
	 * @param $data
	 * @param $type	sign:认证签名失败,fail:充值 失败日志，error:金额不对，或者是已处过的订单
	 * 				updatebillfail:更新订单失败，updatecoinfail：更新用户龙币失败
	 * @return unknown_type
	 */
	private static function writePayLog($data,$type){
		$log_dir = SIMPHP_ROOT."/var/paylog/";
		if(!file_exists($log_dir)){
			mkdir($log_dir,0777);
		}
		$log = $log_dir."log_".$type.".txt";
	
		$file = fopen($log,"a");
		if($file){
			$datetime = date("Y-m-d H:i:s");
			fwrite($file,$data.'|'."{$datetime}\r\n");
		}else{
			echo "打开文件日志失败！";
			return false;
		}
		fclose($file);
		return true;
	}
	

	
}