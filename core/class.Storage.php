<?php
/**
 * Storage base class
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
abstract class Storage implements ReadableStorage, WritableStorage {
	
	const SAVE_INSERT = 1;
	const SAVE_UPDATE = 2;
	const SAVE_INSERT_IGNORE = 3;
	const SAVE_UPDATE_IGNORE = 4;
	
	abstract public function load(Array $ids);
	
	abstract public function find(BaseQuery $query, Array $opts = []);
	
	abstract public function save(Array $data, &$flag = NULL);
	
	abstract public function remove(Array $ids);
	
	abstract public function id_exists($id);

	abstract public function totalCount(BaseQuery $query, $no_primary_key = FALSE);
	
	public static function escape($string) {
		return $string;
	}
}
 
/*----- END FILE: class.Storage.php -----*/