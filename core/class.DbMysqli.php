<?php
/**
 * MySQLi操作驱动类
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
class DbMysqli extends DbDriver {
  
  /**
   * Connect to db, return connect identifier
   *
   * @param string $dbhost, The MySQL server hostname.
   * @param string $dbuser, The username.
   * @param string $dbpass, The password.
   * @param string $dbname, The db name, optional, defualt to ''
   * @param string $dbport, The MySQL server port, optional, defualt to '3306'
   * @param string $charset, Connect charset, optional, default to 'utf8'
   * @param bool $pconnect, Whether persistent connection: 1 - Yes, 0 - No
   * @return link_identifier
   */
  function connect($dbhost, $dbuser, $dbpass, $dbname = '', $dbport = '3306', $charset = 'utf8', $pconnect = 0) {
    
    if ($pconnect && version_compare(PHP_VERSION, '5.3.0','>=')) { //PHP since 5.3.0 added the ability of persistent connections.
      $dbhost = 'p:'.$dbhost;
    }
    
    //$mysqli = mysqli_init();
    $mysqli = new mysqli();
    if (!$mysqli) {
      $this->halt('mysqli_init failed');
    }
    
    //$mysqli->options(MYSQLI_INIT_COMMAND, 'SET AUTOCOMMIT = 0');
    $mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, $this->connTimeout);
    
    // - TRUE makes mysql_connect() always open a new link, even if
    //   mysql_connect() was called before with the same parameters.
    //   This is important if you are using two databases on the same
    //   server.
    // - 2 means CLIENT_FOUND_ROWS: return the number of found
    //   (matched) rows, not the number of affected rows.
    if (!@$mysqli->real_connect($dbhost, $dbuser, $dbpass, $dbname, $dbport, null, MYSQLI_CLIENT_COMPRESS | MYSQLI_CLIENT_FOUND_ROWS)) {
      $this->halt("Connect to {$dbuser}@{$dbhost}:{$dbport} Error: ({$mysqli->connect_errno}){$mysqli->connect_error}");
    }
    $mysqli->set_charset($charset);
    $this->linkId = $mysqli;
  
    return $this->linkId;
  }
  
  /**
   * Pings a server connection, or tries to reconnect if the connection has gone down
   *
   * Checks whether the connection to the server is working. If it has gone down, and global option mysqli.reconnect is enabled an automatic reconnection is attempted.
   * This function can be used by clients that remain idle for a long while, to check whether the server has closed the connection and reconnect if necessary.
   *
   * @return bool
   *   Returns TRUE if the connection to the server MySQL server is working, otherwise FALSE.
   */
  public function ping() {
    return $this->linkId->ping();
  }
  
  /**
   * Select a MySQL database
   *
   * @param string $dbname
   *   The name of the database that is to be selected.
   * @return boolean
   *   Returns true on success or false on failure.
   */
  function select_db($dbname) {
    return $this->linkId->select_db($dbname);
  }
  
  /**
   * Execute sql statement
   *
   * @param string $sql: sql statement
   * @param string $type: default '', option: BUFFERED | UNBUFFERED | SILENT
   * @return DbResult
   *   For SELECT, SHOW, DESCRIBE, EXPLAIN and other statements returning resultset, returns a DbResult object on success, or FALSE on error.
   *   For other type of SQL statements, INSERT, UPDATE, DELETE, DROP, etc, returns TRUE on success or FALSE on error.
   */
  function query($sql, $type = '') {
    $flag = $type == 'UNBUFFERED' ? MYSQLI_USE_RESULT : MYSQLI_STORE_RESULT;
    if(!($query = $this->linkId->query($sql, $flag)) && $type != 'SILENT') {
      $this->halt('MySQL Query Error', $sql);
    }
    $this->resultSet = new DbResult($query,$this);
    return $this->resultSet;
  }
  
  /**
   * Get a result row as an enumerated array
   *
   * @param resource $query
   *   The result resource that is being evaluated. This result comes from a call to mysql_query().
   * @return array
   *   an numerical array of strings that corresponds to the fetched row, or FALSE if there are no more rows.
   */
  function fetch_row($query) {
    if ($query instanceof mysqli_result) {
      return $query->fetch_row();
    }
    return FALSE;
  }
  
  /**
   * Get one row data as associate array from resultset.
   *
   * @param resource $query
   *   The result resource that is being evaluated. This result comes from a call to mysql_query().
   * @param int[optional] $result_type
   *   The type of array that is to be fetched. It's a constant and can take the following values: MYSQLI_ASSOC, MYSQLI_NUM, and MYSQLI_BOTH.
   * @return array
   *   One row data in $query, or FALSE if there are no more rows
   */
  function fetch_array($query, $result_type = MYSQLI_ASSOC) {
    if ($query instanceof mysqli_result) {
      return $query->fetch_array($result_type);
    }
    return FALSE;
  }
  
  /**
   * Fetch a result row as an associative array.
   *
   * @param resource $query
   *   The result resource that is being evaluated. This result comes from a call to mysql_query().
   * @return array
   *   Returns an associative array that corresponds to the fetched row and moves the internal data pointer ahead.
   */
  function fetch_assoc($query) {
    if ($query instanceof mysqli_result) {
      return $query->fetch_assoc();
    }
    return FALSE;
  }
  
  /**
   * Fetch a result row as an object
   *
   * @param resource $query
   *   A database query result resource, as returned from $this->query().
   * @return object
   *   an object with string properties that correspond to the fetched row, or FALSE if there are no more rows.
   */
  function fetch_object($query) {
    if ($query instanceof mysqli_result) {
      return $query->fetch_object();
    }
    return FALSE;
  }
  
  /**
   * Get result data
   *
   * @param mysqli_result $query
   *   The result resource that is being evaluated. This result comes from a call to mysql_query().
   * @param int $row
   *   The row number from the result that's being retrieved. Row numbers start at 0.
   * @param mixed[int|string] $field
   *   The name or offset of the field being retrieved.
   *   It can be the field's offset, the field's name, or the field's table dot field name (tablename.fieldname). If the column name has been aliased ('select foo as bar from...'), use the alias instead of the column name. If undefined, the first field is retrieved.
   * @return mixed
   *   The contents of one cell from a MySQL result set on success, or FALSE on failure.
   */
  function result($query, $row = 0, $field = 0) {
    $ret = FALSE;
    if ($query instanceof mysqli_result && $query->num_rows) {
      $query->data_seek($row);
      $ret = $query->fetch_array(is_numeric($field) ? MYSQLI_NUM : MYSQLI_ASSOC);
      if ($ret) {
        $ret = $ret[$field];
      }
    }
    return $ret;
  }
  
  /**
   * Move internal result pointer.
   *
   * @param mysqli_result $query
   *   The result resource that is being evaluated. This result comes from a call to mysql_query().
   * @param int $row
   *   The desired row number of the new result pointer.
   * @return bool
   *   Returns TRUE on success or FALSE on failure.
   */
  function data_seek($query, $row = 0) {
    if ($query instanceof mysqli_result) {
      return $query->data_seek($row);
    }
    return FALSE;
  }
  
  /**
   * Get number of rows in result
   *
   * @param mysqli_result $query
   *   The result resource that is being evaluated. This result comes from a call to mysql_query().
   * @return int
   *   The number of rows in a result set on success & return false for failure
   */
  function num_rows($query) {
    if ($query instanceof mysqli_result) {
      return $query->num_rows;
    }
    return FALSE;
  }
  
  /**
   * Get number of fields in result
   *
   * @param mysqli_result $query
   *   The result resource that is being evaluated. This result comes from a call to mysql_query().
   * @return int
   *   The number of fields in the result set resource on success & return false for failure
   */
  function num_fields($query) {
    if ($query instanceof mysqli_result) {
      return $query->field_count;
    }
    return FALSE;
  }
  
  /**
   * Free ResultSet memory
   *
   * @param mysqli_result $query
   *   The result resource that is being evaluated. This result comes from a call to mysql_query().
   * @return bool
   *   Returns true on success or false on failure.
   */
  function free_result($query) {
    if ($query instanceof mysqli_result) {
      return $query->free();
    }
    return FALSE;
  }
  
  /**
   * Get number of affected rows in previous MySQL operation
   *
   * @return int
   *   the number of affected rows on success, and -1 if the last query failed.
   */
  function affected_rows() {
    return $this->linkId->affected_rows;
  }
  
  /**
   * Get the ID generated in the last query
   *
   * @return int
   *   The ID generated for an AUTO_INCREMENT column by the previous query on success, 0 if the previous query does not generate an AUTO_INCREMENT value, or false if no MySQL connection was established.
   */
  function insert_id() {
    return $this->linkId->insert_id;
  }
  
  /**
   * Escapes special characters in a string for use in an SQL statement
   *
   * @param string $text
   *   The string that is to be escaped.
   * @return string
   *  the escaped string, or false on error.
   */
  function escape_string($text) {
    return $this->linkId->real_escape_string($text);
  }
  
  /**
   * Escapes special characters in a string for use in an SQL statement
   *
   * @param string $text
   *   The string that is to be escaped.
   * @return string
   *  the escaped string, or false on error.
   */
  function encode_blob($text) {
    return "'". $this->linkId->real_escape_string($text) ."'";
  }
  
  /**
   * Get MySQL server info
   *
   * @return string
   *   the MySQL server version on success & return false for failure
   */
  function version() {
    return $this->linkId->server_info;
  }
  
  /**
   * Closes a previously opened database connection
   *
   * @return bool
   *   Returns true on success or false on failure.
   */
  function close() {
    $ret = FALSE;
    if ($this->linkId instanceof mysqli) {
      $ret = $this->linkId->close();
      unset($this->linkId);
    }
    return $ret;
  }
  
  /**
   * Returns a string description of the last error
   * @return string
   *   the error text from the last MySQL function, or '' (empty string) if no error occurred.
   */
  function error() {
    if ($this->linkId instanceof mysqli) {
      return $this->linkId->error;
    }
    return '';
  }
  
  /**
   * Returns the error number from the last MySQL function.
   * @return int
   *   the error number from the last MySQL function, or 0 (zero) if no error occurred.
   */
  function errno() {
    if ($this->linkId instanceof mysqli) {
      return $this->linkId->errno;
    }
    return 0;
  }
  
}
 
/*----- END FILE: class.DbMysqli.php -----*/