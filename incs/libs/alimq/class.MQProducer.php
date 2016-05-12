<?php
/**
 * 消息生产者
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
//包含工具类
include("class.MQUtil.php");

/*
 * 消息生产者
*/
class MQProducer
{
	//签名
	private static $signature = "Signature";
	//生产者组ID
	private static $producerid = "ProducerId";
	//访问码
	private static $aks = "AccessKey";
	//配置信息
	private static $configs = null;

	//构造函数
	function __construct()
	{
		//读取配置信息
		self::$configs = parse_ini_file("config.ini");
	}

	//计算md5
	private function md5($str)
	{
		return md5($str);
	}

	//生产消息流程
	public function process()
	{
		//打印配置信息
		var_dump(self::$configs);
		//获取topic
		$topic = self::$configs["topic"];
		//获取保存topic的url路径
		$url = self::$configs["url"];
		//读取访问码
		$ak = self::$configs["user_accesskey"];
		//读取密钥
		$sk = self::$configs["user_secretkey"];
		//读取生产者组id
		$pid = self::$configs["producer_group"];

		//http请求体内容
		$body = "aaaaa";
		$newline = "\n";

		//构造工具对象
		$util = new MQUtil();
		for ($i = 0; $i<500; $i++) {
			//计算时间戳
			$date = (int)($util->microtime_float()*1000);

			//post请求url
			$postUrl = $url."/message/?topic=".$topic."&time=".$date."&tag=http&key=http";

			//签名字符串
			$signString = $topic.$newline.$pid.$newline.$this->md5($body).$newline.$date;

			//计算签名
			$sign = $util->calSignatue($signString,$sk);

			//初始化网络通信模块
			$ch = curl_init();

			//构造签名标记
			$signFlag = self::$signature.":".$sign;
			//构造密钥标记
			$akFlag = self::$aks.":".$ak;
			//构造生产者组标记
			$producerFlag = self::$producerid.":".$pid;
			//构造http请求头部内容类型标记
			$contentFlag = "Content-Type:text/html";

			//构造http请求头部
			$headers = array(
					$signFlag,
					$akFlag,
					$producerFlag,
					$contentFlag,
			);

			//设置http头部内容
			curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);

			//设置http请求类型,此处为POST
			curl_setopt($ch,CURLOPT_CUSTOMREQUEST,"POST");

			//设置http请求的url
			curl_setopt($ch,CURLOPT_URL,$postUrl);

			//设置http请求的body
			curl_setopt($ch,CURLOPT_POSTFIELDS,$body);

			//构造执行环境
			ob_start();

			//开始发送http请求
			curl_exec($ch);

			//获取请求应答消息
			$result = ob_get_contents();

			//清理执行环境
			ob_end_clean();

			//打印请求应答结果
			var_dump($result);

			//关闭连接
			curl_close($ch);
		}

	}
}

//构造消息生产者对象
$producer = new MQProducer();

//启动消息生产者
$producer->process();
 
/*----- END FILE: class.MQProducer.php -----*/