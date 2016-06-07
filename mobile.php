<?php
/**
 * Mobile端请求入口
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
//error_reporting(E_ALL);
//ini_set('display_errors', 'on');

//~ require init.php
require (__DIR__.'/core/init.php');

$request  = new Request();
$response = new Response();

//if ($request->ip()!='14.153.245.212') {
//	Fn::show_error_message('系统升级维护中，请稍后再访问...');
//}

try {
  SimPHP::I(['modroot'=>'mobiles'])
  ->boot(RC_ALL ^ RC_MEMCACHE)
  ->dispatch($request,$response);
}
catch (ViewException $ve) {
	$ve->getView()->assign('errmsg', $ve->getMessage());
	$response->send($ve->getView());
}
catch (SimPHPException $me) {
  $response->dump($me->getMessage());
}
catch (Exception $e) {
  $response->dump($e->getMessage());
}

 
/*----- END FILE: mobile.php -----*/