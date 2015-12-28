<?php
/**
 * Storage base class
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
abstract class Storage implements ReadableStorage, WritableStorage {
	
	abstract public function load(Array $ids);
	
	abstract public function find(BaseQuery $query, Array $opts = []);
	
	abstract public function save(Array $data);
	
	abstract public function remove(Array $ids);
	
	abstract public function id_exists($id);
	
	public static function escape($string) {
		return $string;
	}
}
 
/*----- END FILE: class.Storage.php -----*/