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
	 * Web server ID
	 * @var integer
	 */
	protected static $server_id;
	
	/**
	 * Parse input original path, return OSS path or original path
	 * 
	 * @param string $ori_path
	 * @return string OSS path or original path
	 */
	static function path($ori_path) {
		$ori_path = trim($ori_path);
		if (empty($ori_path) || preg_match('/^http(s?):\/\//i', $ori_path)) {
			return $ori_path;
		}
		
		if (!isset(self::$server_id)) {
			self::$server_id = Config::get('env.server_id', 1);
		}
		
		$std_path = $ori_path; //standardizing media key
		if ($ori_path{0} != '/') {
			$std_path = '/'.$ori_path;
		}
		$mkey = md5(self::$server_id.':'.$std_path);
		
		$media = Media::load($mkey);
		if (!$media->is_exist()) { //不存在，则先登记进数据库，让后台cron job同步
			$media->mid     = $mkey;
			$media->mime    = get_mime(SIMPHP_ROOT . '/../' . SHOP_PLATFORM . $std_path); //TODO 这里要改成下载的方式
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
		return preg_match('/^http(s?):\/\//i', $media->osspath) ? $media->osspath : $std_path;
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
			$mime_arr=explode('/', $this->mime);
		}
		else {
			$mime_arr=explode('.', $media->oripath);
		}
		$file_ext = end($mime_arr);
		$file_ext = 'jpeg'==$file_ext ? 'jpg' : $file_ext;
		return "{$this->sid}/{$this->mtype}/{$this->mid}.{$file_ext}";
	}

	/**
	 * Get file media type, option values: img,video,music,flash,attach and so on.
	 * @param string $file
	 * @return string
	 */
	static function get_media_type($mime) {
		$type = '';
		if (preg_match('/^image\//', $mime)) {
			$type = 'img';
		}
		return $type;
	}
}

/*----- END FILE: class.Media.php -----*/