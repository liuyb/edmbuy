<?php
/**
 * 购物流程控制器
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Trade_Controller extends Controller {
  
  private $nav_no     = 2;       //主导航id
  private $topnav_no  = 0;       //顶部导航id
  private $nav_flag1  = 'cart';  //导航标识1
  private $nav_flag2  = '';      //导航标识2
  private $nav_flag3  = '';      //导航标识3
  
  /**
   * hook init
   *
   * @param string $action
   * @param Request $request
   * @param Response $response
   */
  public function init($action, Request $request, Response $response)
  {
    if (!$request->is_post()) {
      $this->v = new PageView();
      $this->v->add_render_filter(function(View $v){
        $v->assign('nav_no',     $this->nav_no)
          ->assign('topnav_no',  $this->topnav_no)
          ->assign('nav_flag1',  $this->nav_flag1)
          ->assign('nav_flag2',  $this->nav_flag2)
          ->assign('nav_flag3',  $this->nav_flag3);
      });
      $this->v->assign('no_display_cart', true);
    }
  }
  
  /**
   * hook menu
   * @see Controller::menu()
   */
  public function menu()
  {
    return [
      'trade/cart/add'    => 'cart_add',
      'trade/cart/list'   => 'cart_list',
      'trade/cart/delete' => 'cart_delete',
      'trade/cart/chgnum' => 'cart_chgnum',
      'trade/order/confirm'  => 'order_confirm',
      'trade/order/submit'   => 'order_submit',
      'trade/order/upaddress'=> 'order_upaddress',
      'trade/order/cancel'   => 'order_cancel',
      'trade/order/confirm_shipping'   => 'order_confirm_shipping',
      'trade/order/record'   => 'order_record',
      'trade/order/topay'    => 'order_topay',
    ];
  }
  
  /**
   * default action 'index'
   *
   * @param Request $request
   * @param Response $response
   */
  public function index(Request $request, Response $response)
  {
    
  }
  
  /**
   * 添加购物车
   *
   * @param Request $request
   * @param Response $response
   */
  public function cart_add(Request $request, Response $response)
  {
    if ($request->is_post()) {
      
      $goods_id = $request->post('goods_id',0);
      $goods_num= $request->post('goods_num',1);
      
      $ec_user_id = $GLOBALS['user']->ec_user_id;
      if (!$ec_user_id) {
        $ec_user_id = session_id();
      }
      
      $ret = Goods::addToCart($goods_id, $goods_num, $ec_user_id);
      if ($ret['code']>0) {
        $ret['cart_num'] = Goods::getUserCartNum($ec_user_id);
      }
      $response->sendJSON($ret);
    }
  }
  
  /**
   * 删除购物车中的商品
   *
   * @param Request $request
   * @param Response $response
   */
  public function cart_delete(Request $request, Response $response)
  {
    if ($request->is_post()) {
      $ret = ['flag'=>'FAIL','msg'=>'删除失败'];
      $rec_ids = $request->post('rec_id',[]);
      
      if(empty($rec_ids)) {
        $ret['msg'] = '没有要删的记录';
        $response->sendJSON($ret);
      }
      
      $user_id = $GLOBALS['user']->ec_user_id;
      if (!$user_id) {
        $ret['msg'] = '请先登录';
        $response->sendJSON($ret);
      }
      
      $ret = Goods::deleteCartGoods($rec_ids, $user_id);
      if ($ret['code']>0) {
        $ret['flag'] = 'SUC';
        $ret['rec_ids'] = $rec_ids;
      }
      $response->sendJSON($ret);
    }
  }
  
  /**
   * 改变购物车中的商品选购数量
   *
   * @param Request $request
   * @param Response $response
   */
  public function cart_chgnum(Request $request, Response $response)
  {
    if ($request->is_post()) {
      $ret = ['flag'=>'FAIL','msg'=>'更改失败'];
      $rec_ids = $request->post('rec_id',[]);
      $gnums   = $request->post('gnum',[]);
      
      if(empty($rec_ids)) {
        $ret['msg'] = '没有要更改的记录';
        $response->sendJSON($ret);
      }
      
      $user_id = $GLOBALS['user']->ec_user_id;
      if (!$user_id) {
        $ret['msg'] = '请先登录';
        $response->sendJSON($ret);
      }
      
      $i = 0;
      $succ_rids = [];
      foreach ($rec_ids AS $rid) {
        if (Goods::changeCartGoodsNum($user_id, $rid, $gnums[$i], true, true)) {
          $succ_rids[] = $rid;
        }
        ++$i;
      }
      
      
      if (count($succ_rids)>0) {
        $ret['flag'] = 'SUC';
        $ret['succ_rids'] = $succ_rids;
      }
      $response->sendJSON($ret);
    }
  }
  
  /**
   * 添加购物车
   *
   * @param Request $request
   * @param Response $response
   */
  public function cart_list(Request $request, Response $response)
  {
    $this->v->set_tplname('mod_trade_cart_list');
    $this->nav_flag2 = 'cartlist';
    $this->topnav_no = 1; // >0: 表示有topnav bar，具体值标识哪个topnav bar(有多个的情况下)
    if ($request->is_hashreq()) {
      $user_id  = $GLOBALS['user']->ec_user_id;
      if (!$user_id) $user_id = session_id(); 
      $cartGoods = Goods::getUserCart($user_id);
      $cartNum   = Goods::getUserCartNum($user_id);
      $this->v->assign('cartGoods', $cartGoods);
      $this->v->assign('cartNum', intval($cartNum));
      $this->v->assign('cartRecNum', count($cartGoods));
    }
    else {
      $backurl = U('explore');
      $this->v->assign('backurl', $backurl);
    }
    $response->send($this->v);
  }
  
  /**
   * 购买记录
   *
   * @param Request $request
   * @param Response $response
   */
  public function order_record(Request $request, Response $response)
  {
    $this->v->set_tplname('mod_trade_order_record');
    $this->nav_flag2 = 'buyrecord';
    $this->nav_no    = 0;
    $this->topnav_no = 1; // >0: 表示有topnav bar，具体值标识哪个topnav bar(有多个的情况下)
    if ($request->is_hashreq()) {
      
      $orders_num = 0;
      $errmsg = '';
      $this->v->add_render_filter(function(View $v) use(&$orders_num, &$errmsg){
        $v->assign('errmsg', $errmsg)
          ->assign('orders_num', $orders_num);
      });
      
      $ec_user_id = $GLOBALS['user']->ec_user_id;
      if (!$ec_user_id) {
        $errmsg = "无效请求";
        $response->send($this->v);
      }
      
      $orders = Goods::getOrderList($ec_user_id);
      $orders_num = count($orders);
      $this->v->assign('orders', $orders);
      
    }
    else {
      $refer = $request->refer();
      $backurl = U('explore');
      if (strpos($refer, '/user')!==false) { //来自用户中心
        $backurl = U('user');
      }
      $this->v->assign('backurl', $backurl);
    }
    $response->send($this->v);
  }
  
  /**
   * 订单确认
   *
   * @param Request $request
   * @param Response $response
   */
  public function order_confirm(Request $request, Response $response)
  {
    $this->v->set_tplname('mod_trade_order_confirm');
    $this->nav_flag1 = 'order';
    $this->nav_flag2 = 'order_confirm';
    $this->nav_no    = 0;
    if ($request->is_hashreq()) {
      $cart_rids = $request->get('cart_rids','');
      $timestamp = $request->get('t',0);
      $cart_rids = trim($cart_rids);
      
      $errmsg = '';
      $this->v->add_render_filter(function(View $v) use(&$errmsg){
        $v->assign('errmsg', $errmsg);
      });
      
      $now = simphp_time();
      $diff= abs($now-$timestamp);
      $this->v->assign('diff', $diff);
      if ( $diff > 60*150000) { //误差不能超过15分钟，否则判无效请求
        $errmsg = "无效请求";
        $response->send($this->v);
      }
      
      if (''==$cart_rids || !preg_match('/^(\d)+[,\d ]*$/', $cart_rids)) {
        $errmsg = "结账商品为空";
        $response->send($this->v);
      }
      
      $cart_rids = explode(',', $cart_rids);
      foreach ($cart_rids AS &$rid) {
        $rid = trim($rid);
      }
      
      //订单商品信息
      $order_goods = Goods::getCartsGoods($cart_rids, null, $total_price);
      $this->v->assign('order_goods', $order_goods);
      $this->v->assign('order_goods_num', count($order_goods));
      $this->v->assign('total_price', $total_price);
      $this->v->assign('cart_rids_str', implode(',',$cart_rids));
      
      //搜索地址
      $ec_user_id = $GLOBALS['user']->ec_user_id;
      $user_addrs = Goods::getUserAddress($ec_user_id);
      $this->v->assign('user_addrs', $user_addrs);
      $this->v->assign('user_addrs_num', count($user_addrs));
      
    }
    else {
      $code = $request->get('code', '');
      if (''!=$code) { //微信base授权
        
        $state = $request->get('state', '');
        
        //授权出错
        if (!in_array($state, array('base','detail'))) {
          Fn::show_error_message('授权出错，提交订单失败！', true);
        }
        
        $wx = new Weixin([Weixin::PLUGIN_JSADDR]);
        
        //用code换取access token
        $code_ret = $wx->request_access_token($code);
        if (!empty($code_ret['errcode'])) {
          Fn::show_error_message('微信授权错误<br/>'.$code_ret['errcode'].'('.$code_ret['errmsg'].')', true);
        }
        
        $accessToken = $code_ret['access_token'];
        $wxAddrJs = $wx->jsaddr->js($accessToken);
        $this->v->add_append_filter(function(PageView $v) use($wxAddrJs) {
          $v->append_to_foot_js .= $wxAddrJs;
        },'foot');
        
      }
      else { //正常访问
        if (Weixin::isWeixinBrowser()) {
          (new Weixin())->authorizing($request->url(), 'base'); //base授权获取access token以便于操作收货地址
        }
      }
    }
    $response->send($this->v);
  }
  
  /**
   * 订单确认
   *
   * @param Request $request
   * @param Response $response
   */
  public function order_submit(Request $request, Response $response)
  {
    if ($request->is_post()) {
      
      $ret = ['flag'=>'FAIL','msg'=>'订单提交失败'];
      
      $ec_user_id = $GLOBALS['user']->ec_user_id;
      if (!$ec_user_id) {
        $ret['msg'] = '未登录, 请登录';
        $response->sendJSON($ret);
      }
      
      $address_id    = $request->post('address_id', 0);
      $cart_rids_str = $request->post('cart_rids', '');
      $order_msg     = $request->post('order_msg', '');
      $pay_id        = $request->post('pay_id', 2); //2是微信支付，见ec payment表
      $pay_id = intval($pay_id);
      
      // 检查数据
      $address_id = intval($address_id);
      if (!$address_id) {
        $ret['msg'] = '请填写收货地址';
        $response->sendJSON($ret);
      }
      if (''==$cart_rids_str || !preg_match('/^(\d)+[,\d]*$/', $cart_rids_str)) { //要严格匹配类似格式"1,2,3",连空格也不能存在(因为自家合法的数据是不会有空格的)
        $ret['msg'] = '该订单无商品，请返回购物车添加';
        $response->sendJSON($ret);
      }
      
      // 收货地址
      $addr_info = Goods::getAddressInfo($address_id);
      if (empty($addr_info)) {
        $ret['msg'] = '收货地址无效，请重新填写';
        $response->sendJSON($ret);
      }
      
      // 支付信息
      $pay_info = Goods::getPaymentInfo($pay_id);
      if (empty($pay_info)) {
        $ret['msg'] = '该支付方式暂不可用，请重新选择';
        $response->sendJSON($ret);
      }
      
      // 配送信息
      $shipping_id = 1; //TODO 先不管配送方式，默认1先
      $shipping_info = Goods::getShippingInfo($shipping_id);
      if (empty($shipping_info)) {
        $ret['msg'] = '该配送方式暂不可用，请重新选择';
        $response->sendJSON($ret);
      }
      
      // 购物车商品列表
      $cart_rids_arr = explode(',', $cart_rids_str);
      $total_price = 0;
      $order_goods = Goods::getCartsGoods($cart_rids_arr, $ec_user_id, $total_price);
      if (count($order_goods)!=count($cart_rids_arr)) {
        $ret['msg'] = '该订单商品无效，请返回购物车重新添加';
        $response->sendJSON($ret);
      }
      
      $order_sn = Fn::gen_order_no();
      
      $ectb_order = ectable('order_info');
      $order = [
        'order_sn'         => $order_sn,
        'user_id'          => $ec_user_id,
        'order_status'     => OS_UNCONFIRMED,
        'shipping_status'  => SS_UNSHIPPED,
        'pay_status'       => PS_UNPAYED,
        'consignee'        => $addr_info['consignee'],
        'country'          => $addr_info['country'],
        'province'         => $addr_info['province'],
        'city'             => $addr_info['city'],
        'district'         => $addr_info['district'],
        'address'          => $addr_info['address'],
        'zipcode'          => $addr_info['zipcode'],
        'tel'              => $addr_info['tel'],
        'mobile'           => $addr_info['mobile'],
        'email'            => $addr_info['email'],
        'best_time'        => $addr_info['best_time'],
        'sign_building'    => $addr_info['sign_building'],
        'postscript'       => $order_msg,
        'shipping_id'      => $shipping_info['shipping_id'],
        'shipping_name'    => $shipping_info['shipping_name'],
        'pay_id'           => $pay_info['pay_id'],
        'pay_name'         => $pay_info['pay_name'],
        'how_oos'          => Fn::oos_status(OOS_WAIT),
        'how_surplus'      => '',
        //...
        'goods_amount'     => $total_price,
        'shipping_fee'     => 0,
        'order_amount'     => $total_price,
        //...
        'referer'          => '本站',
        'add_time'         => simphp_gmtime(), //跟从ecshop习惯，使用格林威治时间
        //...
      ];
      $order['order_amount'] = $order['goods_amount'] + $order['shipping_fee'];
      
      $order_id = D()->insert($ectb_order, $order, true, true);
      if ($order_id) { //订单表生成成功
        
        // 处理表 order_goods
        $order_update = []; //存储一些可能需要更新的字段数据
        $succ_goods   = []; //存储成功购买了的商品
        $true_amount  = 0;  //因为有可能存在失败商品，该字段存储真正产生的费用，而不是$total_price
        foreach ($order_goods AS $cg) {
          $curr_goods_id = $cg['goods_id'];
          $ginfo = Goods::getGoodsInfo($curr_goods_id, ['is_on_sale'=>1]);
          if (empty($ginfo) || $ginfo['goods_number']==0) { //商品下架或者库存为0，都不能购买
            continue;
          }
          
          //TODO 并发？
          $true_goods_number = $cg['goods_number']>$ginfo['goods_number'] ? $ginfo['goods_number']: $cg['goods_number'];
          Goods::changeGoodsStock($curr_goods_id, -$true_goods_number); //立即冻结商品对应数量的库存
          
          $rel_goods = [
            'order_id'     => $order_id,
            'goods_id'     => $curr_goods_id,
            'goods_name'   => $cg['goods_name'],
            'goods_sn'     => $cg['goods_sn'],
            'product_id'   => $cg['product_id'],
            'goods_number' => $true_goods_number,
            'market_price' => $cg['market_price'],
            'goods_price'  => $cg['goods_price'],
            'goods_attr'   => $cg['goods_attr'],
            'send_number'  => 0,
            'is_real'      => $cg['is_real'],
            'extension_code' => $cg['extension_code'],
            'parent_id'    => $cg['parent_id'],
            'is_gift'      => $cg['is_gift'],
            'goods_attr_id'=> $cg['goods_attr_id']
          ];
          $rec_id = D()->insert(ectable('order_goods'), $rel_goods, true, true);
          if ($rec_id) {
            $succ_goods[] = $cg;
            $true_amount += $cg['goods_price']*$true_goods_number;
          }
          else {
            Goods::changeGoodsStock($curr_goods_id, $true_goods_number); //立即恢复刚才冻结的商品库存
          }
        }
        
        //检测订单变化
        if ($true_amount!=$order['goods_amount']) {
          $order_update['goods_amount'] = $true_amount;
          $order_update['order_amount'] = $order_update['goods_amount'] + $order['shipping_fee'];
        }
        if (empty($succ_goods)) { //如果一个商品都没有购买成功，则需要更改此订单状态为"无效"OS_INVALID
          $order_update['order_status'] = OS_INVALID;
        }
        if (!empty($order_update)) {
          D()->update($ectb_order, $order_update, ['order_id'=>$order_id], true);
        }
        
        // 处理表 pay_log
        Trade_Model::insertPayLog($order_id, $order_sn, $true_amount, PAY_ORDER);
        
        // 没有成功购买的商品，则返回错误告诉用户重新添加
        if (empty($succ_goods)) {
          $ret['msg'] = '订单生成失败，请返回购物车更改数量后重新添加';
          $response->sendJSON($ret);
        }
        
        // 清除购物车
        Goods::deleteCartGoods($cart_rids_arr, $ec_user_id);
        
        $ret = ['flag'=>'SUC','msg'=>'订单提交成功','order_id'=>$order_id,'true_amount'=>$true_amount];
        $response->sendJSON($ret);
      }
      else {
        $ret['msg'] = '订单生成失败，请返回购物车重新添加';
        $response->sendJSON($ret);
      }
      
    }
    else {
      $this->v->set_tplname('mod_trade_order_submit');
      $this->nav_flag1 = 'order';
      $this->nav_flag2 = 'order_submit';
      $this->nav_no    = 0;
      if ($request->is_hashreq()) {
      
      }
      else {
      
      }
      $response->send($this->v);
    }
  }

  /**
   * 更新收货地址
   *
   * @param Request $request
   * @param Response $response
   */
  public function order_upaddress(Request $request, Response $response)
  {
    if ($request->is_post()) {
      $ret = ['flag'=>'FAIL','msg'=>'更新失败'];
      
      $ec_user_id = $GLOBALS['user']->ec_user_id;
      if (!$ec_user_id) {
        $ret['msg'] = '未登录, 请登录';
        $response->sendJSON($ret);
      }
      
      $address_id    = $request->post('address_id', 0);
      $consignee     = $request->post('consignee', '');
      $contact_phone = $request->post('contact_phone', '');
      $country       = $request->post('country', 1);
      $country_name  = $request->post('country_name', '中国');
      $province      = $request->post('province', 0);
      $province_name = $request->post('province_name', '');
      $city          = $request->post('city', 0);
      $city_name     = $request->post('city_name', '');
      $district      = $request->post('district', 0);
      $district_name = $request->post('district_name', '');
      $address       = $request->post('address', '');
      $zipcode       = $request->post('zipcode', '');
      
      $address_id = intval($address_id);
      $data = [
        'user_id'       => $ec_user_id,
        'consignee'     => $consignee,
        'country'       => $country,
        'country_name'  => $country_name,
        'province'      => $province,
        'province_name' => $province_name,
        'city'          => $city,
        'city_name'     => $city_name,
        'district'      => $district,
        'district_name' => $district_name,
        'address'       => $address,
        'zipcode'       => $zipcode,
      ];
      /*
      if (preg_match('/^1\d{10}$/', $contact_phone)) { //是手机号
        $data['mobile'] = $contact_phone;
      }
      else {
        $data['tel'] = $contact_phone;
      }
      */
      $data['tel'] = $contact_phone; //遵循ecshop习惯，优先使用tel(因为后台都是优先选择tel,mobile作为第二电话)
      
      $address_id = Goods::saveUserAddress($data, $address_id);
      $ret = ['flag'=>'SUC','msg'=>'更新成功','address_id'=>$address_id];
      
      $response->sendJSON($ret);
    }
  }

  /**
   * 取消订单
   *
   * @param Request $request
   * @param Response $response
   */
  public function order_cancel(Request $request, Response $response)
  {
    if ($request->is_post()) {
      $ret = ['flag'=>'FAIL','msg'=>'取消失败'];
      
      $ec_user_id = $GLOBALS['user']->ec_user_id;
      if (!$ec_user_id) {
        $ret['msg'] = '未登录, 请登录';
        $response->sendJSON($ret);
      }
      
      $order_id = $request->post('order_id', 0);
      if (!$order_id) {
        $ret['msg'] = '订单id为空';
        $response->sendJSON($ret);
      }
      
      $b = Order::cancel($order_id);
      if ($b) {
        $ret = ['flag'=>'SUC','msg'=>'取消成功', 'order_id'=>$order_id];
      }
      
      $response->sendJSON($ret);
    }
  }

  /**
   * 取消订单
   *
   * @param Request $request
   * @param Response $response
   */
  public function order_confirm_shipping(Request $request, Response $response)
  {
    if ($request->is_post()) {
      $ret = ['flag'=>'FAIL','msg'=>'取消失败'];
      
      $ec_user_id = $GLOBALS['user']->ec_user_id;
      if (!$ec_user_id) {
        $ret['msg'] = '未登录, 请登录';
        $response->sendJSON($ret);
      }
      
      $order_id = $request->post('order_id', 0);
      if (!$order_id) {
        $ret['msg'] = '订单id为空';
        $response->sendJSON($ret);
      }
      
      $b = Order::confirm_shipping($order_id);
      if ($b) {
        $ret = ['flag'=>'SUC','msg'=>'确认成功', 'order_id'=>$order_id];
      }
      
      $response->sendJSON($ret);
    }
  }
  
  /**
   * tips页显示
   * @param Request $request
   * @param Response $response
   */
  public function order_topay(Request $request, Response $response){
    
    
    if ($request->is_post()) {
      
      global $user;
      if (!$user->uid) {
        Fn::show_error_message('未登录，请先登录');
      }
      
      $this->v = new PageView('','topay');
      
      $pay_mode = $request->post('pay_mode', 'wxpay'); //默认微信支付
      $order_id = $request->post('order_id', 0);
      $back_url = $request->post('back_url', '');
      
      $supported_paymode = [
        'wxpay'  => '微信安全支付',
        'alipay' => '支付宝支付',
      ];
      
      if (!in_array($pay_mode, array_keys($supported_paymode))) {
        Fn::show_error_message('不支持该支付方式: '.$pay_mode);
      }
      if (!$order_id) {
        Fn::show_error_message('订单为空');
      }
      
      $order_info = Order::info($order_id);
      if (empty($order_info)) {
        Fn::show_error_message('订单不存在');
      }
      else {
        $order_info['order_goods'] = Goods::getOrderGoods($order_info['order_id']);
        if (empty($order_info['order_goods'])) {
          Fn::show_error_message('订单下没有对应商品');
        }
      }
      
      if ('wxpay'==$pay_mode) {
        $jsApiParams = Wxpay::unifiedOrder($order_info, $user->openid);
        $this->v->assign('jsApiParams', $jsApiParams);
      }
      
      $this->v->assign('pay_mode', $pay_mode);
      $this->v->assign('supported_paymode', $supported_paymode);
      
      $this->v->assign('back_url', $back_url);
      
      $response->send($this->v);
      
    }
    else {
      Fn::show_error_message('非法访问');
    }
    
  }
  
}
 
/*----- END FILE: Trade_Controller.php -----*/