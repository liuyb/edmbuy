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
	 * 接口调用返回码
	 * @var array
	 */
	static $_code = array(
		'1'   => '发送短信成功',
		'0'   => '调用接口失败',
		'-1'  => '发送失败',
		'-2'  => '帐户信息错误',
		'-3'  => '用户或密码错误',
		'-4'  => '不是普通帐户',
		'-5'  => '发送短信内容为空',
		'-6'  => '短信内容过长',
		'-7'  => '发送号码为空',
		'-8'  => '余额不足',
		'-9'  => '接收数据失败',
		'-10' => '发送失败',
		'-11' => '定时发送时间或格式错误',
		'-12' => '定时发送时间失败',
		'-13' => '内容信息含关键字',
		'-14' => '信息内容格式与限定格式不符',
		'-15' => '信息没带签名',
		'-16' => '黑名单号码',
		'-30' => '非绑定IP',
		'-100'=> '客户端获取状态失败(系统预留)'
	);
	
	/**
	 * 
	 * @param integer $code
	 * @return string
	 */
	static function code_msg($code) {
		return isset(self::$_code[$code]) ? self::$_code[$code] : '其他错误';
	}
	
    /**
     * <SendState xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns="http://tempuri.org/">
     * <FailPhone/>
     * <State>1</State>
     * <Id>69958941</Id>
     * </SendState>
     * @param $mobile
     * @param $content
     * @return integer
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
        
        $content = sprintf($content, $code, $imgCode);
        if($type=="reg_success"){
        	$pwd = $_SESSION['password'];
        	$config = C("storage.cookie.mch");
        	$url = $config['edmmch.fxmapp.com'];
        	$content =sprintf($content,$url,$mobile,$pwd);
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
        	return 0;
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
        $data['result'] = $xmlObj->State;
        D()->insert($tablename, $data);
        return $data['result'];
    }
    
    /**
     * 发送验证码
     * @param string $mobile
     * @param string $type
     * @param string $vcode
     * @return integer
     */
    static function sendVCode($mobile, $type, $vcode)
    {
    	error_reporting(0);
    	$SmsConfig    = C("port.sms");
    	$contentArray = C("msg.sms");
    	$content      = isset($contentArray[$type]) ? $contentArray[$type] : '';
    	if (!$content) {
    		return 0;
    	}
    
    	$content   = sprintf($content, $vcode);
    	$uname     = $SmsConfig['username'];
    	$smsnumber = $SmsConfig['smsnumber'];
    	$pwd       = $SmsConfig['userpwd'];
    	$pwd       = strtoupper(md5($pwd));
    	$url       = $SmsConfig['url'];
    	$text      = urlencode($content);
    	$time      = simphp_time();
    	$url = "{$url}:8180/service.asmx/SendMessage?Id={$smsnumber}&Name={$uname}&Psw={$pwd}&Message={$text}&Phone={$mobile}&Timestamp={$time}";
    	$sendState = file_get_contents($url);
    	if (FALSE===$sendState) {
    		return 0;
    	}
    	$xmlObj = simplexml_load_string($sendState, 'SimpleXMLElement');
    	$data = [];
    	$data['receivePhone']= $mobile;
    	$data['type']        = $type;
      $data['touchTime']   = $time;
      $data['overdueTime'] = $data['touchTime'] + 60 * 1;
    	$data['verifyCode']  = $vcode;
    	$data['sendContent'] = $content;
    	$data['result']      = $xmlObj->State;
    	D()->insert("`shp_usersms_log`", $data);
    	return $data['result'];
    }

}

/*----- END FILE: class.Tpl.php -----*/