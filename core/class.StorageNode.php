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
		if (isset($id)) {
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
		
		$clsname = get_called_class();
		if (empty($id)) return is_array($id) ? [] : new $clsname(); //确保单个id查询时返回一个StorageNode对象
		$ids = is_array($id) ? array_filter($id) : [$id];
		$ids = array_combine($ids, $ids);
		
		if ($refresh) {
			self::removeStaticCache($ids);
		}
		
		$cached_data  = self::getStaticCache($ids);
		$storage_data = self::storage()->load(array_diff_key($ids, $cached_data));
		$caching_data = $return = [];
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
		
		return is_array($id) ? $return : (!empty($return) ? current($return) :  new $clsname()); //确保单个id查询时返回一个StorageNode对象
	}
	
	/**
	 * Find nodes
	 * @param BaseQuery $query
	 * @param array $option contains "from"(string),"size"(string),"sort"(array)
	 * @return array StorageNode list
	 */
	final public static function find(BaseQuery $query = null, $option = []) {
		return self::load(self::find_ids($query, $option));
	}
	
	/**
	 * Find one node
	 * @param BaseQuery $query
	 * @param array $option contains "from"(string),"size"(string),"sort"(array)
	 * @return StorageNode
	 */
	final public static function find_one(BaseQuery $query = null, $option = []) {
		$ids = self::find_ids($query, $option);
		return self::load(isset($ids[0]) ? $ids[0] : 0);
	}
	
	/**
	 * Find nodes ids
	 * @param BaseQuery $query
	 * @param array $option contains "from"(string),"size"(string),"sort"(array)
	 * @return array id list
	 */
	final public static function find_ids(BaseQuery $query = null, $option = []) {
		return self::storage()->find($query ? : new TrueQuery(), $option);
	}
	
	/**
	 * Find DISTINCT a field as a unique column 
	 * @param string $field
	 * @param BaseQuery $query
	 * @param array $option contains "from"(string),"size"(string),"sort"(array)
	 * @return array id list
	 */
	final public static function find_unique_ids($field, BaseQuery $query = null, $option = []) {
		return self::storage()->findUnique($field, $query ? : new TrueQuery(), $option);
	}
	
	/**
	 * Save(insert or update) a node
	 * @param $flag
	 */
	final public function save($flag = NULL) {
		$this->save_before();
		$id  = self::storage()->save($this->__DATA__, $flag);
		$key = $this->meta()['key'];
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
		$key = $this->meta()['key'];
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
	 * Return the column data
	 * @return array
	 */
	public function column_data() {
		return $this->__DATA__;
	}
	
	/**
	 * Check node object whether exist
	 * @return boolean
	 */
	public function is_exist() {
		return $this->id ? TRUE : FALSE;
	}
	
	/**
	 * Check whether id exist
	 * @param int|string $id value of id filed
	 */
	public static function id_exists($id) {
		$cache_node = self::getStaticCache($id);
		if (!$cache_node) {
			return self::storage()->id_exists($id);
		}
		return true;
	}
	
	/**
	 * Return meta 'table' value
	 * @return string
	 */
	public static function table() {
		$clsname = get_called_class();
		$meta = $clsname::meta();
		return $meta['table'];
	}
	
	/**
	 * Return meta 'key' value
	 * @return string
	 */
	public static function key() {
		$clsname = get_called_class();
		$meta = $clsname::meta();
		return $meta['key'];
	}
	
	/**
	 * Return meta id column name('columns[meta[key]]')
	 * @return string
	 */
	public static function id_column() {
		$clsname = get_called_class();
		$meta = $clsname::meta();
		return self::column($meta['key']);
	}

	/**
	 * Return meta 'columns[$key]' value
	 * 
	 * @param $key when $key is NULL, then return the whole $meta['columns']
	 * @return string
	 */
	public static function column($key = NULL) {
		$clsname = get_called_class();
		$meta = $clsname::meta();
		return !isset($key)
		       ? $meta['columns']
		       : (is_array($meta['columns']) ? (isset($meta['columns'][$key]) ? $meta['columns'][$key] : null) : $key);
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
			self::$_storage[$clsname] = new StorageDb($clsname::meta());
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
			$meta = $this->meta();
			if ('id'==$name) { // Hack 'id' field
				return isset($this->__DATA__[$meta['key']]) ? $this->__DATA__[$meta['key']] : null;
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
			$key = $this->meta()['key'];
			$this->__DATA__[$key] = $value;
		}
		else {
			$this->__DATA__[$name] = $value;
		}
	}
	
}
 
/*----- END FILE: class.StorageNode.php -----*/