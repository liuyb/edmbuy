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
	 * @param string $flag
	 * @return array
	 * @see Storage::load()
	 */
	public function load(Array $ids, $flag = NULL) {
		if (empty($ids)) return [];
		$res = D()->from($this->table)->where(D()->in($this->column($this->key), $ids))->for_update($flag===Storage::SELECT_FOR_UPDATE)->select($this->getColumnAlias())->fetch_assoc_all();
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
		$field = $this->column($this->key);
		$forup = '';
		if (isset($opts[Storage::SELECT_FOR_UPDATE]) && $opts[Storage::SELECT_FOR_UPDATE]) {
			$forup = ' FOR UPDATE';
		}
		elseif (isset($opts[Storage::SELECT_LOCK_IN_SHARE]) && $opts[Storage::SELECT_LOCK_IN_SHARE]) {
			$forup = ' LOCK IN SHARE MODE';
		}
		$sql = "SELECT `{$field}` AS `id` " .
		       "FROM {$this->table} {$where} {$order} {$limit}{$forup}";
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
		$field = $this->column($field);
		$forup = '';
		if (isset($opts[Storage::SELECT_FOR_UPDATE]) && $opts[Storage::SELECT_FOR_UPDATE]) {
			$forup = ' FOR UPDATE';
		}
		elseif (isset($opts[Storage::SELECT_LOCK_IN_SHARE]) && $opts[Storage::SELECT_LOCK_IN_SHARE]) {
			$forup = ' LOCK IN SHARE MODE';
		}
		$sql = "SELECT DISTINCT `{$field}` AS `id` " .
		       "FROM {$this->table} {$where} {$order} {$limit}{$forup}";
		$ids = D()->query($sql)->fetch_column('id');
		return $ids;
	}
	
	/**
	 * Insert or update data to db
	 * 
	 * @param array $data
	 * @param integer $flag
	 * @return string
	 * @see Storage::save()
	 */
	public function save(Array $data, &$flag = NULL) {
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
		
		$is_insert = FALSE;
		$is_ignore = FALSE;
		if (isset($flag)) {
			$is_insert = $flag===self::SAVE_INSERT || $flag===self::SAVE_INSERT_IGNORE ? TRUE : FALSE;
			$is_ignore = $flag===self::SAVE_INSERT_IGNORE || $flag===self::SAVE_UPDATE_IGNORE ? TRUE : FALSE; 
		}
		else {
			if ($id && $this->id_exists($id)) {
				$is_insert = FALSE;
			}
			else {
				$is_insert = TRUE;
			}
		}
		
		if ($is_insert) { //插入
			$flag = $is_ignore ? self::SAVE_INSERT_IGNORE : self::SAVE_INSERT; //返回最终保存动作
			if (D()->insert($this->table, $params, FALSE, ($is_ignore ? 'IGNORE' : ''))) {
				$id = D()->insert_id(DB::WRITABLE);
				if (0===$id) { //表示不是AUTO_INCREMENT的主键
					$id = $params[$primary_key];
				}
			}
		}
		else { //更新
			$flag = $is_ignore ? self::SAVE_UPDATE_IGNORE : self::SAVE_UPDATE; //返回最终保存动作
			unset($params[$primary_key]); //id字段不更新
			if (empty($params)) return FALSE; //如果要更新的数据为空，返回false
			D()->update($this->table, $params, [$primary_key => $id], ($is_ignore ? 'IGNORE' : ''));
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
	 * @param string  $id
	 * @param boolean $for_update, default to false
	 * @return bool
	 * @see Storage::remove()
	 */
	public function id_exists($id, $for_update = FALSE) {
		$idcol = $this->column($this->key);
		$forup = $for_update ? ' FOR UPDATE' : '';
		$idval = D()->query("SELECT {$idcol} FROM ".$this->table." WHERE {$idcol}='%s'{$forup}", $id)->result();
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
	
	/**
	 * Query total record count
	 * @param BaseQuery $query
	 * @param boolean   $no_primary_key
	 * @see Storage::totalCount()
	 * @return integer
	 */
	public function totalCount(BaseQuery $query, $no_primary_key = FALSE) {
		$where = "WHERE " . (new QueryBuilderMysql($query, $this->columns))->query();
		$sql = 'SELECT COUNT('.(!$no_primary_key?'1':'*').') AS `cnt` FROM `' . $this->table . '` ' . $where;
		return intval(D()->query($sql)->result());
	}
	
	private function cause(BaseQuery $query, Array $opts = []) {
		$where = "WHERE " . (new QueryBuilderMysql($query, $this->columns))->query();
	
		$order = '';
		if (isset($opts['sort']) && $opts['sort']) {
			$ords = [];
			foreach ($opts['sort'] as $field => $order) {
				$field = $this->column($field);
				$ords[] = "`{$field}` {$order}";
			}
			$order = "ORDER BY " . join(', ', $ords);
		}
	
		if (!isset($opts['size'])) $opts['size'] = 10;
		if (!isset($opts['from'])) $opts['from'] = 0;
		$limit = "LIMIT {$opts['from']},{$opts['size']}";
		if ($opts['size'] < 0) {
			$limit = "";
		}
	
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