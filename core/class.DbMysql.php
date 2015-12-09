<?php
/**
 * MySQL操作驱动类
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
class DbMysql extends DbDriver {

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
    
    $dbserver = $dbhost.':'.$dbport;
    if ($pconnect) {
      // - 2 means CLIENT_FOUND_ROWS: return the number of found
      //   (matched) rows, not the number of affected rows.
      if(!$this->linkId = @mysql_pconnect($dbserver, $dbuser, $dbpass, 2)) {
        $this->halt("Can not connect to MySQL server({$dbserver})");
      }
    }
    else {
      // - TRUE makes mysql_connect() always open a new link, even if
      //   mysql_connect() was called before with the same parameters.
      //   This is important if you are using two databases on the same
      //   server.
      // - 2 means CLIENT_FOUND_ROWS: return the number of found
      //   (matched) rows, not the number of affected rows.
      if(!$this->linkId = @mysql_connect($dbserver, $dbuser, $dbpass, FALSE, 2)) {
        $this->halt("Can not connect to MySQL server({$dbuser}@{$dbserver})");
      }
    }

    //~ get mysql version
    $dbver = $this->version();
    if ($dbver) {
      //~ when mysql version > 4.1, use database charset setting
      if($dbver > '4.1') {
        mysql_query("SET NAMES '{$charset}'" , $this->linkId);
      }
      
      //~ when mysql version > 5.0 setting sql mode
      if($dbver > '5.0') {
        mysql_query("SET sql_mode=''" , $this->linkId);
      }
    }

    //~ select db
    if($dbname && !$this->select_db($dbname)) {
      $this->halt("Cannot select database: {$dbname}");
    }

    return $this->linkId;
  }
  
  /**
   * Ping a server connection or reconnect if there is no connection
   *
   * Note: Automatic reconnection is disabled by default in versions of MySQL >= 5.0.3.
   *
   * @return bool
   *   Returns TRUE if the connection to the server MySQL server is working, otherwise FALSE.
   */
  public function ping() {
    return mysql_ping($this->linkId);
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
    return mysql_select_db($dbname , $this->linkId);
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
    $func = $type == 'UNBUFFERED' ? 'mysql_unbuffered_query' : 'mysql_query';
    if(!($query = $func($sql , $this->linkId)) && $type != 'SILENT') {
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
    if (is_resource($query)) {
      return mysql_fetch_row($query);
    }
    return FALSE;
  }

  /**
   * Get one row data as associate array from resultset.
   *
   * @param resource $query
   *   The result resource that is being evaluated. This result comes from a call to mysql_query().
   * @param int[optional] $result_type
   *   The type of array that is to be fetched. It's a constant and can take the following values: MYSQL_ASSOC, MYSQL_NUM, and MYSQL_BOTH.
   * @return array
   *   One row data in $query, or FALSE if there are no more rows
   */
  function fetch_array($query, $result_type = MYSQL_ASSOC) {
    if (is_resource($query)) {
      return mysql_fetch_array($query, $result_type);
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
    if (is_resource($query)) {
      return mysql_fetch_assoc($query);
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
    if (is_resource($query)) {
      return mysql_fetch_object($query);
    }
    return FALSE;
  }
  
  /**
   * Get result data
   *
   * @param resource $query
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
    if (mysql_num_rows($query)) {
      $ret = mysql_result($query, $row, $field);
    }
    return $ret;
  }

  /**
   * Move internal result pointer.
   *
   * @param resource $query
   *   The result resource that is being evaluated. This result comes from a call to mysql_query().
   * @param int $row
   *   The desired row number of the new result pointer.
   * @return bool
   *   Returns TRUE on success or FALSE on failure.
   */
  function data_seek($query, $row = 0) {
    if (is_resource($query)) {
      return mysql_data_seek($query, $row);
    }
    return FALSE;
  }
  
  /**
   * Get number of rows in result
   *
   * @param resource $query
   *   The result resource that is being evaluated. This result comes from a call to mysql_query().
   * @return int
   *   The number of rows in a result set on success & return false for failure
   */
  function num_rows($query) {
    return mysql_num_rows($query);
  }
  
  /**
   * Get number of fields in result
   *
   * @param resource $query
   *   The result resource that is being evaluated. This result comes from a call to mysql_query().
   * @return int
   *   The number of fields in the result set resource on success & return false for failure
   */
  function num_fields($query) {
    return mysql_num_fields($query);
  }

  /**
   * Free ResultSet memory
   *
   * @param resource $query
   *   The result resource that is being evaluated. This result comes from a call to mysql_query().
   * @return bool
   *   Returns true on success or false on failure.
   */
  function free_result($query) {
    return @mysql_free_result($query);
  }
  
  /**
   * Get number of affected rows in previous MySQL operation
   *
   * @return int
   *   the number of affected rows on success, and -1 if the last query failed.
   */
  function affected_rows() {
    return mysql_affected_rows($this->linkId);
  }

  /**
   * Get the ID generated in the last query
   *
   * @return int
   *   The ID generated for an AUTO_INCREMENT column by the previous query on success, 0 if the previous query does not generate an AUTO_INCREMENT value, or false if no MySQL connection was established.
   */
  function insert_id() {
    return mysql_insert_id($this->linkId);
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
    return mysql_real_escape_string($text, $this->linkId);
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
    return "'". mysql_real_escape_string($text, $this->linkId) ."'";
  }

  /**
   * Get MySQL server info
   *
   * @return string
   *   the MySQL server version on success & return false for failure
   */
  function version() {
    return mysql_get_server_info($this->linkId);
  }

  /**
   * Close MySQL connection
   *
   * @return bool
   *   Returns true on success or false on failure.
   */
  function close() {
    $ret = FALSE;
    if (is_resource($this->linkId)) {
      $ret = mysql_close($this->linkId);
      unset($this->linkId);
    }
    return $ret;
  }

  /**
   * Returns the text of the error message from previous MySQL operation
   * @return string
   *   the error text from the last MySQL function, or '' (empty string) if no error occurred.
   */
  function error() {
    return mysql_error($this->linkId);
  }

  /**
   * Returns the error number from the last MySQL function.
   * @return int
   *   the error number from the last MySQL function, or 0 (zero) if no error occurred.
   */
  function errno() {
    return mysql_errno($this->linkId);
  }
  
}
 
/*----- END FILE: class.DbMysql.php -----*/