<?php
/**
 * 存储(数据库、memcache等)配置
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

return [
  'mysql-write' => [
    [
      'host'	  => '127.0.0.1',
      'port'		=> '3306',
      'user'		=> 'root',
      'pass'		=> 'root',
      'name'		=> 'edmbuy',
      'charset' => 'utf8',
      'pconnect'=> 0,
    ],
  ],
  'mysql-read' => [
    [
      'host'	  => '127.0.0.1',
      'port'		=> '3306',
      'user'		=> 'root',
      'pass'		=> 'root',
      'name'		=> 'edmbuy',
      'charset' => 'utf8',
      'pconnect'=> 0,
    ],
  ],
  'mysql-config' => [
    'driverType'   => 'mysqli', // DB Driver Type, maybe mysql, mysqli...
    'tablePrefix'  => 'tb_',    // Table prefix
    'connTimeout'  => 5,        // Connect timeout(seconds)
    'pingInterval' => 5,        // Ping interval(seconds)
  ],
  'memcache' => [
    'node' => [
      ['host' => '127.0.0.1', 'port' => '11211', 'prefix' => 'FXM_'],
    ],
  ],
  'session' => [
    'default' => [
      'sessname'  => '',		//new session name, if keep empty, default to 'PHPSESSID'
      'interval'	=> '300',
      'lifetime'	=> '1800',
      'handler'	  => 'db',	//option value: 'file', 'mm', 'db'
      'dbtable'	  => 'tb_session',	//when handler=='db', indicating the session table
    ],
    'adm' => [
      'sessname'  => 'PHPSESSID_ADM',		//new session name, if keep empty, default to 'PHPSESSID'
      'interval'	=> '300',
      'lifetime'	=> '1800',
      'handler'	  => 'db',	//option value: 'file', 'mm', 'db'
      'dbtable'	  => 'tb_session_adm',	//when handler=='db', indicating the session table
    ],
  ],
  'cookie' => [
    'default' => [
      'domain'	  => 'm.ydmbuy.com',
      'path'		  => '/',
      'lifetime'  => 0,
      'secure'    => 0,
      'httponly'  => 0,
      'prefix'    => 'FXM',
    ],
    'api' => [
      'domain'	  => 'edmapi.fxmapp.com',
      'path'		  => '/',
      'lifetime'  => 0,
      'secure'    => 0,
      'httponly'  => 0,
      'prefix'    => 'FXM',
    ],
    'adm' => [
      'domain'	  => 'adm.ydmbuy.com',
      'path'		  => '/',
      'lifetime'  => 0,
      'secure'    => 0,
      'httponly'  => 0,
      'prefix'    => 'ADM',
    ],
  ],
];
 
/*----- END FILE: storage.php -----*/