<?php
/**
 * ApiRequest接口类
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
interface ApiRequestInterface {
	
	/**
	 * 生成请求签名
	 * @return calling object(this)
	 */
	public function sign();

	/**
	 * 发送请求
	 * @return calling object(this)
	 */
	public function send();
	
	/**
	 * 接收响应返回
	 * @return Array
	 */
	public function recv();
	
}


/*--- END FILE: class.ApiRequestInterface.php ---*/