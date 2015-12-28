<?php
/**
 * 可读存储接口
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
interface ReadableStorage {
	
	public function load(Array $ids);
	public function find(BaseQuery $query, Array $opts = []);
	
}
 
/*----- END FILE: class.ReadableStorage.php -----*/