<?php
/**
 * Rest API 接口配置
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

return [
  'weixin_fxmgou' => [
    //production setting
  	//edmbuy
    'appId'          => 'wxa9e5e7de9a850cea',
    'appSecret'      => 'd4624c36b6795d1d99dcf0547af5443d',
    'token'          => '8P1QED9KRN3VOQPH',
    'encodingAesKey' => 'bbeJit1rwF6qbXnhAIxKeO2vsEMTSj6QNbJrtrpITQm',
    'paySignKey'     => '', //公众号支付请求中用于加密的密钥Key
  ]
];
/*----- END FILE: api.php -----*/