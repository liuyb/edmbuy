<?php
/**
 * File handle
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
class File extends CStatic {
	
	/**
	 * Some constant
	 * @var constant
	 */
	const FOLDER_FILE_LIMIT = 30000;
	const TEMP_FILE_EXT     = '.tmp';
	
	/**
	 * Check whether curl extension exists.
	 * @return boolean
	 */
	static function curl_exists() {
		return function_exists('curl_init') ? true : false;
	}
	
	/**
	 * Check whether gd extension exists.
	 * @return boolean
	 */
	static function gd_exists() {
		return extension_loaded('gd') ? true : false;
	}
	
	/**
	 * Standardizing path
	 * @return string
	 */
	static function std_path($path) {
		return str_replace('\\', '/', $path);
	}
	
	/**
	 * Get file extension by mime
	 * @param string $filename
	 */
	static function ext($filename, $delimiter = '.') {
		$arr = explode($delimiter, $filename);
		$ext = end($arr);
		switch ($ext) {
			case 'jpg':
			case 'jpeg':
			case 'pjpeg':
				$ext = 'jpg';
				break;
			case 'png':
			case 'x-png':
				$ext = 'png';
				break;
			default:
				
		}
		return '.'.$ext;
	}
	
	/**
	 * Generate unique by type
	 * @param string $type, option vlaue: 'id','random'...
	 * @param mixed(integer|string) $id
	 * @param string $prefix
	 * @return string the unique dir, with the tail slash '/'
	 */
	static function gen_unique_dir($type, $id = 0, $prefix = '') {
		$dir = '/';
		if ('id'==$type) {
			$dir = '/' . intval($id / self::FOLDER_FILE_LIMIT) . '/';
		}
		elseif ('random'==$type) {
			
		}
		return rtrim($prefix,'/').$dir;
	}
	
	/**
	 * Create a temporary file path 
	 * @param string $base_info
	 * @param string $file_ext
	 * @return string absolute path(including SIMPHP_ROOT) of temp file
	 */
	static function temp_file($base_info = '',$file_ext = self::TEMP_FILE_EXT) {
		if (!$file_ext) $file_ext = self::TEMP_FILE_EXT;
		if ($file_ext{0}!='.') $file_ext = '.'.$file_ext;
		$tmp_dir = SIMPHP_ROOT . '/var/tmp/';
		mkdirs($tmp_dir);
		$filename= md5($base_info ? : uniqid()).$file_ext;
		return $tmp_dir . $filename;
	}
	
	/**
	 * Get file from a remote url, and save to local path or temporary dir
	 * @param string $url remote url
	 * @param string $local_path save to local file path
	 * @return string|boolean, when a string, indicating the local file path; when false, indicating fail
	 */
	static function get_remote($url, $local_path = NULL) {
		if (empty($url)) return false;
		
		if (empty($local_path)) {
			$local_path = self::temp_file($url);
		}
		else {
			$local_path = self::std_path($local_path);
			if (strpos($local_path, SIMPHP_ROOT)===false) {
				$local_path = SIMPHP_ROOT . '/' . ltrim($local_path,'/');
			}
			$dir = dirname($local_path);
			mkdirs($dir);
		}

		$fp = @fopen($local_path, 'w');
		if (!$fp) {
			throw new Exception("Path '{$local_path}' not writable.");
		}
		
		$succ = false;
		if (self::curl_exists()) {
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_FILE, $fp);
			curl_exec($ch);
			$err = curl_error($ch);
			curl_close($ch);
			$succ = empty($err) ? true : false;
		}
		else {
			
		}
		
		// Get file mime to rename to currect extension
		if ($succ) {
			$ext  = self::ext(get_mime($local_path), '/');
			$local_path_final = preg_replace('/'.preg_quote(self::TEMP_FILE_EXT).'$/', $ext, $local_path);
			@rename($local_path, $local_path_final);
			return str_replace(SIMPHP_ROOT, '', $local_path_final);
		}
		return $succ;
	}
	
	/**
	 * 为图片增加水印
	 *
	 * @param       string      filename            原始图片文件名，包含完整路径
	 * @param       string      target_file         需要加水印的图片文件名，包含完整路径。如果为空则覆盖源文件
	 * @param       string      $watermark          水印完整路径
	 * @param       array       $watermark_place    水印位置代码，包括 x:原始图x位置;y:原始图y位置;w:水印在原始图的宽度;h:水印在原始图的高度
	 * @param       int         $watermark_alpha    水印透明度(0~100)
	 * @return      mix         如果成功则返回文件路径，否则返回false
	 */
	static function add_watermark($filename, $target_file = '', $watermark = '', Array $watermark_place = array(), $watermark_alpha = 65)
	{	
		// 是否安装了GD
		if (!self::gd_exists())
		{
			return false;
		}
		
		// 文件是否存在
		if ((!file_exists($filename)) || (!is_file($filename)))
		{
			return false;
		}
		
		/* 如果水印的位置为0，则返回原图 */
		if (empty($watermark_place) || empty($watermark))
		{
			return str_replace(SIMPHP_ROOT, '', str_replace('\\', '/', realpath($filename)));
		}
		
		// 获得水印文件以及源文件的信息
		$watermark_info     = @getimagesize($watermark);
		$watermark_handle   = self::img_resource($watermark, $watermark_info[2]);
		
		if (!$watermark_handle)
		{
			return false;
		}
		
		// 根据文件类型获得原始图片的操作句柄
		$source_info    = @getimagesize($filename);
		$source_handle  = self::img_resource($filename, $source_info[2]);
		if (!$source_handle)
		{
			return false;
		}
		
		$thumb_width  = $watermark_info[0];
		$thumb_height = $watermark_info[1];
		if (isset($watermark_place['w']) && $watermark_place['w']) {
			$thumb_width  = $watermark_place['w'];
			if (isset($watermark_place['h']) && $watermark_place['h']) {
				$thumb_height = $watermark_place['h'];
			}
			else {
				$thumb_scale = $watermark_info[0] / ($watermark_info[1] ? : 1);
				$thumb_height= intval($watermark_place['w'] / $thumb_scale);
			}
		}
		if (function_exists('imagecreatetruecolor')) {
			$watermark_thumb  = imagecreatetruecolor($thumb_width, $thumb_height);
		}
		else {
			$watermark_thumb  = imagecreate($thumb_width, $thumb_height);
		}
		
		/* 将水印图片进行缩放处理 */
		if (function_exists('imagecopyresampled'))
		{
			imagecopyresampled($watermark_thumb, $watermark_handle, 0, 0, 0, 0, $thumb_width, $thumb_height, $watermark_info[0], $watermark_info[1]);
		}
		else
		{
			imagecopyresized($watermark_thumb, $watermark_handle, 0, 0, 0, 0, $thumb_width, $thumb_height, $watermark_info[0], $watermark_info[1]);
		}
		
		/* 将水印缩略图合并到原图 */
		if (strpos(strtolower($watermark_info['mime']), 'png') !== false)
		{
			imageAlphaBlending($watermark_thumb, true);
			imagecopy($source_handle, $watermark_thumb, $watermark_place['x'], $watermark_place['y'], 0, 0, $thumb_width, $thumb_height);
		}
		else
		{
			imagecopymerge($source_handle, $watermark_thumb, $watermark_place['x'], $watermark_place['y'], 0, 0, $thumb_width, $thumb_height, $watermark_alpha);
		}
		$target = empty($target_file) ? $filename : $target_file;
		
		switch ($source_info[2] )
		{
			case 'image/gif':
			case 1:
				imagegif($source_handle,  $target);
				break;
		
			case 'image/pjpeg':
			case 'image/jpeg':
			case 2:
				imagejpeg($source_handle, $target);
				break;
		
			case 'image/x-png':
			case 'image/png':
			case 3:
				imagepng($source_handle,  $target);
				break;
		
			default:
				
		}
		
		imagedestroy($watermark_handle);
		imagedestroy($watermark_thumb);
		imagedestroy($source_handle);
		
		$path = realpath($target);
		if ($path)
		{
			return str_replace(SIMPHP_ROOT, '', str_replace('\\', '/', $path));
		}
		return false;
	}
	
	/**
	 * 根据来源文件的文件类型创建一个图像操作的标识符
	 *
	 * @param   string      $img_file   图片文件的路径
	 * @param   string      $mime_type  图片文件的文件类型
	 * @return  resource    如果成功则返回图像操作标志符，反之则返回错误代码
	 */
	static function img_resource($img_file, $mime_type)
	{
		switch ($mime_type)
		{
			case 1:
			case 'image/gif':
				$res = imagecreatefromgif($img_file);
				break;
	
			case 2:
			case 'image/pjpeg':
			case 'image/jpeg':
				$res = imagecreatefromjpeg($img_file);
				break;
	
			case 3:
			case 'image/x-png':
			case 'image/png':
				$res = imagecreatefrompng($img_file);
				break;
	
			default:
				return false;
		}
	
		return $res;
	}
	
}
 
/*----- END FILE: class.File.php -----*/