<?php
/**
 * Mysql Query Builder
 */
class QueryBuilderMysql extends QueryBuilder {
	protected $or_mapping;
	public function __construct($query, $or_mapping) {
		parent::__construct($query);
		$this->or_mapping = $or_mapping;
	}

	protected function buildQuery($query) {
		$q = '';
		switch (get_class($query)) {
			case 'AndQuery':
				$q = '(' . join(' AND ' , array_reverse(array_map([$this, 'buildQuery'], $query->children))) . ')';
				break;
			case 'OrQuery':
				$q = '(' . join(' OR ' , array_reverse(array_map([$this, 'buildQuery'], $query->children))) . ')';
				break;
			case 'NotQuery':
				$q = '(NOT ' . $this->buildQuery($query->query) . ')';
				break;
			case 'InQuery':
				$values = array_values(object_map($query->children, 'value'));
				$values = array_map([$this, 'mysql_value'], $values);
				$q = $this->mysql_field($query->field) . ' IN (\'' . join('\',\'', $values) . '\')';
				break;
			case 'TrueQuery':
				$q = "1=1";
				break;
			case 'FalseQuery':
				$q = "1=0";
				break;
			case 'RangeQuery':
				$lower = $this->mysql_value($query->lower);
				$upper = $this->mysql_value($query->upper);
				$lower = is_null($lower) ? null : (is_numeric($lower) ? $lower : "'{$lower}'");
				$upper = is_null($upper) ? null : (is_numeric($upper) ? $upper : "'{$upper}'");
				if (is_null($lower)) {
					$q = $this->mysql_field($query->field) . ' <= ' . $upper;
				} elseif (is_null($upper)) {
					$q = $this->mysql_field($query->field) . ' >= ' . $lower;
				} else {
					$q = '(' . $this->mysql_field($query->field) . ' BETWEEN ' . $lower . ' AND ' . $upper . ')';
				}
				break;
			case 'ExistQuery':
				$q = $this->mysql_field($query->field) . ' != \'\'';// cause we do not have NULL field when use ORM.
				break;
			case 'RawQuery':
				$q = $this->buildQuery($query->query);
				break;
			case 'RawSqlQuery':
				$q = $this->mysql_sql($query->sql, $query->args);
				break;
			case 'Query':
				$q = $this->mysql_field($query->field) . ' '.$query->operator.' \'' . $this->mysql_value($query->value) . '\'';
				break;
		}
		return $q;
	}

	private function mysql_value($value) {
		if (is_null($value)) return null;
		return StorageDb::escape($value);
	}

	private function mysql_field($object_field) {
		if (is_array($this->or_mapping) && !isset($this->or_mapping[$object_field])) {
			throw new Exception("field `{$object_field}` is not a valid mysql column");
		}
		return is_array($this->or_mapping) ? "`{$this->or_mapping[$object_field]}`" : "`{$object_field}`";
	}
	
	private function mysql_sql($sql, $args) {
		$db = D();
		$server_mode = $db->check_server_mode($sql);
		$db->query_callback($args, TRUE, $server_mode);
		$sql = preg_replace_callback(DB::DB_QUERY_REGEXP, array($db,'query_callback'), $sql);
		return $sql;
	}
}
 
/*----- END FILE: class.QueryBuilderMysql.php -----*/