<?php
/**
 * 通用API入口文件
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
if (!isset($_GET['q']) || empty($_GET['q'])) {
  $_GET['q'] = 'weixin/edmbuy';
}

//~ require init.php
require (__DIR__.'/core/init.php');

ignore_user_abort(TRUE);
set_time_limit(60);

$request  = new Request();
$response = new Response();

try {
  SimPHP::I(['modroot'=>'apis'])
	  ->boot(RC_DATABASE)
	  ->dispatch($request,$response);
}
catch (ApiException $eapi) {
  $response->sendAPI($eapi->getResponse(), $eapi->getCode(), $eapi->getMessage());
}
catch (Exception $e) {
  $response->dump($e->getMessage());
}
 
/*----- END FILE: api.php -----*/