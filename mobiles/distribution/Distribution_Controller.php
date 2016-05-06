<?php
defined('IN_SIMPHP') or die('Access Denied');

/**
 * 多米分销 控制器
 * @author Jean
 *
 */
class Distribution_Controller extends MobileController{
    
    /**
     * hook menu
     * @see Controller::menu()
     */
    public function menu()
    {
        return [
            'distribt' => 'index',
            'distribution/merchants' => 'merchants',
            'distribution/merchants/list' => 'merchant_list',
            'distribution/my' => 'my_center',
            'distribution/my/parent' => 'my_parent',
            'distribution/my/child/agent' => 'my_child_agent',
            'distribution/my/child/agent/list' => 'my_child_agent_list',
            'distribution/my/child/shop' => 'my_child_shop',
            'distribution/my/child/shop/list' => 'my_child_shop_list',
            'distribution/my/child/agent/count' => 'getMyChildAgencyCount',
            'distribution/my/child/shop/count' => 'getMyChildShopCount',
            'distribution/spread' => 'spread',
            'distribution/shop' => 'shop_info',
            'distribution/agent' => 'agent_center',
            'distribution/agent/paid/succ' => 'agent_pay_succ',
            'distribution/agent/package' => 'show_agent_package',
            /* 'trade/order/package/confirm' => 'confirm_agent_premium', */
            'distribution/agent/premium/buy' => 'free_buy_agent_premium',
            'distribution/agent/premium/succ' => 'buy_premium_succ',
            'distribution/test/buy' => 'test_buy_agent',
            'distribution/test/clear' => 'clear_buy_agent',
        ];
    }
    
    /**
     * hook init
     *
     * @param string $action
     * @param Request $request
     * @param Response $response
     */
    public function init($action, Request $request, Response $response)
    {
        $this->nav_flag1 = 'dmfx';
        parent::init($action, $request, $response);
    }
    
    /**
     * default action 'index'
     *
     * @param Request $request
     * @param Response $response
     */
    public function index(Request $request, Response $response)
    {
        $this->setPageView($request, $response, '_page_spa');
        throw new ViewResponse($this->v);
    }
    
    /**
     * 商家联盟
     * @param Request $request
     * @param Response $response
     * @throws ViewResponse
     */
    public function merchants(Request $request, Response $response){
        $this->setPageView($request, $response, '_page_mpa');
        $this->v->set_page_render_mode(View::RENDER_MODE_GENERAL);
        $this->nav_flag2 = 'merchants';
        $this->topnav_no = 1;
        $this->v->set_tplname('mod_distribution_merchants');
        throw new ViewResponse($this->v);
    }
    
    /**
     * 商家联盟列表
     * @param Request $request
     * @param Response $response
     */
    public function merchant_list(Request $request, Response $response){
        $curpage = $request->get('curpage', 1);
        $orderby = $request->get('orderby', 'oc');
        $pager = new PagerPull($curpage, 20);
        Distribution_Model::getMerchantsList($pager, array("orderby" => $orderby));
        $ret = $pager->outputPageJson();
        $response->sendJSON($ret);
    }
    
    /**
     * 店铺信息
     * @param Request $request
     * @param Response $response
     * @throws ViewResponse
     */
    public function shop_info(Request $request, Response $response){
        $this->setPageView($request, $response, '_page_mpa');
        $this->v->set_tplname('mod_distribution_shop');
        $this->v->set_page_render_mode(View::RENDER_MODE_GENERAL);
        $this->nav_flag2 = 'shop';
        global $user;
        $merchant = Merchant::getMerchantByUserId($user->uid);
        if(!$merchant->is_exist()){
            throw new ViewResponse($this->v);
        }
        $muid = $merchant->uid;
        $all_goods = Merchant::getGoodsTotalByIsSale(-1, $muid);
        $all_orders = Merchant::getOrderTotalByStatus(-1, $muid);
        $sale_amount = Merchant::getOrderSalesMoney($muid);
        $wait_pay_orders = Merchant::getOrderTotalByStatus(CS_AWAIT_PAY, $muid);
        $wait_ship_orders = Merchant::getOrderTotalByStatus(CS_AWAIT_SHIP, $muid);
        $wait_return_orders = Merchant::getWaitRefundOrderTotal($muid);
        
        $this->v->assign('merchant', $merchant);
        $this->v->assign('user', $user);
        $this->v->assign('all_goods', $all_goods);
        $this->v->assign('all_orders', $all_orders);
        $this->v->assign('sale_amount', $sale_amount);
        $this->v->assign('wait_pay_orders', $wait_pay_orders);
        $this->v->assign('wait_ship_orders', $wait_ship_orders);
        $this->v->assign('wait_return_orders', $wait_return_orders);
        throw new ViewResponse($this->v);
    }
    
    /**
     * 个人中心
     * @param Request $request
     * @param Response $response
     * @throws ViewResponse
     */
    public function my_center(Request $request, Response $response){
        $this->setPageView($request, $response, '_page_mpa');
        $this->v->set_tplname('mod_distribution_my');
        $this->v->set_page_render_mode(View::RENDER_MODE_GENERAL);
        $this->nav_flag2 = 'my';
        global $user;
        if($user->parentid){
            $parent = Users::load($user->parentid);
            $this->v->assign('parent', $parent);
        }
        $uid = $user->uid;
        //我的代理总数
        $agentLevel1 = Partner::findFirstLevelCount($uid, Partner::LEVEL_TYPE_AGENCY);
        $agentLevel2 = Partner::findSecondLevelCount($uid, Partner::LEVEL_TYPE_AGENCY);
        $agentLevel3 = Partner::findThirdLevelCount($uid, Partner::LEVEL_TYPE_AGENCY);
        $agentTotal = intval($agentLevel1) + intval($agentLevel2) + intval($agentLevel3);
        //我的店铺总数
        $shopLevel1 = Distribution_Model::findFirstLevelShopCount($uid);
        $shopLevel2 = Distribution_Model::findSecondLevelShopCount($uid);
        $shopLevel3 = Distribution_Model::findThirdLevelShopCount($uid);
        $shopTotal = intval($shopLevel1) + intval($shopLevel2) + intval($shopLevel3);
        
        $this->v->assign('user', $user);
        $this->v->assign('agentTotal', $agentTotal);
        $this->v->assign('shopTotal', $shopTotal);
        throw new ViewResponse($this->v);
    }
    
    /**
     * 我的推荐人
     * @param Request $request
     * @param Response $response
     * @throws ViewResponse
     */
    public function my_parent(Request $request, Response $response){
        $this->setPageView($request, $response, '_page_mpa');
        $this->v->set_tplname('mod_distribution_myparent');
        $this->v->set_page_render_mode(View::RENDER_MODE_GENERAL);
        $this->nav_no = 0;
        $this->nav_flag2 = 'my';
        global $user;
        $parent = Users::load($user->parentid);
        $this->v->assign('parent', $parent);
        throw new ViewResponse($this->v);
    }
    
    /**
     * 我发展的代理
     * @param Request $request
     * @param Response $response
     * @throws ViewResponse
     */
    public function my_child_agent(Request $request, Response $response){
        $this->setPageView($request, $response, '_page_mpa');
        $this->v->set_tplname('mod_distribution_myagency');
        $this->v->set_page_render_mode(View::RENDER_MODE_GENERAL);
        $this->nav_no = 0;
        $uid = $GLOBALS['user']->uid;
        $level1 = Partner::findFirstLevelCount($uid, Partner::LEVEL_TYPE_AGENCY);
        $level2 = Partner::findSecondLevelCount($uid, Partner::LEVEL_TYPE_AGENCY);
        $level3 = Partner::findThirdLevelCount($uid, Partner::LEVEL_TYPE_AGENCY);
        $this->v->assign('level1', $level1);
        $this->v->assign('level2', $level2);
        $this->v->assign('level3', $level3);
        $this->topnav_no = 1;
        $this->nav_flag2 = 'my';
        throw new ViewResponse($this->v);
    }
    
    /**
     * 我发展的代理列表
     * @param Request $request
     * @param Response $response
     * @throws ViewResponse
     */
    public function my_child_agent_list(Request $request, Response $response){
        $curpage = $request->get('curpage', 1);
        $level = $request->get('level', 1);
        global $user;
        $pager = new PagerPull($curpage, null);
        Distribution_Model::getChildAgentList($pager, $level, $user->uid);
        $ret = $pager->outputPageJson();
        $response->sendJSON($ret);
    }
    
    /**
     * 我发展的店铺
     * @param Request $request
     * @param Response $response
     * @throws ViewResponse
     */
    public function my_child_shop(Request $request, Response $response){
        $this->setPageView($request, $response, '_page_mpa');
        $this->v->set_tplname('mod_distribution_childshop');
        $this->v->set_page_render_mode(View::RENDER_MODE_GENERAL);
        $this->nav_no = 0;
        $uid = $GLOBALS['user']->uid;
        $level1 = Distribution_Model::findFirstLevelShopCount($uid);
        $level2 = Distribution_Model::findSecondLevelShopCount($uid);
        $level3 = Distribution_Model::findThirdLevelShopCount($uid);
        $this->v->assign('level1', $level1);
        $this->v->assign('level2', $level2);
        $this->v->assign('level3', $level3);
        $this->topnav_no = 1;
        $this->nav_flag2 = 'my';
        throw new ViewResponse($this->v);
    }
    
    /**
     * 我发展的店铺列表
     * @param Request $request
     * @param Response $response
     * @throws ViewResponse
     */
    public function my_child_shop_list(Request $request, Response $response){
        $curpage = $request->get('curpage', 1);
        $level = $request->get('level', 1);
        global $user;
        $pager = new PagerPull($curpage, null);
        Distribution_Model::getChildShopList($pager, $level, $user->uid);
        $ret = $pager->outputPageJson();
        $response->sendJSON($ret);
    }
    
    /**
     * 推广素材
     * @param Request $request
     * @param Response $response
     * @throws ViewResponse
     */
    public function spread(Request $request, Response $response){
        $this->setPageView($request, $response, '_page_mpa');
        $this->v->set_tplname('mod_distribution_spread');
        $this->v->set_page_render_mode(View::RENDER_MODE_GENERAL);
        $this->nav_no = 0;
        $this->nav_flag2 = 'my';
        $this->topnav_no = 1;
        throw new ViewResponse($this->v);
    }
    
    /**
     * 代理
     * @param Request $request
     * @param Response $response
     * @throws ViewResponse
     */
    public function agent_center(Request $request, Response $response){
        $this->setPageView($request, $response, '_page_mpa');
        $this->v->set_tplname('mod_distribution_agent');
        $this->v->set_page_render_mode(View::RENDER_MODE_GENERAL);
        $this->nav_flag2 = 'agency';
        global $user;
        $u = Users::load($user->uid);
        $agent = AgentPayment::getAgentByUserId($u->uid, $u->level);
        $this->v->assign('user', $u);
        $this->v->assign('isAgent', Users::isAgent($u->level));
        $this->v->assign('agent', $agent);
        $this->v->assign('gold_agent', GOLD_AGENT_GOODS_ID);
        $this->v->assign('silver_agent', SILVER_AGENT_GOODS_ID);
        throw new ViewResponse($this->v);
    }
    
    /**
     * 代理支付成功跳转页面
     * @param Request $request
     * @param Response $response
     * @throws ViewResponse
     */
    public function agent_pay_succ(Request $request, Response $response){
        $this->setPageView($request, $response, '_page_mpa');
        $this->v->set_tplname('mod_distribution_agent_paysucc');
        $this->v->set_page_render_mode(View::RENDER_MODE_GENERAL);
        $this->nav_no = 0;
        $order_type = $request->get('order_type', '');
        $this->v->assign('order_type', $order_type);
        throw new ViewResponse($this->v);
    }
    
    /**
     * 展示代理可领取的套餐
     * @param Request $request
     * @param Response $response
     * @throws ViewResponse
     */
    public function show_agent_package(Request $request, Response $response){
        $this->setPageView($request, $response, '_page_mpa');
        $this->v->set_tplname('mod_distribution_agent_pack');
        $this->v->set_page_render_mode(View::RENDER_MODE_GENERAL);
        $this->nav_flag2 = 'agency';
        $this->topnav_no = 1;
        $this->nav_no = 0;
        global $user;
        $u = Users::load($user->uid);
        if(!Users::isAgent($u->level)){
            $response->redirect('/distribution/agent');
        }
        $packages = AgentPayment::getAgentPackage($u->level);
        $this->v->assign('user', $u);
        $this->v->assign('packages', $packages);
        throw new ViewResponse($this->v);
    }
    
    /**
     * 领取套餐  不需要支付，order_amount 跟 commission 不需要
     * @param Request $request
     * @param Response $response
     */
    public function free_buy_agent_premium(Request $request, Response $response){
        if ($request->is_post()) {
        
            $ret = ['flag'=>'FAIL','msg'=>'领取套餐失败'];
        
            $user_id = $GLOBALS['user']->uid;
            if (!$user_id) {
                $ret['msg'] = '未登录, 请登录';
                $response->sendJSON($ret);
            }
            
            $u = Users::load($user_id);
            $agent = AgentPayment::getAgentByUserId($user_id, $u->level);
            if(!Users::isAgent($u->level) || !$agent->is_exist()){
                $ret['msg'] = '你还不是代理，不能领取';
                $response->sendJSON($ret);
            }
            if($agent->premium_id){
                $ret['msg'] = '你已经领取过套餐，不能重复领取';
                $response->sendJSON($ret);
            }
        
            $address_id    = $request->post('address_id', 0);
            $order_msg     = $request->post('order_msg', '');
            $package_id    = $request->post('package_id', 0);
            // 检查数据
            $address_id = intval($address_id);
            if (!$address_id) {
                $ret['msg'] = '请填写收货地址';
                $response->sendJSON($ret);
            }
        
            // 收货地址
            $exAddr = UserAddress::load($address_id);
            if (!$exAddr->is_exist()) {
                $ret['msg'] = '收货地址无效，请重新填写';
                $response->sendJSON($ret);
            }
        
            //商品ID
            $cart_rids_str = D()->query("select goods_ids from shp_premium_package where enabled = 1 and type='%d' and pid = '%d' ", $u->level, $package_id)->result();
            
            if (!$cart_rids_str || ''==$cart_rids_str || !preg_match('/^(\d)+[,\d]*$/', $cart_rids_str)) { //要严格匹配类似格式"1,2,3",连空格也不能存在(因为自家合法的数据是不会有空格的)
                $ret['msg'] = '当前套餐没有商品，请重新领取';
                $response->sendJSON($ret);
            }
        
            // 套餐商品列表
            $total_price = 0;
            $cart_rids_str = explode(',', $cart_rids_str);
            $order_goods = Distribution_Model::getGoods($cart_rids_str, $total_price);
            if (count($order_goods) == 0 || count($cart_rids_str) != count($order_goods)) {
                $ret['msg'] = '该套餐商品无效，请重新领取';
                $response->sendJSON($ret);
            }
            $order_sn = Fn::gen_order_no();
            $newOrder = new Order();
            $newOrder->order_sn     = $order_sn;
            $newOrder->pay_trade_no = '';
            $newOrder->user_id      = $user_id;
            $newOrder->order_status = OS_CONFIRMED;
            $newOrder->shipping_status = SS_UNSHIPPED;
            $newOrder->pay_status   = PS_PAYED;
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
            $newOrder->pay_id       = 2;
            $newOrder->pay_name     = '微信支付';
            $newOrder->how_oos      = Fn::oos_status(OOS_WAIT);
            $newOrder->how_surplus  = '';
            //...
            $newOrder->goods_amount = $total_price;
            $newOrder->shipping_fee = 0;
            $newOrder->order_amount = 0;//$newOrder->goods_amount + $newOrder->shipping_fee;
            $newOrder->commision    = 0;
            //...
            $newOrder->referer      = '本站';
            $newOrder->add_time     = simphp_gmtime(); //跟从ecshop习惯，使用格林威治时间
            $newOrder->confirm_time = simphp_gmtime();
            $newOrder->pay_time = simphp_gmtime();
            //...
            $newOrder->relate_order_id = $agent->order_id;//对应上购买代理的订单ID
            $newOrder->save(Storage::SAVE_INSERT);
            $order_id = 0;
            if ($newOrder->id) { //订单表生成成功
        
                $order_id = $newOrder->id;
        
                // 处理表 order_goods
                $order_update = [];   //存储一些可能需要更新的字段数据
                $succ_goods   = [];   //存储成功购买了的商品
                $rel_merchants= [];   //关联商家
                $true_amount  = 0;    //因为有可能存在失败商品，该字段存储真正产生的费用，而不是$total_price
                $total_commision = 0; //总佣金
                foreach ($order_goods AS $cg) {
                    $cItemId = $cg['goods_id'];
                    $cItem = Items::load($cItemId);
        
                    if (!$cItem->is_exist()) {
                        continue;
                    }
        
                    $true_goods_number = 1;
                    //Items::changeStock($cItemId, -$true_goods_number); //立即冻结商品对应数量的库存
        
                    $newOI = new OrderItems();
                    $newOI->order_id    = $order_id;
                    $newOI->goods_id    = $cItemId;
                    $newOI->goods_name  = $cItem->item_name;
                    $newOI->goods_sn    = $cItem->item_sn;
                    $newOI->goods_number= $true_goods_number;
                    $newOI->market_price= $cItem->market_price; //market_price,shop_price,income_price这三个字段使用最新的信息
                    $newOI->goods_price = $cItem->shop_price;
                    $newOI->income_price= $cItem->income_price;
                    $newOI->goods_attr  = '';
                    $newOI->send_number = 0;
                    $newOI->is_real     = $cg['is_real'];
                    $newOI->extension_code = 0;
                    $newOI->parent_id   = 0;
                    $newOI->is_gift     = 1;
                    $newOI->goods_attr_id = 0;
                    $newOI->save(Storage::SAVE_INSERT);
        
                    if ($newOI->id) {
                        $succ_goods[]     = $cg;
                        $true_amount     += $cItem->shop_price*$true_goods_number;
                        //$total_commision += $cItem->commision *$true_goods_number;
        
                        //关联订单与商家
                        //Order::relateMerchant($newOrder->id, $cItem->merchant_uid);
                        if (!in_array($cItem->merchant_id, $rel_merchants)) {
                            array_push($rel_merchants, $cItem->merchant_id);
                        }
                    }
                    else {
                        //Items::changeStock($cItemId, $true_goods_number); //立即恢复刚才冻结的商品库存
                    }
        
                }//END foreach loop
        
                //检测订单变化
                $order_update['merchant_ids'] = implode(',', $rel_merchants);
                //$order_update['commision'] = $total_commision; //订单总佣金
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
        
                // 没有成功购买的商品，则返回错误告诉用户重新添加
                if (empty($succ_goods)) {
                    $ret['msg'] = '订单生成失败，请返回重新领取';
                    $response->sendJSON($ret);
                }
        
                // 生成表 pay_log 记录
                //PayLog::insert($order_id, $order_sn, $true_amount, PAY_ORDER);
        
                // 生成子订单(如果有多个商家)
                Distribution_Model::genSubOrderForPermium($order_id, $rel_merchants, $agent->order_id);
                
                //更新用户已经领取过赠品了
                $agent->premium_id = $package_id;
                $agent->save(Storage::SAVE_UPDATE);
                $ret = ['flag'=>'SUC','msg'=>'订单提交成功','order_id'=>$order_id,'true_amount'=>$true_amount];
                $response->sendJSON($ret);
            }
            else {
                $ret['msg'] = '订单生成失败，请返回重新领取';
                $response->sendJSON($ret);
            }
        
        }
    }
    
    /**
     * 领取成功页面
     * @param Request $request
     * @param Response $response
     * @throws ViewResponse
     */
    public function buy_premium_succ(Request $request, Response $response){
        $this->setPageView($request, $response, '_page_mpa');
        $this->v->set_page_render_mode(View::RENDER_MODE_GENERAL);
        $this->v->set_tplname('mod_distribution_premium_succ');
        $this->nav_flag2 = 'agency';
        $this->topnav_no = 1;
        $level = $request->get('level', 0);
        $this->v->assign('money', AgentPayment::getAgentPaidMoney($level));
        throw new ViewResponse($this->v);
    }
    
    public function test_buy_agent(Request $request, Response $response){
        global $user;
        $agent = AgentPayment::find_one(new Query('user_id', $user->uid));
        if(!$agent->is_exist()){
            $ret = ['flag'=>'FAIL','msg'=>'请购买之后再点击'];
            $response->sendJSON($ret);
        }
        if ($agent->is_paid){
            $ret = ['flag'=>'FAIL','msg'=>'已购买代理'];
            $response->sendJSON($ret);
        }
        $agent->is_paid=1;
        $agent->save(Storage::SAVE_UPDATE);
        $user = Users::load($user->uid);
        $user->level = $agent->level;
        $user->save(Storage::SAVE_UPDATE);
        $ret = ['flag'=>'SUC','msg'=>'成功', 'type' => $agent->level];
        $response->sendJSON($ret);
    }
    
    public function clear_buy_agent(Request $request, Response $response){
        global $user;
        D()->query('delete from shp_agent_payment where user_id=%d', $user->uid);
        D()->query('update shp_users set level = 0 where user_id = %d', $user->uid);
        $response->sendJSON("");
    }
}
