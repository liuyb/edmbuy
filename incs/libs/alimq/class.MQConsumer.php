<?php
/**
 * 消息消费者
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
include ("class.MQUtil.php");

/*
 * 消息消费者
*/
class MQConsumer
{
	//签名
	private static $signature = "Signature";
	//消费者组id
	private static $consumerid = "ConsumerId";
	//访问码
	private static $ak = "AccessKey";
	//配置信息
	private static $config = null;

	//构造函数
	function __construct()
	{
		//读取配置信息
		self::$config = parse_ini_file("config.ini");
	}

	//消费者流程
	public function process()
	{
		//打印配置信息
		var_dump(self::$config);
		//获取topic
		$topic = self::$config["topic"];
		//获取topic的url路径
		$url = self::$config["url"];
		//访问码
		$ak = self::$config["user_accesskey"];
		//密钥
		$sk = self::$config["user_secretkey"];
		//消费者组id
		$cid = self::$config["consumer_group"];

		$newline = "\n";

		//构造工具对象
		$util = new MQUtil();
		while (true)
		{
			try
			{
				//构造时间戳
				$date = (int)($util->microtime_float()*1000);
				//签名字符串
				$signString = $topic.$newline.$cid.$newline.$date;
				//计算签名
				$sign = $util->calSignatue($signString,$sk);
				//构造签名标记
				$signFlag = $this::$signature.":".$sign;
				//构造密钥标记
				$akFlag = $this::$ak.":".$ak;
				//构造消费者组标记
				$consumerFlag = $this::$consumerid.":".$cid;
				//构造http请求发送内容类型标记
				$contentFlag = "Content-Type:text/html";

				//构造http头部信息
				$headers = array(
						$signFlag,
						$akFlag,
						$consumerFlag,
						$contentFlag,
				);

				//构造http请求url
				$getUrl = $url."/message/?topic=".$topic."&time=".$date."&num=32";

				//初始化网络通信模块
				$ch = curl_init();

				//填充http头部信息
				curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);

				//设置http请求类型,此处为GET
				curl_setopt($ch,CURLOPT_CUSTOMREQUEST,"GET");
				//设置http请求url
				curl_setopt($ch,CURLOPT_URL,$getUrl);

				//构造执行环境
				ob_start();

				//开始发送http请求
				curl_exec($ch);

				//获取请求应答消息
				$result = ob_get_contents();

				//清理执行环境
				ob_end_clean();

				//打印请求应答信息
				var_dump($result);

				//关闭http网络连接
				curl_close($ch);

				//解析http应答信息
				$messages = json_decode($result,true);

				//如果应答信息中的没有包含任何的topic信息,则直接跳过
				if (count($messages) ==0)
				{
					continue;
				}

				//依次遍历每个topic消息
				foreach ((array)$messages as $message)
				{
					var_dump($message);
					//获取时间戳
					$date = (int)($util->microtime_float()*1000);

					//构造删除topic消息url
					$delUrl = $url."/message/?msgHandle=".$message['msgHandle']."&topic=".$topic."&time=".$date;

					//签名字符串
					$signString = $topic.$newline.$cid.$newline.$message['msgHandle'].$newline.$date;

					//计算签名
					$sign = $util->calSignatue($signString,$sk);

					//构造签名标记
					$signFlag = $this::$signature.":".$sign;

					//构造密钥标记
					$akFlag = $this::$ak.":".$ak;

					//构造消费者组标记
					$consumerFlag = $this::$consumerid.":".$cid;

					//构造http请求头部信息
					$delheaders = array(
							$signFlag,
							$akFlag,
							$consumerFlag,
							$contentFlag,
					);

					//初始化网络通信模块
					$ch = curl_init();

					//填充http请求头部信息
					curl_setopt($ch,CURLOPT_HTTPHEADER,$delheaders);

					//设置http请求url信息
					curl_setopt($ch,CURLOPT_URL,$delUrl);

					//设置http请求类型,此处为DELETE
					curl_setopt($ch,CURLOPT_CUSTOMREQUEST,'DELETE');

					//构造执行环境
					ob_start();

					//开始发送http请求
					curl_exec($ch);

					//获取请求应答消息
					$result = ob_get_contents();

					//清理执行环境
					ob_end_clean();

					//打印应答消息
					var_dump($result);

					//关闭连接
					curl_close($ch);
				}

			}
			catch (Exception $e)
			{
				//打印异常信息
				echo $e->getMessage();
			}
		}

	}
}

//构造消息消费者
$consumer = new MQConsumer();

//启动消息消费者
$consumer->process();
 
/*----- END FILE: class.MQConsumer.php -----*/