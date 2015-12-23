<?php
/**
 * 可写存储接口
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
interface WritableStorage {
	
	public function save(Array $data);
	public function remove(Array $ids);
	
}
 
/*----- END FILE: class.WritableStorage.php -----*/