<?php
/**
 * 微信接口相关类
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

require_once (SIMPHP_INCS . '/libs/ApiRequest/class.ApiRequest.php');

class Weixin {
  
  /**
   * Some configure values
   */
	public $appId         = '';
	public $appSecret     = '';
	public $token         = '';
	public $encodingAesKey= '';
	public $paySignKey    = '';
	public $caFile        = '';
	public $siteUrl       = '';
	
	/**
	 * Allow weixin public accounts, and default account
	 * @var array
	 */
	public static $defaultAccount = 'edmbuy';   //default weixin account
	public static $allowAccounts  = ['edmbuy']; //allowed weixin accounts set
	
	/**
	 * Weixin OAuth2 scope
	 * @var constant
	 */
	const OAUTH_BASE    = 'base';
	const OAUTH_DETAIL  = 'detail';
	
	/**
	 * Allow weixin OAuth2 scopes
	 * @var array
	 */
	public static $allowOAuthScopes = [self::OAUTH_BASE, self::OAUTH_DETAIL];

	/**
	 * Weixin constant name
	 * @var constant
	 */
	const PLUGIN_JSSDK  = 'jssdk';
	const PLUGIN_JSADDR = 'jsaddr';
	const PLUGIN_QRCODE = 'qrcode';
	const PLUGIN_MSGSEND= 'msgsend';
	
	/**
	 * Message type
	 * @var constant
	 */
	const MSG_TYPE_TEXT  = 'text';
	const MSG_TYPE_CUSTOMER = 'customer';
	const MSG_TYPE_IMAGE = 'image';
	const MSG_TYPE_VOICE = 'voice';
	const MSG_TYPE_VIDEO = 'video';
	const MSG_TYPE_MUSIC = 'music';
	const MSG_TYPE_NEWS  = 'news';
	const MSG_TYPE_NEWS_ITEM = 'news_item';
	const MSG_TYPE_CARD  = 'wxcard';
	const MSG_TYPE_MPNEWS= 'mpnews';
	const MSG_TYPE_MPVIDEO = 'mpvideo';
	
	/**
	 * QR request constant
	 * @var constant
	 */
	const QR_SCENE           = 1;
	const QR_LIMIT_SCENE     = 2;
	const QR_LIMIT_STR_SCENE = 3;
	
	/**
	 * Max persistent QR code nums
	 * @var constant
	 */
	const QR_MAX_PERSISTENT  = 100000;
	
	/**
	 * All weixin plugins name
	 * @var array
	 */
	public static $plugins = array(self::PLUGIN_JSSDK, self::PLUGIN_JSADDR, self::PLUGIN_QRCODE, self::PLUGIN_MSGSEND);
	
	/**
	 * WeixinHelper instance
	 * @var WeixinHelper
	 */
	public $helper;
	
	/**
	 * OAuth2 instance
	 * @var OAuth2
	 */
	public $oauth;

	/**
	 * WeixinJSSDK instance
	 * @var WeixinJSSDK
	 */
	public $jssdk;
	
	/**
	 * WeixinJSAddr instance
	 * 
	 * @var WeixinJSAddr
	 */
	public $jsaddr;
	
	/**
	 * WeixinQRCode instance
	 * @var WeixinQRCode
	 */
	public $qrcode;
	
	/**
	 * WeixinMsgSend instance
	 * @var WeixinMsgSend
	 */
	public $msgsend;
	
	/**
	 * API address url prefix
	 * @var array
	 */
	public static $apiUrlPrefix = array(
	  'api_cgi'   => 'https://api.weixin.qq.com/cgi-bin',
	  'api_sns'   => 'https://api.weixin.qq.com/sns',
	  'open_conn' => 'https://open.weixin.qq.com/connect',
	  'qyapi_cgi' => 'https://qyapi.weixin.qq.com/cgi-bin',
	);
	
	/**
	 * 响应消息结构
	 * @var array
	 */
	public static $msgTpl = array(
	  //文本消息
	  'text' => "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[%s]]></Content>
</xml>",
	  //文本消息(转发至多客服)
	  'customer' => "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[transfer_customer_service]]></MsgType>
</xml>",
	  //图片消息
	  'image'=> "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[image]]></MsgType>
<Image>
<MediaId><![CDATA[%s]]></MediaId>
</Image>
</xml>",
	  //语音消息
	  'voice'=> "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[voice]]></MsgType>
<Voice>
<MediaId><![CDATA[%s]]></MediaId>
</Voice>
</xml>",
	  //视频消息
	  'video'=> "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[video]]></MsgType>
<Video>
<MediaId><![CDATA[%s]]></MediaId>
<Title><![CDATA[%s]]></Title>
<Description><![CDATA[%s]]></Description>
</Video>
</xml>",
	  //音乐消息
	  'music'=> "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[music]]></MsgType>
<Music>
<Title><![CDATA[%s]]></Title>
<Description><![CDATA[%s]]></Description>
<MusicUrl><![CDATA[%s]]></MusicUrl>
<HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
<ThumbMediaId><![CDATA[%s]]></ThumbMediaId>
</Music>
</xml>",
	  //图文消息 - 总结构, 注意：ArticleCount <=10
	  'news' => "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[news]]></MsgType>
<ArticleCount>%d</ArticleCount>
<Articles>%s</Articles>
</xml>",
	  //图文消息 - 单条记录
	  'news_item' => "<item>
<Title><![CDATA[%s]]></Title>
<Description><![CDATA[%s]]></Description>
<PicUrl><![CDATA[%s]]></PicUrl>
<Url><![CDATA[%s]]></Url>
</item>"
	);

	/**
	 * 初始化配置
	 * @param array $plugins, 额外的插件，如 'jssdk', 'wxpay' 等
	 * @param string $target, 目标平台
	 */
	private function init(Array $plugins = array(), $target = '')
	{
		if (''===$target) {
			$target = self::$defaultAccount;
		}
	  if (!in_array($target, self::$allowAccounts)) {
	    throw new Exception("Weixin public account not allowed: {$target}");
	  }
	  
	  $wx_config       = Config::get('api.weixin_'.$target);
	  
		$this->appId     = $wx_config['appId'];
		$this->appSecret = $wx_config['appSecret'];
		$this->token     = $wx_config['token'];
		$this->encodingAesKey = $wx_config['encodingAesKey'];
		$this->paySignKey= $wx_config['paySignKey'];
		$this->caFile    = SIMPHP_INCS.'/incs/libs/weixin/cacerts.pem';
		
		$this->siteUrl   = Config::get('env.site.mobile');
		
		$this->helper    = new WeixinHelper($this);
		$this->oauth     = new OAuth2(array('client_id'=>$this->appId,'secret_key'=>$this->appSecret,'response_type'=>'code','scope'=>'snsapi_base','state'=>'base'),'weixin');
		if (in_array(self::PLUGIN_JSSDK, $plugins)) {
		  $this->jssdk   = new WeixinJSSDK($this->appId, $this);
		}
		if (in_array(self::PLUGIN_JSADDR, $plugins)) {
		  $this->jsaddr  = new WeixinJSAddr($this->appId, $this);
		}
		if (in_array(self::PLUGIN_QRCODE, $plugins)) {
		  $this->qrcode  = new WeixinQRCode($this->appId, $this);
		}
		if (in_array(self::PLUGIN_MSGSEND, $plugins)) {
		  $this->msgsend = new WeixinMsgSend($this->appId, $this);
		}
	}
	
	/**
	 * 初始化构造函数
	 * @param array $plugins, 额外的插件，如 'jssdk', 'wxpay', 'qrcode' 等
	 * @param string $target, 目标平台
	 */
	public function __construct(Array $plugins = array(), $target = '')
	{
		$this->init($plugins, $target); //该句必须出现在所有对外方法的最开始
	}
	
	/**
	 * 验证接口地址有效性
	 */
	public function valid()
	{
	  $echoStr = $_GET['echostr'];
    if($this->checkSignature()){
      echo $echoStr;
    }
    else {
      echo '';
    }
    exit;
	}

	/**
	 * 检查签名
	 * @return boolean
	 */
  public function checkSignature()
  {
    $signature = $_GET["signature"];
    $timestamp = $_GET["timestamp"];
    $nonce     = $_GET["nonce"];
    
    $token  = $this->token;
    $tmpArr = array($token, $timestamp, $nonce);
    sort($tmpArr, SORT_STRING); //use SORT_STRING rule
    $tmpStr = implode( $tmpArr );
    $tmpStr = sha1( $tmpStr );
    
    if( $tmpStr == $signature ){
      return TRUE;
    }
    return FALSE;
  }
  
  /**
   * 响应微信服务器回调消息
   */
  public function responseMsg()
  {
    $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
    $respMsg = '';
    if (!empty($postStr)){
      /* libxml_disable_entity_loader is to prevent XML eXternal Entity Injection,
       the best way is to check the validity of xml by yourself */
      libxml_disable_entity_loader(TRUE);
      $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
      switch ($postObj->MsgType) {
        case 'event':
          $respMsg = $this->dealEventMsg($postObj);
          break;
        case 'text':
        case 'customer':
          $respMsg = $this->dealTextMsg($postObj);
          break;
        case 'image':
          $respMsg = $this->dealImageMsg($postObj);
          break;
        case 'location':
          break;
        case 'voice':
          $respMsg = $this->dealVoiceMsg($postObj);
          break;
        case 'video':
          break;
        case 'link':
          break;
        default:
          //unknown msg type
          break;
      }
    }
    echo $respMsg;
    exit;
  }
  
  /**
   * 处理事件消息
   *
   * @param SimpleXMLElement $postObj
   * @return response msg string
   */
  private function dealEventMsg($postObj)
  {
    $fromUserName= $postObj->FromUserName;
    $toUserName  = $postObj->ToUserName;
    $openid      = $fromUserName;
    $reqtime     = intval($postObj->CreateTime);
    //trace_debug('weixin_event_msg', $postObj);
    
    $contentText = '';
    $responseText= '';
    switch ($postObj->Event) {
    	case 'SCAN':
      case 'subscribe':
        $contentText = $this->helper->onSubscribe($openid, $reqtime, $postObj->Event, !empty($postObj->EventKey)?$postObj->EventKey:null, !empty($postObj->Ticket)?$postObj->Ticket:null);
        break;
      case 'unsubscribe':
        $this->helper->onUnsubscribe($openid, $reqtime);
        break;
      case 'CLICK':
      	//return '';//临时屏蔽
      	$contentText = '';
        switch ($postObj->EventKey) {
          case '200': //最新文章
            $latest_news = $this->helper->latestArticles();
            $item  = '';
            foreach ($latest_news AS $news) {
              $extra = array();
              $extra['title']       = $news['title'];
              $extra['description'] = $news['digest'];
              $extra['picUrl']      = $this->siteUrl.$news['thumb_media_url'];
              $extra['url']         = $news['url'];
              $item .= self::packMsg(self::MSG_TYPE_NEWS_ITEM, '', '', $extra);
            }
            $responseText = self::packMsg(self::MSG_TYPE_NEWS, $fromUserName, $toUserName, array('articleCount'=>count($latest_news), 'articles'=>$item));
            return $responseText;
            break;
          case '300': //官方客服
            //$contentText = '点击左下角键盘图标，切换到交互模式可联系官方客服。';
            $contentText = '客服微信: edmbuykf1001';
            break;
          case '301': //联系小蜜
            $contentText = $this->helper->contact();
            break;
        }
        break;
      case 'LOCATION':
        $longitude = $postObj->Longitude;
        $latitude  = $postObj->Latitude;
        $precision = $postObj->Precision;
        $this->helper->onLocation($openid, $reqtime, $longitude, $latitude, $precision);
        break;
      case 'VIEW':
        break;
      case 'MASSSENDJOBFINISH':
        break;
      default:
        //未知事件
    }
    
    if (''!=$contentText) { //默认返回text文本
      $responseText = self::packMsg(self::MSG_TYPE_TEXT, $fromUserName, $toUserName, array('content' => $contentText));
    }
    return $responseText;
  }
  
  /**
   * 处理文本消息
   * 
   * @param SimpleXMLElement $postObj
   * @param boolean          $is_voice
   * @return response msg string
   */
  private function dealTextMsg($postObj, $is_voice = FALSE)
  {
    $fromUserName = $postObj->FromUserName;
    $toUserName   = $postObj->ToUserName;
    $keyword      = trim($postObj->Content);
    $openid       = $fromUserName;
    $reqtime      = intval($postObj->CreateTime);
    
    /*
    if ('odyFWsxLsxs9U2P6Z3sV4S7O7D5I'==$openid) {
    	trace_debug('weixin_reply_'.($is_voice?'voice':'text'), $keyword);
    	$responseText = self::packMsg(self::MSG_TYPE_TEXT, $fromUserName, $toUserName, array('content' => $this->helper->about(1)));
    	return $responseText;
    }
    */
    
    $responseText = '';
    $queryResult  = $this->helper->onTextQuery($keyword, $result_type);
    if ('string'==$result_type) {
    	if (!empty($queryResult)) {
    		$responseText = self::packMsg(self::MSG_TYPE_TEXT, $fromUserName, $toUserName, array('content' => $queryResult));
    	}
    	elseif (in_array($keyword, ['商家认证','店铺审核','产品审核'])) {
    		$mediaId = '';
    		$mediaUrl= '';
    		switch ($keyword) {
    			case '商家认证':
    				$mediaId = 'g-Q3naBWic4T5UnQMO_GmyQe96JrZVQE_62yiqDxZNU';
    				$mediaUrl= 'http://mmbiz.qpic.cn/mmbiz/TmfzfPupvpR3Mpria2WrASRoziayIeDA4Ukn9pTCCM7fp0g4DI1ib8EzN1BX9xsMDWcTQnHatxukzUOiacgvBjt2kQ/0?wx_fmt=jpeg';
    				break;
    			case '店铺审核':
    				$mediaId = 'g-Q3naBWic4T5UnQMO_Gm0lazCOVgTiaYKfLGLtLbWY';
    				$mediaUrl= 'http://mmbiz.qpic.cn/mmbiz/TmfzfPupvpR3Mpria2WrASRoziayIeDA4UMJzF9cLC3FsyrtWZJOoH3LXSwIQBeb5gJxk4O6yp9mdYvVv9Cywg3A/0?wx_fmt=jpeg';
    				break;
    			case '产品审核':
    				$mediaId = 'g-Q3naBWic4T5UnQMO_Gm2ZJNRQIQ-MjVjyrtuKcosk';
    				$mediaUrl= 'http://mmbiz.qpic.cn/mmbiz/TmfzfPupvpR3Mpria2WrASRoziayIeDA4UXWbhH7pkhUjrhTzEiaiaqh3949gVahTjjurBpbMuupRFqEWGpJ6VdU1w/0?wx_fmt=jpeg';
    				break;
    		}
    		if ($mediaId) {
    			$responseText = self::packMsg(self::MSG_TYPE_IMAGE, $fromUserName, $toUserName, array('mediaId' => $mediaId));
    			trace_debug('weixin_reply_image', $responseText);
    		}
    	}
    	else {
    		$responseText = self::packMsg(self::MSG_TYPE_CUSTOMER/*要转到多客服系统*/, $fromUserName, $toUserName, array('content' => $queryResult));
    	}
    }
    else {
    	if ('arr_article'==$result_type) { //最新文章
    		$item  = '';
    		foreach ($queryResult AS $news) {
    			$extra = array();
    			$extra['title']       = $news['title'];
    			$extra['description'] = $news['digest'];
    			$extra['picUrl']      = $this->siteUrl.$news['thumb_media_url'];
    			$extra['url']         = $news['url'];
    			$item .= self::packMsg(self::MSG_TYPE_NEWS_ITEM, '', '', $extra);
    		}
    		$responseText = self::packMsg(self::MSG_TYPE_NEWS, $fromUserName, $toUserName, array('articleCount'=>count($queryResult), 'articles'=>$item));
    	}
    	elseif ('arr_goods'==$result_type) { //匹配搜索的商品
    		$item  = '';
    		foreach ($queryResult AS $goods) {
    			$extra = array();
    			$extra['title']       = $goods['goods_name'];
    			$extra['description'] = $goods['goods_name'];
    			$extra['picUrl']      = preg_match('/^http:\/\//', $goods['goods_thumb']) ? $goods['goods_thumb'] : Goods::goods_picurl($goods['goods_thumb']);
    			$extra['url']         = Goods::goods_url($goods['goods_id'], TRUE);
    			$item .= self::packMsg(self::MSG_TYPE_NEWS_ITEM, '', '', $extra);
    		}
    		$responseText = self::packMsg(self::MSG_TYPE_NEWS, $fromUserName, $toUserName, array('articleCount'=>count($queryResult), 'articles'=>$item));
    	}
    }
    
    return $responseText;
  }
  
  /**
   * 处理语音识别结果
   * 
   * @param SimpleXMLElement $postObj
   * @return response msg string
   */
  private function dealVoiceMsg($postObj)
  {
    $fromUserName = $postObj->FromUserName;
    $toUserName   = $postObj->ToUserName;
    $keyword      = trim($postObj->Recognition);
    
    if (''==$keyword) {
      $contentText = "抱歉，没听清你说什么，请重试？";
      $responseText= self::packMsg(self::MSG_TYPE_TEXT, $fromUserName, $toUserName, array('content' => $contentText));
      return $responseText;
    }
    
    $postObj->Content = rtrim($keyword,'！');
    return $this->dealTextMsg($postObj, TRUE);
  }
  
  /**
   * 处理图片类消息
   * 
   * @param SimpleXMLElement $postObj
   * @return response msg string
   */
  private function dealImageMsg($postObj)
  {
    return '';    
  }
  
  /**
   * 打包组合响应消息
   * 
   * @param string $msgType      消息类型，可选值：text, image, voice, video, music, news, news_item
   * @param string $toUserName   发送目标用户openid
   * @param string $fromUserName 发送源openid(自身)
   * @param string $extra <br>
   *   $msgType = 'text' : $extra['content' => 'xx'] <br>
   *   $msgType = 'customer' : $extra['content' => 'xx'] <br>
   *   $msgType = 'image': $extra['mediaId' => 'xx'] <br>
   *   $msgType = 'voice': $extra['mediaId' => 'xx'] <br>
   *   $msgType = 'video': $extra['mediaId' => 'xx', 'title' => 'xx', 'description' => 'xx'] <br>
   *   $msgType = 'music': $extra['title' => 'xx', 'description' => 'xx', 'musicUrl' => 'xx', 'hqMusicUrl' => 'xx', 'thumbMediaId' => 'xx'] <br>
   *   $msgType = 'news': $extra['articleCount' => 'xx', 'articles' => 'xx'] <br>
   *   $msgType = 'news_item': $extra['title' => 'xx', 'description' => 'xx', 'picUrl' => 'xx', 'url' => 'xx'] <br><br>
   * @return string
   */
  public static function packMsg($msgType, $toUserName, $fromUserName, $extra = array())
  {
    if (!in_array($msgType, ['text','customer','image','voice','video','music','news','news_item'])) {
      return '';
    }

    $createTime = time();
    switch ($msgType) {
      case 'text':
        if (empty($extra['content'])) {
          return '';
        }
        return sprintf(self::$msgTpl[$msgType], $toUserName, $fromUserName, $createTime,
                       $extra['content']);
        break;
      case 'customer':
      	return sprintf(self::$msgTpl[$msgType], $toUserName, $fromUserName, $createTime);
      	break;
      case 'image':
      case 'voice':
      case 'video':
        if (empty($extra['mediaId'])) {
          return '';
        }
        return sprintf(self::$msgTpl[$msgType], $toUserName, $fromUserName, $createTime,
                       $extra['mediaId'],
                       isset($extra['title'])       ? $extra['title']       : '',
                       isset($extra['description']) ? $extra['description'] : '');
        break;
      case 'music':
        if (empty($extra['thumbMediaId'])) {
          return '';
        }
        return sprintf(self::$msgTpl[$msgType], $toUserName, $fromUserName, $createTime,
                       isset($extra['title'])       ? $extra['title']       : '',
                       isset($extra['description']) ? $extra['description'] : '',
                       isset($extra['musicUrl'])    ? $extra['musicUrl']    : '',
                       isset($extra['hqMusicUrl'])  ? $extra['hqMusicUrl']  : '',
                       $extra['thumbMediaId']);
        break;
      case 'news':
        if (empty($extra['articleCount']) || empty($extra['articles'])) {
          return '';
        }
        return sprintf(self::$msgTpl[$msgType], $toUserName, $fromUserName, $createTime,
                       $extra['articleCount'],
                       $extra['articles']);
        break;
      case 'news_item':
        return sprintf(self::$msgTpl[$msgType],
                       isset($extra['title'])       ? $extra['title']       : '',
                       isset($extra['description']) ? $extra['description'] : '',
                       isset($extra['picUrl'])      ? $extra['picUrl']      : '',
                       isset($extra['url'])         ? $extra['url']         : '');
        break;
      default:
        return '';
    }
    
    return '';
  }
  
  /**
   * 微信接口通用调用函数
   * 
   * @param string $uri_path URI path
   * @param array $params 对应请求参数
   * @param string $method http请求方法：GET,POST
   * @param string $type API地址类型，对应self::$apiUrlPrefix的key部分：api_cgi,api_sns,open_conn
   * @param string $outfile 下载地址
   * @return mixed JSON Array or string
   */
  public function apiCall($uri_path, $params=array(), $method='get', $type='api_cgi', $outfile = '')
  {
    if (!in_array($type, array_keys(self::$apiUrlPrefix))) {
      return false;
    }
    $method = strtolower($method);
    if (!in_array($method, array('get','post'))) {
      return false;
    }
    if (empty($uri_path)) {
      return false;
    }
    if ($method=='post' && is_array($params)) {
      $params = json_encode($params, JSON_UNESCAPED_UNICODE);
    }
    
    $requrl = self::$apiUrlPrefix[$type] . $uri_path;
    $apiConfig = ['method'=>$method,'protocol'=>'https','timeout'=>60,'timeout_connect'=>30];
    if (''!==$outfile) {
      $apiConfig['outfile'] = $outfile;
    }
    $req = new ApiRequest($apiConfig);
    return $req->setUrl($requrl)->setParams($params)->send()->recv(TRUE);
  }
  
  /**
   * 获取微信接口需要的基本型(basic)AccessToken
   * 
   * @return string access token
   */
  public function fecthAccessToken()
  {
    $type   = 'basic';
    $result = $this->helper->onFetchAccessTokenBefore($type, $this->appId);
    if (!empty($result)) {
      return $result;
    }
    
    $ret    = array();
    $params = array(
      'grant_type' => 'client_credential',
      'appid'      => $this->appId,
      'secret'     => $this->appSecret,
    );
    $ret = $this->apiCall('/token', $params);
    if (!empty($ret['errcode'])) {
      return false;
    }
    $result = $ret['access_token'];
    
    if (!empty($result)) {
      $this->helper->onFetchAccessTokenSuccess($type, $this->appId, '', $ret);
    }
    return $result;
  }

  /**
   * 创建菜单
   * 
   * @param mixed(string|array) $data
   * @return boolean
   */
  public function createMenu($data = '')
  {
    if (empty($data)) return false;
    $access_token = $this->fecthAccessToken();
    $ret = $this->apiCall("/menu/create?access_token={$access_token}", $data, 'post');
    if (0===$ret['errcode']) {
      return true;
    }
    return false;
  }
  
  /**
   * 获取微信用户基本型信息
   * 
   * @param string $openid
   * @return array
   */
  public function userInfo($openid, $lang = 'zh_CN')
  {
    $access_token = $this->fecthAccessToken();
    $params = array(
      'access_token'=> $access_token,
      'openid'      => $openid,
      'lang'        => $lang,
    );
    return $this->apiCall("/user/info", $params, 'get');
  }
  
  /**
   * 通过OAuth2获取微信用户详细信息
   * @param string $openid
   * @param string $access_token
   * @param string $lang
   * @return boolean|Ambigous <mixed, boolean, multitype:boolean multitype: >
   */
  public function userInfoByOAuth2($openid, $access_token, $lang = 'zh_CN')
  {
    $params = array(
      'access_token'=> $access_token,
      'openid'      => $openid,
      'lang'        => $lang,
    );
    return $this->apiCall("/userinfo", $params, 'get', 'api_sns');
  }
  
  /**
   * 微信OAuth2授权
   * 
   * @param string $redirect_uri
   * @param string $state 可选值：'base' or 'detail'
   */
  public function authorizing($redirect_uri, $state = 'base')
  {
    if (!in_array($state,array('base','detail'))) $state = 'base';
    $scope = 'detail'==$state ? 'snsapi_userinfo' : 'snsapi_base';
    $url = $this->oauth->setConfig(array('redirect_uri'=>$redirect_uri,'scope'=>$scope,'state'=>$state))->authorize_url();
    Response::redirect($url);
  }
  
  /**
   * 微信OAuth2基本授权(snsapi_base)
   * 
   * @param string $act
   * @param string $refer
   * @param string $host
   */
  public function authorizing_base($act = '', $refer = '', $host = '') {
  	if (empty($host))  $host  = Request::host();
  	if (empty($refer)) $refer = Request::url();
  	(new Weixin())->authorizing("http://{$host}/user/oauth/weixin?act={$act}&refer=".rawurlencode($refer), 'base');
  }
  
  /**
   * 微信OAuth2详细授权(snsapi_userinfo)
   * 
   * @param string $act
   * @param string $refer
   * @param string $host
   */
  public function authorizing_detail($act = '', $refer = '', $host = '') {
  	if (empty($host))  $host  = Request::host();
  	if (empty($refer)) $refer = Request::url();
  	(new Weixin())->authorizing("http://{$host}/user/oauth/weixin?act={$act}&refer=".rawurlencode($refer), 'detail');
  }
  
  /**
   * 换取 access_token
   * 
   * @param string $code
   * @return mixed(string|array|boolean)
   */
  public function request_access_token($code)
  {
    return $this->oauth->request_access_token($code);
  }
  
  /**
   * 获取素材总数
   * 
   * @return boolean|array
   */
  public function getMaterialCount()
  {
    $ret    = array();
    $params = array();
    $access_token = $this->fecthAccessToken();
    $ret = $this->apiCall("/material/get_materialcount?access_token={$access_token}", $params);
    if (!empty($ret['errcode'])) {
      return false;
    }
    return $ret;
  }
  
  /**
   * 获取素材列表
   * 
   * @param string  $type    素材类型
   * @param integer $offset  返回的开始偏移位置
   * @param integer $count   返回的素材数量
   * @return array
   */
  public function getMaterialList($type = 'news', $offset = 0, $count = 10)
  {
    if (!in_array($type, array('news','image','video','voice'))) {
      $type = 'news';
    }
    if (!is_int($count) || $count < 1 || $count > 20) {
      $count = 10;
    }
    $ret    = array();
    $params = array(
      'type'   => $type,
      'offset' => $offset,
      'count'  => $count
    );
    $access_token = $this->fecthAccessToken();
    $ret = $this->apiCall("/material/batchget_material?access_token={$access_token}", $params, 'post');
    if (!empty($ret['errcode'])) {
      return false;
    }
    return $ret;
  }
  
  /**
   * 获取单个素材信息
   * 
   * @param string $media_id
   * @param string $outfile
   * @return boolean|array
   */
  public function getMaterial($media_id, &$outfile = '')
  {
    $ret    = array();
    $params = array(
      'media_id' => $media_id
    );
    $access_token = $this->fecthAccessToken();
    if (!$outfile) {
      $outfile = SIMPHP_ROOT . "/a/wx/".md5($media_id).".jpg";
    }
    $ret = $this->apiCall("/material/get_material?access_token={$access_token}", $params, 'post', 'api_cgi', $outfile);
    $outfile = str_replace(SIMPHP_ROOT, '', $outfile); //去掉前缀
    if (!empty($ret['errcode'])) {
      $outfile = false;
      return false;
    }
    if (!$ret) $outfile = false;
    
    return $ret;
  }
  
  //~ the following is some util functions
  
  /**
   * 是否微信浏览器
   * 
   * @return boolean
   */
  public static function isWeixinBrowser() {
    $b = strrpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') === false;
    return !$b;
  }
  
  /**
   * 获取微信浏览器版本号
   * 
   * @return int|string
   */
  public static function browserVer() {
    $ver = 0;
    if (preg_match('/.*?(MicroMessenger\/([0-9.]+))\s*/', $_SERVER['HTTP_USER_AGENT'], $matches)) {
      $ver = $matches[2];
    }
    return $ver;
  }
  
  /**
   * 创建随机 nonce 字符串
   * 
   * @param string $length
   * @return string
   */
  public static function createNonceStr($length = 16) {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $str = "";
    for ($i = 0; $i < $length; $i++) {
      $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
    }
    return $str;
  }
  
  /**
   * 返回当前请求的精确url地址
   * @return string
   */
  public static function requestUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $url = "{$protocol}{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
    return $url;
  } 
  
}

/**
 * Weixin JS-SDK 类
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
class WeixinJSSDK {
  
  /**
   * App Id
   * 
   * @var string
   */
  private $appId;
  
  /**
   * App Secret
   * 
   * @var string
   */
  private $appSecret;
  
  /**
   * js sdk file path
   * 
   * @var string
   */
  private static $sdkFile = 'http://res.wx.qq.com/open/js/jweixin-1.1.0.js'; //旧版本是: jweixin-1.0.0.js
  
  /**
   * Weixin Object
   * @var Weixin
   */
  private $wx;
  
  public function __construct($appId, Weixin $wx = NULL) {
    $this->appId = $appId;
    $this->wx    = $wx;
  }
  
  /**
   * 获取前端JS-SDK需要的签名包数据
   * 
   * @return array
   */
  private function getSignPackage() {
    
    $jsapiTicket = $this->getJsApiTicket();

    // 注意 URL 一定要动态获取，不能 hardcode.
    $url = $this->wx->requestUrl();
  
    $timestamp = time();
    $nonceStr = $this->wx->createNonceStr();
  
    // 这里参数的顺序要按照 key 值 ASCII 码升序排序
    $string = "jsapi_ticket={$jsapiTicket}&noncestr={$nonceStr}&timestamp={$timestamp}&url={$url}";
  
    $signature = sha1($string);
  
    $signPackage = array(
      "appId"     => $this->appId,
      "nonceStr"  => $nonceStr,
      "timestamp" => $timestamp,
      "url"       => $url,
      "signature" => $signature,
      "rawString" => $string
    );
    return $signPackage;
  }
  
  /**
   * 返回JS-SDK文件路径
   * @return string
   */
  public static function getSdkFile() {
    return self::$sdkFile;
  }
  
  /**
   * 获取jsapi_ticket
   * 
   * @return string
   */
  private function getJsApiTicket() {
    
    $type   = 'jsapi';
    $result = $this->wx->helper->onFetchAccessTokenBefore($type, $this->appId);
    if (!empty($result)) {
      return $result;
    }
    
    $accessToken = $this->wx->fecthAccessToken();
    
    $ret    = array();
    $params = array(
      'type'         => 'jsapi',
      'access_token' => $accessToken
    );
    $ret = $this->wx->apiCall('/ticket/getticket', $params);
    if (!empty($ret['errcode'])) {
      return false;
    }
    $result = $ret['ticket'];
    
    if (!empty($result)) {
      $this->wx->helper->onFetchAccessTokenSuccess($type, $this->appId, '', $ret);
    }
    return $result;
  }
  
  /**
   * 获取前端html Weinxin JS-SDK引入代码及初始配置
   *
   * @param array $jsApiList
   * @param string $readyJs wx.ready 函数中执行的js字串
   * @return string
   */
  public function js(Array $jsApiList = array(), $readyJs = '') {
  	if (empty($jsApiList)) {
  		$jsApiList = [
  				'onMenuShareTimeline',
  				'onMenuShareAppMessage',
  				'onMenuShareQQ',
  				'onMenuShareWeibo',
  				'chooseImage',
  				'previewImage',
  				'uploadImage',
  				'downloadImage',
  				'getNetworkType',
  				'openLocation',
  				'getLocation',
  				'hideOptionMenu',
  				'showOptionMenu',
  				'closeWindow',
  				'scanQRCode',
  				'chooseWXPay',
  		];
  	}
  
  	$debugWhiteList = C('env.debug_white_list');
  	$signPackage = $this->getSignPackage();
  	$jsApiStr    = "'" . implode("','", $jsApiList) . "'";
  	$debugStr    = 0&&in_array($GLOBALS['user']->uid, $debugWhiteList) ? 'true' : 'false';
  	$now         = time();
  
  	$jsfile = '<script type="text/javascript" src="'.self::$sdkFile.'"></script>';
  	$jsconf =<<<HEREDOC
<script type="text/javascript">
if (typeof(wx)=='object') {
  wx.config({
    debug: {$debugStr},
    appId: '{$signPackage["appId"]}',
    timestamp: {$signPackage["timestamp"]},
    nonceStr: '{$signPackage["nonceStr"]}',
    signature: '{$signPackage["signature"]}',
    jsApiList: [{$jsApiStr}]
  });
  wx.ready(function(){wxData.isReady=true;{$readyJs}});
}
</script>
HEREDOC;
  
  	return $jsfile . $jsconf;
  
  }
}

/**
 * Weixin JS收货地址 类
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
class WeixinJSAddr {
  
  /**
   * App Id
   * 
   * @var string
   */
  private $appId;
  
  /**
   * App Secret
   * 
   * @var string
   */
  private $appSecret;
  
  /**
   * Weixin Object
   * @var Weixin
   */
  private $wx;
  
  public function __construct($appId, Weixin $wx = NULL) {
    $this->appId = $appId;
    $this->wx    = $wx;
  }
  
  /**
   * 获取appId
   * @return string
   */
  public function getAppId() {
    return $this->appId;
  }
  
  /**
   * 获取收获地址签名
   * 
   * @param string $appId
   * @param string $url
   * @param string $timeStamp
   * @param string $nonceStr
   * @param string $accessToken
   * @return string 返回签名字串
   */
  public static function sign($appId, $url, $timeStamp, $nonceStr, $accessToken) {
    $str  = "accesstoken={$accessToken}&appid={$appId}&noncestr={$nonceStr}&timestamp={$timeStamp}&url={$url}";
    $sign = sha1($str);
    return $sign;
  }
  
  /**
   * 返回 address 收获地址 js
   */
  public function js($accessToken) {
    $appId     = $this->appId;
    $url       = $this->wx->requestUrl(); // 注意URL一定要动态获取，不能hardcode.
    $timeStamp = time();
    $nonceStr  = $this->wx->createNonceStr();
    $sign      = $this->sign($appId, $url, $timeStamp, $nonceStr, $accessToken);
    
    $js =<<<HEREDOC
<script type="text/javascript">
function wxEditAddress(func_cb) {
  if (typeof(WeixinJSBridge)=='object') {
    WeixinJSBridge.invoke('editAddress', {
      "appId": "{$appId}",
      "scope": "jsapi_address",
      "signType": "sha1",
      "addrSign": "{$sign}",
      "timeStamp": "{$timeStamp}",
      "nonceStr": "{$nonceStr}",
    }, function (res) {
      if(typeof(func_cb)=='function') {
        func_cb(res);
      }
    });
  }
}
</script>
HEREDOC;
    
    return $js;
  }
  
  
}

/**
 * Weixin 二维码 类
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
class WeixinQRCode {
  
	/**
	 * 临时二维码最大有效时间秒数
	 * @var constant
	 */
	const MAX_EXPIRE_SECONDS = 604800;//7天
	
	/**
	 * 二维码图片下载链接前缀
	 * @var constant
	 */
	const IMG_PREFIX = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=';
	
  /**
   * App Id
   * 
   * @var string
   */
  private $appId;
  
  /**
   * Weixin Object
   * @var Weixin
   */
  private $wx;
  
  public function __construct($appId, Weixin $wx = NULL) {
    $this->appId = $appId;
    $this->wx    = $wx;
  }
  
  /**
   * 获取appId
   * @return string
   */
  public function getAppId() {
    return $this->appId;
  }
  
  /**
   * 获取QRCode
   * @param integer|string $scene_id
   * @param integer $action_name one value of QR_SCENE, QR_LIMIT_SCENE, QR_LIMIT_STR_SCENE
   * @param integer $expire_seconds when $action_name==QR_SCENE, indicating expire seconds of the temp QRCode
   * @return Ambigous <string, mixed>|boolean|mixed
   */
  public function getQRCode($scene_id, $action_name = Weixin::QR_SCENE, $expire_seconds = self::MAX_EXPIRE_SECONDS) {
  
  	if (!in_array($action_name, [Weixin::QR_SCENE, Weixin::QR_LIMIT_SCENE, Weixin::QR_LIMIT_STR_SCENE])) {
  		throw new Exception('action name \''.$action_name.'\' invalid.');
  	}
  	if ($action_name==Weixin::QR_SCENE && $expire_seconds > self::MAX_EXPIRE_SECONDS) {
  		$expire_seconds = self::MAX_EXPIRE_SECONDS;
  	}
  	
  	$type   = 'qrcode';
  	$ticket = $this->wx->helper->onFetchAccessTokenBefore($type, $this->appId, '', ['scene_id'=>$scene_id, 'scene_type'=>$action_name]);
  	if (!empty($ticket)) {
  		return $ticket;
  	}
  
  	$accessToken = $this->wx->fecthAccessToken();
  
  	$action_name_txt = $action_name==Weixin::QR_LIMIT_SCENE ? 'QR_LIMIT_SCENE' : ($action_name==Weixin::QR_LIMIT_STR_SCENE ? 'QR_LIMIT_STR_SCENE' : 'QR_SCENE');
  	$scene  = $action_name==Weixin::QR_LIMIT_STR_SCENE ? ['scene_str'=>strval($scene_id)] : ['scene_id'=>intval($scene_id)];
  	$ret    = array();
  	$params = array(
  			'action_name'  => $action_name_txt,
  			'action_info'  => array('scene' => $scene)
  	);
  	if ($action_name==Weixin::QR_SCENE) {
  		$params['expire_seconds'] = intval($expire_seconds);
  	}
  	$ret = $this->wx->apiCall('/qrcode/create?access_token='.$accessToken, $params, 'post');
  	if (!empty($ret['errcode'])) {
  		return false;
  	}
  	$ticket = $ret['ticket'];
  
  	if (!empty($ticket)) {
  		$ret['scene_id']   = $scene_id;
  		$ret['scene_type'] = $action_name;
  		$ret['img'] = self::IMG_PREFIX.$ticket;
  		$this->wx->helper->onFetchAccessTokenSuccess($type, $this->appId, '', $ret);
  	}
  	return $ticket;
  }
  
}


/**
 * Weixin 消息群发 类
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
class WeixinMsgSend {

	/**
	 * App Id
	 *
	 * @var string
	 */
	private $appId;

	/**
	 * Weixin Object
	 * @var Weixin
	 */
	private $wx;

	public function __construct($appId, Weixin $wx = NULL) {
		$this->appId = $appId;
		$this->wx    = $wx;
	}

	/**
	 * 获取appId
	 * @return string
	 */
	public function getAppId() {
		return $this->appId;
	}
	
	/**
	 * 预览群发消息
	 *
	 * @param string $openid      用户OpenID 或者 微信号(仅预览时可用)
	 * @param array  $msgcontent  消息内容数组，如['content'=>'CONTENT'], ['media_id'=>'MEDIA_ID'], ['card_id'=>'CARD_ID','card_ext'=>'CARD_EXT ARRAY']
	 * @param string $msgtype     
	 * @return boolean|array
	 */
	public function preview($openid, Array $msgcontent, $msgtype = Weixin::MSG_TYPE_TEXT)
	{
		$ret    = array();
		$params = array();
		if (strlen($openid) > 20) { //OpenID
			$params['touser'] = $openid;
		}
		else { //微信号
			$params['towxname'] = $openid;
		}
		$params[$msgtype]  = $msgcontent;
		$params['msgtype'] = $msgtype;
		$access_token = $this->wx->fecthAccessToken();
		$ret = $this->wx->apiCall("/message/mass/preview?access_token={$access_token}", $params, 'post', 'api_cgi');
		if (!empty($ret['errcode'])) {
			return false;
		}
	
		return $ret;
	}
	
	/**
	 * 通过OpenID群发消息
	 *
	 * @param array  $openid      用户OpenID集合，其中 2 =< count($openid) <= 10000
	 * @param array  $msgcontent  消息内容数组，如['content'=>'CONTENT'], ['media_id'=>'MEDIA_ID'], ['card_id'=>'CARD_ID','card_ext'=>'CARD_EXT ARRAY']
	 * @param string $msgtype     包括：'mpnews','text','voice','music','image','video','wxcard'
	 * @return boolean|array
	 */
	public function sendByOpenid(Array $openid, Array $msgcontent, $msgtype = Weixin::MSG_TYPE_TEXT)
	{
		$num = count($openid);
		if ($num < 2 || $num > 10000) return false;
		
		$ret    = array();
		$params = array(
			'touser' => $openid,
			$msgtype => $msgcontent,
			'msgtype'=> $msgtype
		);
		$access_token = $this->wx->fecthAccessToken();
		$ret = $this->wx->apiCall("/message/mass/send?access_token={$access_token}", $params, 'post', 'api_cgi');
		if (!empty($ret['errcode'])) {
			return false;
		}
	
		return $ret;
	}
	
	/**
	 * 向全部用户或者根据分组进行群发
	 *
	 * @param array  $msgcontent  消息内容数组，如['content'=>'CONTENT'], ['media_id'=>'MEDIA_ID'], ['card_id'=>'CARD_ID','card_ext'=>'CARD_EXT ARRAY']
	 * @param string $msgtype     包括：'mpnews','text','voice','music','image','video','wxcard'
	 * @return boolean|array
	 */
	public function send(Array $msgcontent, $msgtype = Weixin::MSG_TYPE_TEXT, $is_to_all = true, $group_id = 0)
	{
		$ret    = array();
		$params = array(
				'filter' => ['is_to_all' => $is_to_all, 'group_id' => $group_id],
				$msgtype => $msgcontent,
				'msgtype'=> $msgtype
		);
		$access_token = $this->wx->fecthAccessToken();
		//$ret = $this->wx->apiCall("/message/mass/sendall?access_token={$access_token}", $params, 'post', 'api_cgi');
		if (!empty($ret['errcode'])) {
			return false;
		}
	
		return $ret;
	}
	
	/**
	 * 发送模板消息
	 * @param string $openid       接收用户OpenID
	 * @param string $template_id  消息模板ID
	 * @param string $url          点击查看链接
	 * @param array  $data         消息模板内容
	 * @return boolean|mixed
	 */
	public function sendTplMsg($openid, $template_id, $url, Array $data)
	{
		$ret    = array();
		$params = array(
				'touser'      => $openid,
				'template_id' => $template_id,
				'url'         => $url,
				'data'        => $data
		);
		$access_token = $this->wx->fecthAccessToken();
		$ret = $this->wx->apiCall("/message/template/send?access_token={$access_token}", $params, 'post', 'api_cgi');
		if (!empty($ret['errcode'])) {
			return false;
		}
	
		return $ret;
	}
	
}

/**
 * Weixin帮助类
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
class WeixinHelper {

  private $from = 'weixin';
  
  /**
   * Weixin Object
   * @var Weixin
   */
  private $wx;
  
  public function __construct(Weixin $wx = NULL) {
    $this->wx = $wx;
  }

  /**
   * 调用未定义函数，则作默认返回空串处理
   * @param string $name
   * @param array $args
   */
  public function __call($name, $args) {
    return '';
  }

  /**
   * 关注事件
   *
   * @param string  $openid
   * @param integer $reqtime
   * @param string  $event
   * @param string  $eventKey
   * @param string  $ticket
   * @return string
   */
  public function onSubscribe($openid, $reqtime, $event, $eventKey = NULL, $ticket = NULL) {
  	
    $wxuinfo   = $this->wx->userInfo($openid);
    
    if (empty($wxuinfo['errcode'])) {
    	if (isset($wxuinfo['unionid']) && ''!=$wxuinfo['unionid']) { //只有有unionid时才操作(因为UNIQUE索引)
    		
    		$save_type = Storage::SAVE_INSERT_IGNORE;
    		$exUser = UsersPending::load_by_unionid($wxuinfo['unionid']);
    		
    		if ($exUser->is_exist()) { //已存在，已存在不会变更上级关系
    			$upUser = new UsersPending($exUser->id);
    			$upUser->update_time = simphp_dtime();
    			$save_type = Storage::SAVE_UPDATE;
    		}
    		else { //未存在，会"尝试"建立上下级关系
    			$upUser = new UsersPending();
    			$upUser->unionid   = $wxuinfo['unionid'];
    			$upUser->openid    = $openid;
    			$upUser->parent_id = 0;
    			$upUser->auth_method= !empty($eventKey) ? 'scan' : 'base';
    			$upUser->touch_time = simphp_dtime();
    			$upUser->update_time= simphp_dtime();
    			if(isset($eventKey) && $eventKey) { //确定上级
    				if (preg_match('/^qrscene_(\d+)$/', $eventKey, $matches)) {
    					$scene_id = $matches[1];
    				}
    				else {
    					$scene_id = $eventKey;
    				}
    				if (is_numeric($scene_id)) {
    					$wxqr = Wxqrcode::load($scene_id); //EventKey就是scene_id
    					if ($wxqr->is_exist()) {
    						$upUser->parent_id  = $wxqr->user_id;
    					}
    				}
    			}
    		}
    		
    		if ('subscribe'==$event || !$exUser->is_exist()) {
    			$upUser->subscribe   = $wxuinfo['subscribe'];
    			$upUser->subscribe_time = $wxuinfo['subscribe_time'];
    			$upUser->nick      = $wxuinfo['nickname'];
    			$upUser->logo      = $wxuinfo['headimgurl'];
    			$upUser->gender    = $wxuinfo['sex'];
    			$upUser->lang      = $wxuinfo['lang'];
    			$upUser->country   = $wxuinfo['country'];
    			$upUser->province  = $wxuinfo['province'];
    			$upUser->city      = $wxuinfo['city'];
    		}
    		
    		$upUser->save($save_type);
    	}
    }
    
    $msg = $this->about();
    $cUser = Users::load_by_openid($openid, $this->from);
    if ($cUser->is_exist()) {
    	$msg .= "\n\n您的多米号: ".$cUser->id;
    	if ($cUser->parentid) {
    		$pUser = Users::load($cUser->parentid);
    		if ($pUser->is_exist()) {
    			$promoter = '推荐人米号: ' . $pUser->id;
    			if ($pUser->nickname) $promoter .= "\n推荐人昵称: " . $pUser->nickname;
    			$msg .= "\n".$promoter;
    			if ($pUser->mobile) {
    				$msg .= "\n推荐人手机:".$pUser->mobile;
    			}
    		}
    	}
    }
    
    return $msg;
    
  }

  /**
   * 取消关注事件
   *
   * @param string $openid
   * @param integer $reqtime
   * @return string
   */
  public function onUnsubscribe($openid, $reqtime) {
    $exUser = Users::load_by_openid($openid, $this->from);
    if ($exUser->is_exist()) {
    	$upUser = new Users($exUser->id);
    	$upUser->subscribe     = 0;
    	$upUser->subscribetime = $reqtime;
    	$upUser->save(Storage::SAVE_UPDATE);
    }
    return '';
  }

  /**
   * 获取位置信息事件
   *
   * @param string $openid
   * @param integer $reqtime
   * @param double $longitude
   * @param double $latitude
   * @param double $precision
   * @return string
   */
  public function onLocation($openid, $reqtime, $longitude, $latitude, $precision) {
    $exUser = Users::load_by_openid($openid, $this->from);
    if ($exUser->is_exist()) {
    	$upUser = new Users($exUser->id);
    	$upUser->longitude = $longitude;
    	$upUser->latitude  = $latitude;
    	$upUser->precision = $precision;
    	$upUser->save(Storage::SAVE_UPDATE);
    }
    return '';
  }

  /**
   * 文本关键词查询事件
   *
   * @param string  $keyword     关键词
   * @param string  $result_type 输出返回结果类型: <br>
   *   'string'      : 一个纯字符串内容，默认<br>
   *   'arr_article' : 文章数组<br>
   *   'arr_goods'   : 商品数组<br><br>
   * @return string|array
   */
  public function onTextQuery($keyword, &$result_type = 'string') {
    if (''==$keyword) {
      return '';
    }
    
    $result = '';
    $result_type = 'string';
    if (in_array($keyword, array('益多米','关于'))) {
      $result = $this->about();
    }
    elseif ( preg_match('/你|您/', $keyword) && preg_match('/是谁|干什么|做什么/', $keyword) ) {
      $result = $this->about(2);
    }
    elseif (preg_match('/我/', $keyword) && preg_match('/是谁|干什么|做什么/', $keyword)) {
      $result = '你懂的！';
    }
    elseif (preg_match('/百度|度娘/', $keyword)) {
      $result = '这是她的地址：<a href="http://www.baidu.com">www.baidu.com</a>';
    }
    elseif (preg_match('/错了|不喜欢|不好玩|不感兴趣|不爱|无用|干扰|骚扰/', $keyword)) {
      $result = '不好意思/::~';
    }
    elseif (preg_match('/喜欢|很好|感兴趣|大爱|太棒|真棒|棒极|哇塞|不错|太酷|忒酷|真酷/', $keyword)) {
      $result = '谢谢/::$';
    }
    elseif (preg_match('/^http(s)?:\/\//i', $keyword)) {
      $result = "请访问: <a href=\"{$keyword}\">{$keyword}</a>";
    }
    elseif(in_array($keyword, array('?','？','阿','啊','在','在?','在？','哈哈','呵呵','哈','呵','哼'))
      || is_numeric($keyword)
      || preg_match("!^/:!", $keyword) //表情
      || preg_match("/(在吗|你好|您好|哈哈|呵呵|哈|呵|哼|hi|hello|hallo)/i", $keyword)
    ){
      $result = $this->defaultHello();
    }
    elseif(in_array($keyword, array('常见问题','招商','商品推荐','米商'))) {
    	$url = '';
    	switch ($keyword) {
    		case '常见问题':
    			$url = 'http://mp.weixin.qq.com/s?__biz=MzAwODc2NjMyNw==&mid=502672432&idx=1&sn=efe87a2baccb5a5ca16d88ed5303a2c0#rd';
    			$result = "请访问: <a href=\"{$url}\">常见问题</a>";
    			break;
    		case '招商':
    			$url = 'http://mp.weixin.qq.com/s?__biz=MzAwODc2NjMyNw==&mid=502672432&idx=2&sn=88f3d69bfe01b35ef00dd71e8af7eead#rd';
    			$result = "请访问: <a href=\"{$url}\">招商计划</a>";
    			break;
    		case '商品推荐':
    			$url = 'http://mp.weixin.qq.com/s?__biz=MzAwODc2NjMyNw==&mid=502672432&idx=3&sn=098868a92afbee61551713dff741b8dd#rd';
    			$result = "请访问: <a href=\"{$url}\">商品推荐</a>";
    			break;
    		case '米商':
    			$url = 'http://mp.weixin.qq.com/s?__biz=MzAwODc2NjMyNw==&mid=502672432&idx=4&sn=c1cf4352e13b12a8700e3c395d929ce2#rd';
    			$result = "请访问: <a href=\"{$url}\">米商计划</a>";
    			break;
    	}
    }
    else { //查询数据库
    	//$result = "您好老师，现在可以通过二维码进行锁定上下级和预热。18号正式上线后，下级购买了产品你就可以获得佣金了。\n\n点击底部的“推广二维码”，把这个二维码发给陌生的朋友，让他关注益多米，看看他的推荐人是不是您。";
    }
    
    return $result;
  }

  /**
   * 默认招呼语
   */
  private function defaultHello() {
    $now_h  = intval(date('G'));
    $hello  = '';
    if ($now_h < 5) {
      $hello = '您好，凌晨了';
    }
    elseif ($now_h < 11) {
      $hello = '早上好';
    }
    elseif ($now_h < 14) {
      $hello = '中午好';
    }
    elseif ($now_h < 19) {
      $hello = '下午好';
    }
    else {
      $hello = '晚上好';
    }
    return $hello.'！请问有什么可以帮到您？';
  }
  
  /**
   * "关于" 的返回文字
   * 
   * @param int $type
   * @return string
   */
  public function about($type = 0) {
  	$type = 1;
  	if (!$type) {
  		$text  = "益多米作为新型社交电商购物平台，为广大消费者提供琳琅满目的优质商品，满足大家消费需求的同时，采用三级分销的模式，让消费者转变为消费商，通过分销商品赚取佣金。";
  		$text .= "\n\n快去分享底部推广二维码、米商计划和常见问题至朋友圈，提前推广锁粉吧！益多米上线后，下级用户购物，上级都是可以拿到购物佣金的哦！";
  	}
    else {
    	$text  = "终于等到你！/:hug/:hug/:hug\n\n";
    	$text .= "【恭喜】你已成为益多米的会员，we are 伐木累！/::*/::*/::*\n\n";
    	$text .= "益多米作为良心平台，专为“米粉们”提供超值特惠的商品，从此不用“剁手”也可以买到称心如意的商品啦！\n\n";
    	$text .= "益多米，你生活中的省钱利器！\n\n";
    	$text .= "快带领你的小伙伴们一起进入多米商城选购心仪的商品吧！/::$\n\n";
    	$text .= "/::B/::B/::B另外，邀请他人购物还有惊喜哟！\n\n";
    	$text .= "客服微信: edmbuykf1001";
    }
    return $text;
  }
  
  /**
   * "联系小蜜" 的返回文字
   * 
   * @return string
   */
  public function contact() {
    $text = "联系微信号：\n\n购物相关：cloverzhaoyue\n业务合作：GavinLan\n公众号　：fxmgou";
    $text.= "\n\n也可以在当前公众号下切换到回复模式直接咨询。";
    return $text;
  }
  
  /**
   * 返回搜索地址
   * 
   * @param string $keyword
   * @param string $type    'baidu'...
   * @return string
   */
  public function sou($keyword, $type = 'baidu') {
    if (empty($keyword)) return '';
    if ('baidu'==$type) {
      return "https://www.baidu.com/s?wd={$keyword}";
    }
    return '';
  }
  
  /**
   * "最新文章" 的返回文字
   * @return string
   */
  public function latestArticles() {
    $exclude_media_ids = array(
      "-F65ngPm5FgC2MkjlByRQ9OArBXfSA_EXwClAVDaeho"
    );
    $exclude_media_ids_str = "'" . implode("','",$exclude_media_ids) . "'";
    $news = D()->from('material_wx')->where("`type`='news' AND `media_id` NOT IN({$exclude_media_ids_str})")
               ->order_by("`update_time` DESC")->limit(5)->select()->fetch_array_all();
    
    return $news ? : [];
  }

  /**
   * 获取access token之前的检查事件
   *
   * @param string $type, optional value: 'basic':基本型; 'oauth':OAuth2型; 'jsapi': jsapi ticket; 'qrcode': QRCode ticket
   * @param string $appId,
   * @param string $openId,
   * @param array  $extra,
   * @return string
   */
  public function onFetchAccessTokenBefore($type, $appId, $openId = '', Array $extra = []) {
    $token = '';
    $now   = time();
    if ('basic'==$type) {
      $token = D()->result("SELECT `access_token` FROM `{access_token_weixin}` WHERE `type`='{$type}' AND `appid`='%s' AND `expires_at`>{$now} ORDER BY `rid` DESC LIMIT 1", $appId);
    }
    elseif ('oauth'==$type) {
      $token = D()->result("SELECT `access_token` FROM `{access_token_weixin}` WHERE `type`='{$type}' AND `appid`='%s' AND `openid`='%s' AND `expires_at`>{$now} ORDER BY `rid` DESC LIMIT 1", $appId, $openId);
    }
    elseif ('jsapi'==$type) {
      $token = D()->result("SELECT `access_token` FROM `{access_token_weixin}` WHERE `type`='{$type}' AND `appid`='%s' AND `expires_at`>{$now} ORDER BY `rid` DESC LIMIT 1", $appId);
    }
    elseif ('qrcode'==$type) {
    	$where_extra = '';
    	if ($extra['scene_type']==Weixin::QR_SCENE) {
    		$where_extra = "AND `created`>{$now}-`expire_seconds`";
    	}
      $token = D()->result("SELECT `ticket` FROM `{weixin_qrcode}` WHERE `scene_id`=%d {$where_extra}", $extra['scene_id']);
      $token = $token ? : '';
    }
    return $token;
  }

  /**
   * 获取access token成功事件
   *
   * @param string $type, optional value: 'basic':基本型; 'oauth':OAuth2型; 'jsapi': jsapi ticket; 'qrcode': QRCode ticket
   * @param string $appId,
   * @param string $openid,
   * @param array $retdata, 微信服务器返回的数据
   * @return integer
   */
  public function onFetchAccessTokenSuccess($type, $appId, $openid = '', Array $retdata = array()) {
    $now  = time();
    if ('qrcode'==$type) {
    	$wxqr = new Wxqrcode($retdata['scene_id']);
    	$wxqr->scene_type = $retdata['scene_type'];
    	$wxqr->url        = $retdata['url'];
    	$wxqr->img        = isset($retdata['img']) ? $retdata['img'] : '';
    	$wxqr->expire_seconds = isset($retdata['expire_seconds']) ? $retdata['expire_seconds'] : -1;
    	$wxqr->ticket     = $retdata['ticket'];
    	$wxqr->save(Storage::SAVE_UPDATE);
    	return $wxqr->id;
    }
    else {
    	$data = array(
    			'type'         => $type,
    			'access_token' => in_array($type, ['jsapi','qrcode']) ? $retdata['ticket'] : $retdata['access_token'],
    			'expires_at'   => $now + $retdata['expires_in'] - 10, //减10是为了避免网络误差时间
    			'appid'        => $appId,
    			'openid'       => isset($retdata['openid']) ? $retdata['openid'] : $openid,
    			'refresh_token'=> isset($retdata['refresh_token']) ? $retdata['refresh_token'] : '',
    			'scope'        => isset($retdata['scope']) ? $retdata['scope'] : '',
    			'data'         => isset($retdata['data']) ? $retdata['data'] : '',
    			'timeline'     => $now,
    	);
    	$rid = D()->insert('access_token_weixin', $data);
    	return $rid;
    }
  }

}


 
/*----- END FILE: class.Weixin.php -----*/