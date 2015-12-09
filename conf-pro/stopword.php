<?php
/**
 * 敏感语配置文件
 *
 * 
 */
defined('IN_SIMPHP') or die('Access Denied');

return [
	'uname' =>
	[
		  '/admin(istrator)?/i',
		  '/gm(fxmgou)?/i',
		  '/gm(fxm)?/i',
	],
	'content' => 
	[
	  '',
	],
];
