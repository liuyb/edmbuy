<?php
/**
 * Query Builder Base Class
 */
class QueryBuilder {
	protected $query;
	public function __construct($query) {
		$this->query = $query;
	}

	public function query() {
		return $this->buildQuery($this->query);
	}

	protected function buildQuery($query) {
		return null;
	}
}
 
/*----- END FILE: class.QueryBuilder.php -----*/