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

$_ip = $request->ip();
if ($_ip!='113.97.237.86') {
	//$response->send('<center style="margin-top:50px;font-size: 40px;">系统升级维护中，请过一段时间再访问。</center>');
}

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