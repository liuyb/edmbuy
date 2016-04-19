<?php
/**
 * 应用环境配置
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

return [
  //[General]
  'env'      => 'pro',
  'version'  => '1.0.0',
  'timezone' => 'Asia/Shanghai',
  'charset'  => 'utf-8',
  'lang'     => 'zh-cn',
  'sitetheme'=> 'default',
  'contextpath' => '/',
  'cleanurl' => 1,
  'usecdn'   => 1,
  'allowed_moddirs' => ['modules','mobiles','admins','apis'],
  //[Log]
  'log_dir' => '/var/log',
  //[TemplateSetting]
  'tplclass' => 'PlainTpl',
  'tplpostfix' => '.htm.php',
  'tplcachedir' => '/var/run/tpl',
  'tplcache' => 0,
  'tplcache_expires' => 900,
  'tplcompile_check' => 1,
  'tplforce_compile' => 1,
  'tpldebug' => 1,
	//当前服务器ID(用于多web服务器部署时的资源访问)
  'server_id'=> 1,
	'resource_servers'  => [
		1 => 'http://m.ydmbuy.com',	
	],
  //站点信息
  'site' => [
  	'mobile' => 'http://m.ydmbuy.com',
  	'shop' => 'http://m.ydmbuy.com',
    'merchant' => 'http://mch.ydmbuy.com'
  ],
  //阿里云上的图片样式名称 名称为 prefix+'_std|thumb'.jpg
  'picstyle' => [
      'goods' => array('std' => 'gpic_std', 'thumb' => 'gpic_thumb'),//640*640
      'category' => array('std' => 'category_std', 'thumb' => 'category_std'), //240*240
      'shoplogo' => array('std' => 'shoplogo_std', 'thumb' => 'shoplogo_std'), //320*320
      'carousel' => array('std' => 'carousel_std', 'thumb' => 'carousel_std') //640*250
  ],
  //debug白名单uid
  //'debug_white_list' => ['10001','109','104'],
  'debug_white_list' => [],
  //上传文件保存目录 
  'picsavedir'=>'/a/',
  //平台币兑人民币比率,1元=10平台币
  'rmb_platform'=>10,
  //是否与UC同步
  'uc_sync'=>1,
  //数据加密key
  'au_key'=>'WIEJEKI1349*&^%234KKiekeisiehSKGJ',
];

/*----- END FILE: env.php -----*/