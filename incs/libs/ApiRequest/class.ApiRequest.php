<?php
/**
 * ApiRequest请求入口文件
 * 
 * 使用方法实例：
 * require (dirname(__FILE__).'/ApiRequest/class.ApiRequest.php');
 * $req = new ApiRequest();
 * $req->sign()->send()->recv();
 */
//~ set class auto load
function __api_request_autoload($class) {
	static $api_request_dir = NULL;
	if (!isset($api_request_dir)) {
		$api_request_dir = dirname(__FILE__).DIRECTORY_SEPARATOR;
	}
	$filepath = $api_request_dir.'class.'.$class.'.php';
	if (file_exists($filepath)) {
		require $filepath;
	}
}
spl_autoload_register('__api_request_autoload');

/**
 * ApiRequest请求驱动类
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
class ApiRequest implements ApiRequestInterface {
	
	const SIGN_SKEY_LEFT  = 1;
	const SIGN_SKEY_RIGHT = 2;
	
	static public $allowed_encfunc = array('md5','sha1'); //限定允许的加密函数
	
	protected $io         = null;					//io请求类对象
	protected $response   = array('flag'=>FALSE,'ret'=>array()); //响应结果存储
	protected $error      = array();      //错误信息存储
	protected $apiConfig  = array (
              'ioClass' => 'ApiIOCurl', //io请求类名
              'url'     => '',          //请求地址
              'params'  => array(),     //表单参数,可以是array, 也可以是经过url编码之后的string
              'cookies' => array(),     //cookie参数,可以是array, 也可以是经过url编码之后的string
              'files'   => array(),     //file参数，必须数组,且键名带前缀'@'
              'signinfo'=> array(       //签名相关信息，包括：
                 'name' => 'sig',       //签名名字，&key=val的key部分
                 'skey' => '',          //签名密钥
              'skeypos' => self::SIGN_SKEY_RIGHT, //skey在拼接串的位置(前或后)
                 'sep'  => '',          //签名密钥与源串拼接符
               'encfunc'=> 'sha1',      //加密算法函数: md5/sha1
               'debug'  => FALSE,       //是否打印输出调试信息
           'sign_params'=> array(),     //如果此数组不为空，则签名时只让该数组成员参与签名
    'urlencode_level'   => 2,           //urlencode级别:
                                        //  5: base64url_encode key 和 value
                                        //  4: base64url_encode value
                                        //  3: rawurlencode key 和 value
                                        //  2: 仅rawurlencode value
                                        //  1: 仅rawurlencode key
                                        //  0: key 和 value 都不rawurlencode
              ),
              'protocol'=> 'http',      //请求协议，http/https
              'method'  => 'get',       //请求方法, get/post
              'packto'  => '',       		//当请求方法为get时，设置此参数可以让所有提交参数(包括签名字段)都用base64编码打包到一个参数，比如'd'
              'outfile' => '',          //输出目标地，当非空和不等于STDOUT时，将输出重定向到该参数指定的文件路径
              'timeout' => 10,          //请求执行超时时间(单位：秒)
              'timeout_connect' => 5,   //请求连接超时时间(单位：秒)
              'sslcert' => array(),     //SSL证书信息，包括：cert_file, cert_passwd, cert_type("PEM"默认,"DER","ENG") 三个可选"字段 => 值"，仅对protocol=https有效
              'cafile'  => '',          //证书文件地址(绝对地址)，仅对protocol=https有效
            );
	
	/**
	 * 构造函数，可传入配置覆盖默认的配置
	 * @param Array $config
	 */
	public function __construct(Array $apiConfig = array()) {
		$this->apiConfig = array_merge($this->apiConfig, $apiConfig);
		$this->io = new $this->apiConfig['ioClass']();
	}
	
	/**
	 * 生成请求签名
	 * @param  Array  $signinfo,  签名信息，包括：
	 *                array(
	 *                  'name'    => 'sig',    //签名名字，&key=val的key部分
	 *                  'skey'    => '7bd5aeb51db999a5d28cafe7af915cad',    //签名密钥
	 *                  'skeypos' => self::SIGN_SKEY_RIGHT, //skey在拼接串的位置
	 *                  'sep'     => '',       //签名密钥与源串拼接符
	 *                  'encfunc' => 'sha1',   //加密算法函数
	 *                  'debug'   => FALSE,    //是否打印输出调试信息
	 *               'sign_params'=> array(),  //如果此数组不为空，则签名时只让该数组成员参与签名
	 *        'urlencode_level'   => 2,        //urlencode级别
	 *                                         //  5: base64url_encode key 和 value
	 *                                         //  4: base64url_encode value
	 *                                         //  3: rawurlencode key 和 value
	 *                                         //  2: 仅rawurlencode value
	 *                                         //  1: 仅rawurlencode key
	 *                                         //  0: key 和 value 都不rawurlencode
	 *                )
	 * @param  Callable $callback
	 * @return ApiRequest
	 */
	public function sign($signinfo=array(), $callback=NULL) {
		if (is_callable($signinfo)) { //让支持第一个参数也可以是函数
			$callback = $signinfo;
			$signinfo = array();
		}
		
		if (is_callable($callback)) {
			$callback($this,$signinfo);
		}
		else {
			
			//~ checking $signinfo
			try {
				if (!is_array($signinfo)) {
					throw new ApiRequestException('Parameters $signinfo is not a array');
				}
				$signinfo = array_merge($this->signinfo, $signinfo);
				if (''==$signinfo['name'] || ''==$signinfo['skey']) {
					throw new ApiRequestException('ApiRequest->signinfo required setting');
				}
				elseif (!is_callable($signinfo['encfunc'])) {
					throw new ApiRequestException("signinfo['encfunc'] is not callable");
				}
				elseif (!in_array(strtolower($signinfo['encfunc']), self::$allowed_encfunc)) {
					throw new ApiRequestException('unsupported encrypt algorithm function(allowed '.join(',',self::$allowed_encfunc).')');
				}
			}
			catch (ApiRequestException $e) {
				$this->setError($e)->dumpError(TRUE);
			}
			
			$sign_params = array();
			if (!empty($signinfo['sign_params'])) {
				$sign_params = $signinfo['sign_params'];
			}
			else {
				if (is_string($this->params)) { //传入的$this->params是经过url编码之后的string
					parse_str($this->params, $sign_params);
				}
				else { //Array
					$sign_params = $this->params;
				}
			}
			
			//~ 1.按字典顺序升值排序查询数组
			$this->sortParams($sign_params);
			$signinfo['debug'] && self::debug($sign_params,'After Sort');
			
			//~ 2.拼接排好序的query string
			$query_string = $this->makeQueryString($sign_params,$signinfo['urlencode_level']);
			$signinfo['debug'] && self::debug($query_string,'Before Connect');
			
			//~ 3.拼接query string与签名密钥
			if ($signinfo['skeypos']!=self::SIGN_SKEY_RIGHT) {
				$query_string  = $signinfo['skey'].$signinfo['sep'].$query_string;
			}
			else {
				$query_string .= $signinfo['sep'].$signinfo['skey'];
			}
			$signinfo['debug'] && self::debug($query_string,'After Connect');
			
			//~ 4.用加密算法加密，即得参数签名sign
			$sign = $signinfo['encfunc']($query_string);
			$signinfo['debug'] && self::debug($sign,'Sign');
			
			//~ 5.合并签名数据到$this->params
			$this->params = array_merge($this->params,array($signinfo['name'] => $sign));
			$signinfo['debug'] && self::debug($this->params,'Final Post');
		}
		return $this;
	}
	
	/**
	 * 发送ApiRequest请求
	 * @return ApiRequest
	 */
	public function send() {
		try {
			$this->io->makeRequest($this);
		}
		catch (ApiRequestException $e) {
			$this->setError($e)->dumpError(TRUE);
		}
		return $this;
	}
	
	/**
	 * 接受响应处理
	 * 
	 * @param Bool or Callable $raw_return, 是否输出原始返回，还是输出经过array('flag'=>true,'ret'=>array())的包裹，默认后者
	 * @param Bool or Callable $callback, 当$callback为可调用函数时，做函数回调；当$callback===TRUE时，直接打印原始输出
	 * @throws ApiRequestException, 当$raw_return=true，array['flag']=false时，会抛出此异常供上层捕捉
	 * @return mixed
	 */
	public function recv($raw_return=FALSE, $callback=NULL) {
		
		if (is_string($this->response['ret'])) {
			$this->response['ret'] = json_decode($this->response['ret'],TRUE);
		}
		
		if (is_callable($raw_return)) {
			$callback   = $raw_return;
			$raw_return = FALSE;
		}
		if (is_callable($callback)) {
			return $callback($this,$raw_return);
		}
		
		if (TRUE===$callback) {
			$this->dumpResponse(TRUE,$raw_return);
		}
		if (TRUE===$raw_return) {
			if (!$this->response['flag']) {
				throw new ApiRequestException('Network Request Error('.$this->response['ret'].')',$this->response['errno']);
			}
			return $this->response['ret'];
		}
		
		return $this->response;
	}
	
	/**
	 * merge query params array by appending possible files params
	 * @param Array or String $params
	 * @param Array $files
	 * @return Array
	 */
	public function mergeQueryParams($params=array(), Array $files=array()) {
		if (is_string($params)) {
			$this->parse_str_raw($params,$params);
		}
		$params = array_merge($params,$files);
		return $params;
	}
	
	/**
	 * 拼接查询参数
	 * @param Array or String $params
	 * @param Int $encode_level
	 *        5 => base64url_encode key 和 value
	 *        4 => base64url_encode value
	 *        3 => rawurlencode key and value
	 *        2 => just rawurlencode value
	 *        1 => just rawurlencode key
	 *        0 => neither key nor value rawurlencode
	 * @return String
	 */
	public function makeQueryString($params,$encode_level=2) {
		if (is_string($params)) {
			return $params;
		}
	
		$query_string = array();
		foreach ($params as $key => $val) {
			switch ($encode_level) {
				case 5:
					$key = self::base64url_encode($key);
					$val = self::base64url_encode($val);
					break;
				case 4:
					$val = self::base64url_encode($val);
					break;
				case 3:
					$key = rawurlencode($key);
					$val = rawurlencode($val);
					break;
				case 2:
					$val = rawurlencode($val);
					break;
				case 1:
					$key = rawurlencode($key);
					break;
				case 0:
				default:
			}
			array_push($query_string, $key . '=' . $val);
		}
		$query_string = join('&', $query_string);
		
		return $query_string;
	}
	
	/**
	 * 拼接Cookie参数
	 * @param Array or String $params
	 * @return String
	 */
	public function makeCookieString($params) {
		if (is_string($params)) {
			return $params;
		}
	
		$cookie_string = array();
		foreach ($params as $key => $val) {
			array_push($cookie_string, $key . '=' . $val);
		}
		$cookie_string = join('; ', $cookie_string);
		
		return $cookie_string;
	}
	
	/**
	 * 按字典升序排序查询参数
	 * @param String $method
	 * @param String $url_path
	 * @param Array or String $params
	 * @return String
	 */
	static public function sortParams(Array &$params) {
		ksort($params,SORT_STRING);
		return $params;
	}
	
	/**
	 * set request url
	 * @param string $url
	 * @return ApiRequest
	 */
	public function setUrl($url) {
		$this->apiConfig['url'] = $url;
		return $this;
	}
	
	/**
	 * set request params
	 * @param string $params
	 * @return ApiRequest
	 */
	public function setParams($params) {
		$this->apiConfig['params'] = $params;
		return $this;
	}
	
	/**
	 * set request cookies
	 * @param string $cookies
	 * @return ApiRequest
	 */
	public function setCookies($cookies) {
		$this->apiConfig['cookies'] = $cookies;
		return $this;
	}
	
	/**
	 * set files for posting
	 * @param Array $files
	 * @return ApiRequest
	 */
	public function setFiles(Array $files) {
		$this->apiConfig['files'] = $files;
		return $this;
	}
	
	/**
	 * set signature info
	 * @param Array $signinfo
	 * @return ApiRequest
	 */
	public function setSigninfo(Array $signinfo) {
		$this->apiConfig['signinfo'] = $signinfo;
		return $this;
	}
	
	/**
	 * set request protocol: http/https
	 * @param string $protocol
	 * @return ApiRequest
	 */
	public function setProtocol($protocol) {
		$this->apiConfig['protocol'] = $protocol;
		return $this;
	}
	
	/**
	 * set request http method: get/post
	 * @param string $method
	 * @return ApiRequest
	 */
	public function setMethod($method) {
		$this->apiConfig['method'] = $method;
		return $this;
	}
	
	/**
	 * set response result
	 * @param Array $response
	 * @return ApiRequest
	 */
	public function setResponse(Array $response) {
		$this->response = $response;
		return $this;
	}
	
	/**
	 * get response result
	 * @return Array
	 */
	public function getResponse() {
		return $this->response;
	}
	
	/**
	 * set error message
	 * @param Array $error
	 * @return ApiRequest
	 */
	public function setError($error) {
		if ($error instanceof Exception) {
			$error = array(
				'errMsg'  => $error->getMessage(),
				'errFile' => $error->getFile(),
				'errLine' => $error->getLine(),
			);
		}
		$this->error = $error;
		return $this;
	}
	
	/**
	 * get error message
	 * @return Array
	 */
	public function getError() {
		return $this->error;
	}
	
	/**
	 * get final request url
	 * 
	 * @param int $urlencode_level, like $signinfo['urlencode_level']
	 * @return string
	 */
	public function getFinalUrl($urlencode_level=2) {
		$url = $this->url;
		if ('get'==$this->method) {
			$url .= (FALSE===strrpos($url,'?') ? '?' : '&') . $this->makeQueryString($this->params,$urlencode_level);
		}
		return $url;
	}
	
	/**
	 * dump error message
	 */
	public function dumpError($is_die=FALSE) {
		echo '<pre>';
		print_r($this->error);
		echo '</pre>';
		$is_die && exit;
	}
	
	/**
	 * dump response message
	 */
	public function dumpResponse($is_die=FALSE,$raw_ouput=FALSE) {
		echo '<pre>';
		print_r($raw_ouput?$this->response['ret']:$this->response);
		echo '</pre>';
		$is_die && exit;
	}
	
	/**
	 * magic method '__get'
	 */
	public function __get($name) {
		return array_key_exists($name, $this->apiConfig) ? $this->apiConfig[$name] : null;
	}
	
	/**
	 * magic method '__set'
	 */
	public function __set($name, $value) {
		$this->apiConfig[$name] = $value;
	}
	
	/**
	 * magic method '__isset'
	 */
	public function __isset($name) {
		return isset($this->apiConfig[$name]);
	}
	
	/**
	 * magic method '__unset'
	 */
	public function __unset($name) {
		unset($this->apiConfig[$name]);
	}
	
	/**
	 * like standard function 'parse_str', difference in 'Array Query String' dealing
	 *
	 * for example, for input $str='name=gavin&args[type]=person'
	 *
	 * parse_str_raw will set $arr to:
	 *   Array (
	 *     [name] => gavin
	 *     [args[type]] => person
	 *   )
	 *
	 * and parse_str will set $arr to:
	 *   Array(
	 *     [name] => gavin
	 *     [args] => Array (
	 *       [type] => person
	 *     )
	 *   )
	 *
	 * @param string $str
	 * @param array $array
	 * @return void
	 */
	public function parse_str_raw($str, &$array=array()) {
		$str = rawurldecode($str);
		$str = trim($str, $sep1.' ');
		$arr = explode($sep1, $str);
		
		$result = array();
		if (count($arr)) {
			foreach ($arr AS $_str) {
				$_arr = explode($sep2, $_str);
				$_arr[0] = trim($_arr[0]);
				if ($_arr[0] !== '') {
					$result[$_arr[0]] = isset($_arr[1])?trim($_arr[1]):'';
				}
			}
		}
		
		$array = $result;
	}
	
	/**
	 * Url safe version of base64_encode
	 * @param string $data
	 * @return string
	 */
	static function base64url_encode($data) {
		return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
	}
	
	/**
	 * Url safe version of base64_decode
	 * @param string $data
	 * @return string
	 */
	static function base64url_decode($data) {
		return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
	}
	
	/**
	 * debug output
	 * @param string $msg
	 * @param string $tit
	 */
	static public function debug($msg, $tit='') {
		if (is_string($msg)) {
			echo (''==$tit?'':('<strong>'.$tit.':</strong>')).'<p>'.$msg.'</p>';
		}
		else {
			echo (''==$tit?'':('<strong>'.$tit.':</strong>')).'<pre>';print_r($msg);echo '</pre>';
		}
	}
}

/**
 * ApiRequest请求例外类
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
class ApiRequestException extends Exception {
	/**
	 * @param message[optional]
	 * @param code[optional]
	 */
	public function __construct ($message = '', $code = -1) {
		parent::__construct($message,$code);
	}
}

/*--- END FILE: class.ApiRequest.php ---*/