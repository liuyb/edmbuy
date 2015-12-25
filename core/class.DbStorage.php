<?php
/**
 * DB storage
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
class DbStorage extends Storage {
	
	protected $table;
	protected $columns;
	protected $key;
	
	public function __construct($config) {
		$this->table   = $config['table'];
		$this->key     = $config['key'];
		$this->columns = $config['columns'];
	}
	
	public function load(Array $ids) {
		if (empty($ids)) return [];
		$res = D()->from($this->table)->where(D()->in($this->column($this->key), $ids))->select($this->getColumnAlias())->fetch_assoc_all();
		$ret = [];
		foreach ($res AS $row) {
			$ret[$row[$this->key]] = $row;
		}
		return $ret;
	}
	
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
		if ($id && $this->idExist($id)) { //更新
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
	
	public function remove(Array $ids) {
		if (!array_filter($ids)) return 0;
		D()->query("DELETE FROM ".$this->table." WHERE ".D()->in($this->column($this->key), $ids));
		return D()->affected_rows();
	}
	
	public function idExist($id) {
		$idcol = $this->column($this->key);
		$idval = D()->query("SELECT {$idcol} FROM ".$this->table." WHERE {$idcol}='%s'", $id)->result();
		return $idval ? : FALSE;
	}
	
	private function column($key) {
		return is_array($this->columns) ? $this->columns[$key] : $key;
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
 
/*----- END FILE: class.DbStorage.php -----*/