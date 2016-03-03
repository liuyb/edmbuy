<?php
/**
 * DB操作总接口类
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
class DB {
  
  // Indicates the place holders that should be replaced in query_callback().
  const DB_QUERY_REGEXP = '/(%d|%s|%%|%f|%b|%n)/';
  
  // Writable && Readonly(master && slave)
  const WRITABLE = 'write';
  const READONLY = 'read';
  
  // Lock type
  const LOCK_READ       = 'READ';
  const LOCK_READ_LOCAL = 'READ LOCAL';
  const LOCK_WRITE      = 'WRITE';
  const LOCK_WRITE_LOW  = 'LOW_PRIORITY WRITE';
  
  /**
   * Record current db connection mode(WRITABLE or READONLY), default to 'write'
   * @var string
   */
  protected $_dbMode    = self::WRITABLE;
  
  /**
   * Driver Class Object
   * @var object array
   */
  protected $_driverObj = array(self::WRITABLE => null, self::READONLY => null);
  
  /**
   * DB Result Set
   * @var DbResult
   */
  protected $_query;
  
  /**
   * DB connection info
   * @var array
   */
  protected $_dbInfo = array(
    //DB writable connecting Info
    self::WRITABLE  => array(
      'host'    => 'localhost', // DB Connecting Host
      'port'    => 3306,        // DB Connecting Port
      'user'    => 'root',      // DB Connecting User
      'pass'    => '',          // DB Connecting Password
      'name'    => 'test',      // DB Connecting Name
      'charset' => 'utf8',      // DB charset
      'pconnect'=> 0,           // Whether Persistent Connection
    ),
    //DB readonly connecting Info
    self::READONLY => array(
      'host'    => 'localhost', // DB Connecting Host
      'port'    => 3306,        // DB Connecting Port
      'user'    => 'root',      // DB Connecting User
      'pass'    => '',          // DB Connecting Password
      'name'    => 'test',      // DB Connecting Name
      'charset' => 'utf8',      // DB charset
      'pconnect'=> 0,           // Whether Persistent Connection
    )
  );
  
  /**
   * Default Config Set
   * @var array
   */
  protected $_config = array(
      'driverType'   => 'mysql',  // DB Driver Type, maybe mysql, mysqli...
      'tablePrefix'  => 'tb_',    // Table prefix
      'connTimeout'  => 5,        // Connect timeout(seconds)
      'pingInterval' => 5,        // Ping interval(seconds)
  );
  
  /**
   * DB Write && Read whether the same connection
   * @var bool
   */
  protected $_isSameWR = FALSE;
  
  /**
   * The singleton instance
   * @var DB
   */
  protected static $_instance;
  
  /**
   * The current 'select' sql server mode
   * @var string
   */
  protected $_select_mode = self::READONLY;
  
  /**
   * The current 'select' cache where condition
   * @var array
   */
  protected $_select_cache= array();
  
  /**
   * SQL of submiting to $this->query
   * @var string
   */
  protected $_sql = '';
  
  /**
   * The current submiting query SQL string, read only
   * @var string
   */
  protected $_sql_final = '';
  
  /**
   * Whether realtime query
   * @var bool
   */
  public $realtime_query = FALSE;
  
  /**
   * Get the singleton instance
   * @param array $config_write, required
   * @param array $config_read, optional
   * @param array $config_extra, optional
   * @return DB
   */
  public static function I(Array $config_write = array(), Array $config_read = array(), Array $config_extra = array()) {
    if ( !isset(self::$_instance) ) {
      self::$_instance = new self($config_write, $config_read, $config_extra);
    }
    return self::$_instance;
  }
  
  /**
   * Constructor
   * @param array $config_write, required
   * @param array $config_read, optional
   * @param array $config_extra, optional
   */
  public function __construct(Array $config_write = array(), Array $config_read = array(), Array $config_extra = array()) {
    if (empty($config_write) && empty($config_read)) {
      throw new DbException('parameter \'config_write\' or \'config_read\' required.');
    }
    
    $this->_isSameWR = FALSE;
    if (empty($config_read) && !empty($config_write)) {
      $this->_isSameWR = TRUE;
      $config_read = $config_write;
    }
    elseif (empty($config_write) && !empty($config_read)) {
      $this->_isSameWR = TRUE;
      $config_write = $config_read;
    }
    $this->_dbInfo[self::WRITABLE] = array_merge($this->_dbInfo[self::WRITABLE], $config_write);
    $this->_dbInfo[self::READONLY] = array_merge($this->_dbInfo[self::READONLY], $config_read);
    $this->_config                 = array_merge($this->_config, $config_extra);
    if (!$this->_isSameWR && $this->isSameConnection($this->_dbInfo[self::WRITABLE], $this->_dbInfo[self::READONLY])) {
      $this->_isSameWR = TRUE;
    }
    
    $driverType = $this->driverType;
    unset($this->driverType);
    if ($driverType == 'mysql') { //mysql driver interface
      $this->_driverObj[self::WRITABLE] = new DbMysql($this->_config);
      $this->_driverObj[self::READONLY] = $this->_driverObj[self::WRITABLE];
      if (!$this->_isSameWR) {
        $this->_driverObj[self::READONLY] = new DbMysql($this->_config);
      }
    }
    elseif ($driverType == 'mysqli') { // mysqli driver interface
      $this->_driverObj[self::WRITABLE] = new DbMysqli($this->_config);
      $this->_driverObj[self::READONLY] = $this->_driverObj[self::WRITABLE];
      if (!$this->_isSameWR) {
        $this->_driverObj[self::READONLY] = new DbMysqli($this->_config);
      }
    }
    //Other driverType...
  }
  
  /**
   * Destructor
   */
  public function __destruct() {
    unset($this->_driverObj[self::READONLY], $this->_driverObj[self::WRITABLE]);
  }
  
  /**
   * Check whether the same connection configure
   * @param array $config1
   * @param array $config2
   * @return bool
   */
  protected function isSameConnection(Array $config1 = array(), Array $config2 = array()) {
    return (isset($config1['host']) && isset($config1['port']) && isset($config1['user'])
            && $config1['host']==$config2['host']
            && $config1['port']==$config2['port']
            && $config1['user']==$config2['user']
           ) ? TRUE : FALSE;
  }

  /**
   * Check server mode(WRITABLE or READONLY)
   *
   * @param string $qstring, query SQL string
   * @return string WRITABLE or READONLY
   */
  public function check_server_mode($qstring) {
    return ($this->realtime_query
    		   || preg_match('/^\s*(insert|delete|update|replace|create|alter|truncate|drop|lock|unlock)\s+/i', $qstring)
    		   || preg_match('/for\s+update/i', $qstring)
    		   || preg_match('/lock\s+in\s+share\s+mode/i', $qstring))
           ? self::WRITABLE : self::READONLY;
  }
  
  /**
   * Get the current query server mode, can appoint a $server_mode for checking
   * @param string $server_mode
   * @return string, self::WRITABLE or self::READONLY
   */
  protected function getServerMode($server_mode = NULL) {
    $server_mode = !isset($server_mode) ? $this->_dbMode : $server_mode;
    if (!in_array($server_mode, array(self::WRITABLE,self::READONLY))) {
      throw new DbException("server mode '{$mode}' invalid.");
    }
    return $server_mode;
  }
  
  /**
   * Connect to db, and then set the link identifier
   *
   * @param string $server_mode, self::WRITABLE or self::READONLY
   * @return DB
   */
  public function connect($server_mode = self::WRITABLE) {
    $link_status = $this->_driverObj[$server_mode]->check_link();
    if (1==$link_status) { //no connection exists
      $dbinfo = $this->_dbInfo[$server_mode];
      $this->_driverObj[$server_mode]->connect($dbinfo['host'], $dbinfo['user'], $dbinfo['pass'], $dbinfo['name'], $dbinfo['port'], $dbinfo['charset'], $dbinfo['pconnect']);
    }
    elseif (2==$link_status) { //the previous connection is disconnection or bad
      $this->_driverObj[$server_mode]->close();
      $this->connect($server_mode); //reconnecting
    }
    return $this;
  }
  
  /**
   * Runs a basic query in the active database.
   *
   * User-supplied arguments to the query should be passed in as separate
   * parameters so that they can be properly escaped to avoid SQL injection
   * attacks.
   *
   * @param string $sql
   *   A string containing an SQL query.
   * @param ...
   *   A variable number of arguments which are substituted into the query
   *   using printf() syntax. Instead of a variable number of query arguments,
   *   you may also pass a single array containing the query arguments.
   *
   *   Valid %-modifiers are: %s, %d, %f, %b (binary data, do not enclose
   *   in '') and %%.
   *
   *   NOTE: using this syntax will cast NULL and FALSE values to decimal 0,
   *   and TRUE values to decimal 1.
   *
   * @return DbResult
   */
  public function query($sql) {
    if (is_array($sql)) { // 'All arguments in one array' syntax
      $args = $sql;
    }
    else {
      $args = func_get_args();
    }
    $sql = array_shift($args);
    
    $append = TRUE;
    if (is_bool($sql)) { //called by raw_query
      $append = FALSE;
      $sql = array_shift($args);
    }

    if (isset($args[0]) && is_array($args[0])) { // 'All arguments in one array', but the array is the second income argument(the first is the sql statement)
      $args = $args[0];
    }
    
    $server_mode = $this->check_server_mode($sql);
    $this->connect($server_mode);
    if ($append) {
      $sql = $this->append_prefix_tables($sql, $this->tablePrefix);
    }
    $this->query_callback($args, TRUE, $server_mode);
    $sql = preg_replace_callback(self::DB_QUERY_REGEXP, array($this,'query_callback'), $sql);
    $this->_sql_final = $sql;
    $this->_dbMode = $server_mode;
    $this->_query  = $this->_driverObj[$server_mode]->query($sql);
    return $this->_query;
  }
  
  /**
   * Like query method, just no do 'append_prefix_tables' action
   * 
   * @param mixed(string|array) $sql
   *   A string containing an SQL query.
   * @param ...
   *   A variable number of arguments which are substituted into the query
   *   using printf() syntax. Instead of a variable number of query arguments,
   *   you may also pass a single array containing the query arguments.
   *
   *   Valid %-modifiers are: %s, %d, %f, %b (binary data, do not enclose
   *   in '') and %%.
   *
   *   NOTE: using this syntax will cast NULL and FALSE values to decimal 0,
   *   and TRUE values to decimal 1.
   * @return DbResult
   */
  public function raw_query($sql) {
    if (is_array($sql)) {
      $args = $sql;
    }
    else {
      $args = func_get_args();
    }
    return $this->query(array_merge(array(FALSE),$args));
  }
  
  /**
   * Pager query
   *
   * @param string  $sqlquery, query sql string
   * @param integer $limit, limit num, optional
   * @param string  $sqlcnt, count sql stirng, optional
   * @param integer $element, pager index, optional
   * @return DbResult
   */
  public function pager_query($sqlquery, $limit = 30, $sqlcnt = NULL, $element = 0) {
    global 	$pager_totalrecord_arr,	// total record num array
            $pager_totalpage_arr, 	// total page num array
            $pager_currpage_arr; 		// current page no. array
  
    $pagername = 'p';
    $page = isset($_GET[$pagername]) ? $_GET[$pagername] : '';
  
    // Substitute in query arguments.
    $args = func_get_args();
    $args = array_slice($args, 4);
    // Alternative syntax for '...'
    if (isset($args[0]) && is_array($args[0])) {
      $args = $args[0];
    }
  
    // Construct a count query if none was given.
    if (!isset($sqlcnt)) {
      $sqlcnt = preg_replace(array('/SELECT.*?FROM /As', '/ORDER BY .*/'), array('SELECT COUNT(*) FROM ', ''), $sqlquery);
    }
  
    // We calculate the total of pages as ceil(items / limit).
    $pager_totalrecord_arr[$element] = $this->result($sqlcnt, $args);
    $pager_totalpage_arr[$element] 	 = ceil($pager_totalrecord_arr[$element] / $limit);
    if (is_numeric($page) || empty($page)) {
      $pager_currpage_arr[$element]  = max(1, min((int)$page, ((int)$pager_totalpage_arr[$element])));
    }
    else {
      if ($page == 'last') {
        $pager_currpage_arr[$element]= $pager_totalpage_arr[$element];
      }
      else {
        $pager_currpage_arr[$element]= 1;
      }
    }
    $start  = (($pager_currpage_arr[$element]-1) * $limit);
    $start  = $start>0 ? $start : 0;
    $sqlquery .= " LIMIT {$start},{$limit}";
  
    return $this->query($sqlquery, $args);
  }
  
  /**
   * Execute sql statement, only get one row record
   *
   * @param string $sql
   *  A string containing an SQL query.
   *
   * @param ...
   *
   * @return array
   *   One row data in from current query, or false if there are no more rows
   */
  public function get_one($sql) {
    return $this->query(func_get_args())->get_one();
  }
  
  /**
   * Get result data
   *
   * @param mixed(DbResult|string) $query
   *   The DbResult object that is being evaluated, or SQL string
   * @param int $row,
   *   The row number from the result that's being retrieved. Row numbers start at 0.
   * @param mixed[optional] $field,
   *   The name or offset of the field being retrieved.
   *   It can be the field's offset, the field's name, or the field's table dot field name (tablename.fieldname). If the column name has been aliased ('select foo as bar from...'), use the alias instead of the column name. If undefined, the first field is retrieved.
   * @return mixed
   *   The contents of one cell from a MySQL result set on success, or false on failure.
   */
  public function result($query = NULL, $row = 0, $field = 0) {
    if (is_string($query)) {
      $result = $this->query(func_get_args())->result();
    }
    else {
      $query = !isset($query) ? $this->_query : $query;
      $result= $query->result($row, $field);
    }
    return $result;
  }
  
  /**
   * insert data to a table
   *
   * @param string $tablename
   *  table name
   *
   * @param array $insertarr
   *  insert key-value array
   *
   * @param boolean $returnid
   *  whether return insert id
   *
   * @param string $flag
   *  insert flag, option values: '','IGNORE','LOW_PRIORITY','DELAYED','HIGH_PRIORITY'
   *
   * @return int
   *  insert id if set $returnid=1, else affected rows
   */
  public function insert($tablename, Array $insertarr, $returnid = TRUE, $flag = '') {
    $server_mode  = self::WRITABLE; //Because of 'INSERT', so use self::WRITABLE
    $insertkeysql = $insertvaluesql = $comma = '';
    foreach ($insertarr as $insert_key => $insert_value) {
      $insertkeysql   .= $comma.'`'.trim($insert_key).'`';
      $insertvaluesql .= $comma.'\''.$this->escape_string($insert_value, $server_mode).'\'';
      $comma = ',';
    }
    
    $tablename = $this->true_table_name($tablename);
    $this->realtime_query = TRUE;  //make sure use writable mode
    $rs = $this->raw_query("INSERT {$flag} INTO {$tablename} ({$insertkeysql}) VALUES ({$insertvaluesql})");
    $this->realtime_query = FALSE; //restore
    return $returnid ? $rs->insert_id() : $rs->affected_rows();
  }

  /**
   * update a table's data
   *
   * @param string $tablename
   *  table name
   *
   * @param array $setarr
   *  set sql key-value array
   *
   * @param array $wherearr
   *  where condition
   *  
   * @param string $flag
   *  insert flag, option values: '','IGNORE','LOW_PRIORITY'
   *
   * @return int
   *  affected rows
   */
  public function update($tablename, Array $setarr, Array $wherearr, $flag = '') {
    $server_mode = self::WRITABLE; //Because of 'UPDATE', so use self::WRITABLE
    $setsql = $comma = '';
    foreach ($setarr as $set_key => $set_value) {
      $setsql .= $comma.'`'.trim($set_key).'`=\''.$this->escape_string($set_value, $server_mode).'\'';
      $comma = ',';
    }
    $where = $comma = '';
    if(empty($wherearr)) {
      $where = '0'; // force avoiding misoperation
    }
    elseif(is_array($wherearr)) {
      foreach ($wherearr as $key => $value) {
        $where .= $comma.'`'.trim($key).'`=\''.$this->escape_string($value, $server_mode).'\'';
        $comma  = ' AND ';
      }
    }
    else {
      $where = $wherearr; //unsafe
    }
    
    $tablename = $this->true_table_name($tablename);
    $this->realtime_query = TRUE;  //make sure use writable mode
    $rs = $this->raw_query("UPDATE {$flag} {$tablename} SET {$setsql} WHERE {$where}");
    $this->realtime_query = FALSE; //restore
    return $rs->affected_rows();
  }
  
  /**
   * update table record
   * 
   * @param string $tablename
   * @param array $wherearr
   *  whether raw mode, when in raw mode, $tablename use original value, rather than with table prefix
   * @return int
   *   affected rows
   */
  public function delete($tablename, Array $wherearr) {
    $server_mode = self::WRITABLE; //Because of 'DELETE', so use self::WRITABLE
    $where = '';
    if(empty($wherearr)) {
      $where = '0'; // force avoiding misoperation
    }
    elseif(is_array($wherearr)) {
      $comma = '';
      foreach ($wherearr as $key => $value) {
        $where .= $comma.'`'.trim($key).'`=\''.$this->escape_string($value, $server_mode).'\'';
        $comma  = ' AND ';
      }
    }
    else {
      $where = $wherearr; //unsafe
    }
    
    $tablename = $this->true_table_name($tablename);
    $this->realtime_query = TRUE;  //make sure use writable mode
    $rs = $this->raw_query("DELETE FROM {$tablename} WHERE {$where}");
    $this->realtime_query = FALSE; //restore 
    return $rs->affected_rows();
  }
  
  /**
   * Get the sql to query() method 
   * 
   * @return string
   */
  public function getSql() {
    return $this->_sql;
  }
  
  /**
   * Get the final sql to MySQL
   * 
   * @return string
   */
  public function getSqlFinal() {
    return $this->_sql_final;
  }
  
  /**
   * sql SELECT part
   * 
   * @param string $fields
   *   SELECT fileds string
   * @param ...
   *   Separate writing fields
   * @return DbResult
   */
  public function select($fields = '*') {
    $args = func_get_args();
    if (count($args) > 1) {
      $fields = implode(',', $args);
    }
    $this->_sql = "SELECT {$fields}".$this->_sql;
    if($this->_select_mode==self::WRITABLE) $this->realtime_query = TRUE;  //make sure use writable mode
    $rs = $this->query(empty($this->_select_cache) ? $this->_sql : array_merge(array($this->_sql), $this->_select_cache));
    if($this->_select_mode==self::WRITABLE) $this->realtime_query = FALSE; //restore
    $this->_sql = ''; //finished, clear it
    $this->_select_cache = array();
    return $rs;
  }
  
  /**
   * sql FROM part, all 'select' chain begin with it, end endof select() method
   *
   * @param string $table_refes
   *   support format like as:
   *   from('users')               == from('tb_users')
   *   from('tb_users')            == from('tb_users')
   *   from('tb_users AS u')       == from('tb_users AS u')
   *   from('users u')             == from('tb_users u')
   *   from('{users}')             == from('tb_users')
   *   from('{users} u')           == from('tb_users u')
   *   from('db2.`users`')         == from('db2.`users`')
   *   from('{users} u INNER JOIN db2.`users` u2 ON u.user_id=u2.user_id') == from('tb_users u INNER JOIN db2.`users` u2 ON u.user_id=u2.user_id')
   *   ...and so on
   * @param $server_mode
   *   DB::READONLY or DB::WRITABLE
   * @return DB
   */
  public function from($table_refes, $server_mode = self::READONLY) {
    $this->_sql = '';//begin with this
    $this->_select_cache = array();
    $this->_select_mode  = $server_mode;
    $table_refes = $this->true_table_name($table_refes);
    $this->_sql .= " FROM {$table_refes}";
    return $this;
  }
  
  /**
   * sql WHERE part
   * 
   * @param string|array $where_condition,
   *   when $where_condition is an array, use 'AND' to connecting the confitions
   *   when $where_condition is a string, support like as %d, %s placeholder
   * @param ... 
   * @return DB
   */
  public function where($where_condition) {
    if (!empty($where_condition)) {
      if (is_array($where_condition)) {
        $where = $comma = '';
        foreach ($where_condition AS $k => $v) {
          $where .= $comma . '`'.trim($k).'`=\''.$this->escape_string($v, $this->_select_mode).'\'';
          $comma  = ' AND ';
        }
      }
      else {
        $where = $where_condition; //support like as %d, %s placeholder
        $args = func_get_args();
        array_shift($args); // the rest of $args is for the %d, %s parameters
        $this->_select_cache = $args; // Cache it
      }
      $this->_sql .= " WHERE {$where}";
    }
    return $this;
  }
  
  /**
   *  sql GROUP BY part
   * 
   * @param string $string
   * @param ...
   * @return DB
   */
  public function group_by($string) {
    $args = func_get_args();
    if (count($args) > 0) {
      $string = implode(',', $args);
      $this->_sql .= " GROUP BY {$string}";
    }
    return $this;
  }
  
  /**
   * sql HAVING part
   * 
   * @param string|array $where_condition,
   *   when $where_condition is an array, use 'AND' to connecting the confitions
   * @return DB
   */
  public function having($where_condition) {
    if (!empty($where_condition)) {
      $where = $where_condition;
      if (is_array($where_condition)) {
        $where = $comma = '';
        foreach ($where_condition AS $k => $v) {
          $where .= $comma . $k.'=\''.$this->escape_string($v, $this->_select_mode).'\'';
          $comma  = ' AND ';
        }
      }
      $this->_sql .= " HAVING {$where}";
    }
    return $this;
  }
  
  /**
   * sql ORDER BY part
   * 
   * @param string $string
   * @param ...
   * @return DB
   */
  public function order_by($string) {
    $args = func_get_args();
    if (count($args) > 0) {
      $string = implode(',', $args);
      $this->_sql .= " ORDER BY {$string}";
    }
    return $this;
  }
  
  /**
   * sql LIMIT part
   * 
   * @param integer $offset
   * @param integer $row_count
   * @return DB
   */
  public function limit($offset, $row_count = NULL) {
    if (!isset($row_count)) {
      $row_count = $offset;
      $offset = 0;
    }
    $this->_sql .= " LIMIT {$offset},{$row_count}";
    return $this;
  }
  
  /**
   * sql FOR UPDATE part
   * @return DB
   */
  public function for_update() {
  	$this->_sql .= " FOR UPDATE";
  	return $this;
  }
  
  /**
   * Call like SQL:
   * LOCK TABLES t READ
   * 
   * @param string|array $table_name
   *  when $table_name is a string, then indicating a table name
   *  when $table_name is a array, then indicating one or more table, its' format like as:
   *  array(
   *    array('table_name'=>'t1','lock_type'=>DB::LOCK_READ,'alias'=>'t1_alias','raw_mode'=>false),
   *    array('table_name'=>'t2','lock_type'=>DB::LOCK_WRITE,'alias'=>'','raw_mode'=>true),
   *  )
   * @return DB
   */
  public function lock_tables($table_name, $lock_type = self::LOCK_READ, $alias = '', $raw_mode = FALSE) {
    if (is_string($table_name)) {
      $table_name = array(array(
        'table_name' => $table_name,
        'lock_type'  => $lock_type,
        'alias'      => $alias,
        'raw_mode'   => $raw_mode,
      ));
    }
    elseif (is_array($table_name)) {
      if (count($table_name)>0 && !isset($table_name[0])) {
        $table_name = array($table_name); //Make sure it is a two-dimensional array
      }
    }
    else {
      $table_name = [];
    }
    
    if (count($table_name) > 0) {
      
      $sql = "LOCK TABLES";
      foreach ($table_name AS $it) {
        // table name
        $tbname = $it['table_name'];
        if (!$it['raw_mode']) {
          $tbname = '`' . $this->tablePrefix . $tbname . '`';
        }
        $sql  .= ' '.$tbname;
        
        // alias
        if (!empty($it['alias'])) {
          $sql.= ' AS '.$it['alias'];
        }
        
        // lock type
        $sql  .= ' '.$it['lock_type'].',';
        
      }
      $sql = substr($sql, 0, -1); //trim the last ','
      
      $this->query($sql);
    }
    
    return $this;
  }
  
  /**
   * Call SQL: 
   * UNLOCK TABLES
   * 
   * @return DB
   */
  public function unlock_tables() {
    $this->query("UNLOCK TABLES");
    return $this;
  }
  
  /**
   * Build IN query condition
   * @param string $field
   * @param array $value_set
   * @param boolean $NOT_IN
   * @return string
   */
  public function in($field, Array $value_set, $NOT_IN = FALSE) {
  	if (empty($value_set)) return '0';
  	if (count($value_set)==1) { // only one value
  		return '`'.$field.'`'.($NOT_IN ? '<>': '=')."'".array_pop($value_set)."'";
  	}
  	else { // more than one value
  		foreach ($value_set AS &$val) {
  			$val = $this->escape_string($val, self::READONLY);
  			$val = "'{$val}'";
  		}
  		return '`'.$field.'`'.($NOT_IN ? ' NOT': '').' IN('.implode(',', $value_set).')';
  	}
  }
  
  /**
   * Build NOT IN query condition
   * @param string $field
   * @param array $value_set
   * @return string
   */
  public function not_in($field, Array $value_set) {
  	return $this->in($field, $value_set, TRUE);
  }
  
  /**
   * Append a database prefix to all tables in a query.
   *
   * Queries sent to Drupal should wrap all table names in curly brackets. This
   * function searches for this syntax and adds Drupal's table prefix to all
   * tables, allowing Drupal to coexist with other systems in the same database if
   * necessary.
   *
   * @param $sql string
   *   A string containing a partial or entire SQL query.
   * @param $db_prefix mixed(string|array)
   *   DB talbe prefix
   * @return
   *   The properly-prefixed string.
   */
  protected function append_prefix_tables($sql, $db_prefix = '') {
  
    if (is_array($db_prefix)) {
      if (array_key_exists('default', $db_prefix)) {
        $tmp = $db_prefix;
        unset($tmp['default']);
        foreach ($tmp as $key => $val) {
          $sql = strtr($sql, array('{'. $key .'}' => $val . $key));
        }
        return strtr($sql, array('{' => $db_prefix['default'], '}' => ''));
      }
      else {
        foreach ($db_prefix as $key => $val) {
          $sql = strtr($sql, array('{'. $key .'}' => $val . $key));
        }
        return strtr($sql, array('{' => '', '}' => ''));
      }
    }
    else {
      return strtr($sql, array('{' => $db_prefix, '}' => ''));
    }
    
  }
  
  /**
   * Get true table name
   * @param string $table_name
   * @return string
   */
  protected function true_table_name($table_name) {
  	$table_name = trim($table_name);
  	if (strpos($table_name, '{')!==false) {
  		$table_name = strtr($table_name, array('{' => $this->tablePrefix, '}' => ''));
  	}
  	elseif (strpos($table_name, '`')!==false || strpos($table_name, '.')!==false) {
  				//no need parsing, directly passthrough or hand over to the lower logic
  	}
  	else {
			if (0===strpos($table_name, $this->tablePrefix)) { //begin with table prefix, such as 'tb_xxx'
				//no need parsing, directly passthrough
			}
			else {
				$table_name = $this->tablePrefix . $table_name;
			}
  	}
  	return $table_name;
  }
  
  /**
   * Helper function for query().
   * 
   * @param array $match
   * @param bool $init
   * @param string $server_mode
   *   self::READONLY or self::WRITABLE or NULL(default)
   */
  public function query_callback($match, $init = FALSE, $server_mode = NULL) {
    static $args = NULL, $the_mode = NULL;
    if ($init) {
      $args = $match;
      $the_mode = $server_mode;
      return;
    }
  
    switch ($match[1]) {
      case '%d': // We must use type casting to int to convert FALSE/NULL/(TRUE?)
        return (int) array_shift($args); // We don't need escape_string as numbers are db-safe
      case '%s':
        return $this->escape_string(array_shift($args), $the_mode);
      case '%n':
        // Numeric values have arbitrary precision, so can't be treated as float.
        // is_numeric() allows hex values (0xFF), but they are not valid.
        $value = trim(array_shift($args));
        return is_numeric($value) && !preg_match('/x/i', $value) ? $value : '0';
      case '%%':
        return '%';
      case '%f':
        return (float) array_shift($args);
      case '%b': // binary data
        return $this->encode_blob(array_shift($args), $the_mode);
    }
  }
  
  /**
   * Get number of affected rows in previous MySQL operation
   *
   * @param string $server_mode
   *   self::READONLY or self::WRITABLE or NULL(default)
   * @return int
   *   the number of affected rows on success, and -1 if the last query failed.
   */
  public function affected_rows($server_mode = NULL) {
    $server_mode = $this->getServerMode($server_mode);
    return $this->_driverObj[$server_mode]->affected_rows();
  }
  
  /**
   * Get the ID generated in the last query
   *
   * @param string $server_mode
   *   self::READONLY or self::WRITABLE or NULL(default)
   * @return int
   *   The ID generated for an AUTO_INCREMENT column by the previous query on success, 0 if the previous query does not generate an AUTO_INCREMENT value, or false if no MySQL connection was established.
   */
  public function insert_id($server_mode = NULL) {
    $server_mode = $this->getServerMode($server_mode);
    return $this->_driverObj[$server_mode]->insert_id();
  }
  
  /**
   * Escapes special characters in a string for use in an SQL statement
   * 
   * @param string $text
   *   The string that is to be escaped.
   * @param string $server_mode
   *   self::READONLY or self::WRITABLE or NULL(default)
   * @return string
   *  the escaped string, or false on error.
   */
  public function escape_string($text, $server_mode = NULL) {
    $server_mode = $this->getServerMode($server_mode);
    $this->connect($server_mode);
    return $this->_driverObj[$server_mode]->escape_string($text);
  }
  
  /**
   * Escapes special characters in a string for use in an SQL statement
   *
   * @param string $data
   *   The string that is to be escaped.
   * @param string $server_mode
   *   self::READONLY or self::WRITABLE or NULL(default)
   * @return string
   *  the escaped string, or false on error.
   */
  public function encode_blob($data, $server_mode = NULL) {
    $server_mode = $this->getServerMode($server_mode);
    $this->connect($server_mode);
    return $this->_driverObj[$server_mode]->encode_blob($data);
  }
  
  /**
   * Get MySQL server info
   *
   * @param string $server_mode
   *   self::READONLY or self::WRITABLE or NULL(default)
   * @return string
   *   the MySQL server version on success & return false for failure
   */
  public function version($server_mode = NULL) {
    $server_mode = $this->getServerMode($server_mode);
    $this->connect($server_mode);
    return $this->_driverObj[$server_mode]->version();
  }
  
  /**
   * Close MySQL connection
   *
   * @param string $server_mode
   *   self::READONLY or self::WRITABLE or NULL(default)
   * @return bool
   *   Returns true on success or false on failure.
   */
  public function close($server_mode = NULL) {
    $server_mode = $this->getServerMode($server_mode);
    return $this->_driverObj[$server_mode]->close();
  }
  
  /**
   * Returns the text of the error message from previous MySQL operation
   * 
   * @param string $server_mode
   *   self::READONLY or self::WRITABLE or NULL(default)
   * @return string
   *   the error text from the last MySQL function, or '' (empty string) if no error occurred.
   */
  public function error($server_mode = NULL) {
    $server_mode = $this->getServerMode($server_mode);
    return $this->_driverObj[$server_mode]->error();
  }
  
  /**
   * Returns the error number from the lastest MySQL operation.
   * 
   * @param string $server_mode
   *   self::READONLY or self::WRITABLE or NULL(default)
   * @return int
   *   the error number from the last MySQL function, or 0 (zero) if no error occurred.
   */
  public function errno($server_mode = NULL) {
    $server_mode = $this->getServerMode($server_mode);
    return $this->_driverObj[$server_mode]->errno();
  }
  
  /**
   * Set transaction characteristics
   * @param string $characteristics  optional value: 'ISOLATION LEVEL','READ WRITE','READ ONLY'
   * @param string $level            optional value: 'REPEATABLE READ | READ COMMITTED | READ UNCOMMITTED | SERIALIZABLE'
   * @param string $scope            optional value: 'GLOBAL' | 'SESSION'
   */
  public function setTransaction($characteristics, $level = '', $scope = '') {
  	$this->realtime_query = TRUE;
  	$this->query("SET {$scope} TRANSACTION {$characteristics} {$level}");
  }
  
  /**
   * Begin transaction
   */
  public function beginTransaction() {
  	$this->realtime_query = TRUE;
  	$this->query("SET autocommit = 0");
  	$this->query("START TRANSACTION");
  }
  
  /**
   * Commit transaction
   */
  public function commit() {
  	$this->query("COMMIT");
  	$this->query("SET autocommit = 1");
  	$this->realtime_query = FALSE;
  }
  
  /**
   * Rollback transaction
   */
  public function rollback() {
  	$this->query("ROLLBACK");
  	$this->query("SET autocommit = 1");
  	$this->realtime_query = FALSE;
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
  
	/**
	 * magic method '__isset'
	 * 
	 * @param string $name
	 */
	public function __isset($name) {
		return isset($this->_config[$name]);
	}
	
	/**
	 * magic method '__unset'
	 *
	 * @param string $name
	 */
	public function __unset($name) {
	  if (isset($this->_config[$name])) unset($this->_config[$name]);
	}
  
}

/**
 * Db Exception
 */
class DbException extends Exception {
  public function __construct($message = null, $code = null) {
    parent::__construct($message, $code);
  }
}

/*----- END FILE: class.DB.php -----*/