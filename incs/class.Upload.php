<?php
defined('IN_SIMPHP') or die('Access Denied');

/**
 * 上传公共类
 *
 * @author Jean
 *        
 */
class Upload
{

    const FOLDER_ORI = 'original';

    const FOLDER_THUMB = 'thumb';

    const FILE_NOT_SUPPORT = - 1;

    const FILE_UPLOAD_ERROR = - 100;

    const FILE_UPLOAD_SUCC = 200;
    
    // 图片base64数据
    private $img_data;
    
    // 图片存放目录
    private $img_dir;
    
    // 是否生成缩略图
    public $has_thumb = false;
    
    // 缩略图宽度
    public $thumbwidth;
    
    // 固定ID 用户头像二维码等用固定ID存放
    public $fixed_id;

    public function __construct($img_data, $img_dir)
    {
        $this->img_data = $img_data;
        $this->img_dir = $img_dir;
    }

    /**
     * 保存base64式图片到文件系统，返回文件路径
     *
     * @return array(result,oripath,thumbpath) result -1: 图片错误
     *         result -100: 保存发生错误
     */
    function saveImgData()
    {
        $img_data = $this->img_data;
        $img_dir = $this->img_dir;
        
        $pos = strpos($img_data, ','); // $img_data like 'data:image/jpeg;base64,/9j/4AAQSk...'
        if (false === $pos) {
            return UPload::FILE_NOT_SUPPORT;
        }
        
        $file_data = substr($img_data, $pos + 1);
        $file_data = base64_decode($file_data);
        $img_info = getimagesizefromstring($file_data);
        if (FALSE === $img_info) {
            return UPload::FILE_NOT_SUPPORT;
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
        // 文件名
        $oripath = '';
        $thumbpath = '';
        if ($this->fixed_id) {
            $filecode = $this->fixed_id;
            $dstpath = File::gen_unique_dir('id', $this->fixed_id, $img_dir);
            $oripath = $dstpath . $filecode . $extpart;
        } else {
            $filecode = date('d_His') . '_' . randstr();
            $oripath = $img_dir . Upload::FOLDER_ORI . '/' . date('Ym') . '/'. $filecode . $extpart;;
            $thumbpath = $img_dir . Upload::FOLDER_THUMB . '/' . date('Ym') . '/'. $filecode . $extpart;;
        }
        
        // 写ori版本
        $oripath = $this->writeImgData($oripath, $file_data);
        if ($thumbpath) {
            // thumb版本
            $thumbpath = $this->generateThumb($oripath, $thumbpath, $imgtype, $width, $height, $ratio);
        }
        if (! $oripath || empty($oripath)) {
            return UPload::FILE_UPLOAD_ERROR;
        }
        return array(
            'oripath' => $oripath,
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

    private function generateThumb($oripath, $thumbpath, $imgtype, $width, $height, $ratio)
    {
        $img = FALSE;
        if (is_string($oripath)) {
            
            $oripath = SIMPHP_ROOT . $oripath;
            
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
                $thumbwidth = $this->thumbwidth;
                // thumb版本
                if ($width > $thumbwidth) { // 只有宽度大于$thumbwidth才需要生成缩略图，否则直接用原图做缩略图
                    $rv = $this->writeImgFile($img, SIMPHP_ROOT . $thumbpath, $imgtype, $width, $height, $thumbwidth, intval($thumbwidth / $ratio));
                    if ($rv) {
                        return preg_replace("/^" . preg_quote(SIMPHP_ROOT, '/') . "/", '', $thumbpath);
                    }
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
    private function writeImgFile($srcimg, $dstpath, $imgtype, $src_w, $src_h, $dst_w, $dst_h)
    {
        $rv = FALSE;
        $dstimg = imagecreatetruecolor($dst_w, $dst_h);
        if (is_resource($dstimg)) {
            if (! is_dir(dirname($dstpath))) {
                mkdirs(dirname($dstpath), 0777, TRUE);
            }
            imagecopyresampled($dstimg, $srcimg, 0, 0, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
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
            if ($result == Upload::FILE_NOT_SUPPORT) {
                $ret['errMsg'] = '上传失败，图片格式不正确！';
            }
        } else {
            $filePath = $result['oripath'];
            if ($filePath) {
                $ret = [
                    'flag' => 'SUC',
                    'result' => $filePath,
                    'thumb' => (isset($result['thumbpath']) ? $result['thumbpath'] : '')
                ];
            }
        }
        return $ret;
    }
}