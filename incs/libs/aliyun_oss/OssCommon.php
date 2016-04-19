<?php

require_once __DIR__ . '/autoload.php';

use OSS\OssClient;
use OSS\Core\OssException;

/**
 * Class OssCommon
 *
 * 示例程序【Samples/*.php】 的OssCommon类，用于获取OssClient实例和其他公用方法
 */
class OssCommon
{
	  
    //Develop environment
    const endpoint_foreign= 'oss-cn-hangzhou.aliyuncs.com';
    const endpoint_domain = 'edmdev.oss-cn-hangzhou.aliyuncs.com';
    const endpoint_domain_image = 'edmdev.img-cn-hangzhou.aliyuncs.com';
    const endpoint        = 'oss-cn-hangzhou.aliyuncs.com';
    const bucket          = 'edmdev';
	  
    //Production environment
    /*
    const endpoint_foreign= 'oss-cn-hangzhou.aliyuncs.com';
    const endpoint_domain = 'oss.edmbuy.com';
    const endpoint        = 'vpc100-oss-cn-hangzhou.aliyuncs.com';
    //const endpoint        = 'oss-cn-hangzhou-internal.aliyuncs.com';
    const bucket          = 'edmbuy';
    */
    
    //Access key
    const accessKeyId     = 'DezqmGVURg7snFsD';
    const accessKeySecret = 'HhifDR04j6W8We92Nz7mygAxEN90zV';

    /**
     * 根据Config配置，得到一个OssClient实例
     *
     * @return OssClient 一个OssClient实例
     */
    public static function getOssClient()
    {
        try {
            $ossClient = new OssClient(self::accessKeyId, self::accessKeySecret, self::endpoint, false);
        } catch (OssException $e) {
            printf(__FUNCTION__ . "creating OssClient instance: FAILED\n");
            printf($e->getMessage() . "\n");
            return null;
        }
        return $ossClient;
    }

    public static function getBucketName()
    {
        return self::bucket;
    }
    
    public static function getOssPath($oss_file) {
    	return "http://".self::endpoint_domain."/{$oss_file}";;
    }
    
    public static function getOssImgPath($oss_file) {
        return "http://".self::endpoint_domain_image."/{$oss_file}";;
    }

    /**
     * 工具方法，创建一个存储空间，如果发生异常直接exit
     */
    public static function createBucket()
    {
        $ossClient = self::getOssClient();
        if (is_null($ossClient)) exit(1);
        $bucket = self::getBucketName();
        $acl = OssClient::OSS_ACL_TYPE_PUBLIC_READ;
        try {
            $ossClient->createBucket($bucket, $acl);
        } catch (OssException $e) {

            $message = $e->getMessage();
            if (\OSS\Core\OssUtil::startsWith($message, 'http status: 403')) {
                echo "Please Check your AccessKeyId and AccessKeySecret" . "\n";
                exit(0);
            } elseif (strpos($message, "BucketAlreadyExists") !== false) {
                echo "Bucket already exists. Please check whether the bucket belongs to you, or it was visited with correct endpoint. " . "\n";
                exit(0);
            }
            printf(__FUNCTION__ . ": FAILED\n");
            printf($e->getMessage() . "\n");
            return;
        }
        print(__FUNCTION__ . ": OK" . "\n");
    }

    public static function println($message)
    {
        if (!empty($message)) {
            echo strval($message) . "\n";
        }
    }
}
