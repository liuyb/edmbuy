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

//记录日志
$apilog = new ApiLog();
$apilog->reqTime   = simphp_msec();
$request->__apilog = $apilog;
$ret = ['code' => -1, 'msg' => Api::code(-1), 'res' => []];

try {
  SimPHP::I(['modroot'=>'apis'])
	  ->boot(RC_DATABASE)
	  ->dispatch($request,$response);
}
catch (ApiException $eapi) {
	$ret['code'] = $eapi->getCode();
	$ret['code'] = intval($ret['code']);
	$ret['msg']  = $eapi->getMessage();
	$ret['res']  = $eapi->getResponse();
}
catch (Exception $e) {
	$ret['code'] = 1503;
	$ret['msg']  = Api::code($ret['code']);
}

//保存日志
$apilog->dealTime = simphp_msec() - $apilog->reqTime;
$apilog->resp     = json_encode($ret);
$apilog->save();
$response->sendJSON($ret);
 
/*----- END FILE: api.php -----*/