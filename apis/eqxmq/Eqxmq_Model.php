<?php
/**
 * 一起享消息队列Model
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Eqxmq_Model extends Model {

	const AUTH_KEY_5 = 'q7sh97i27rics3vxo8s4kjokdg93ysee';
	
	/**
	 * 格式化header
	 * @return array()
	 */
	static function get_all_headers()
	{
		static $headers = array();
		if (empty($headers)) {
			foreach ($_SERVER as $name => $value)
			{
				if (substr($name, 0, 5) == 'HTTP_')
				{
					$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
				}
			}
		}
		return $headers;
	}
	
	function get_by_url($url)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
	
		$output = curl_exec($ch);
	
		curl_close($ch);
	
		return $output;
	}
	
	function verify($data, $signature, $pubKey)
	{
		$res = openssl_get_publickey($pubKey);
		$result = (bool) openssl_verify($data, base64_decode($signature), $res);
		openssl_free_key($res);
	
		return $result;
	}
	
	/**
	 * 检查消息签名
	 * @return string|boolean
	 */
	static function check_mq_sign() {
		$tmpHeaders = array();
		$headers = self::get_all_headers();
		foreach ($headers as $key => $value)
		{
			if (0 === strpos($key, 'X-Mns-'))
			{
				$tmpHeaders[$key] = $value;
			}
		}
		ksort($tmpHeaders, SORT_STRING);
		$canonicalizedMNSHeaders = implode("\n", array_map(function ($v, $k) { return $k . ":" . $v; }, $tmpHeaders, array_keys($tmpHeaders)));
		
		$method = $_SERVER['REQUEST_METHOD'];
		$canonicalizedResource = $_SERVER['REQUEST_URI'];
		$contentMd5 = '';
		if (array_key_exists('Content-MD5', $headers))
		{
			$contentMd5 = $headers['Content-MD5'];
		}
		$contentType = '';
		if (array_key_exists('Content-Type', $headers))
		{
			$contentType = $headers['Content-Type'];
		}
		$date = $headers['Date'];
		
		$stringToSign = strtoupper($method) . "\n" . $contentMd5 . "\n" . $contentType . "\n" . $date . "\n" . $canonicalizedMNSHeaders . "\n" . $canonicalizedResource;
		
		$publicKeyURL = base64_decode($headers['X-Mns-Signing-Cert-Url']);
		$publicKey = self::get_by_url($publicKeyURL);
		$signature = $headers['Authorization'];
		$pass = self::verify($stringToSign, $signature, $publicKey);
		return $pass ? TRUE : FALSE;
	}
	
	/**
	 * 检查数据参数签名
	 * @param array $data
	 * @return boolean
	 */
	static function check_data_sign(Array $data) {
		$sign = self::sign($data, self::AUTH_KEY_5, TRUE);
		return $sign==$data['sig'] ? TRUE : FALSE;
	}
	
	/**
	 * Parameters signature by secret
	 * @param array $params
	 * @param string $secret
	 * @param boolean $is_json whether json, default to false
	 * @return string
	 */
	static function sign(Array $params, $secret, $is_json = FALSE) {
	
		//~ 取出sig, sig参数不需要签名
		if(isset($params['sig'])) unset($params['sig']);
	
		//~ 解析数组
		$params = self::parse_params($params, $is_json);
	
		//~ 1.按字典顺序升值排序查询数组
		ksort($params,SORT_STRING);
	
		//~ 2.拼接排好序的query string
		$params_kv = array_map(function($k, $v){
			return $k.'='.$v;
		}, array_keys($params), array_values($params));
		$query_string = join('&', $params_kv);
	
		//~ 3.拼接query string与签名密钥
		$query_string .= '|' . $secret;
	
		//~ 4.用加密算法加密，即得参数签名sign
		$sign = sha1($query_string);
	
		return $sign;
	}
	
	/**
	 * Parsed parameters to one dimension array
	 * @param array $params
	 * @param boolean $is_json whether json, default to false
	 * @return array
	 */
	static function parse_params(Array $params, $is_json = FALSE) {
		$params_parsed = [];
		foreach ($params AS $key => $val) {
			if ('args'==$key || 'res'==$key) {
				if ($is_json) {
					$params_parsed[$key] = Api::json_encode($val);
				}
				else {
					foreach ($val AS $k => $v) {
						$uk = $key.'['.$k.']';
						$params_parsed[$uk] = $v;
					}
				}
			}
			else {
				$params_parsed[$key] = $val;
			}
		}
		return $params_parsed;
	}

}
 
/*----- END FILE: Eqxmq_Model.php -----*/