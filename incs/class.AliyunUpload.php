<?php

/**
 * 基于阿里云的图片上传
 * @author Jean
 *
 */
defined('IN_SIMPHP') or die('Access Denied');
//阿里云OSS库
require SIMPHP_INCS . '/libs/aliyun_oss/OssCommon.php';

use OSS\OssClient;

class AliyunUpload {
    
    const FILE_NOT_SUPPORT = -1;
    
    //图片宽度不正确
    const FILE_WIDTH_INCORRET = -2;
    
    //图片高度不正确
    const FILE_HEIGHT_INCORRET = -3;
    
    const FILE_UPLOAD_ERROR = -100;
    
    const FILE_UPLOAD_SUCC = 200;
    
    private $img_dir = '/a/mch/';
    
    // 图片base64数据
    private $img_data;
    
    // 图片存放目录
    private $img_folder;
    
    private $compress_stype;
    
    //是否需要画布
    private $need_canvas;
    
    //画布的宽高
    private $canvas_width;
    
    private $canvas_height;
    
    private $bgcolor = '#FFFFFF';
    
    //规定宽高
    private $limitWidth;
    private $limitHeight;
    
    /**
     * 
     * @param unknown $img_data
     * @param unknown $img_folder 图片上传的文件夹，对应阿里云
     * @param unknown $compress_stype 阿里云压缩的样式
     * @param string $need_canvas 是否需要固定画布
     * @param number $canvas_width
     * @param number $canvas_height
     */
    public function __construct($img_data, $img_folder, $compress_stype = '', $need_canvas = false, $canvas_width = 0, $canvas_height = 0)
    {
        $this->img_data = $img_data;
        $this->img_folder = $img_folder;
        if(!$compress_stype){
            $compress_stype = $img_folder;
        }
        $this->compress_stype = $compress_stype;
        $this->need_canvas = $need_canvas;
        $this->canvas_width = $canvas_width;
        $this->canvas_height = $canvas_height;
    }
    
    /**
     * 校验图片 宽度高度限制在 规定的宽高 10px里面
     * @param unknown $limitW
     * @param unknown $limitH
     * @return string
     */
    function checkAndSaveImg($limitW, $limitH){
        $this->limitWidth = $limitW;
        $this->limitHeight = $limitH;
        
        $img_data = $this->img_data;
        
        $pos = strpos($img_data, ','); // $img_data like 'data:image/jpeg;base64,/9j/4AAQSk...'
        if (false === $pos) {
            return AliyunUpload::FILE_NOT_SUPPORT;
        }
        
        $file_data = substr($img_data, $pos + 1);
        $file_data = base64_decode($file_data);
        $img_info = getimagesizefromstring($file_data);
        if (FALSE === $img_info) {
            return AliyunUpload::FILE_NOT_SUPPORT;
        }
        $width = $img_info[0];
        $height = $img_info[1];
        if($width < ($limitW - 10) || $width > ($limitW + 10)){
            return AliyunUpload::FILE_WIDTH_INCORRET;
        }
        if($height < ($limitH - 10) || $height > ($limitH + 10)){
            return AliyunUpload::FILE_HEIGHT_INCORRET;
        }
        
        $imgtype = $img_info[2];
        $ratio = $width / ($height ?: 1);
        
        $extpart = '.jpg';
        switch ($imgtype) { // image type
            case IMAGETYPE_GIF:
                $extpart = '.gif';
                break;
            case IMAGETYPE_PNG:
                $extpart = '.png';
                break;
        }
        $YM = date('Ym');
        $filecode = date('d_His') . '_' . randstr() . $extpart;
        $img_dir = $this->img_dir.$this->img_folder.'/';
        $oripath = $img_dir . $YM . '/' . $filecode;
        $remote_file = $this->img_folder . '/' . $YM . '/' . $filecode;
        $oss_path = '';
        try{
            // 写ori版本
            $oripath = $this->writeImgData($oripath, $file_data);
            if($this->need_canvas){
                $temp_path = $img_dir . $YM . '/temp/' . $filecode;
                $destheight = $this->canvas_height ? $this->canvas_height : intval($this->canvas_height / $ratio);
                $new_oripath = $this->generateNewImage($oripath, $temp_path, $this->canvas_width, $imgtype, $width, $height, $destheight, $ratio);
                unlink(SIMPHP_ROOT . $oripath);
                $oripath = $new_oripath;
            }
            $ossClient = OssCommon::getOssClient();
            $bucket    = OssCommon::getBucketName();
            $oripath = SIMPHP_ROOT . $oripath;
            $ossClient->uploadFile($bucket, $remote_file, $oripath);
            $oss_path = OssCommon::getOssImgPath($remote_file);
            unlink($oripath);
        }catch (Exception $e){
        }
        if (! $oss_path || empty($oss_path)) {
            return UPload::FILE_UPLOAD_ERROR;
        }
        //通过阿里云的压缩规则处理
        $style = C('env.picstyle.'.$this->compress_stype);
        $stardardpath = $oss_path.'@!'.$style['std'].'.jpg';
        $thumbpath = $oss_path.'@!'.$style['thumb'].'.jpg';
        return array(
            'oripath' => $oss_path,
            'stdpath' => $stardardpath,
            'thumbpath' => $thumbpath
        );
    }
    
    /**
     * 保存base64式图片到文件系统，返回文件路径
     *
     * @return array(result,oripath,thumbpath) result -1: 图片错误
     *         result -100: 保存发生错误
     */
    function saveImgData(){
        
        $img_data = $this->img_data;
        
        $pos = strpos($img_data, ','); // $img_data like 'data:image/jpeg;base64,/9j/4AAQSk...'
        if (false === $pos) {
            return AliyunUpload::FILE_NOT_SUPPORT;
        }
        
        $file_data = substr($img_data, $pos + 1);
        $file_data = base64_decode($file_data);
        $img_info = getimagesizefromstring($file_data);
        if (FALSE === $img_info) {
            return AliyunUpload::FILE_NOT_SUPPORT;
        }
        
        $width = $img_info[0];
        $height = $img_info[1];
        $imgtype = $img_info[2];
        $ratio = $width / ($height ?: 1);
        
        $extpart = '.jpg';
        switch ($imgtype) { // image type
            case IMAGETYPE_GIF:
                $extpart = '.gif';
                break;
            case IMAGETYPE_PNG:
                $extpart = '.png';
                break;
        }
        $YM = date('Ym');
        $filecode = date('d_His') . '_' . randstr() . $extpart;
        $img_dir = $this->img_dir.$this->img_folder.'/';
        $oripath = $img_dir . $YM . '/' . $filecode;
        $remote_file = $this->img_folder . '/' . $YM . '/' . $filecode;
        $oss_path = '';
        try{
            // 写ori版本
            $oripath = $this->writeImgData($oripath, $file_data);
            if($this->need_canvas){
                $temp_path = $img_dir . $YM . '/temp/' . $filecode;
                $destheight = $this->canvas_height ? $this->canvas_height : intval($this->canvas_height / $ratio);
                $new_oripath = $this->generateNewImage($oripath, $temp_path, $this->canvas_width, $imgtype, $width, $height, $destheight, $ratio);
                unlink(SIMPHP_ROOT . $oripath);
                $oripath = $new_oripath;
            }
            $ossClient = OssCommon::getOssClient();
            $bucket    = OssCommon::getBucketName();
            $oripath = SIMPHP_ROOT . $oripath;
            $ossClient->uploadFile($bucket, $remote_file, $oripath);
            $oss_path = OssCommon::getOssImgPath($remote_file);
            unlink($oripath);
        }catch (Exception $e){
        }
        if (! $oss_path || empty($oss_path)) {
            return UPload::FILE_UPLOAD_ERROR;
        }
        //通过阿里云的压缩规则处理
        $style = C('env.picstyle.'.$this->compress_stype);
        $stardardpath = $oss_path.'@!'.$style['std'].'.jpg';
        $thumbpath = $oss_path.'@!'.$style['thumb'].'.jpg';
        return array(
            'oripath' => $oss_path,
            'stdpath' => $stardardpath,
            'thumbpath' => $thumbpath
        );
    }
    
    private function writeImgData($filepath, $filedata)
    {
        $filepath_abs = SIMPHP_ROOT . $filepath;
        $filedir_abs = dirname($filepath_abs);
        if (! is_dir($filedir_abs)) {
            mkdirs($filedir_abs, 0777, TRUE);
        }
        if (FALSE !== file_put_contents($filepath_abs, $filedata)) {
            chmod($filepath_abs, 0777);
            return $filepath;
        }
        return '';
    }
    
    private function generateNewImage($oripath, $destpath, $destwidth, $imgtype, $width, $height, $destheight, $ratio)
    {
        $oripath = SIMPHP_ROOT . $oripath;
    
        $img = FALSE;
        if (is_string($oripath)) {
    
            switch ($imgtype) { // image type
                default:
                case IMAGETYPE_JPEG:
                    $img = imagecreatefromjpeg($oripath);
                    break;
                case IMAGETYPE_GIF:
                    $img = imagecreatefromgif($oripath);
                    break;
                case IMAGETYPE_PNG:
                    $img = imagecreatefrompng($oripath);
                    break;
            }
    
            if (is_resource($img)) {
    
                $rv = $this->writeImgFile($img, SIMPHP_ROOT . $destpath, $imgtype, $width, $height, $destwidth, $destheight, $ratio);
                if ($rv) {
                    return preg_replace("/^" . preg_quote(SIMPHP_ROOT, '/') . "/", '', $destpath);
                }
    
                imagedestroy($img);
            }
        }
        return '';
    }
    
    /**
     * 生成新图片
     *
     * @param unknown $srcimg
     * @param unknown $dstpath
     * @param unknown $imgtype
     * @param unknown $src_w
     * @param unknown $src_h
     * @param unknown $dst_w
     * @param unknown $dst_h
     */
    private function writeImgFile($srcimg, $dstpath, $imgtype, $src_w, $src_h, $dst_w, $dst_h, $ratio)
    {
        $rv = FALSE;
        $dstimg = imagecreatetruecolor($dst_w, $dst_h);
        $bgcolor = trim($this->bgcolor,"#");
        sscanf($bgcolor, "%2x%2x%2x", $red, $green, $blue);
        $clr = imagecolorallocate($dstimg, $red, $green, $blue);
        imagefilledrectangle($dstimg, 0, 0, $dst_w, $dst_h, $clr);
    
        if ($src_w / $dst_w > $src_h / $dst_h)
        {
            $lessen_width  = $dst_w;
            $lessen_height  = $dst_w / $ratio;
        }
        else
        {
            /* 原始图片比较高，则以高度为准 */
            $lessen_width  = $dst_h * $ratio;
            $lessen_height = $dst_h;
        }
    
        $dst_x = ($dst_w  - $lessen_width)  / 2;
        $dst_y = ($dst_h - $lessen_height) / 2;
        if (is_resource($dstimg)) {
            if (! is_dir(dirname($dstpath))) {
                mkdirs(dirname($dstpath), 0777, TRUE);
            }
            imagecopyresampled($dstimg, $srcimg, $dst_x, $dst_y, 0, 0, $lessen_width, $lessen_height, $src_w, $src_h);
            switch ($imgtype) { // image type
                default:
                case IMAGETYPE_JPEG:
                    $rv = imagejpeg($dstimg, $dstpath);
                    break;
                case IMAGETYPE_GIF:
                    $rv = imagegif($dstimg, $dstpath);
                    break;
                case IMAGETYPE_PNG:
                    $rv = imagepng($dstimg, $dstpath);
                    break;
            }
            if ($rv) {
                chmod($dstpath, 0777);
            }
            imagedestroy($dstimg);
        }
        return $rv;
    }
    
    public function buildUploadResult($result)
    {
        $ret = [
            'flag' => 'FAIL',
            'errMsg' => '上传失败，请稍后重试！'
        ];
        if (is_numeric($result)) {
            if ($result == AliyunUpload::FILE_NOT_SUPPORT) {
                $ret['errMsg'] = '上传失败，图片格式不正确！';
            }else if($result == AliyunUpload::FILE_WIDTH_INCORRET || $result == AliyunUpload::FILE_HEIGHT_INCORRET){
                $w = $this->limitWidth;
                $h = $this->limitHeight;
                $ret['errMsg'] = "当前图片尺寸要求为 (宽：$w * 高：$h) ，上传失败。";
            }
        } else {
            $filePath = $result['oripath'];
            if ($filePath) {
                $ret = [
                    'flag' => 'SUC',
                    'result' => $filePath,
                    'stdpath' =>  (isset($result['stdpath']) ? $result['stdpath'] : ''),
                    'thumb' => (isset($result['thumbpath']) ? $result['thumbpath'] : '')
                ];
            }
        }
        return $ret;
    }
}

?>