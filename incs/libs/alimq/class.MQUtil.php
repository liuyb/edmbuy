<?php
/**
 * 工具类
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
class MQUtil
{
	//计算签名
	public static function calSignatue($str,$key)
	{
		$sign = "";
		if(function_exists("hash_hmac"))
		{
			$sign = base64_encode(hash_hmac("sha1",$str,$key,true));
		}
		else
		{
			$blockSize = 64;
			$hashfunc = "sha1";
			if(strlen($key) > $blockSize)
			{
				$key = pack('H*',$hashfunc($key));
			}

			$key = str_pad($key,$blockSize,chr(0x00));
			$ipad = str_repeat(chr(0x36),$blockSize);
			$opad = str_repeat(chr(0x5c),$blockSize);
			$hmac = pack(
					'H*',$hashfunc(
							($key^$opad).pack(
									'H*',$hashfunc($key^$ipad).$str
							)
					)
			);

			$sign = base64_encode($hmac);
		}
		return $sign;
	}

	//计算时间戳
	public static function microtime_float()
	{
		list($usec,$sec) = explode(" ",microtime());
		return ((float)$usec+(float)$sec);
	}
}
 
/*----- END FILE: class.MQUtil.php -----*/