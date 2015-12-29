<?php
/**
 * DB storage
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
class StorageDb extends Storage {
	
	protected $table;
	protected $columns;
	protected $key;
	
	public function __construct($config) {
		$this->table   = $config['table'];
		$this->key     = $config['key'];
		$this->columns = $config['columns'];
	}
	
	/**
	 * Load node from db
	 * 
	 * @param array $ids
	 * @return array
	 * @see Storage::load()
	 */
	public function load(Array $ids) {
		if (empty($ids)) return [];
		$res = D()->from($this->table)->where(D()->in($this->column($this->key), $ids))->select($this->getColumnAlias())->fetch_assoc_all();
		$ret = [];
		foreach ($res AS $row) {
			$ret[$row[$this->key]] = $row;
		}
		return $ret;
	}
	
	/**
	 * Find nodes from db
	 * 
	 * @param BaseQuery $query
	 * @param array $opts
	 * @return array
	 * @see Storage::find()
	 */
	public function find(BaseQuery $query, Array $opts = []) {
		list($where, $order, $limit) = $this->cause($query, $opts);
		$sql = "SELECT `{$this->columns[$this->key]}` AS `id` " .
		       "FROM {$this->table} {$where} {$order} {$limit}";
		$ids = D()->query($sql)->fetch_column('id');
		return $ids;
	}
	
	/**
	 * Find DISTINCT nodes from db
	 *
	 * @param string $field
	 * @param BaseQuery $query
	 * @param array $opts
	 * @return array
	 */
	public function findUnique($field, BaseQuery $query, Array $opts = []) {
		list($where, $order, $limit) = $this->cause($query, $opts);
		$field = $this->columns[$field];
		$sql = "SELECT DISTINCT `{$field}` AS `id` " .
		       "FROM {$this->table} {$where} {$order} {$limit}";
		$ids = D()->query($sql)->fetch_column('id');
		return $ids;
	}
	
	/**
	 * Insert or update data to db
	 * 
	 * @param array $data
	 * @return string
	 * @see Storage::save()
	 */
	public function save(Array $data) {
		$id     = 0;
		$params = [];
		$primary_key = $this->column($this->key);
		foreach ($data as $key => $val) {
			$k = $this->column($key);
			if ($k) { //防止不存在的字段名出现
				if ($k==$primary_key) {
					$id = $val;
				}
				$params[$k] = $val;
			}
		}
		
		if (empty($params)) return FALSE;
		if ($id && $this->id_exists($id)) { //更新
			unset($params[$primary_key]); //id字段不更新
			D()->update($this->table, $params, [$primary_key => $id]);
		}
		else { //插入
			if (D()->insert($this->table, $params, FALSE)) {
				$id = D()->insert_id(DB::WRITABLE);
				if (0===$id) { //表示不是AUTO_INCREMENT的主键
					$id = $params[$primary_key];
				}
			}
		}
		
		return strval($id);
	}
	
	/**
	 * Remove nodes from db
	 * 
	 * @param array $ids
	 * @return integer affected rows
	 * @see Storage::remove()
	 */
	public function remove(Array $ids) {
		if (!array_filter($ids)) return 0;
		D()->query("DELETE FROM ".$this->table." WHERE ".D()->in($this->column($this->key), $ids));
		return D()->affected_rows();
	}
	
	/**
	 * Chec whether id exists
	 *
	 * @param string $id
	 * @return bool
	 * @see Storage::remove()
	 */
	public function id_exists($id) {
		$idcol = $this->column($this->key);
		$idval = D()->query("SELECT {$idcol} FROM ".$this->table." WHERE {$idcol}='%s'", $id)->result();
		return $idval ? : FALSE;
	}
	
	/**
	 * Escape input string
	 * @param string $string
	 * @return string
	 */
	public static function escape($string) {
		return D()->escape_string($string);
	}
	
	private function cause(BaseQuery $query, Array $opts = []) {
		$where = "WHERE " . (new QueryBuilderMysql($query, $this->columns))->query();
	
		$order = '';
		if (isset($opts['sort']) && $opts['sort']) {
			$ords = [];
			foreach ($opts['sort'] as $field => $order) {
				$ords[] = "`{$this->columns[$field]}` {$order}";
			}
			$order = "ORDER BY " . join(', ', $ords);
		}
	
		if (!isset($opts['size'])) $opts['size'] = 10;
		if (!isset($opts['from'])) $opts['from'] = 0;
		$limit = "LIMIT {$opts['from']},{$opts['size']}";
	
		return [$where, $order, $limit];
	}
	
	private function column($key) {
		return is_array($this->columns) ? (isset($this->columns[$key]) ? $this->columns[$key] : null) : $key;
	}
	
	private function getColumnAlias() {
		if (is_array($this->columns)) {
			$string = '';
			foreach($this->columns as $alias => $col) {
				$string .= ",`{$col}` as `{$alias}`";
			}
			return ltrim($string, ',');
		}
		else {
			return $this->columns;
		}
	}
	
}
 
/*----- END FILE: class.StorageDb.php -----*/