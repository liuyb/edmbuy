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
	 * Get file mime type
	 * @param string $file
	 * @return string
	 */
	static function mime($file) {
		if (!file_exists($file)) {
			return '';
		}
		$fi = new finfo(FILEINFO_MIME_TYPE);
		return $fi->file($file);
	}
	
	/**
	 * Get file extension by mime
	 * @param string  $filename
	 * @return string the extension, with the prefix '.'
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
		elseif ('random'==$type) { //TODO random type
			
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
	 * @param string $local_path save to local file path, when null, then use temporary path
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
		else { //TODO other method
			
		}
		
		// Get file mime to rename to currect extension
		if ($succ) {
			$ext  = self::ext(self::mime($local_path), '/');
			$local_path_final = preg_replace('/'.preg_quote(self::TEMP_FILE_EXT).'$/', $ext, $local_path);
			@rename($local_path, $local_path_final);
			return str_replace(SIMPHP_ROOT, '', $local_path_final);
		}
		return $succ;
	}
	
	/**
	 * 为图片增加水印
	 *
	 * @param       string      $filename           原始图片文件名，包含完整路径
	 * @param       string      $target_file        需要加水印的图片文件名，包含完整路径。如果为空则覆盖源文件
	 * @param       string      $watermark          水印完整路径
	 * @param       array       $watermark_place    水印位置代码，包括 x:水印在原始图的x位置;y:水印在原始图的y位置;w:水印在原始图的宽度;h:水印在原始图的高度。可选参数pos值: 'lt'(左上),'rt'(右上),'lb'(左下),'rb'(右下),'ct'(居中)
	 * @param       int         $watermark_alpha    水印透明度(0~100)
	 * @param       string      $bgcolor            背景颜色
	 * @param       array       $extra              额外控制参数，如 $extra['png2jpg'=>true]
	 * @return      mix         如果成功则返回文件路径，否则返回false
	 */
	static function add_watermark($filename, $target_file = '', $watermark = '', Array $watermark_place = array(), $watermark_alpha = 85, $bgcolor = '', Array $extra = array())
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
		
		// 如果水印的位置为0，则返回原图
		if (empty($watermark_place) || empty($watermark) || false===($watermark_info=@getimagesize($watermark)))
		{
			return str_replace(SIMPHP_ROOT, '', str_replace('\\', '/', realpath($filename)));
		}
		
		// 获得水印文件以及源文件的信息
		$watermark_handle   = self::img_resource($watermark, $watermark_info[2]);
		if (!$watermark_handle)
		{
			return false;
		}
		
		// 根据文件类型获得原始图片的操作句柄
		$source_info = @getimagesize($filename);
		if (isset($extra['png2jpg'])&&$extra['png2jpg'] && 3==$source_info[2])
		{ //PNG图片，且需要转成JPG图片
			$_filename = self::img_png2jpg($filename,'',$bgcolor);
			if ($_filename && $_filename!=$filename)
			{
				$filename = $_filename;
				$source_info= @getimagesize($filename);
			}
			unset($_filename);
		}
		$source_handle  = self::img_resource($filename, $source_info[2]);
		if (!$source_handle)
		{
			imagedestroy($watermark_handle);
			return false;
		}
		
		// 决定是否要缩放水印
		$thumb_width    = $watermark_info[0];
		$thumb_height   = $watermark_info[1];
		$thumb_scale    = $watermark_info[1] ? ($watermark_info[0]/$watermark_info[1]) : 1;
		$need_resize    = false;
		if (isset($watermark_place['w']) && $watermark_place['w'] && $watermark_place['w']!=$watermark_info[0])
		{ //以宽为基准缩放
			$need_resize  = true;
			$thumb_width  = $watermark_place['w'];
			$thumb_height = intval($thumb_width / $thumb_scale);
		}
		elseif (isset($watermark_place['h']) && $watermark_place['h'] && $watermark_place['h']!=$watermark_info[1])
		{ //以高为基准缩放
			$need_resize  = true;
			$thumb_height = $watermark_place['h'];
			$thumb_width  = intval($thumb_height * $thumb_scale);
		}
		
		if ($need_resize)
		{ //需要缩放
			if (function_exists('imagecreatetruecolor'))
			{
				$watermark_thumb  = imagecreatetruecolor($thumb_width, $thumb_height);
			}
			else
			{
				$watermark_thumb  = imagecreate($thumb_width, $thumb_height);
			}
			if (!$watermark_thumb)
			{
				imagedestroy($watermark_handle);
				imagedestroy($source_handle);
				return false;
			}
			
			/* 背景颜色 */
			if (self::is_color($bgcolor))
			{
				list($red, $green, $blue) = self::hexcolor2rgb($bgcolor);
				imagefilledrectangle($watermark_thumb, 0, 0, $thumb_width, $thumb_height, imagecolorallocate($watermark_thumb, $red, $green, $blue));
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
		}
		else
		{ //不需要缩放，直接用原水印图
			$watermark_thumb = $watermark_handle;
		}
		
		/* 检测特殊位置 */
		if (isset($watermark_place['pos'])) {
			switch ($watermark_place['pos']) 
			{
				default:
				case 'ct': //居中
				case 'center': //居中
					$watermark_place['x'] = round(($source_info[0] - $thumb_width)/2);
					$watermark_place['y'] = round(($source_info[1] - $thumb_height)/2);
					break;
				case 'lt': //左上
					$watermark_place['x'] = 0;
					$watermark_place['y'] = 0;
					break;
				case 'rt': //右上
					$watermark_place['x'] = $source_info[0] - $thumb_width;
					$watermark_place['y'] = 0;
					break;
				case 'lb': //左下
					$watermark_place['x'] = 0;
					$watermark_place['y'] = $source_info[1] - $thumb_height;
					break;
				case 'rb': //右下
					$watermark_place['x'] = $source_info[0] - $thumb_width;
					$watermark_place['y'] = $source_info[1] - $thumb_height;
					break;
			}
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
		
		/* 写文件到目标目录 */
		$target = empty($target_file) ? $filename : $target_file;
		$wrsucc = self::img_write($source_handle, $target, $source_info[2]);
		
		/* 清除image资源对象 */
		imagedestroy($watermark_handle);
		if(is_resource($watermark_thumb)) imagedestroy($watermark_thumb);
		imagedestroy($source_handle);
		
		$path = realpath($target);
		if ($wrsucc && $path)
		{
			return str_replace(SIMPHP_ROOT, '', str_replace('\\', '/', $path));
		}
		return false;
	}
	
	/**
	 * 为图片增加水印
	 *
	 * @param       string      $source_file        原始图片文件名，包含完整路径
	 * @param       string      $target_file        需要加水印的图片文件名，包含完整路径。如果为空则覆盖源文件
	 * @param       array       $text_info          一个二维数组，包含要写的一个或多个文本信息的内容(text)和位置(x,y)、字体大小(fontsize)、字体颜色(color)
	 */
	static function add_text($source_file, $target_file = '', Array $text_info = [])
	{
		// 检查输入
		if (empty($text_info)) return false;
		if (isset($text_info['text']))
		{
			$text_info = [$text_info];
		}
		
		// 是否安装了GD
		if (!self::gd_exists())
		{
			return false;
		}
		
		// 文件是否存在
		if ((!file_exists($source_file)) || (!is_file($source_file)))
		{
			return false;
		}
		
		// 获得源文件的信息
		$source_info     = @getimagesize($source_file);
		$source_handle   = self::img_resource($source_file, $source_info[2]);
		if (!$source_handle)
		{
			return false;
		}
		
		// 循环写入
		$font = SIMPHP_ROOT . '/misc/font/simsun.ttc';
		foreach ($text_info AS $txt)
		{
			if (!isset($txt['text']) || empty($txt['text'])) continue;
			
			// 默认参数
			$fontsize = 20;
			$color    = '#000000';
			$x        = 0;
			$y        = 0;
			if (isset($txt['fontsize'])) $fontsize = $txt['fontsize'];
			if (isset($txt['color']))    $color    = $txt['color'];
			if (isset($txt['x']))        $x        = $txt['x'];
			if (isset($txt['y']))        $y        = $txt['y'];
			
			list($red, $green, $blue) = self::hexcolor2rgb($color);
			$color = imagecolorallocate($source_handle, $red, $green, $blue);
			
			//将ttf文字写到图片中
			if (function_exists('imagettftext'))
			{
				imagettftext($source_handle, $fontsize, 0, $x, $y, $color, $font, $txt['text']);
			}
		}
		
		/* 写文件到目标目录 */
		$target = empty($target_file) ? $source_file : $target_file;
		$wrsucc = self::img_write($source_handle, $target, $source_info[2]);
		
		imagedestroy($source_handle);
		
		$path = realpath($target);
		if ($wrsucc && $path)
		{
			return str_replace(SIMPHP_ROOT, '', str_replace('\\', '/', $path));
		}
		
		return false;
	}
	
	/**
	 * 将png图片改成jpg图片格式保存
	 * @param string $img_src 源PNG图片路径
	 * @param string $img_dst 目标JPG图片保存路径, 该参数为空时，用$img_src加后缀'.jpg'替代，表示jpg图片格式
	 * @param string $bgcolor 背景填充颜色，空或者格式：#FFFFFF
	 * @return string 返回转换过的图片路径
	 */
	static function img_png2jpg($img_src, $img_dst = '', $bgcolor = '')
	{
		$img_info   = @getimagesize($img_src);
		if (false===$img_info || 3!=$img_info[2]) { //如果不是PNG图片格式，直接返回原图路径
			return $img_src;
		}
		
		if (function_exists('imagecreatetruecolor')) {
			$bg_handle = imagecreatetruecolor($img_info[0], $img_info[1]);
		}
		else {
			$bg_handle = imagecreate($img_info[0], $img_info[1]);
		}
		if (!$bg_handle) { //如果创建底图失败，也直接返回原图路径
			return $img_src;
		}
		
		//背景填充色
		if (self::is_color($bgcolor)) {
			list($red, $green, $blue) = self::hexcolor2rgb($bgcolor);
			imagefilledrectangle($bg_handle, 0, 0, $img_info[0], $img_info[1], imagecolorallocate($bg_handle, $red, $green, $blue));
		}
		imagealphablending($bg_handle, TRUE);
		$img_handle  = self::img_resource($img_src, $img_info[2]);
		imagecopy($bg_handle, $img_handle, 0, 0, 0, 0, $img_info[0], $img_info[1]);
		
		$target = !empty($img_dst) ? $img_dst : $img_src.'.jpg'; //默认在原路径后加'.jpg'后缀表示jpg图片格式
		$wrsucc = self::img_write($bg_handle, $target, 3); //$mime==3表示JPG图片
		imagedestroy($img_handle);
		imagedestroy($bg_handle);
		if ($wrsucc) {
			return $target;
		}
		
		return $img_src;
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
		$res = false;
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
				
		}
	
		return $res;
	}
	
	/**
	 * 
	 * @param resource              $img      图片资源对象
	 * @param string                $target   目标文件目录
	 * @param mixed(integer|string) $mime     图片mime
	 * @return boolean
	 */
	static function img_write($img, $target, $mime)
	{
		$b = false;
		switch ($mime)
		{
			case 'image/gif':
			case 1:
				$b = imagegif($img,  $target);
				break;
		
			case 'image/pjpeg':
			case 'image/jpeg':
			case 2:
				$b = imagejpeg($img, $target);
				break;
		
			case 'image/x-png':
			case 'image/png':
			case 3:
				$b = imagepng($img,  $target);
				break;
		
			default:
				
		}
		
		return $b;
	}
	
	/**
	 * 转换十六进制的颜色值成RGB值
	 * @param string $color 如 #ff6600
	 * @return array 包含[red, green, blue]
	 */
	static function hexcolor2rgb($color) {
		$color = preg_replace ('/^#/','',$color);
		sscanf($color, "%2x%2x%2x", $red, $green, $blue);
		return [$red,$green,$blue];
	}
	
	/**
	 * 检查一个颜色值是否合法的 #FFFFFF 这种格式的值
	 * @param string $color
	 * @return boolean
	 */
	static function is_color($color) {
		return preg_match('/^#[a-zA-Z0-9]{6}$/', $color);
	}
	
}
 
/*----- END FILE: class.File.php -----*/