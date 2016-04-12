<?php
/**
 * Static functions class 'Fn::', extend from Func:: 
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
class Fn extends Func {
  
  /**
   * 将yyyyMMddHHmmss格式的时间串转成Unix Timestamp
   * @param string $time_string
   * @return integer
   */
  static function to_timestamp($time_string) {
    $time_string = substr($time_string, 0, 4).'-'.substr($time_string, 4, 2). '-'.substr($time_string, 6, 2).' '
                  .substr($time_string, 8, 2).':'.substr($time_string, 10, 2).':'.substr($time_string, 12, 2); //转成 2014-10-30 13:35:25 的格式
    $time_string = strtotime($time_string); //转成 Unix Timestamp 的格式
    return $time_string;
  }
  
  /**
   * 默认头像
   */
  static function default_logo(){
    return C('env.contextpath').'misc/images/avatar/default_ulogo.png';
  }
  
  /**
   * 显示错误消息
   *
   * @param string $msg 显示的消息
   * @param boolean $with_back_btn 带“返回”按钮
   * @param string $title 文档标题(不包括appname部分)
   */
  static function show_error_message($msg='非法访问！', $with_back_btn=false, $title='错误发生') {
    $ctrl_str = '';
    if ($with_back_btn) {
      $ctrl_str .= '<a href="javascript:history.back()">返回</a>&nbsp;|&nbsp;';
    }
    $title .= ' - '.L('appname');
    $str = '<!DOCTYPE html>';
    $str.= '<html>';
    $str.= '<head>';
    $str.= '<meta http-equiv="Content-Type" Content="text/html;charset=utf-8" />';
    $str.= '<title>'.$title.'</title>';
    $str.= '<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">';
    $str.= '<style type="text/css">html,body,table,tr,td,a{margin:0;padding:0;border:0;font-size:100%;font:inherit;vertical-align:baseline;} html {font-size: 62.5%;} body {font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;text-rendering: optimizeLegibility;} html,body{display:block;width:100%;height:100%;} table{width:100%;height:100%;border-top:3px solid #44b549;-moz-box-sizing: border-box;-webkit-box-sizing: border-box;-o-box-sizing: border-box;-ms-box-sizing: border-box;box-sizing: border-box;} table td{text-align:center;vertical-align:middle;font-size:22px;font-size:2.2rem;font-weight:bold;} table td a{font-size:18px;font-size:1.8rem;font-weight:normal;}</style>';
    $str.= '</head>';
    $str.= '<body>';
    $str.= '<table><tr><td>'.$msg.'<br/><br/>'.$ctrl_str.'<a href="javascript:;" id="closeWindow">关&nbsp;闭</a></td></tr></table>';
    $str.= '<script>var readyFunc = function(){document.querySelector("#closeWindow").addEventListener("click", function(e){if(typeof WeixinJSBridge === "undefined") window.close();else WeixinJSBridge.invoke("closeWindow",{},function(res){});return false;});};if (typeof WeixinJSBridge === "undefined") {document.addEventListener("WeixinJSBridgeReady", readyFunc, false);} else {readyFunc();}</script>';
    $str.= '</body>';
    $str.= '</html>';
    echo $str;
    exit;
  }
  
  /**
   * 显示PC端的错误消息
   *
   * @param string $msg 显示的消息
   * @param boolean $with_back_btn 带“返回”按钮
   * @param string $title 文档标题(不包括appname部分)
   */
  static function show_pcerror_message($msg='非法访问！', $with_back_btn=true, $title='错误发生') {
      $ctrl_str = '';
      if ($with_back_btn) {
          $ctrl_str .= '<a href="javascript:history.back()">返回</a>';
      }
      $title .= ' - '.L('appname');
      $str = '<!DOCTYPE html>';
      $str.= '<html>';
      $str.= '<head>';
      $str.= '<meta http-equiv="Content-Type" Content="text/html;charset=utf-8" />';
      $str.= '<title>'.$title.'</title>';
      $str.= '<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">';
      $str.= '<style type="text/css">html,body,table,tr,td,a{margin:0;padding:0;border:0;font-size:100%;font:inherit;vertical-align:baseline;} html {font-size: 62.5%;} body {font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;text-rendering: optimizeLegibility;} html,body{display:block;width:100%;height:100%;} table{width:100%;height:100%;border-top:3px solid #44b549;-moz-box-sizing: border-box;-webkit-box-sizing: border-box;-o-box-sizing: border-box;-ms-box-sizing: border-box;box-sizing: border-box;} table td{text-align:center;vertical-align:middle;font-size:22px;font-size:2.2rem;font-weight:bold;} table td a{font-size:18px;font-size:1.8rem;font-weight:normal;}</style>';
      $str.= '</head>';
      $str.= '<body>';
      $str.= '<table><tr><td>'.$msg.'<br/><br/>'.$ctrl_str.'</td></tr></table>';
      $str.= '</body>';
      $str.= '</html>';
      echo $str;
      exit;
  }
  
  /**
   * 检查order sn格式是否合法
   * @return boolean
   */
  static function check_order_sn($order_sn) {
  	if (!empty($order_sn) && preg_match('/^E\d{19}$/', $order_sn)) {
  		return true;
  	}
  	return false;
  }
  
  /**
   * 生成订单号
   * @return string
   */
  static function gen_order_no() {
    /* 选择一个随机的方案 */
    mt_srand((double) microtime() * 1000000);
    return 'E'.date('YmdHis') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
  }
  
  /**
   * 根据输入id生成二维码目录
   * 
   * @param integer $id
   * @param string  $prefix
   * @param boolean $is_absolute
   * @return string
   */
  static function gen_qrcode_dir($id, $prefix = 'item', $is_absolute = FALSE) {
  	$dir = ($is_absolute ? SIMPHP_ROOT : '').'/a/qrcode/';
  	$folder_limit = File::FOLDER_FILE_LIMIT; //Linux ext3 limit 32000
  	if (!empty($prefix)) {
  		$dir .= $prefix . '/';
  	}
  	$dir .= intval($id / $folder_limit) . '/';
  	return $dir;
  }
  
  /**
   * 通用状态读取逻辑
   * 
   * @param array $status_set
   * @param integer $status
   * @return mixed(string|array) 当传入的$status<0时，返回整个状态数组，否则，返回对应状态码描述，返回false时表示不存在$status指定的状态
   */
  protected static function status(Array &$status_set, $status) {
    if ($status < 0) {
      return $status_set;
    }
    else {
      if (isset($status_set[$status])) {
        return $status_set[$status];
      }
    }
    return false;
  } 
  
  /**
   * 订单状态
   * 
   * @param integer $status
   * @return mixed(string|array) 当传入的$status<0时，返回整个状态数组，否则，返回对应状态码描述，返回false时表示不存在$status指定的状态
   */
  static function order_status($status) {
    static $status_set = array(
      OS_UNCONFIRMED   => '未确认',
      OS_CONFIRMED     => '已确认',
      OS_CANCELED      => '已取消',
      OS_INVALID       => '无效',
      OS_RETURNED      => '退货',
      OS_SPLITED       => '已分单',
      OS_SPLITING_PART => '部分分单',
      OS_REFUND        => '已退款',
      OS_REFUND_PART   => '部分退款',
    );
    return self::status($status_set, $status);
  }
  
  /**
   * 配送状态
   * 
   * @param integer $status
   * @return mixed(string|array) 当传入的$status<0时，返回整个状态数组，否则，返回对应状态码描述，返回false时表示不存在$status指定的状态
   */
  static function shipping_status($status) {
    static $status_set = array(
      SS_UNSHIPPED    => '未发货',
      SS_SHIPPED      => '已发货',
      SS_RECEIVED     => '已收货',
      SS_PREPARING    => '备货中',
      SS_SHIPPED_PART => '已发货(部分商品)',
      SS_SHIPPED_ING  => '发货中(处理分单)',
      OS_SHIPPED_PART => '已发货(部分商品)',
      SS_RECEIVED_PART=> '已收货(部分商品)',
    );
    return self::status($status_set, $status);
  }
  
  /**
   * 支付状态
   * 
   * @param integer $status
   * @return mixed(string|array) 当传入的$status<0时，返回整个状态数组，否则，返回对应状态码描述，返回false时表示不存在$status指定的状态
   */
  static function pay_status($status) {
    static $status_set = array(
      PS_UNPAYED => '未付款',
      PS_PAYING  => '付款中',
      PS_PAYED   => '已付款',
    	PS_REFUNDING=> '退款中',
      PS_REFUND  => '已退款',
      PS_CANCEL  => '取消支付',
      PS_FAIL    => '支付失败',
    );
    return self::status($status_set, $status);
  }
  
  /**
   * 综合状态
   * 
   * @param integer $status
   * @return mixed(string|array) 当传入的$status<0时，返回整个状态数组，否则，返回对应状态码描述，返回false时表示不存在$status指定的状态
   */
  static function zonghe_status($status) {
    static $status_set = array(
      CS_AWAIT_PAY  => '待付款', //货到付款且已发货且未付款，非货到付款且未付款
      CS_AWAIT_SHIP => '待发货', //货到付款且未发货，非货到付款且已付款且未发货
      CS_FINISHED   => '已完成'  //已完成：已确认、已付款、已发货
    );
    return self::status($status_set, $status);
  }
  
  /**
   * 缺货处理
   * 
   * @param integer $status
   * @return mixed(string|array) 当传入的$status<0时，返回整个状态数组，否则，返回对应状态码描述，返回false时表示不存在$status指定的状态
   */
  static function oos_status($status) {
    static $status_set = array(
      OOS_WAIT    => '等待货物备齐后再发',
      OOS_CANCEL  => '取消订单',
      OOS_CONSULT => '与店主协商'
    );
    return self::status($status_set, $status);
  }
  
  /**
   * 读取($value为NULL时)或者设置缓存refer uri
   * @param string $value
   */
  static function cache_refer_uri($value = NULL) {
    $ck = 'refer_uri';
    if (!isset($value)) {
      $_SESSION[$ck] = $value;
    }
    else {
      return isset($_SESSION[$ck]) ? $_SESSION[$ck] : null;
    }
  }
    
  /**
   * 解析"钱"数字，单位：元
   * @param float $val
   * @return string
   */
  static function money_yuan($val) {
  	$val = number_format($val,2,'.','');
  	if (preg_match('/\.0+$/', strval($val))) {
  		$val = intval($val);
  	}
  	return $val;
  }
  
  /**
   * 待付款   查询的时候需要判断订单是否已关闭
   * 已关闭用或条件，其他用and条件
   * 根据传入的综合状态返回 订单状态
   * @param unknown $composite_status
   */
  static function get_order_status($composite_status){
      switch($composite_status){
          //待付款  
          case CS_AWAIT_PAY :
              $ret = array("pay_status" => array(PS_UNPAYED, PS_PAYING));
              break;
          //备货中
          case CS_STOCKING :
              $ret = array("pay_status" => PS_PAYED,
              "shipping_status" => array(SS_PREPARING, SS_SHIPPED_ING));
              break;
          //待发货
          case CS_AWAIT_SHIP : 
              $ret = array("pay_status" => PS_PAYED,
                           "shipping_status" => array(SS_UNSHIPPED));
              break;
          //待收货
          case CS_AWAIT_RECEIVE : 
              $ret = array("pay_status" => PS_PAYED,
                  "shipping_status" => array(SS_SHIPPED, SS_SHIPPED_PART, OS_SHIPPED_PART));
              break;
          //已完成
          case CS_FINISHED : 
              $ret = array("pay_status" => PS_PAYED, "shipping_status" => SS_RECEIVED);
              break;
          //已关闭  
          case CS_CLOSED : 
              $ret = array("pay_status" => array(PS_CANCEL, PS_REFUND), 
                           "order_status" => array(OS_CANCELED, OS_INVALID, OS_REFUND, OS_RETURNED));
              break;
          default:
              $ret = [];
      }
      return $ret;
  }
  
  /**
   * 根据订单状态返回显示的订单文本
   * @param unknown $pay_status
   * @param unknown $ship_status
   * @param unknown $order_status
   */
  static function get_order_text($pay_status, $ship_status, $order_status){
      $ret = "未知状态";
      if(in_array($pay_status, array(PS_CANCEL, PS_REFUND)) 
            || in_array($order_status, array(OS_CANCELED, OS_INVALID, OS_REFUND, OS_RETURNED))){
          $ret = "已关闭";
      }else if(in_array($pay_status, array(PS_UNPAYED, PS_PAYING))){
          $ret = "待付款";
      }else if($pay_status == PS_PAYED){
          if($ship_status == SS_RECEIVED){
              $ret = "已完成";
          }else if($ship_status == SS_UNSHIPPED){
              $ret = "待发货";
          }else if(in_array($ship_status, array(SS_PREPARING, SS_SHIPPED_ING))){
              $ret = "备货中";
          }else if(in_array($ship_status, array(SS_SHIPPED, SS_SHIPPED_PART, OS_SHIPPED_PART))){
              $ret = "待收货";
          }
      }
      return $ret;
  }
  
}
 
/*----- END FILE: class.Fn.php -----*/