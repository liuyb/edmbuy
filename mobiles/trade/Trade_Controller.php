<?php
/**
 * 购物流程控制器
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Trade_Controller extends MobileController {
  
  /**
   * hook init
   *
   * @param string $action
   * @param Request $request
   * @param Response $response
   */
  public function init($action, Request $request, Response $response)
  {
    $this->nav_flag1 = 'cart';
		parent::init($action, $request, $response);
  }
  
  /**
   * hook menu
   * @see Controller::menu()
   */
  public function menu()
  {
    return [
      'trade/buy'            => 'buy',
      'trade/cart/add'       => 'cart_add',
      'trade/cart/list'      => 'cart_list',
      'trade/cart/delete'    => 'cart_delete',
      'trade/cart/chgnum'    => 'cart_chgnum',
      'trade/order/confirm'  => 'order_confirm',
      'trade/order/submit'   => 'order_submit',
      'trade/order/upaddress'=> 'order_upaddress',
      'trade/order/cancel'   => 'order_cancel',
      'trade/order/confirm_shipping'   => 'order_confirm_shipping',
      'trade/order/record'   => 'order_record',
      'trade/order/topay'    => 'order_topay',
      'trade/order/payok'    => 'order_payok',
    ];
  }

  /**
   * 直接购买
   *
   * @param Request $request
   * @param Response $response
   */
  public function buy(Request $request, Response $response)
  {
  	if ($request->is_post()) {
  
  		$item_id  = $request->post('item_id' , 0);
  		$item_num = $request->post('item_num', 1);
  
  		$shopping_uid = Cart::shopping_uid();
  		$ret = Cart::addItem($item_id, $item_num, true, $shopping_uid);
  		if ($ret['code']>0) {
  			$ret['ts'] = simphp_time();
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
  public function cart_add(Request $request, Response $response)
  {
    if ($request->is_post()) {
      
      $item_id  = $request->post('item_id' , 0);
      $item_num = $request->post('item_num', 1);
      
      $shopping_uid = Cart::shopping_uid();
      
      $ret = Cart::addItem($item_id, $item_num, false, $shopping_uid);
      if ($ret['code']>0) {
        $ret['cart_num'] = Cart::getUserCartNum($shopping_uid);
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
      
      $user_id = $GLOBALS['user']->uid;
      if (!$user_id) {
        $ret['msg'] = '请先登录';
        $response->sendJSON($ret);
      }
      
      $ret = Cart::deleteGoods($rec_ids, $user_id);
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
        if (Cart::changeCartGoodsNum($user_id, $rid, $gnums[$i], true, true)) {
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
    $this->nav_flag1 = 'cart';
    $this->nav_no    = 1;
    $this->topnav_no = 1;
    $this->backurl = '/';
    
    $shop_uid = Cart::shopping_uid();
    $cartNum  = Cart::getUserCartNum($shop_uid);
    if (!$cartNum) {
    	$this->nav_no    = 0;
    }
    
    $mnav = $request->get('mnav', 0);
    if ($mnav) {
    	$this->nav_no    = 2;
    	$this->nav_flag1 = 'cart_mnav';
    	if (!$cartNum) {
    		$this->nav_no  = 2;
    	}
    }
    else {
    	$this->backurl = 'javascript:history.back();';
    }
    $this->v->assign('mnav', $mnav);
    
    if ($request->is_hashreq()) {
      $cartGoods= Cart::getUserCart($shop_uid);
      
      //将数据库列表转化成根据商家聚合列表
      $cartMerchantGoods = [];
      foreach ($cartGoods AS $cg) {
      	if (!isset($cartMerchantGoods[$cg->merchant_uid])) {
      		$cartMerchantGoods[$cg->merchant_uid] = ['merchant_uid'=>$cg->merchant_uid,'merchant_name'=>$cg->merchant_name,'glist'=>[]];
      	}
      	array_push($cartMerchantGoods[$cg->merchant_uid]['glist'], $cg);
      }
      $this->v->assign('cartGoods', $cartMerchantGoods);
      $this->v->assign('cartNum', intval($cartNum));
    }
    else {
    	
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
      
      $user_id = $GLOBALS['user']->ec_user_id;
      if (!$user_id) {
        $errmsg = "无效请求";
        $response->send($this->v);
      }
      
      $orders = Goods::getOrderList($user_id);
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
      $cart_nums = $request->get('cart_nums','');
      $timestamp = $request->get('t',0);
      $cart_rids = trim($cart_rids);
      
      //检查输入
      $now  = simphp_time();
      $diff = abs($now-$timestamp);
      if ( $diff > 60*60*15) { //误差不能超过15分钟，否则判无效请求
        throw new ViewException($this->v, "无效请求");
      }
      if (''==$cart_rids || !preg_match('/^(\d)+[,\d ]*$/', $cart_rids)) {
        throw new ViewException($this->v, "结账商品为空");
      }
      
      //标准化商品id，同时如果$cart_nums不为空，则更新相应的cartnum
      $cart_rids = explode(',', $cart_rids);
      $cart_nums = explode(',', $cart_nums);
      $i = 0;
      $shopping_uid = Cart::shopping_uid();
      foreach ($cart_rids AS &$rid) {
        $rid = trim($rid);
        if (isset($cart_nums[$i]) && !empty($cart_nums[$i])) {
        	Cart::changeCartGoodsNum($shopping_uid, $rid, $cart_nums[$i], true, true);
        }
        $i++;
      }
      
      //订单商品信息
      $order_goods = Cart::getGoods($cart_rids, null, $total_price);
      $this->v->assign('order_goods', $order_goods);
      $this->v->assign('order_goods_num', count($order_goods));
      $this->v->assign('total_price', $total_price);
      $this->v->assign('cart_rids_str', implode(',',$cart_rids));
      
      //搜索地址
      $user_addrs = Users::getAddress($GLOBALS['user']->uid);
      $this->v->assign('user_addrs', $user_addrs);
      $this->v->assign('user_addrs_num', count($user_addrs));
      
    }
    else {
      $code = $request->get('code', '');
      if (''!=$code) { //微信base授权
        
        $state = $request->get('state', '');
        
        //授权出错
        if (!in_array($state, Weixin::$allowOAuthScopes)) {
          Fn::show_error_message('授权出错，提交订单失败！', true);
        }
        
        $wx = new Weixin([Weixin::PLUGIN_JSADDR]);
        
        //用code换取access token
        $code_ret = $wx->request_access_token($code);
        if (!empty($code_ret['errcode'])) {
          Fn::show_error_message('微信授权错误<br/><span style="font-size:16px;">'.$code_ret['errcode'].'('.$code_ret['errmsg'].')</span>', true);
        }
        
        $accessToken = $code_ret['access_token'];
        $wxAddrJs = $wx->jsaddr->js($accessToken);
        $this->v->add_append_filter(function(PageView $v) use($wxAddrJs) {
          $v->append_to_foot_js .= $wxAddrJs;
        },'foot');
        
      }
      else { //正常访问
        if (Weixin::isWeixinBrowser()) {
        	(new Weixin())->authorizing_base('jsapi_address',$request->url());//base授权获取access token以便于操作收货地址
        }
      }
    }
    
    throw new ViewResponse($this->v);
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
      
      $user_id = $GLOBALS['user']->uid;
      if (!$user_id) {
        $ret['msg'] = '未登录, 请登录';
        $response->sendJSON($ret);
      }
      
      $address_id    = $request->post('address_id', 0);
      $cart_rids_str = $request->post('cart_rids', '');
      $order_msg     = $request->post('order_msg', '');
      $pay_id        = $request->post('pay_id', 2); //2是微信支付，见ec payment表
      $pay_id        = intval($pay_id);
      
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
      $exAddr = UserAddress::load($address_id);
      if (!$exAddr->is_exist()) {
        $ret['msg'] = '收货地址无效，请重新填写';
        $response->sendJSON($ret);
      }
      
      // 支付信息
      $exPay = Payment::load($pay_id);
      if (!$exPay->is_exist()) {
        $ret['msg'] = '该支付方式暂不可用，请重新选择';
        $response->sendJSON($ret);
      }
      
      // 配送信息
      $shipping_id = 1; //TODO 先不管配送方式，默认1先
      $exShip = Shipping::load($shipping_id);
      if (!$exShip->is_exist()) {
        $ret['msg'] = '该配送方式暂不可用，请重新选择';
        $response->sendJSON($ret);
      }
      
      // 购物车商品列表
      $cart_rids_arr = explode(',', $cart_rids_str);
      $total_price = 0;
      $order_goods = Cart::getGoods($cart_rids_arr, $user_id, $total_price);
      if (count($order_goods)!=count($cart_rids_arr)) {
        $ret['msg'] = '该订单商品无效，请返回购物车重新添加';
        $response->sendJSON($ret);
      }
      
      $order_sn = Fn::gen_order_no();
      $newOrder  = new Order();
      $newOrder->order_sn     = $order_sn;
      $newOrder->pay_trade_no = '';
      $newOrder->user_id      = $user_id;
      $newOrder->order_status = OS_UNCONFIRMED;
      $newOrder->shipping_status = SS_UNSHIPPED;
      $newOrder->pay_status   = PS_UNPAYED;
      $newOrder->consignee    = $exAddr->consignee;
      $newOrder->country      = $exAddr->country;
      $newOrder->province     = $exAddr->province;
      $newOrder->city         = $exAddr->city;
      $newOrder->district     = $exAddr->district;
      $newOrder->address      = $exAddr->address;
      $newOrder->zipcode      = $exAddr->zipcode;
      $newOrder->tel          = $exAddr->tel;
      $newOrder->mobile       = $exAddr->mobile;
      $newOrder->email        = $exAddr->email;
      $newOrder->best_time    = $exAddr->best_time;
      $newOrder->sign_building= $exAddr->sign_building;
      $newOrder->postscript   = $order_msg;
      $newOrder->shipping_id  = $exShip->shipping_id;
      $newOrder->shipping_name = $exShip->shipping_name;
      $newOrder->pay_id       = $exPay->pay_id;
      $newOrder->pay_name     = $exPay->pay_name;
      $newOrder->how_oos      = Fn::oos_status(OOS_WAIT);
      $newOrder->how_surplus  = '';
      //...
      $newOrder->goods_amount = $total_price;
      $newOrder->shipping_fee = 0;
      $newOrder->order_amount = $newOrder->goods_amount + $newOrder->shipping_fee;
      $newOrder->commision    = 0;
      //...
      $newOrder->referer      = '本站';
      $newOrder->add_time     = simphp_gmtime(); //跟从ecshop习惯，使用格林威治时间
      //...
      
      $newOrder->save(Storage::SAVE_INSERT);
      $order_id = 0;
      if ($newOrder->id) { //订单表生成成功
      	
        $order_id = $newOrder->id;
        
        // 处理表 order_goods
        $order_update = [];   //存储一些可能需要更新的字段数据
        $succ_goods   = [];   //存储成功购买了的商品
        $true_amount  = 0;    //因为有可能存在失败商品，该字段存储真正产生的费用，而不是$total_price
        $total_commision = 0; //总佣金
        foreach ($order_goods AS $cg) {
          $currItemId = $cg['goods_id'];
          $cItem = Items::load($currItemId);
          
          if (!$cItem->is_exist() || !$cItem->is_on_sale || !$cItem->item_number) { //商品下架或者库存为0，都不能购买
            continue;
          }
          
          //TODO 并发？
          $true_goods_number = $cg['goods_number']>$cItem->item_number ? $cItem->item_number: $cg['goods_number'];
          Items::changeStock($currItemId, -$true_goods_number); //立即冻结商品对应数量的库存
          
          $newOI = new OrderItems();
          $newOI->order_id    = $order_id;
          $newOI->goods_id    = $currItemId;
          $newOI->goods_name  = $cg['goods_name'];
          $newOI->goods_sn    = $cg['goods_sn'];
          $newOI->product_id  = $cg['product_id'];
          $newOI->goods_number= $true_goods_number;
          $newOI->market_price= $cg['market_price'];
          $newOI->goods_price = $cg['goods_price'];
          $newOI->goods_attr  = $cg['goods_attr'];
          $newOI->send_number = 0;
          $newOI->is_real     = $cg['is_real'];
          $newOI->extension_code = $cg['extension_code'];
          $newOI->parent_id   = $cg['parent_id'];
          $newOI->is_gift     = $cg['is_gift'];
          $newOI->goods_attr_id = $cg['goods_attr_id'];
          $newOI->save(Storage::SAVE_INSERT);
          
          if ($newOI->id) {
            $succ_goods[] = $cg;
            $true_amount += $cg['goods_price']*$true_goods_number;
            $total_commision += $cItem->commision*$true_goods_number;
          }
          else {
            Items::changeStock($currItemId, $true_goods_number); //立即恢复刚才冻结的商品库存
          }
          
          //关联订单与商家
          Order::relateMerchant($newOrder->id, $cItem->merchant_uid);
          
        }//END foreach loop
        
        //总佣金
        $order_update['commision'] = $total_commision;
        
        //检测订单变化
        if ($true_amount!=$newOrder->goods_amount) {
          $order_update['goods_amount'] = $true_amount;
          $order_update['order_amount'] = $order_update['goods_amount'] + $newOrder->shipping_fee;
        }
        if (empty($succ_goods)) { //如果一个商品都没有购买成功，则需要更改此订单状态为"无效"OS_INVALID
          $order_update['order_status'] = OS_INVALID;
          $order_update['commision']    = 0;
        }
        if (!empty($order_update)) {
          D()->update(Order::table(), $order_update, ['order_id'=>$order_id]);
        }
        
        // 处理表 pay_log
        PayLog::insert($order_id, $order_sn, $true_amount, PAY_ORDER);
        
        // 没有成功购买的商品，则返回错误告诉用户重新添加
        if (empty($succ_goods)) {
          $ret['msg'] = '订单生成失败，请返回购物车更改数量后重新添加';
          $response->sendJSON($ret);
        }
        
        // 清除购物车
        Cart::deleteItems($cart_rids_arr, $user_id);
        
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
      $this->v->set_page_render_mode(View::RENDER_MODE_GENERAL);
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
    if ($request->is_post(true)) {
      $ret = ['flag'=>'FAIL','msg'=>'更新失败'];
      
      $user_id = $GLOBALS['user']->uid;
      if (!$user_id) {
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
      $upAddr = new UserAddress($address_id);
      $upAddr->user_id       = $user_id;
      $upAddr->consignee     = $consignee;
      $upAddr->country       = $country;
      $upAddr->country_name  = $country_name;
      $upAddr->province      = $province;
      $upAddr->province_name = $province_name;
      $upAddr->city          = $city;
      $upAddr->city_name     = $city_name;
      $upAddr->district      = $district;
      $upAddr->district_name = $district_name;
      $upAddr->address       = $address;
      $upAddr->zipcode       = $zipcode;
      $upAddr->tel           = $contact_phone; //遵循ecshop习惯，优先使用tel(因为后台都是优先选择tel,mobile作为第二电话)
      $upAddr->mobile        = $contact_phone;
      $upAddr->save();
      
      $ret = ['flag'=>'SUC','msg'=>'更新成功','address_id'=>$upAddr->id];
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
      
      $user_id = $GLOBALS['user']->ec_user_id;
      if (!$user_id) {
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
      
      $user_id = $GLOBALS['user']->ec_user_id;
      if (!$user_id) {
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
  public function order_topay(Request $request, Response $response)
  {
    
    if ($request->is_post()) {
      
      global $user;
      if (!$user->uid) {
        Fn::show_error_message('未登录，请先登录');
      }
      
      $this->v = new PageView('','topay');
      
      $pay_mode = $request->post('pay_mode', 'wxpay'); //默认微信支付
      $order_id = $request->post('order_id', 0);
      $back_url = $request->post('back_url', '');
      $back_url = $back_url . (strrpos($back_url, '?')===false ? '?' : '&') . 'order_id='.$order_id;
      
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
      
      $exOrder = Order::load($order_id);
      if (!$exOrder->is_exist()) {
        Fn::show_error_message('订单不存在');
      }
      else {
        $exOrder->order_goods = Order::getItems($exOrder->id);
        if (empty($exOrder->order_goods)) {
          Fn::show_error_message('订单下没有对应商品');
        }
      }
      $order_info = $exOrder->to_array(true);
      
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
  
  /**
   * 支付成功
   * @param Request $request
   * @param Response $response
   */
  public function order_payok(Request $request, Response $response)
  {
  	$this->v->set_tplname('mod_trade_order_payok');
  	$this->v->set_page_render_mode(View::RENDER_MODE_GENERAL);
  	$this->nav_no = 0;
  	
  	$order_id = $request->get('order_id',0);
  	$order = Order::load($order_id);
  	$order_amount = 0;
  	if ($order->is_exist()) {
  		$order_amount = $order->money_paid;
  	}
  	$this->v->assign('order_amount', $order_amount);
  	
  	global $user;
  	$total_paid = $user->total_paid();
  	$user_level = 0;
  	$level_amount = Users::$level_amount[Users::USER_LEVEL_1];
  	if ($total_paid >= $level_amount || $total_paid+$order_amount >= $level_amount) {
  		$user_level = 1;
  	}
  	$this->v->assign('user_level', $user_level);
  	
  	$response->send($this->v);
  }
}
 
/*----- END FILE: Trade_Controller.php -----*/