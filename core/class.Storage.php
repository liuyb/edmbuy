<?php
/**
 * Storage base class
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
abstract class Storage implements ReadableStorage, WritableStorage {
	
	abstract public function load(Array $ids);
	
	abstract public function save(Array $data);
	
	abstract public function remove(Array $ids);
}
 
/*----- END FILE: class.Storage.php -----*/