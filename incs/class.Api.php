<?php
/**
 * API Model
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
class Api extends Model {
	
	private static $codes = [
			'-1'   => 'Undefined error',
			'0'    => 'Operating success',
			'1000' => 'No data arrived',
			'1001' => 'Parameter \'v\' invalid',
			'1002' => 'Parameter \'format\' invalid',
			'1003' => 'Appid not exist',
			'1004' => 'Signature fail',
			//...
			'1300' => 'Wait for define',
			//...
			'1401' => 'Access Denied',
			'1404' => 'API not exist',
			//...
			'1503' => 'API service unavailable',
			//...
			'1999' => 'This API is developing',
	];
	
	static function append_codes(Array $codes = []) {
		self::$codes += $codes;
	}
	
	static function code($code) {
		return isset(self::$codes[$code]) ? self::$codes[$code] : self::$codes['-1'];
	}
	
	static function check(Request $request, Response $response) {
		$params = [];
		if($request->is_post()) {
			$params['args']  = $request->post('args',   []);
			$params['appid'] = $request->post('appid',  '');
			$params['format']= $request->post('format', '');
			$params['sig']   = $request->post('sig',    '');
			$params['ts']    = $request->post('ts',     '');
			$params['v']     = $request->post('v',      '');
		}
		else {
			 $d = $request->get('d','');
			 $d = base64_decode($d);
			 parse_str($d, $params);
		}
		
		if (empty($params)) {
			throw new ApiException(1000);
		}
		
		if (!self::check_version($params['v'])) {
			throw new ApiException(1001);
		}
		if (!self::check_format($params['format'])) {
			throw new ApiException(1002);
		}
		
		$app = App::load($params['appid']);
		if (empty($app)) {
			throw new ApiException(1003);
		}
		else {
			$sign = self::sign($params, $app->appSecret);
			if ($sign != $params['sig']) {
				throw new ApiException(1004);
			}
		}
		
		//通过检查，设定传入值
		$request->appid  = $params['appid'];
		$request->format = $params['format'];
		$request->ts     = $params['ts'];
		$request->v      = $params['v'];
		foreach ($params['args'] AS $k => $v) {
			$request->$k = $v;
		}
	}
	
	/**
	 * Check version
	 * @param string $v
	 * @return boolean
	 */
	private static function check_version($v) {
		return in_array($v, ['1.0.0']) ? TRUE : FALSE;
	}
	
	/**
	 * Check data format
	 * @param string $format
	 * @return boolean
	 */
	private static function check_format($format) {
		return in_array($format, ['json'/*,'xml'*/]) ? TRUE : FALSE;
	}
	
	/**
	 * Parameters signature by secret 
	 * @param array $params
	 * @param string $secret
	 * @return string
	 */
	private static function sign(Array $params, $secret) {
		
		//~ 取出sig, sig参数不需要签名
		unset($params['sig']);
		
		//~ 解析数组
		$params = self::parse_params($params);
		
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
	 * @return array
	 */
	private static function parse_params(Array $params) {
		$params_parsed = [];
		foreach ($params AS $key => $val) {
			if ('args'==$key) {
				foreach ($val AS $k => $v) {
					$uk = $key.'['.$k.']';
					$uk = rawurlencode($uk);
					$params_parsed[$uk] = rawurlencode($v);
				}
			}
			else {
				$key = rawurlencode($key);
				$val = rawurlencode($val);
				$params_parsed[$key] = $val;
			}
		}
		return $params_parsed;
	}
	
}

class ApiException extends Exception {
	
	/**
	 * Api returning result
	 * @var array
	 */
	protected $res = [];
	
	public function __construct($code = null, $message = null, Array $res = []) {
		parent::__construct($message, $code);
		$this->res = $res;
	}
	
	public function getResponse() {
		return $this->res;
	}
}

/*----- END FILE: class.Api.php -----*/