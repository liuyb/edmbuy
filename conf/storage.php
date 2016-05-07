<?php
/**
 * 存储(数据库、memcache等)配置
 *
 * @author root<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

return [
  'mysql-write' => [
    [
      'host'	  => '127.0.0.1',
      'port'		=> '3306',
      'user'		=> 'gavin',
      'pass'		=> 'gavin@asdf',
      'name'		=> 'edmbuy',
      'charset' => 'utf8',
      'pconnect'=> 0,
    ],
  ],
  'mysql-read' => [
    [
      'host'	  => '127.0.0.1',
      'port'		=> '3306',
      'user'		=> 'gavin',
      'pass'		=> 'gavin@asdf',
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
      ['host' => '127.0.0.1', 'port' => '11211', 'prefix' => 'EDM_'],
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
    'mch' => [
      'sessname'  => 'PHPSESSID_MCH',		//new session name, if keep empty, default to 'PHPSESSID'
      'interval'	=> '300',
      'lifetime'	=> '1800',
      'handler'	  => 'db',	//option value: 'file', 'mm', 'db'
      'dbtable'	  => 'tb_session_mch',	//when handler=='db', indicating the session table
    ],
  ],
  'cookie' => [
    'default' => [
      'domain'	  => 'm.ydmbuy.com',
      'path'		  => '/',
      'lifetime'  => 0,
      'secure'    => 0,
      'httponly'  => 0,
      'prefix'    => 'EDM',
    ],
    'api' => [
      'domain'	  => 'edmapi.fxmapp.com',
      'path'		  => '/',
      'lifetime'  => 0,
      'secure'    => 0,
      'httponly'  => 0,
      'prefix'    => 'EDM',
    ],
    'adm' => [
      'domain'	  => 'edmadm.fxmapp.com',
      'path'		  => '/',
      'lifetime'  => 0,
      'secure'    => 0,
      'httponly'  => 0,
      'prefix'    => 'ADM',
    ],
    'mch' => [
      'domain'	  => 'edmmch.fxmapp.com',
      'path'		  => '/',
      'lifetime'  => 0,
      'secure'    => 0,
      'httponly'  => 0,
      'prefix'    => 'MCH',
    ],
  ],
];
 
/*----- END FILE: storage.php -----*/