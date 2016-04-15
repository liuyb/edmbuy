<?php
/**
 * DB操作驱动抽象基类
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
abstract class DbDriver {
  
  /**
   * MySQL link identifier, for extends by inheriting class
   * @var resource
   */
  protected $linkId;
  
  /**
   * Record the lastest result set resource
   * @var DbResult
   */
  protected $resultSet;
  
  /**
   * Some control parameters
   * @var array
   */
  protected $_config = array(
    'tablePrefix'  => 'tb_',    // Table prefix
    'connTimeout'  => 5,        //Connect timeout(seconds)
    'pingInterval' => 5,        //Ping interval(seconds)
  );
  
  /**
   * constructor
   */
  public function __construct(Array $config = array()) {
    $this->_config = array_merge($this->_config, $config);
  }
  
  /**
   * destructor
   */
  public function __destruct() {
  
    //~ free ResultSet
    if (isset($this->resultSet) && $this->resultSet instanceof DbResult) {
      $this->resultSet->free();
    }
  
    //~ close the opened connection(no effect to mysql_pconnect)
    $this->close();
  }
  
  /**
   * Connect to db, return connect identifier
   * 
   * @param string $dbhost, The MySQL server hostname
   * @param string $dbuser, The username.
   * @param string $dbpass, The password.
   * @param string $dbname, The db name
   * @param string $dbport, The MySQL server port
   * @param string $charset, Connect charset
   * @param bool $pconnect, Whether persistent connection
   * @return link_identifier
   */
  abstract public function connect($dbhost, $dbuser, $dbpass, $dbname, $dbport, $charset, $pconnect);
  
  /**
   * Select a MySQL database
   *
   * @param string $dbname
   *   The name of the database that is to be selected.
   * @return boolean
   *   Returns true on success or false on failure.
   */
  abstract public function select_db($dbname);
  
  /**
   * Execute sql statement
   *
   * @param string $sql: sql statement
   * @param string $type: default '', option: CACHE | UNBUFFERED | SILENT
   * @return DbResult
   *   For SELECT, SHOW, DESCRIBE, EXPLAIN and other statements returning resultset, returns a DbResult object on success, or FALSE on error.
   *   For other type of SQL statements, INSERT, UPDATE, DELETE, DROP, etc, returns TRUE on success or FALSE on error.
   */
  abstract public function query($sql, $type);
  
  /**
   * Get a result row as an enumerated array
   *
   * @param resource $query
   *   The result resource that is being evaluated. This result comes from a call to mysql_query().
   * @return array
   *   an numerical array of strings that corresponds to the fetched row, or FALSE if there are no more rows.
   */
  public function fetch_row($query) {
    return array();
  }
  
  /**
   * Get one row data as associate array from resultset.
   *
   * @param resource $query
   *   The result resource that is being evaluated. This result comes from a call to mysql_query().
   * @param $result_type int
   *   The type of array that is to be fetched. It's a constant and can take the following values: 1(MYSQL_ASSOC), 2(MYSQL_NUM), and 3(MYSQL_BOTH).
   * @return array
   *   One row data in $query, or FALSE if there are no more rows
   */
  public function fetch_array($query, $result_type = 1) {
    return array();
  }
  
  /**
   * Fetch a result row as an associative array.
   *
   * @param resource $query
   *   The result resource that is being evaluated. This result comes from a call to mysql_query().
   * @return array
   *   Returns an associative array that corresponds to the fetched row and moves the internal data pointer ahead.
   */
  public function fetch_assoc($query) {
    return array();
  }

  /**
   * Fetch a result row as an object
   *
   * @param resource $query
   *   The result resource that is being evaluated. This result comes from a call to mysql_query().
   * @return object
   *   an object with string properties that correspond to the fetched row, or FALSE if there are no more rows.
   */
  public function fetch_object($query) {
    return new stdClass();
  }
  
  /**
   * Execute sql statement, only get one row record
   *
   * @param string $sql: sql statement
   * @param string $type: default '', option: CACHE | UNBUFFERED | SILENT
   * @return array
   *   One row data in from current query, or FALSE if there are no more rows
   */
  public function get_one($sql, $type = '') {
    $query = $this->query($sql, $type);
    return $query->get_one();
  }
  
  /**
   * Get one column data from result set, return a one dimensionality array.
   * for example:
   *   $arr[0]='xxx',
   *   $arr[1]='yyy'...
   *   $arr[2]='zzz'...
   *
   * @param DbResult $query
   *   The DbResult object that is being evaluated.
   * @param mixed(string|int) $colname
   *   Table column field name or index
   * @return array  :
   *   Contain all rows data in the column $colname from $query; if no record in $query, return an empty array
   */
  public function fetch_column(DbResult $query, $colname = 0) {
    return $query->fetch_column($colname);
  }
  
  /**
   * Get all rows data as associate or index array from resultset.
   *
   * @param DbResult $query
   *   The DbResult object that is being evaluated.
   * @param int[optional] $result_type
   *   The type of array that is to be fetched. It's a constant and can take the following values: MYSQL_ASSOC, MYSQL_NUM, and MYSQL_BOTH.
   * @return array
   *   Contain all rows data in $query; if no record in $query, return an empty array
   */
  public function fetch_array_all(DbResult $query, $result_type = MYSQL_ASSOC) {
    return $query->fetch_array_all($result_type);
  }
  
  /**
   * Get all rows data as associate array from resultset.
   *
   * @param DbResult $query
   *   The DbResult object that is being evaluated.
   *
   * @return array
   *   Contain all rows data in $query; if no record, return an empty array
   */
  public function fetch_assoc_all(DbResult $query) {
    return $query->fetch_assoc_all();
  }
  
  /**
   * Fetch resultset to an array from the previous query as objects.
   *
   * @param DbResult $query
   *   The DbResult object that is being evaluated.
   * @return array
   *   Contain all rows data as objects from $query;
   *   if no record in $query, return an empty array
   */
  public function fetch_object_all(DbResult $query) {
    return $query->fetch_object_all();
  }
  
  /**
   * Get result data
   *
   * @param resource $query
   *   The result resource that is being evaluated. This result comes from a call to mysql_query().
   * @param $row int
   *   The row number from the result that's being retrieved. Row numbers start at 0.
   * @param $field mixed
   *   The name or offset of the field being retrieved.
   *   It can be the field's offset, the field's name, or the field's table dot field name (tablename.fieldname). If the column name has been aliased ('select foo as bar from...'), use the alias instead of the column name. If undefined, the first field is retrieved.
   * @return mixed
   *   The contents of one cell from a MySQL result set on success, or FALSE on failure.
   */
  abstract public function result($query, $row, $field);

  /**
   * Move internal result pointer.
   *
   * @param resource $query
   *   The result resource that is being evaluated. This result comes from a call to mysql_query().
   * @param $row int
   *   The desired row number of the new result pointer.
   * @return bool
   *   Returns TRUE on success or FALSE on failure.
   */
  abstract public function data_seek($query, $row);
  
  /**
   * Get number of rows in result
   *
   * @param resource $query
   *   The result resource that is being evaluated. This result comes from a call to mysql_query().
   * @return int
   *   The number of rows in a result set on success & return false for failure
   */
  public function num_rows($query) {
    return 0;
  }
  
  /**
   * Get number of fields in result
   *
   * @param resource $query
   *   The result resource that is being evaluated. This result comes from a call to mysql_query().
   * @return int
   *   The number of fields in the result set resource on success & return false for failure
   */
  public function num_fields($query) {
    return 0;
  }
  
  /**
   * Get number of affected rows in previous MySQL operation
   * 
   * @return int
   *   the number of affected rows on success, and -1 if the last query failed.
   */
  abstract public function affected_rows();
  
  /**
   * Get the ID generated in the last query
   *
   * @return int
   *   The ID generated for an AUTO_INCREMENT column by the previous query on success, 0 if the previous query does not generate an AUTO_INCREMENT value, or false if no MySQL connection was established.
   */
  abstract public function insert_id();
  
  /**
   * Free ResultSet memory
   *
   * @param resource $query
   * @return bool
   *   Returns TRUE on success or FALSE on failure.
   */
  abstract public function free_result($query);
  
  /**
   * Escapes special characters in a string for use in an SQL statement
   *
   * @param string $text
   *   The string that is to be escaped.
   * @return string
   *  the escaped string, or false on error.
   */
  public function escape_string($text) {
    return $text;
  }
  
  /**
   * Escapes special characters in a string for use in an SQL statement
   *
   * @param string $text
   *   The string that is to be escaped.
   * @return string
   *  the escaped string, or false on error.
   */
  public function encode_blob($text) {
    return "'". $text ."'";
  }
  
  /**
   * Get MySQL server info
   *
   * @return string
   *   the MySQL server version on success & return false for failure
   */
  abstract public function version();
  
  /**
   * Close MySQL connection
   *
   * @return bool
   *   Returns true on success or false on failure.
   */
  abstract public function close();
  
  /**
   * Returns the text of the error message from previous MySQL operation
   * @return string
   *   the error text from the last MySQL function, or '' (empty string) if no error occurred.
   */
  public function error() {
    return '';
  }
  
  /**
   * Returns the error number from the last MySQL function.
   * @return int
   *   the error number from the last MySQL function, or 0 (zero) if no error occurred.
   */
  public function errno() {
    return 0;
  }
  
  /**
   * Display error message and then halt(exit)
   *
   * @param string $message
   * @param string $sql, sql statememt
   */
  public function halt($message = '', $sql = '') {
    $msg = '';
    if ('cli'==PHP_SAPI) { //cli mode
      $br   = "\n";
      $msg .= "Db Query String: {$sql}{$br}";
      $msg .= "Db Error No    : ".$this->errno().$br;
      $msg .= "Db Error Msg   : ".$this->error().$br;
      $msg .= "Message        : {$message}{$br}";
    }
    else {
      $br   = "<br/>";
      $msg .= "<table>";
      $msg .= "<tr><th align=\"right\">Db Query String:&nbsp;</th><td>{$sql}</td></tr>";
      $msg .= "<tr><th align=\"right\">Db Error No:&nbsp;</th><td>".$this->errno()."</td></tr>";
      $msg .= "<tr><th align=\"right\">Db Error Msg:&nbsp;</th><td>".$this->error()."</td></tr>";
      $msg .= "<tr><th align=\"right\">Message:&nbsp;</th><td>{$message}</td></tr>";
      $msg .= "</table>";
    }
    $this->close();
    echo $msg;
    exit;
  }
  
  /**
   * Ping a server connection or reconnect if there is no connection
   *
   * Note: Automatic reconnection is disabled by default in versions of MySQL >= 5.0.3.
   *
   * @return bool
   *   Returns TRUE if the connection to the server MySQL server is working, otherwise FALSE.
   */
  public function ping(){
    return TRUE;
  }
  
  /**
   * Check connection link status 
   * 
   * @return int
   *   1: no connection exists; 2: the previous connection is disconnection or bad; 0: the connection is ok
   */
  public function check_link() {
    static $prev_time;
    if (!isset($prev_time)) {
      $prev_time = (int)microtime(TRUE);
    }
    
    //Check whether can ping, performance consideration
    $now = (int)microtime(TRUE);
    $canping = FALSE;
    if (($now - $prev_time) > $this->pingInterval) {
      $canping = TRUE;
      $prev_time = $now;
    }
    
    $ret = 0;
    if (!is_resource($this->linkId) && !is_object($this->linkId)) {
      $ret = 1;
    }
    elseif ($canping && !$this->ping()) {
      $ret = 2;
    }
    return $ret;
  }
  
  /**
   * Set linkId
   * @param resource $linkId
   * @return DbDriver
   */
  public function setLinkId($linkId) {
    $this->linkId = $linkId;
    return $this;
  }
  
  /**
   * Get the linkId
   * @return resource
   */
  public function getLinkId() {
    return $this->linkId;
  }
  
  /**
   * Set ResultSet
   * @param DbResult $rs
   * @return DbDriver
   */
  public function setResultSet(DbResult $rs) {
    $this->resultSet = $rs;
    return $this;
  }
  
  /**
   * Get the ResultSet
   * @return DbResult
   */
  public function getResultSet() {
    return $this->resultSet;
  }
  
  /**
   * magic method '__get'
   *
   * @param string $name
   */
  public function __get($name) {
    return array_key_exists($name, $this->_config) ? $this->_config[$name] : NULL;
  }
  
  /**
   * magic method '__set'
   *
   * @param string $name
   * @param string $value
   */
  public function __set($name, $value) {
    $this->_config[$name] = $value;
  }
  
}

/**
 * Db ResultSet resource class
 * 
 * @author Gavin
 */
class DbResult {
  
  /**
   * Record the result set resource
   * @var resource
   */
  public $query;
  
  /**
   * Db Class Object
   * @var DbDriver
   */
  public $dbObj;
  
  /**
   * constructor
   * @param resource $query, ResultSet
   * @param DbDriver $dbObj, DbDriver like class object
   */
  public function __construct($query, DbDriver $dbObj) {
    $this->query = $query;
    $this->dbObj = $dbObj;
  }
  
  /**
   * destructor
   */
  public function __destruct() {
     $this->free();
  }
  
  /**
   * Get a result row as an enumerated array
   *
   * @return array
   *   an numerical array of strings that corresponds to the fetched row, or FALSE if there are no more rows.
   */
  public function fetch_row() {
    return $this->dbObj->fetch_row($this->query);
  }
  
  /**
   * Get one row data as associate array from resultset.
   *
   * @param int[optional] $result_type
   *   The type of array that is to be fetched. It's a constant and can take the following values: 1(MYSQL_ASSOC), 2(MYSQL_NUM), and 3(MYSQL_BOTH).
   * @return array
   *   One row data in $query, or FALSE if there are no more rows
   */
  public function fetch_array($result_type = 1) {
    return $this->dbObj->fetch_array($this->query, $result_type);
  }
  
  /**
   * Fetch a result row as an associative array.
   *
   * @return array
   *   Returns an associative array that corresponds to the fetched row and moves the internal data pointer ahead.
   */
  public function fetch_assoc() {
    return $this->dbObj->fetch_assoc($this->query);
  }
  
  /**
   * Fetch a result row as an object
   *
   * @return object
   *   an object with string properties that correspond to the fetched row, or FALSE if there are no more rows.
   */
  public function fetch_object() {
    return $this->dbObj->fetch_object($this->query);
  }
  
  /**
   * Get result data
   *
   * @param int $row
   *   The row number from the result that's being retrieved. Row numbers start at 0.
   * @param mixed(int|string) $field
   *   The name or offset of the field being retrieved.
   *   It can be the field's offset, the field's name, or the field's table dot field name (tablename.fieldname). If the column name has been aliased ('select foo as bar from...'), use the alias instead of the column name. If undefined, the first field is retrieved.
   * @return mixed
   *   The contents of one cell from a MySQL result set on success, or FALSE on failure.
   */
  public function result($row = 0, $field = 0) {
    return $this->dbObj->result($this->query, $row, $field);
  }
  
  /**
   * Fetch a result row as an associative array.
   * like as $this->fetch_assoc(), but will free result set
   *
   * @return array
   *   Returns an associative array that corresponds to the fetched row and moves the internal data pointer ahead.
   */
  public function get_one() {
    $result = $this->fetch_assoc();
    $this->free(); //all result set no need again, free it
    return $result;
  }
  
  /**
   * Move internal result pointer.
   *
   * @param int $row
   *   The desired row number of the new result pointer.
   * @return bool
   *   Returns TRUE on success or FALSE on failure.
   */
  public function data_seek($row = 0) {
    return $this->dbObj->data_seek($this->query, $row);
  }
  
  /**
   * Get number of rows in result
   * 
   * @return int
   *   The number of rows in a result set on success & return false for failure
   */
  public function num_rows() {
    return $this->dbObj->num_rows($this->query);
  }
  
  /**
   * Get number of fields in result
   *
   * @return int
   *   The number of fields in the result set resource on success & return false for failure
   */
  public function num_fields() {
    return $this->dbObj->num_fields($this->query);
  }
  
  /**
   * Get one column data from result set, return a one dimensionality array.
   * for example:
   *   $arr[0]='xxx',
   *   $arr[1]='yyy'...
   *   $arr[2]='zzz'...
   *
   * @param mixed(string|int) $colname
   *   Table column field name
   * @return array  :
   *   Contain all rows data in the column $colname from $query; if no record in $query, return an empty array
   */
  public function fetch_column($colname = 0) {
    //2=MYSQL_NUM, 1=MYSQL_ASSOC 
    $retarr = $this->fetch_array_all(is_numeric($colname) ? 2 : 1);
    foreach ( $retarr as &$v ) {
      $v = $v[$colname];
    }
    return $retarr;
  }
  
  /**
   * Get all rows data as associate or index array from resultset.
   *
   * @param int[optional] $result_type
   *   The type of array that is to be fetched. It's a constant and can take the following values: 1(MYSQL_ASSOC), 2(MYSQL_NUM), and 3(MYSQL_BOTH).
   * @return array
   *   Contain all rows data in $query; if no record in $query, return an empty array
   */
  public function fetch_array_all($result_type = 1) {
    $rows = array();
    while($row = $this->fetch_array($result_type)) {
      $rows[] = $row;
    }
    $this->free();
    return $rows;
  }
  
  /**
   * Get all rows data as associate array from resultset.
   *
   * @return array
   *   Contain all rows data in $query; if no record, return an empty array
   */
  public function fetch_assoc_all() {
    $rows = array();
    while($row = $this->fetch_assoc()) {
      $rows[] = $row;
    }
    $this->free();
    return $rows;
  }
  
  /**
   * Fetch resultset to an array from the previous query as objects.
   * 
   * @return array
   *   Contain all rows data as objects from $query; if no record in $query, return an empty array
   */
  public function fetch_object_all() {
    $result = array();
    while ($obj = $this->fetch_object()) {
      $result[] = $obj;
    }
    $this->free();
    return $result;
  }
  
  /**
   * Get number of affected rows in previous MySQL operation
   *
   * @return int
   *   the number of affected rows on success, and -1 if the last query failed.
   */
  public function affected_rows() {
    return $this->dbObj->affected_rows();
  }
  
  /**
   * Get the ID generated in the last query
   *
   * @return int
   *   The ID generated for an AUTO_INCREMENT column by the previous query on success, 0 if the previous query does not generate an AUTO_INCREMENT value, or false if no MySQL connection was established.
   */
  public function insert_id() {
    return $this->dbObj->insert_id();
  }
  
  /**
   * Frees the memory associated with a result
   * @return bool
   *   Returns TRUE on success or FALSE on failure.
   */
  public function free() {
    if (is_resource($this->query)) {
      return $this->dbObj->free_result($this->query);
    }
    return TRUE;
  }
  
  /**
   * Close the target connection
   *
   * @return bool
   *   Returns true on success or false on failure.
   */
  public function close() {
    return $this->dbObj->close();
  }
  
}
 
/*----- END FILE: class.DbDriver.php -----*/