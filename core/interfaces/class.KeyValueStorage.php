<?php
/**
 * Key-value 存储接口
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
interface KeyValueStorage {
	
	public function get($key);
	public function getMulti(Array $keys);
	
	public function set($key, $value);
	public function setMulti(Array $data);
	
	public function delete($key);
	public function deleteMulti(Array $keys);
	
}
 
/*----- END FILE: class.KeyValueStorage.php -----*/