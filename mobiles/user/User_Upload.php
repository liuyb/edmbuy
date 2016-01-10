<?php
defined('IN_SIMPHP') or die('Access Denied');

class User_Upload{
    
    
    /**
     * 保存base64式图片到文件系统，返回文件路径
     *
     * @param string $img_data
     * @param string $img_dir 上传文件的目录
     * @return number | string
     *   -1: 图片错误
     *   -100: 保存发生错误
     *   string: 正确，保存的文件路径
     */
    static function saveImgData($img_data, $img_dir) {
    
        $pos = strpos($img_data, ','); //$img_data like 'data:image/jpeg;base64,/9j/4AAQSk...'
        if (false===$pos) {
            return -1;
        }
    
        $file_data = substr($img_data, $pos+1);
        $file_data = base64_decode($file_data);
        $img_info  = getimagesizefromstring($file_data);
        if(FALSE===$img_info){
            return -1;
        }
    
        $width    = $img_info[0];
        $height   = $img_info[1];
        $imgtype  = $img_info[2];
        $ratio    = $width / ($height ? : 1);
    
        $extpart  = '.jpg';
        switch ($imgtype) { //image type
            case IMAGETYPE_GIF:
                $extpart = '.gif';
                break;
            case IMAGETYPE_PNG:
                $extpart = '.png';
                break;
        }
        $uid = $GLOBALS['user']->uid;
        //文件名
        $filecode     = $uid;
        
        $dstpath = File::gen_unique_dir('id', $uid, $img_dir);
        $dstpath = $dstpath.$filecode.$extpart;
        //写ori版本
        $oripath = self::writeImgData($dstpath, $file_data);
        return $oripath; 
    }
    
    private static function writeImgData($filepath, $filedata) {
        $filepath_abs= SIMPHP_ROOT . $filepath;
        $filedir_abs = dirname($filepath_abs);
        if(!is_dir($filedir_abs)) {
            mkdirs($filedir_abs, 0777, TRUE);
        }
        if (FALSE !== file_put_contents($filepath_abs, $filedata)) {
            chmod($filepath_abs, 0777);
            return $filepath;
        }
        return -100;
    }
    
    /* private static function writeImgFile($srcimg, $dstpath, $imgtype, $src_w, $src_h, $dst_w, $dst_h) {
        $rv = FALSE;
        $dstimg = imagecreatetruecolor($dst_w, $dst_h);
        if (is_resource($dstimg)) {
            if(!is_dir(dirname($dstpath))) {
                mkdirs(dirname($dstpath), 0777, TRUE);
            }
            imagecopyresampled($dstimg, $srcimg, 0, 0, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
            switch ($imgtype) { //image type
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
                chmod($dstpath, 0444);
            }
            imagedestroy($dstimg);
        }
        return $rv;
    } */
}

?>