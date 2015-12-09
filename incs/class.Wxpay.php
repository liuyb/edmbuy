<?php
/**
 * 微信支付接口入口类
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

//定义微信支付SDK库根目录
define('WXPAY_SDK_ROOT', __DIR__.'/libs/wxpay/');

require_once WXPAY_SDK_ROOT."lib/WxPay.Api.php";

//初始化日志
$logHandler= new CLogFileHandler(LOG_DIR."/wxpay_".date('Y-m-d').'.log');
$log = Log::Init($logHandler, 15);

class Wxpay {
  
  /**
   * 下订单回调接口
   * 
   * @var constant
   */
  const NOFIFY_URL = 'http://api.fxmgou.com/wxpay/notify';
  
  /**
   * 交易类型常量，共4个可选值
   * @var constant
   */
  const TRADE_TYPE_JSAPI  = 'JSAPI';
  const TRADE_TYPE_NATIVE = 'NATIVE';
  const TRADE_TYPE_APP    = 'APP';
  const TRADE_TYPE_WAP    = 'WAP';
  
  /**
   * 统一下单接口
   * 
   * @param array $order 站内订单数组
   * @param string $openId openid
   * @return string
   */
  public static function unifiedOrder(Array $order, $openId = '') {
    
    if (empty($order)) {
      return '';
    }
    
    include_once WXPAY_SDK_ROOT."unit/WxPay.JsApiPay.php";
    
    //获取用户openid
    $tools = new JsApiPay();
    if (empty($openId)) {
      $openId = $tools->GetOpenid();      
    }
    
    $order_detail  = '';
    $wx_order_body = '';
    if (!empty($order['order_goods'])) {
      foreach ($order['order_goods'] As $g) {
        $order_detail .= $g['goods_name'].'('.$g['goods_price'].'x'.$g['goods_number'].")\n";
        if (''==$wx_order_body) {
          $wx_order_body = mb_truncate($g['goods_name'], 27);
        }
      }
      $order_detail = rtrim($order_detail,"\n");
    }
    
    //统一下单
    if (1||empty($order['pay_data1'])) { //订单状态可能会被后台更改，所以同一订单每次支付都要重新生成提交信息
      if (''==$wx_order_body) $wx_order_body = '福小蜜商品';
      $now   = time();
      $input = new WxPayUnifiedOrder();
      $input->SetBody($wx_order_body);
      $input->SetDetail($order_detail);
      $input->SetAttach('fxmgou'); //商家自定义数据，原样返回
      $input->SetOut_trade_no($order['order_sn']);
      $input->SetTotal_fee(intval($order['order_amount']*100)); //'分'为单位
      $input->SetTime_start(date('YmdHis', $now));
      $input->SetTime_expire(date('YmdHis', $now + 60*15)); //15分钟内支付有效
      $input->SetGoods_tag(''); //商品标记，代金券或立减优惠功能的参数
      $input->SetNotify_url(self::NOFIFY_URL);
      $input->SetTrade_type(self::TRADE_TYPE_JSAPI);
      $input->SetOpenid($openId);
      
      $order_wx = WxPayApi::unifiedOrder($input);
      
      if ('SUCCESS'==$order_wx['return_code'] && 'SUCCESS'==$order_wx['result_code']) { //保存信息以防再次重复提交
        $wxpay_data = [
          'appid'      => $order_wx['appid'],
          'mch_id'     => $order_wx['mch_id'],
          'trade_type' => $order_wx['trade_type'],
          'prepay_id'  => $order_wx['prepay_id']
        ];
        if (isset($order_wx['code_url'])) {
          $wxpay_data['code_url'] = $order_wx['code_url'];
        }
        Goods::orderUpdate(['pay_data1'=>json_encode($wxpay_data)], $order['order_id']);
      }
    }
    else {
      $order_wx = json_decode($order['pay_data1'], true);
    }
    
    $jsApiParameters = $tools->GetJsApiParameters($order_wx);
    
    return $jsApiParameters;
    
  }
  
  /**
   * 支付结果回调通知
   * 
   * @param callable $callback
   */
  public static function nofify($callback) {
    Log::DEBUG("begin notify");
    $notify = new PayNotifyCallBack($callback);
    $notify->Handle(false);
  }
  
}

/**
 * 支付回调通知类
 *
 * @author Gavin
 *
 */
include_once WXPAY_SDK_ROOT."lib/WxPay.Notify.php";
class PayNotifyCallBack extends WxPayNotify
{

  private $_notify_callback;

  public function __construct($callback) {
    $this->_notify_callback = $callback;
  }

  //查询订单
  public function QueryOrder($transaction_id)
  {
    $input = new WxPayOrderQuery();
    $input->SetTransaction_id($transaction_id);
    $result = WxPayApi::orderQuery($input);
    Log::DEBUG("query:" . json_encode($result));
    if(array_key_exists("return_code", $result)
      && array_key_exists("result_code", $result)
      && $result["return_code"] == "SUCCESS"
      && $result["result_code"] == "SUCCESS")
    {
      return true;
    }
    return false;
  }

  //重写回调处理函数
  public function NotifyProcess($data, &$msg)
  {
    Log::DEBUG("call back:" . json_encode($data));
    $notfiyOutput = array();

    if(!array_key_exists("transaction_id", $data)){
      $msg = "输入参数不正确";
      return false;
    }
    //查询订单，判断订单真实性
    if(!$this->QueryOrder($data["transaction_id"])){
      $msg = "订单查询失败";
      return false;
    }
    //将控制权交给用户回调函数
    $b = true;
    if (is_callable($this->_notify_callback)) {
      $b = call_user_func_array($this->_notify_callback, array($data, &$msg)); //将$msg引用传递下去，便于回调函数改写
    }
    
    return $b ? true : false;
  }
}

/*----- END FILE: class.Wxpay.php -----*/