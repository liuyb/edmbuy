<?php
/**
 * 商家管理后台入口
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
//error_reporting(E_ALL);
//ini_set('display_errors', 'on');

//~ require init.php
require (__DIR__.'/core/init.php');

//~ use Smarty template engine
Config::set('env.tplclass', 'Smarty');
Config::set('env.tplpostfix', '.htm');

$request  = new Request();
$response = new Response();

try {
	SimPHP::I(['modroot'=>'merchants', 'sessnode'=>'mch'])
	->boot(RC_ALL ^ RC_MEMCACHE)
	->dispatch($request,$response);
}
catch (SimPHPException $me) {
	$response->dump($me->getMessage());
}
catch (Exception $e) {
	$response->dump($e->getMessage());
}
 
/*----- END FILE: merchant.php -----*/