<?php
/**
 * Response Base Class
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Response {
  
  /**
   * response format, option value: 'html','text','json','xml'...
   * @var string
   */
  protected $format = 'html';
  
  /**
   * response header set
   * @var array
   */
  private $headers = array();
  
  /**
   * HTTP status code map
   * @var array
   */
  public static $statusMap = array(
    '100' => 'Continue',
    '101' => 'Switching Protocols',
    '102' => 'Processing',
    '200' => 'OK',
    '201' => 'Created',
    '202' => 'Accepted',
    '203' => 'Non-Authoritative Information',
    '204' => 'No Content',
    '205' => 'Reset Content',
    '206' => 'Partial Content',
    '207' => 'Multi-Status',
    '208' => 'Already Reported',
    '226' => 'IM Used',
    '300' => 'Multiple Choices',
    '301' => 'Moved Permanently',
    '302' => 'Found',
    '303' => 'See Other',
    '304' => 'Not Modified',
    '305' => 'Use Proxy',
    '306' => '(Unused)',
    '307' => 'Temporary Redirect',
    '308' => 'Permanent Redirect',
    '400' => 'Bad Request',
    '401' => 'Unauthorized',
    '402' => 'Payment Required',
    '403' => 'Forbidden',
    '404' => 'Not Found',
    '405' => 'Method Not Allowed',
    '406' => 'Not Acceptable',
    '407' => 'Proxy Authentication Required',
    '408' => 'Request Timeout',
    '409' => 'Conflict',
    '410' => 'Gone',
    '411' => 'Length Required',
    '412' => 'Precondition Failed',
    '413' => 'Request Entity Too Large',
    '414' => 'Request-URI Too Long',
    '415' => 'Unsupported Media Type',
    '416' => 'Requested Range Not Satisfiable',
    '417' => 'Expectation Failed',
    '418' => 'I’m a teapot',
    '420' => 'Policy Not Fulfilled',
    '421' => 'There are too many connections from your internet address',
    '422' => 'Unprocessable Entity',
    '423' => 'Locked',
    '424' => 'Failed Dependency',
    '425' => 'Unordered Collection',
    '428' => 'Precondition Required',
    '429' => 'Too Many Requests',
    '431' => 'Request Header Fields Too Large',
    '500' => 'Internal Server Error',
    '501' => 'Not Implemented',
    '502' => 'Bad Gateway',
    '503' => 'Service Unavailable',
    '504' => 'Gateway Timeout',
    '505' => 'HTTP Version Not Supported',
    '506' => 'Variant Also Negotiates',
    '507' => 'Insufficient Storage',
    '508' => 'Loop Detected',
    '509' => 'Bandwidth Limit Exceeded',
    '510' => 'Not Extended',
    '600' => 'Unparseable Response Headers',
  );
  
  protected static $singleton;
  
  public function __construct() {
  
    if (isset(self::$singleton) && !IS_CLI) {
      throw new NotSingletonException(get_called_class());
    }
    self::$singleton = TRUE;
    
  }
  
  /**
   * setter method for format
   * @param string $format
   * @return Response
   */
  public function setFormat($format) {
    $this->format = $format;
    return $this;
  }
  
  /**
   * set header, both key and value will be trim
   * @param $key
   * @param $value
   * @return Response for chaining
   * @throws Exception when any param contains new line.
   */
  public function header($key, $value) {
    if (preg_match("/[\r\n]/", $key . $value)) {
      throw new Exception('Header should not contains new line (\r,\n)');
    }
    //by default override the prev set.
    $this->headers[trim($key)] = trim($value);
    return $this;
  }
  
  /**
   * 
   * @param string  $content
   * @param string  $format, optional, option value: 'html','text','json','jsonp','xml','wap'...
   * @param string  $status, optional
   */
  public function send($content = '', $format = 'html' , $status = '200') {
    
    // check whether hashreq
    if (Request::is_hashreq()) {
      self::sendJSON(self::hashContent($content));
    }
    
    // make support the second parameter is status code
    if (is_numeric($format)) {
      $status = $format;
      $format = 'html';
    }
    
    // set Content-Type by content format
    $content_type = self::contentType($format);
    if ($content_type) {
      $this->header('Content-Type', $content_type);
    }
    
    // set default header
    if (!$status) $status = '200';
    
    // send headers
    @header($this->status($status));
    foreach ($this->headers as $key => $value) {
      @header("{$key}: {$value}");
    }
    
    // send content
    echo $content; //tigger __toString
    
    exit;
  }
  
  /**
   * send json content response
   * 
   * @param mixed(array or string) $content
   * @param string $status, optional
   */
  public static function sendJSON($content = '', $status = '200') {
    @header("Content-Type: ".self::contentType('json'));
    $jsoncontent = json_encode($content);
    $callback = !empty($_GET['jsoncb']) ? $_GET['jsoncb'] : '';
    echo ''==$callback ? $jsoncontent : $callback.'('.$jsoncontent.')';
    exit;
  }

  /**
   * send jsonp content response
   *
   * @param mixed(array or string) $content
   * @param string $callback json callback, optional, default to 'jsoncb'
   * @param string $status, optional
   */
  public static function sendJSONP($content = '', $callback='', $status = '200') {
    @header("Content-Type: ".self::contentType('jsonp'));
    $jsoncontent = json_encode($content);
    $callback = ''==$callback ? (!empty($_GET['jsoncb']) ? $_GET['jsoncb'] : 'jsoncb') : $callback;
    echo $callback.'('.$jsoncontent.')';
    exit;
  }
  
  /**
   * send XML content response
   * @param string $content
   * @param string $status, optional
   */
  public static function sendXML($content = '', $status = '200') {
    $charset = Config::get('env.charset','utf-8');
    @header("Content-Type: ".self::contentType('xml'));
    $xmlheader = "<?xml version=\"1.0\" encoding=\"{$charset}\"?>";
    echo $xmlheader , $content;
    exit;
  }
  
  /**
   * @param string $url, can be "back" as shortcut for the refer url.
   * @param int $status "302" by default, and can be set as "301"
   * @throws Exception when set wrong status code.
   */
  public static function redirect($url, $status = 302) {
    if (!in_array($status, array(302, 301))) throw new Exception('Can not redirect with status: ' . $status);
    if ($url == 'back') $url = isset($_SERVER['HTTP_REFERER']) ? trim($_SERVER['HTTP_REFERER']) : '/';
    if (!$url) $url = '/';
    if (Request::is_hashreq()) {
    	$hash= '#'.$url;
    	$jscontent = "<script>F.hashRedirect('{$hash}')</script>";
    	self::sendJSON(self::hashContent($jscontent));
    }
    @header(self::status($status));
    @header('Location: '.$url);
    exit;
  }
  
  public static function status($code, $text = null, $protocol = null) {
    $protocol = isset($protocol) ? $protocol : (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1');
    $text = !isset($text) ? self::$statusMap[$code] : $text;
    $status = "{$protocol} {$code} {$text}";
    return $status;
  }
  
  /**
   * get content type by format
   * @param string $format, option value: 'html','text','json','jsonp','xml','wap'...
   * @return string
   */
  public static function contentType($format='html') {
    
    $charset = Config::get('env.charset','utf-8');
    $content_type = '';
    switch ($format) {
      case 'html' :
        $content_type = "text/html; charset={$charset}";
        break;
      case 'json':
        $content_type = "application/json; charset={$charset}";
        break;
      case 'jsonp':
        $content_type = "application/javascript; charset={$charset}";
        break;
      case 'xml':
        $content_type = "text/xml; charset={$charset}";
        break;
      case 'wap' :
        $content_type = "text/vnd.wap.xml; charset={$charset}";
        break;
      case 'text':
        $content_type = "text/plain; charset={$charset}";
        break;
      default:
        
    }
    return $content_type;
  }
  
  /**
   * Generating hash format content
   * @param string $content
   * @return array
   */
  public static function hashContent($content) {
    $jscontent = array(
      'flag'   => 'SUC',
      'maxage' => isset($_GET['maxage']) ? intval($_GET['maxage']) : 60,
      'body'   => simphp_ziphtml($content),
    );
    return $jscontent;
  }
  
  /**
   * Reload current page
   */
  public static function reload() {
    $refer = Request::refer();
    $refer = $refer ? : '/';
    self::redirect($refer);
  }
  
  /**
   * Response error message
   * @param string $msg
   */
  public function error($msg = '') {
    $seo_default = array('title' => 'Oops! Error happened.', 'keyword' => '', 'desc' => $msg);
    $seo_error = C('seo.error', $seo_default);
    if (empty($seo_error['title'])) {
      $seo_error['title'] = $seo_default['title'];
    }
    if (empty($seo_error['desc'])) {
      $seo_error['desc'] = $msg;
    }
    $v = new PageView('error');
    $v->assign('err_msg', $msg)
      ->assign('seo', $seo_error);
    $this->send($v);
  }
  
  /**
   * Dump message
   * @param string $msg
   * @param string $tit, default to ''
   * @param boolean $exit, default to TRUE
   * @return none
   */
  public static function dump($msg, $tit = '', $exit = true) {
    if (is_string($msg)) {
      echo (''===$tit ?'':('<strong>'.$tit.':</strong>')).'<pre>'.$msg.'</pre>';
    }
    else {
      echo (''===$tit?'':('<strong>'.$tit.':</strong>')).'<pre>';
      print_r($msg);
      echo '</pre>';
    }
    if (true === $exit) exit;
  }
  
} 
 
 
/*----- END FILE: class.Response.php -----*/