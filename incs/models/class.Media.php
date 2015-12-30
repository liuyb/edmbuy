<?php
/**
 * Media App
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
//阿里云OSS库
require SIMPHP_INCS . '/libs/aliyun_oss/OssCommon.php';

use OSS\OssClient;
use OSS\Core\OssException;

class Media extends StorageNode {

	protected static function meta() {
		return array(
				'table'   => '{media}',
				'key'     => 'mid',
				'columns' => array(
						'mid'     => 'media_id',
						'sid'     => 'server_id',
						'oripath' => 'ori_path',
						'osspath' => 'oss_path',
						'synced'  => 'synced'
				)
		);
	}
	
	/**
	 * Web server ID
	 * @var integer
	 */
	private static $server_id;
	
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
			self::$server_id = Config::get('env.server_id');
		}
		
		$mkey = $ori_path; //standardizing media key
		if ($ori_path{0} != '/') {
			$mkey = '/'.$ori_path;
		}
		$mkey = md5(self::$server_id.':'.$mkey);
		
		$media = Media::load($mkey);
		if (!$media->is_exist()) { //不存在，则先登记进数据库，让后台cron job同步
			$media->mid     = $mkey;
			$media->sid     = self::$server_id;
			$media->oripath = $ori_path;
			$media->osspath = '';
			$media->synced  = 0;
			$media->save(Storage::SAVE_INSERT);
			return $ori_path;
		}
		else {
			return preg_match('/^http(s?):\/\//i', $media->osspath) ? $media->osspath : $ori_path;
		}
	}
	
	static function sync_to_oss($ori_path) {
		
		$ossClient = OssCommon::getOssClient();
		$bucket    = OssCommon::getBucketName();
		
		if ($ori_path{0} != '/') {
			$ori_path = '/'.$ori_path;
		}
		
		$local_file = SIMPHP_ROOT . $ori_path;
		$file_ext   = strtolower(substr($ori_path, strrpos($ori_path, '.')));
		$remote_file= 'img/'.date('Ym').'/'.date('d').'_'.time().'_'.randstr(6).$file_ext;//format: img/{yyyy}{mm}/{dd}_{time}_{rand:6}
		$ossClient->uploadFile($bucket, $remote_file, $local_file);
		OssCommon::println("{$remote_file} is created");
	}

}

/*----- END FILE: class.Media.php -----*/