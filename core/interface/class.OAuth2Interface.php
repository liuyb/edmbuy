<?php
/**
 * OAuth2 接口类
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
interface OAuth2Interface {
  
  /**
   * Get authorize url
   * @return string
   */
  public function authorize_url();
  
  /**
   * Request access token
   * @param string $code
   * @param mixed(array or json object)
   */
  public function request_access_token($code);
  
}
 
/*----- END FILE: class.OAuth2Interface.php -----*/