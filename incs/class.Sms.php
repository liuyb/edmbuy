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
     * <FailPhone/>
     * <State>1</State>
     * <Id>69958941</Id>
     * </SendState>
     * @param $mobile
     * @param $content
     * @return bool
     */
    static function sendSms($mobile, $type, $verify = true)
    {
        error_reporting(0);
        @header("content-type:text/xml;charset=gb2312");
        $SmsConfig    = C("port.sms");
        $contentArray = C("msg.sms");
        $content      = $contentArray[$type];
        $code         = rand_code();
        $_SESSION[$type] = $code;
        if (!$verify) {
        	$imgCode = rand_code();
        } else {
        	$imgCode = $_SESSION['verifycode'];
        }
        
        $content = sprintf($content, $imgCode, $code);
        if($type=="reg_success"){
        	$pwd = $_SESSION['password'];
        	$config = C("storage.cookie.mch");
        	$url = $config['edmmch.fxmapp.com'];
        	$content =sprintf($content,$imgCode,$url,$pwd);
        }
        $uname = $SmsConfig['username'];
        $smsnumber = $SmsConfig['smsnumber'];
        $pwd = $SmsConfig['userpwd'];
        $url = $SmsConfig['url'];
        $text = urlencode($content);
        $time = time();
        $url = "{$url}:8180/service.asmx/SendMessage?Id={$smsnumber}&Name={$uname}&Psw={$pwd}&Message={$text}&Phone={$mobile}&Timestamp={$time}";
        $sendState = file_get_contents($url);
        if (FALSE===$sendState) {
        	return false;
        }
        $xmlObj = simplexml_load_string($sendState, 'SimpleXMLElement');
        $tablename = "`shp_usersms_log`";
        $data = [];
        $data['receivePhone'] = $mobile;
        $data['type'] = $type;
        $data['touchTime']   = simphp_time();
        $data['overdueTime'] = $data['touchTime'] + 60 * 1;
        $data['verifyCode']  = $code;
        $data['sendContent'] = $content;
        $result = ($xmlObj->State == "1") ? 1 : 0;
        $data['result'] = $result;
        D()->insert($tablename, $data);
        return $result;
    }
    
    static function sendVCode($mobile, $type, $vcode)
    {
    	error_reporting(0);
    	$SmsConfig    = C("port.sms");
    	$contentArray = C("msg.sms");
    	$content      = isset($contentArray[$type]) ? $contentArray[$type] : '';
    	if (!$content) {
    		return false;
    	}
    
    	$content   = sprintf($content, $vcode);
    	$uname     = $SmsConfig['username'];
    	$smsnumber = $SmsConfig['smsnumber'];
    	$pwd       = $SmsConfig['userpwd'];
    	$pwd       = strtoupper(md5($pwd));
    	$url       = $SmsConfig['url'];
    	$text      = urlencode($content);
    	$time      = time();
    	$url = "{$url}:8180/service.asmx/SendMessage?Id={$smsnumber}&Name={$uname}&Psw={$pwd}&Message={$text}&Phone={$mobile}&Timestamp={$time}";
    	$sendState = file_get_contents($url);
    	if (FALSE===$sendState) {
    		return false;
    	}
    	$xmlObj = simplexml_load_string($sendState, 'SimpleXMLElement');
    	$data = [];
    	$data['receivePhone'] = $mobile;
    	$data['type'] = $type;
      $data['touchTime']   = simphp_time();
      $data['overdueTime'] = $data['touchTime'] + 60 * 1;
    	$data['verifyCode']  = $vcode;
    	$data['sendContent'] = $content;
    	$result = ($xmlObj->State == "1") ? 1 : 0;
    	$data['result'] = $result;
    	D()->insert("`shp_usersms_log`", $data);
    	return $result;
    }

}

/*----- END FILE: class.Tpl.php -----*/