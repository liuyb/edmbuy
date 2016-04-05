<?php
/**
 * Template Class functions for registering
 *
 * @author hc_edm
 */
defined('IN_SIMPHP') or die('Access Denied');

class Sms
{
    /**
     * <SendState xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns="http://tempuri.org/">
    <FailPhone/>
    <State>1</State>
    <Id>69958941</Id>
    </SendState>
     * @param $mobile
     * @param $content
     * @return bool
     */
    static function sendSms($mobile,$type)
    {
        error_reporting(0);
        @header("content-type:text ml;charset=gb2312");
        $SmsConfig = C("port");
        $content=C("msg.{$type}");
        $code=self::rand_code();
        $_SESSION['forgetPwd']=$code;
        $content=sprintf($content,$code);
        $uname = $SmsConfig['username'];
        $smsnumber = $SmsConfig['smsnumber'];
        $pwd = $SmsConfig['userpwd'];
        $url = $SmsConfig['url'];
        $text = urlencode($content);
        $time = time();
        $url = "{$url}:8180/service.asmx/SendMessage?Id={$smsnumber}&Name={$uname}&Psw={$pwd}&Message={$text}&Phone={$mobile}&Timestamp={$time}";
        try {
            $sendState = file_get_contents($url);
        } catch (Exception $e) {
            return false;
        }
        $xmlObj =  simplexml_load_string($sendState,'SimpleXMLElement');
        $tablename="`shp_usersms_log`";
        $data ['receivePhone'] = $mobile;
        $data ['overdueTime'] = time() + 60 * 1;
        $data ['verifyCode'] = $code;
        $data ['sendContent'] = $content;
        $result=($xmlObj->State == "1") ? 1 : 0;
        $data['result']=$result;
        D()->insert($tablename,$data);
        return $result;
    }
    /**
     * todo 放入函数库
     * 生成6位数随机验证码
     * @param int $length
     * @return string
     */
    static function rand_code($length = 6) {
        $chars = '123456789';
        for ($i = 0, $count = strlen($chars); $i < $count; $i++) {
            $arr[$i] = $chars[$i];
        }
        mt_srand((double) microtime() * 1000000);
        shuffle($arr);
        return  substr(implode('', $arr), 3, $length);
    }
}

/*----- END FILE: class.Tpl.php -----*/