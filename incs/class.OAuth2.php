<?php
/**
 * OAuth2 Class
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
require_once (SIMPHP_INCS . '/libs/ApiRequest/class.ApiRequest.php');
class OAuth2 implements OAuth2Interface {
  
  /**
   * OAuth Platform, for example: weixin
   * @var string
   */
  protected $_platform;
  
  /**
   * OAuth2 configure
   * @var array
   */
  protected $_config = array(
    'client_id'    => '',
    'secret_key'   => '',
    'redirect_uri' => '',
    'response_type'=> 'code',
    'scope'        => 'basic',
    'state'        => ''
  );
  
  /**
   * Constructor
   *
   * @param array  $config
   * @param string $platform
   */
  public function __construct(Array $config = array(), $platform = 'weixin') {
    $this->_platform = $platform;
    $this->_config = array_merge($this->_config, $config);
  }
  
  /**
   * Dynamic set config values
   * 
   * @param array $config
   * @return OAuth2
   */
  public function setConfig(Array $config = array()) {
    $this->_config = array_merge($this->_config, $config);
    return $this;
  }
  
  /**
   * Get authorize url
   * 
   * @return string
   */
  public function authorize_url() {
    $redirect_uri = $this->redirect_uri;
    $redirect_uri = rawurlencode($redirect_uri);
  
    $url = '';
    if ($this->_platform == 'weixin') {
      $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$this->client_id}&redirect_uri={$redirect_uri}&response_type={$this->response_type}&scope={$this->scope}&state={$this->state}#wechat_redirect";
    }
    return $url;
  }
  
  /**
   * Request access_token
   * @param string $code
   * @return mixed(string|array|boolean)
   */
  public function request_access_token($code) {
    $requrl = '';
    $params = array();
    if ('weixin' == $this->_platform) {
      $requrl = 'https://api.weixin.qq.com/sns/oauth2/access_token';
      $params['appid']     = $this->client_id;
      $params['secret']    = $this->secret_key;
      $params['code']      = $code;
      $params['grant_type']= 'authorization_code';
      $req = new ApiRequest(['method'=>'get','protocol'=>'https','timeout'=>60,'timeout_connect'=>30]);
      return $req->setUrl($requrl)->setParams($params)->send()->recv(TRUE);
    }
  
    return '';
  }
  
  /**
   * magic method '__get'
   *
   * @param string $name
   */
  public function __get($name) {
    return array_key_exists($name, $this->_config) ? $this->_config[$name] : NULL;
  }
  
  /**
   * magic method '__set'
   *
   * @param string $name
   * @param string $value
   */
  public function __set($name, $value) {
    $this->_config[$name] = $value;
  }
}
 
/*----- END FILE: class.OAuth2.php -----*/