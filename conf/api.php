<?php
/**
 * Rest API 接口配置
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

return [
  'weixin_edmbuy' => [
  	/*
  	//测试公众号
    'appId'          => 'wxc58e3d16d980d296',
    'appSecret'      => '7a157d84237cacc90f6a6202d5966ee5',
    'token'          => 'JQ4W443W6D5EFBAT',
    'encodingAesKey' => 'bbeJit1rwF6qbXnhAIxKeO2vsEMTSj6QNbJrtrpITQm',
    'paySignKey'     => '',
  	*/
  	//"多米测"公众号
  	'appId'          => 'wxbb57b9480c1bb208',
  	'appSecret'      => '93b1b2d4883a7332184d524c660f543c',
  	'token'          => 'JQ4W443W6D5EFBAT',
  	'encodingAesKey' => 'bbeJit1rwF6qbXnhAIxKeO2vsEMTSj6QNbJrtrpITQm',
  	'paySignKey'     => '',
  ],
  'meiqia_edmbuy' => [
      // 测试
      /* 'appkey'       => '32e722002626500c0e830e97f67a62e8',
      'secretKey'    => '10e4a34368837e3437835c78e0a53612',
      'createEntUrl' => 'http://eco-api-test03.meiqia.com/platforms/enterprise/new',
      'entSignUrl'   => 'https://app.meiqia.com/open-signin' */
      
      'appkey'       => '34d77bd8a0e085e12791f59cf1ef7136',
      'secretKey'    => '253d402efa6072b779c4dd037f6a5394',
      'createEntUrl' => 'https://eco-api.meiqia.com/platforms/enterprise/new',
      'entSignUrl'   => 'https://app.meiqia.com/open-signin',
      'edm_ent_id'   => '4119'
  ] 
];
 
/*----- END FILE: api.php -----*/