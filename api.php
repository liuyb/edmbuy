<?php
/**
 * 通用API入口文件
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
if (!isset($_GET['q']) || empty($_GET['q'])) {
  $_GET['q'] = 'weixin/fxmgou';
}

//~ require init.php
require (__DIR__.'/core/init.php');

$request  = new Request();
$response = new Response();

try {
  SimPHP::I(['modroot'=>'apis'])
  ->boot(RC_DATABASE)
  ->dispatch($request,$response);
}
catch (SimPHPException $me) {
  $response->dump($me->getMessage());
}
catch (Exception $e) {
  $response->dump($e->getMessage());
}
 
/*----- END FILE: api.php -----*/