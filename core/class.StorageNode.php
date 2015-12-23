<?php
/**
 * Storable Node
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

abstract class StorageNode extends Model {
	
	/**
	 * For PHP static cache
	 * @var array
	 */
	private static $_cache = array();
	
	/**
	 * A storage object
	 * @var Storage
	 */
	private static $_storage;
	
	/**
	 * meta method
	 * define db table meta info
	 */
	protected static function meta() {
		throw new Exception('Method \'meta\' require implemented');
	}
	
	//~ hooks
	protected function load_after()  {}
	protected function save_before() {}
	
	/**
	 * Constructor
	 * @param int|string $id
	 */
	public function __construct($id = NULL) {
		if (!is_null($id)) {
			$this->id = $id;
		}
	}
	
	/**
	 * Load one or more StorageNode
	 * 
	 * @param int|string|array $id
	 * @param string $refresh
	 * @return StorageNode
	 *   or a StorageNode array
	 */
	final public static function load($id, $refresh = FALSE) {
		
		if (empty($id)) return [];
		$ids = is_array($id) ? array_filter($id) : [$id];
		$ids = array_combine($ids, $ids);
		
		if ($refresh) {
			self::removeStaticCache($ids);
		}
		
		$cached_data  = self::getStaticCache($ids);
		$storage_data = self::storage()->load(array_diff_key($ids, $cached_data));
		$caching_data = $return = [];
		$clsname = get_called_class();
		foreach ($ids as $_id) {
			if (isset($cached_data[$_id])) {
				$return[$_id] = $cached_data[$_id];
				continue;
			}
		
			if (!isset($storage_data[$_id])) continue;
			
			$node = new $clsname();
			foreach ($storage_data[$_id] as $key => $value) {
				$node->$key = $value;
			}
			$node->load_after();
			$caching_data[$_id] = $return[$_id] = $node;
		}
		self::setStaticCache($caching_data);
		
		return is_array($id) ? $return : current($return);
	}
	
	final public function save() {
		$this->save_before();
		$clsname = get_class($this);
		$id  = self::storage()->save($this->__DATA__);
		$key = $clsname::meta()['key'];
		if ($id) {
			$this->$key = $id;
			self::removeStaticCache($id);//for safe
		}
		$this->load_after();
	}
	
	/**
	 * Node object self remove
	 * @return number
	 */
	final public function remove() {
		$clsname = get_class($this);
		$key = $clsname::meta()['key'];
		return self::remove_by_ids([$this->$key]);
	}
	
	/**
	 * Remove nodes by ids
	 * @param array $ids
	 * @return number
	 */
	public static function remove_by_ids(Array $ids) {
		$ids = array_filter($ids);
		self::removeStaticCache($ids);
		self::storage()->remove($ids);
		return count($ids);
	}
	
	/**
	 * Check node object whether exist
	 * @return boolean
	 */
	public function is_exist() {
		$clsname = get_class($this);
		$key = $clsname::meta()['key'];
		return self::getStaticCache($this->$key) ? TRUE : FALSE;
	}
	
	/**
	 * Check whether id exist
	 * @param int|string $id
	 */
	public static function id_exist($id) {
		return self::storage()->idExist($id);
	}
	
	/**
	 * Return storage column name
	 * @return string
	 */
	public static function column($key) {
		$clsname = get_called_class();
		$meta = $clsname::meta();
		return is_array($meta['columns']) ? $meta['columns'][$key] : $key;
	}
	
	/**
	 * Return storage primary key name
	 * @return string
	 */
	public static function storage_key() {
		$clsname = get_called_class();
		$meta = $clsname::meta();
		return is_array($meta['columns']) ? $meta['columns'][$meta['key']] : $meta['key'];
	}
	
	/**
	 * Get one or multi cache node
	 * @param int|string|array $ids
	 * @return array|StorageNode
	 */
	private static function getStaticCache($ids) {
		$class = get_called_class();
		if (!isset(self::$_cache[$class])) self::$_cache[$class] = array();
		if (is_array($ids)) {
			return array_intersect_key(self::$_cache[$class], $ids);
		}
		else {
			return isset(self::$_cache[$class][$ids]) ? self::$_cache[$class][$ids] : null;
		}
	}
	
	/**
	 * Set a or more StorageNode to PHP static cache
	 * @param int|string|array $id
	 * @param StorageNode $node
	 */
	private static function setStaticCache($id, StorageNode $node = NULL) {
		$class = get_called_class();
		if (!isset(self::$_cache[$class])) self::$_cache[$class] = array();
		if (is_array($id)) {
			self::$_cache[$class] = $id + self::$_cache[$class];
		}
		else {
			self::$_cache[$class][$id] = $node;
		}
	}
	
	/**
	 * Remove PHP static cache
	 * @param int|string|array $ids
	 */
	private static function removeStaticCache($ids) {
		if (!is_array($ids)) $ids = [$ids];
		$class = get_called_class();
		foreach ($ids as $id) {
			unset(self::$_cache[$class][$id]);
		}
	}
	
	/**
	 * @return Storage
	 */
	private static function storage() {
		$clsname = get_called_class();
		if (!isset(self::$_storage[$clsname])) {
			self::$_storage[$clsname] = new DbStorage($clsname::meta());
		}
		return self::$_storage[$clsname];
	}
	
	/**
	 * magic method '__get'
	 *
	 * @param string $name
	 */
	public function __get($name) {
		if (array_key_exists($name, $this->__DATA__)) return $this->__DATA__[$name];
		else { //默认加一个'id'属性(只用于获取显示，不保存)
			if ('id'==$name) {
				$clsname = get_class($this);
				$key = $clsname::meta()['key'];
				return isset($this->__DATA__[$key]) ? $this->__DATA__[$key] : null;
			}
		}
		return null;
	}
	
	/**
	 * magic method '__set'
	 *
	 * @param string $name
	 * @param string $value
	 */
	public function __set($name, $value) {
		if ('id'==$name) { //对'id'列特殊处理
			$clsname = get_class($this);
			$key     = $clsname::meta()['key'];
			$this->__DATA__[$key] = $value;
		}
		else {
			$this->__DATA__[$name] = $value;
		}
	}
	
}
 
/*----- END FILE: class.StorageNode.php -----*/