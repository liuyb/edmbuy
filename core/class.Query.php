<?php
/**
 * Storage Query Classes
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
abstract class BaseQuery {
	public function __get($name) {
		return isset($this->$name) ? $this->$name : null;
	}
	protected function check($input) {
		$input = trim($input);
		if (0 == strlen($input)) {
			throw new Exception("No value given");
		}
		return $input;
	}
	abstract public function accept($obj);
}

class Query extends BaseQuery {
	
	protected static $allowed_compare_operator = ['=','>','<','>=','<=','!=','<>','<=>'];
	
	protected $field, $value, $operator;

	public function __construct($field, $value, $operator = '=') {
		$this->field = $this->check($field);
		$this->value = $this->check($value);
		$operator    = $this->check($operator);
		if (!in_array($operator, self::$allowed_compare_operator)) {
			throw new Exception("Operator not allowed, should be one of '".join('\',\'', self::$allowed_compare_operator)."'");
		}
		$this->operator = $operator;
	}

	public function accept($obj) {
		$field = $this->field;
		if (is_null($obj->{$field})) {
			return false;
		}
		else {
			switch ($this->operator) {
				case '=':
					return $obj->{$field} == $this->value;
					break;
				case '<=>':
					return $obj->{$field} === $this->value;
					break;
				case '>':
					return $obj->{$field} > $this->value;
					break;
				case '<':
					return $obj->{$field} < $this->value;
					break;
				case '>=':
					return $obj->{$field} >= $this->value;
					break;
				case '<=':
					return $obj->{$field} <= $this->value;
					break;
				case '!=':
				case '<>':
					return $obj->{$field} != $this->value;
					break;
			}
			return false;
		}
	}
}

class TrueQuery extends BaseQuery {
	public function accept($obj) {
		return true;
	}
}

class FalseQuery extends BaseQuery {
	public function accept($obj) {
		return true;
	}
}

class ExistQuery extends BaseQuery {
	protected $field;

	public function __construct($field) {
		$this->field = $this->check($field);
	}

	public function accept($obj) {
		return !is_null($obj->{$this->field});
	}
}

class RangeQuery extends BaseQuery {
	protected $field, $lower, $upper;

	public function __construct($field, $lower = null, $upper = null) {
		$this->field = $this->check($field);
		$this->lower = $lower;
		$this->upper = $upper;
	}

	public function accept($obj) {
		return ( !is_null($obj->{$this->field})
		      && (is_null($this->upper) || $obj->{$this->field} <= $this->upper)
		      && (is_null($this->lower) || $obj->{$this->field} >= $this->lower)
		);
	}
}

class NotQuery extends BaseQuery {
	protected $query;

	public function __construct(BaseQuery $query) {
		$this->query = $query;
	}

	public function accept($obj) {
		return !$this->query->accept($obj);
	}
}

/**
 * 特殊类型的Query，基于字符串分析来拼装Query
 *
 * @todo 非法字符串的校验 + testcase by Gavin.
 */
class RawQuery extends BaseQuery {
	protected $query;

	public function __construct($queryString) {
		$this->query = QueryParser::parse($this->check($queryString));
	}

	public function accept($obj) {
		return $this->query->accept($obj);
	}
}

/**
 * Raw SQL Query
 */
class RawSqlQuery extends BaseQuery {
	protected $sql, $args;
	
	public function __construct($sql) {
		$this->sql = $sql;
		$args = func_get_args();
		array_shift($args);
		$this->args = empty($args) ? [] : $args;
	}
	
	public function accept($obj) {
		return $this->sql ? TRUE : FALSE;
	}
}

/**
 * 以下都是组合查询， 保证共同点：
 *  1. 都继承AndQuery
 *  2. 都有children
 */
class AndQuery extends BaseQuery {
	protected $children = [];

	public function __construct() {
		foreach (func_get_args() as $q) {
			$this->add($q);
		}
	}

	public function add(BaseQuery $q) {
		if (get_class($q) == 'RawQuery') $q = $q->query;
		if (get_class($this) == get_class($q)) {
			$this->children = array_merge($this->children, $q->children);
		} else {
			$this->children[] = $q;
		}
	}

	public function accept($obj) {
		foreach ($this->children as $q) {
			if ($q->accept($obj) === false) {
				return false;
			}
		}
		return true;
	}
}

class OrQuery extends AndQuery {
	public function accept($obj) {
		foreach ($this->children as $q) {
			if ($q->accept($obj)) {
				return true;
			}
		}
		return false;
	}
}

class InQuery extends OrQuery {
	protected $field;
	public function __construct($field = null, Array $values = []) {
		foreach ($values as $_val) {
			$this->add(new Query($field, $_val));
		}
	}
	public function add(BaseQuery $q) {
		if (get_class($q) != 'Query') {
			throw new Exception('It should be simple Query :' . var_export($q, true));
		}
		//InQuery需要保证所有children的Field是同一个值，否则和意义不符
		if (!$this->field) {
			$this->field = $q->field;
		} elseif ($this->field != $q->field) {
			throw new Exception("Different field given!");
		}
		$this->children[] = $q;
	}
}

/*----- END FILE: class.Query.php -----*/