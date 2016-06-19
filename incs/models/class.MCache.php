<?php
/**
 * MCache Node
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
class MCache extends StorageNode {
	
	protected static function meta() {
		return array(
				'table'   => '{mcache}',
				'key'     => 'cid',
				'columns' => array(
						'cid'     => 'cid',
						'key'     => 'key',
						'val'     => 'val',
						'created' => 'created',
						'expired' => 'expired'
				)
		);
	}

	/**
	 * Engine type constants
	 * @var constant
	 */
	const ENGINE_TYPE_DB       = 'db';
	const ENGINE_TYPE_REDIS    = 'redis';
	const ENGINE_TYPE_MEMCACHE = 'memcache';
	
	private static $engine_type;
	private static $engine_handle;
	
	/**
	 * Set cache
	 * @param string $key
	 * @param mixed  $val
	 * @param number $life_time
	 * @return void
	 */
	static function set($key, $val, $life_time = 0) {
		self::check_connection();
		
		switch (self::$engine_type) {
			case self::ENGINE_TYPE_REDIS:
				self::$engine_handle->set($key, $val, $life_time);
				break;
			case self::ENGINE_TYPE_MEMCACHE:
				self::$engine_handle->set($key, $val, 0, $life_time);
				break;
			case self::ENGINE_TYPE_DB:
			default:
				$tbn = self::table();
				$val = serialize($val);
				$now = simphp_time();
				$exp = $life_time > 0 ? $now + $life_time : 0;
				self::$engine_handle->query("UPDATE `{$tbn}` SET `val`='%s', `created`=%d, `expired`=%d WHERE `key`='%s'", $val, $now, $exp, $key);
				if (!self::$engine_handle->affected_rows()) {	//update error, indicating no the session
					self::$engine_handle->query("INSERT INTO `{$tbn}` (`key`, `val`, `created`, `expired`) VALUES('%s', '%s', %d, %d)", $key, $val, $now, $exp);
				}
		}
	}
	
	/**
	 * Get cache
	 * @param string $key
	 * @return mixed
	 */
	static function get($key) {
		self::check_connection();
		
		switch (self::$engine_type) {
			case self::ENGINE_TYPE_REDIS:
			case self::ENGINE_TYPE_MEMCACHE:
				return self::$engine_handle->get($key);
				break;
			case self::ENGINE_TYPE_DB:
			default:
				$row = self::$engine_handle->from(self::table())->where(['key'=>$key])->select('`val`,`expired`')->get_one();
				if (!empty($row) && (0==$row['expired'] || $row['expired']>simphp_time())) {
					return unserialize($row['val']);
				}
		}
		return FALSE;
	}
	
	/**
	 * Delete cache
	 * @param string $key
	 */
	static function delete($key) {
		self::check_connection();
		
		switch (self::$engine_type) {
			case self::ENGINE_TYPE_REDIS:
			case self::ENGINE_TYPE_MEMCACHE:
				self::$engine_handle->delete($key);
				break;
			case self::ENGINE_TYPE_DB:
			default:
				self::$engine_handle->delete(self::table(), ['key'=>$key]);
		}
	}

	/**
	 * Replace a cache
	 * @param string $key
	 * @param string $val
	 * @param number $life_time
	 * @return void
	 */
	static function replace($key, $val, $life_time = 0) {
		self::check_connection();
		self::set($key, $val, $life_time);
	}
	
	/**
	 * Check cache engine connection
	 * @return void
	 */
	private static function check_connection() {
		
		if (!isset(self::$engine_type)) {
			self::$engine_type = Config::get('storage.cache-config.engine','db'); // 'db' is default
		}
		
		if (!isset(self::$engine_handle)) {
			if ('db'==self::$engine_type) {
				self::$engine_handle = D();
			}
			else {
				self::$engine_handle = M(self::$engine_type);
			}
		}
		
	}
	
}
 
/*----- END FILE: class.MCache.php -----*/