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
	 * Allow weixin public account
	 * @var array
	 */
	public static $allowAccount = array('fxmgou');

	/**
	 * Weixin constant name
	 * @var constant
	 */
	const PLUGIN_JSSDK  = 'jssdk';
	const PLUGIN_JSADDR = 'jsaddr';
	
	const MSG_TYPE_TEXT  = 'text';
	const MSG_TYPE_IMAGE = 'image';
	const MSG_TYPE_VOICE = 'voice';
	const MSG_TYPE_VIDEO = 'video';
	const MSG_TYPE_MUSIC = 'music';
	const MSG_TYPE_NEWS  = 'news';
	const MSG_TYPE_NEWS_ITEM = 'news_item';
	
	/**
	 * All weixin plugins name
	 * @var array
	 */
	public static $plugins = array(self::PLUGIN_JSSDK, self::PLUGIN_JSADDR);
	
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
	 * WeixinJSAddr instatnce
	 * 
	 * @var WeixinJSAddr
	 */
	public $jsaddr;
	
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
	 * @param string $target, 目标平台，可选值：'fxmgou' 等
	 */
	private function init(Array $plugins = array(), $target = 'fxmgou')
	{
	  if (!in_array($target, self::$allowAccount)) {
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
	}
	
	/**
	 * 初始化构造函数
	 * @param array $plugins, 额外的插件，如 'jssdk', 'wxpay' 等
	 * @param string $target, 目标平台，可选值：'fxmgou' 等
	 */
	public function __construct(Array $plugins = array(), $target = 'fxmgou')
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
    
    $contentText = '';
    $responseText= '';
    switch ($postObj->Event) {
      case 'subscribe':
        $contentText = $this->helper->onSubscribe($openid, $reqtime, $toUserName);
        break;
      case 'unsubscribe':
        $this->helper->onUnsubscribe($openid, $reqtime);
        break;
      case 'SCAN':
        break;
      case 'CLICK':
      	return '';//临时屏蔽
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
          case '300': //关于小蜜
            $contentText = $this->helper->about();
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
    //trace_debug('weixin_reply_'.($is_voice?'voice':'text'), $keyword);
    
    $responseText = '';
    $queryResult  = $this->helper->onTextQuery($keyword, $result_type);
    if (empty($queryResult)) {//没查找到任何可回复的信息
      $baidu_sou  = $this->helper->sou($keyword);
      $contentText  = "抱歉，没有找到你想要的东西！小蜜帮你<a href=\"{$baidu_sou}\">度娘一下</a>吧/::P";
      $responseText = self::packMsg(self::MSG_TYPE_TEXT, $fromUserName, $toUserName, array('content' => $contentText));
    }
    else {
      if ('string'==$result_type) { //一般文本
        $responseText = self::packMsg(self::MSG_TYPE_TEXT, $fromUserName, $toUserName, array('content' => $queryResult));
      }
      elseif ('arr_article'==$result_type) { //最新文章
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
   * @param string $createTime   消息响应时间
   * @param string $extra <br>
   *   $msgType = 'text' : $extra['content' => 'xx'] <br>
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
    if (!in_array($msgType, ['text','image','voice','video','music','news','news_item'])) {
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
  private static $sdkFile = 'http://res.wx.qq.com/open/js/jweixin-1.0.0.js';
  
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
   * @return string
   */
  public function js(Array $jsApiList = array()) {
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
    $debugStr    = in_array($GLOBALS['user']->uid, $debugWhiteList) ? 'true' : 'false';
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
  wx.ready(function(){wxData.isReady=true});
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
   * @param string $openid
   * @param integer $reqtime
   * @param string $toUserName
   * @return string
   */
  public function onSubscribe($openid, $reqtime, $toUserName = '') {
    $wxuinfo = $this->wx->userInfo($openid);
    if (empty($wxuinfo['errcode'])) {
    	if (isset($wxuinfo['unionid']) && ''!=$wxuinfo['unionid']) { //只有有unionid时才操作
    		
    		$aUser = Users::load_by_unionid($wxuinfo['unionid'], $this->from);
    		if ($aUser->is_exist()) { //已存在
    			$upUser = new Users($aUser->id);
    		}
    		else { //未存在
    			$upUser = new Users();
    			$upUser->unionid   = $wxuinfo['unionid'];
    			$upUser->state     = 0; //0:正常;1:禁止
    			$upUser->from      = $this->from;
    			$upUser->authmethod= 'base';
    		}
    		
    		$upUser->openid      = $openid;
    		$upUser->subscribe   = $wxuinfo['subscribe'];
    		$upUser->subscribetime = $wxuinfo['subscribe_time'];
    		if ($aUser->logo) { //未设过时才更新
    			$upUser->nickname  = $wxuinfo['nickname'];
    			$upUser->logo      = $wxuinfo['headimgurl'];
    			$upUser->sex       = $wxuinfo['sex'];
    			$upUser->lang      = $wxuinfo['lang'];
    			$upUser->country   = $wxuinfo['country'];
    			$upUser->province  = $wxuinfo['province'];
    			$upUser->city      = $wxuinfo['city'];
    		}
    		$upUser->save();
    		
    	}
    }
    
    $msg = $this->about(1);
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
    $aUser = Users::load_by_openid($openid, $this->from);
    if ($aUser->is_exist()) {
    	$upUser = new Users($aUser->id);
    	$upUser->subscribe     = 0;
    	$upUser->subscribetime = $reqtime;
    	$upUser->save();
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
    $aUser = Users::load_by_openid($openid, $this->from);
    if ($aUser->is_exist()) {
    	$upUser = new Users($aUser->id);
    	$upUser->longitude = $longitude;
    	$upUser->latitude  = $latitude;
    	$upUser->precision = $precision;
    	$upUser->save();
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
    if (in_array($keyword, array('小蜜','关于小蜜','关于','福小蜜'))) {
      $result = $this->about();
    }
    elseif ( preg_match('/你|您/', $keyword) && preg_match('/是谁|干什么|做什么/', $keyword) ) {
      $result = $this->about(2);
    }
    elseif (preg_match('/我/', $keyword) && preg_match('/是谁|干什么|做什么/', $keyword)) {
      $result = '你懂的！';
    }
    elseif (in_array($keyword, array('最新文章','最新资讯','文章','资讯'))) {
      $result = $this->latestArticles();
      $result_type = 'arr_article';
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
    elseif (preg_match('/无聊|干什么|什么干|做什么|什么做|什么好做|什么好干/', $keyword)) {
      $poems = [
        "终日昏昏醉梦间，\n忽闻春尽强登山。\n因过竹院逢僧话，\n偷得浮生半日闲。",
        "黄梅时节家家雨，\n青草池塘处处蛙。\n有约不来过夜半，\n闲敲棋子落灯花。",
        "无聊夜里无聊梦，\n心语无聊冷若冰。\n叹写无聊抒郁事，\n无聊目送月零丁。",
        "一别之后，\n两地相悬，\n只说是三四月，\n又谁知五六年。\n七弦琴无心弹，\n八行书不可传，\n九连环从中折断，\n十里长亭望眼欲穿，\n百思想，千系念，万般无奈把郎怨。\n万语千言说不完，百无聊赖十倚栏，\n重九登高看孤雁，\n八月中秋月圆人不圆，\n七月半烧香秉烛问苍天，\n 六月伏天人人摇扇我心寒，\n 五月石榴如火，偏遇阵阵冷雨浇花端；\n四月枇杷未黄，我欲对镜心意乱。\n急匆匆，三月桃花随水转；\n飘零零，二月风筝线儿断。",
        "智者乐山山如画，\n仁者乐水水无涯。\n从从容容一杯酒，\n平平淡淡一杯茶。\n\n细雨朦胧小石桥，\n春风荡漾小竹筏。\n夜无明月花独舞，\n腹有诗书气自华。",
        "浮名浮利，虚苦劳神。\n叹隙中驹，石中火，梦中身。\n几时归去，作个闲人。\n\n网事已成空，还如一梦中。\n相见争如不见，有情还是无情。\n今朝有酒今朝醉，管他对错是与非。",
      ];
      $rand_idx = mt_rand(0, 100000);
      $rand_idx = $rand_idx % count($poems);
      $result = $poems[$rand_idx];
      $baidu_sou = $this->sou($keyword);
      $result.= "\n\n还不解聊？去调戏度娘吧：“度娘，<a href=\"{$baidu_sou}\">{$keyword}</a>”";
    }
    elseif (preg_match('/有什么/', $keyword)) {
      $result = Goods::getGoodsList('','latest');
      if (empty($result)) return '';
      $result_type = 'arr_goods';
    }
    elseif (preg_match('/商城/', $keyword)) {
      $result = "请访问: <a href=\"http://m.fxmgou.com/\">小蜜商城</a>";
    }
    elseif (preg_match('/订单/', $keyword)) {
      $result = "请访问: <a href=\"http://m.fxmgou.com/trade/order/record\">我的订单</a>";
    }
    elseif (preg_match('/收藏/', $keyword)) {
      $result = "请访问: <a href=\"http://m.fxmgou.com/user/collect\">我的收藏</a>";
    }
    elseif (preg_match('/反馈/', $keyword)) {
      $result = "请访问: <a href=\"http://m.fxmgou.com/user/feedback\">我要反馈</a>";
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
    else { //查询数据库
      $result = Goods::search($keyword,10);
      if (empty($result)) return '';
      $result_type = 'arr_goods';
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
    $text = "你好，欢迎关注益多米/:rose";
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
   * @param string $type, optional value: 'basic':基本型; 'oauth':OAuth2型; 'jsapi': jsapi ticket
   * @param string $appId,
   * @param string $openId,
   * @return string
   */
  public function onFetchAccessTokenBefore($type, $appId, $openId = '') {
    $token = '';
    $now   = time();
    if ('basic'==$type) {
      $token = D()->result("SELECT `access_token` FROM `{access_token_weixin}` WHERE `type`='basic' AND `appid`='%s' AND `expires_at`>{$now} ORDER BY `rid` DESC LIMIT 1", $appId);
    }
    elseif ('oauth'==$type) {
      $token = D()->result("SELECT `access_token` FROM `{access_token_weixin}` WHERE `type`='oauth' AND `appid`='%s' AND `openid`='%s' AND `expires_at`>{$now} ORDER BY `rid` DESC LIMIT 1", $appId, $openId);
    }
    elseif ('jsapi'==$type) {
      $token = D()->result("SELECT `access_token` FROM `{access_token_weixin}` WHERE `type`='jsapi' AND `appid`='%s' AND `expires_at`>{$now} ORDER BY `rid` DESC LIMIT 1", $appId);
    }
    return $token;
  }

  /**
   * 获取access token成功事件
   *
   * @param string $access_token
   * @param integer $expires_in
   * @param string $type, optional value: 'basic':基本型; 'oauth':OAuth2型; 'jsapi': jsapi ticket
   * @param string $appId,
   * @param string $openid,
   * @param array $retdata, 微信服务器返回的数据
   */
  public function onFetchAccessTokenSuccess($type, $appId, $openid = '', Array $retdata = array()) {
    $now  = time();
    $data = array(
      'type'         => $type,
      'access_token' => 'jsapi'==$type ? $retdata['ticket'] : $retdata['access_token'],
      'expires_at'   => $now + $retdata['expires_in'] - 10, //减10是为了避免网络误差时间
      'appid'        => $appId,
      'openid'       => isset($retdata['openid']) ? $retdata['openid'] : $openid,
      'refresh_token'=> isset($retdata['refresh_token']) ? $retdata['refresh_token'] : '',
      'scope'        => isset($retdata['scope']) ? $retdata['scope'] : '',
      'timeline'     => $now,
    );
    $rid = D()->insert('access_token_weixin', $data);
    return $rid;
  }

}


 
/*----- END FILE: class.Weixin.php -----*/