<?php
/**
 * Media App
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
class Media extends StorageNode {

	protected static function meta() {
		return array(
				'table'   => '{media}',
				'key'     => 'mid',
				'columns' => array(
						'mid'     => 'media_id',
						'mtype'   => 'media_type',
						'mime'    => 'mime',
						'sid'     => 'server_id',
						'oripath' => 'ori_path',
						'osspath' => 'oss_path',
						'created' => 'created',
						'changed' => 'changed',
						'synced'  => 'synced',
						'locked'  => 'locked'
				)
		);
	}
	
	/**
	 * Web resource server ID
	 * @var integer
	 */
	protected static $server_id;
	
	/**
	 * Web resource servers set
	 * @var array
	 */
	protected static $servers;
	
	/**
	 * Shop server
	 * @var string
	 */
	protected static $server_shop;
	
	/**
	 * Parse input original path, return OSS path or original path
	 * 
	 * @param string  $ori_path
	 * @param boolean $ret_full  是否保证返回的地址都是http://开头的全地址(true)，还是相对项目地址(false)
	 * @return string OSS path or original path
	 */
	static function path($ori_path, $ret_full = false) {
		$ori_path = trim($ori_path);
		if (empty($ori_path) || preg_match('/^http(s?):\/\//i', $ori_path)) {
			return $ori_path;
		}
		
		// Set server id
		if (!isset(self::$server_id)) {
			self::$server_id = Config::get('env.server_id', 1);
		}
		
		// Standardizing media key
		$std_path = $ori_path;
		if ($ori_path{0} != '/') {
			$std_path = '/'.$ori_path;
		}
		$mkey = md5(self::$server_id.':'.$std_path);
		
		// Get true path
		$true_path = SIMPHP_ROOT;
		if (self::is_app_file($std_path)) { //路径以 '/a/' 或 '/var/'开头，则表示文件在edmbuy下，否则在edmshop下
			$true_path .= $std_path;
		}
		else {
			$true_path .= '/../' . SHOP_PLATFORM . $std_path;
		}
		$true_path = realpath($true_path);
		if (!file_exists($true_path)) { //TODO 由于以后很可能是edmshop只部署一个server，而edmbuy会部署多个，所以可能不能总检测到同servser的edmshop的文件，这是需要下载处理
			return $ret_full ? self::full_path($std_path) : $std_path;
		}
		
		$media = Media::load($mkey);
		if (!$media->is_exist()) { //不存在，则先登记进数据库，让后台cron job同步
			$media->mid     = $mkey;
			$media->mime    = get_mime($true_path); //TODO 这里要改成下载的方式
			$media->mtype   = self::get_media_type($media->mime);
			$media->sid     = self::$server_id;
			$media->oripath = $std_path;
			$media->osspath = '';
			$media->created = simphp_dtime();
			$media->changed = $media->created;
			$media->synced  = 0;
			$media->locked  = 0;
			$media->save(Storage::SAVE_INSERT);
		}
		return preg_match('/^http(s?):\/\//i', $media->osspath) ? $media->osspath : ($ret_full ? self::full_path($std_path) : $std_path);
	}

	/**
	 * Generate full path
	 * @param string $rel_path
	 * @return string
	 */
	static function full_path($rel_path) {
		if (!isset(self::$servers)) {
			self::$servers = Config::get('env.resource_servers');
		}
		if (!isset(self::$server_shop)) {
			self::$server_shop = Config::get('env.site.shop');
		}
		return (!self::is_app_file($rel_path) ? self::$server_shop : self::$servers[self::$server_id]) . '/' . ltrim($rel_path,'/');
	}
	
	/**
	 * Get file media type, option values: img,video,music,flash,attach,file and so on.
	 * @param string $file
	 * @return string
	 */
	static function get_media_type($mime) {
		$type = 'file';
		if (preg_match('/^image\//', $mime)) {
			$type = 'img';
		}
		return $type;
	}
	
	/**
	 * Check whether the file($path) is edmbuy file(true), or else edmshop file(false)
	 * @param string  $path
	 * @return boolean
	 */
	static function is_app_file($path) {
		return preg_match('/^\/(a|var)\//', $path) ? true : false;
	}

	/**
	 * Get oss path of a media
	 * @param string $file
	 * @param number $server_id
	 * @param string $prefix, default 'img'
	 * @return string
	 */
	public function oss_path() {
		if ($this->mime) {
			$file_ext = File::ext($this->mime, '/');
		}
		else {
			$file_ext = File::ext($media->oripath, '.');
		}
		return "{$this->sid}/{$this->mtype}/{$this->mid}{$file_ext}";
	}
	
}

/*----- END FILE: class.Media.php -----*/