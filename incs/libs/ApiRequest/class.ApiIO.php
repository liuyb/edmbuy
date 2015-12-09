<?php
/**
 * ApiIO 接口类
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
abstract class ApiIO {	
	/**
	 * 执行一个ApiRequest请求
	 * @param  ApiRequest $request
	 * @return ApiRequest $request
	 */
	abstract public function makeRequest(ApiRequest $request);
}

/*--- END FILE: class.ApiIO.php ---*/