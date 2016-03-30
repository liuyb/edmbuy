<?php
/**
 * 订单控制器
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Order_Controller extends MerchantController {
	
    public function menu()
    {
        return [
            'order/list'    => 'get_orders_list',
            'order/detail'    => 'get_order_detail',
            'order/detail/step'    => 'edit_order_detail',
            'order/region'    => 'get_regions',
            'order/price'    => 'update_order_price',
            'order/consignee'    => 'update_order_consignee'
        ];
    }
    
    /**
     * default action 'index'
     * @param Request $request
     * @param Response $response
     */
    public function index(Request $request, Response $response)
    {
        $this->v->set_tplname('mod_order_index');
        $this->setPageLeftMenu('order', 'list');
        $response->send($this->v);
    }
    
    /**
     * 订单列表
     * @param Request $request
     * @param Response $response
     */
    public function get_orders_list(Request $request, Response $response){
        $curpage = $request->get('curpage', 1);
        $order_sn = $request->get('order_sn', '');
        $buyer = $request->get('buyer', '');
        $start_date = $request->get('start_date','');
        $end_date  = $request->get('end_date', '');
        $status = $request->get('status', 0);
        $orderby   = $request->get('orderby', '');
        $order_field = $request->get('order_field', '');
        $options = array("order_sn" => $order_sn, "buyer" => $buyer,
            "start_date" => $start_date, "end_date" => $end_date,"status"=>$status,
            "orderby"  => $orderby, "order_field" => $order_field
        );
        $pager = new Pager($curpage, 10);
        Order_Model::getPagedOrders($pager, $options);
        $ret = $pager->outputPageJson();
        $response->sendJSON($ret);
    }
    
    /**
     * 查看订单详情
     * @param Request $request
     * @param Response $response
     */
    public function get_order_detail(Request $request, Response $response){
        $this->v->set_tplname('mod_order_detail');
        $order_id  = $request->get('order_id', 0);
        $order = Order::load($order_id);
        $regionIds = [$order->province, $order->city, $order->district];
        $order_region = Order_Model::getOrderRegion($regionIds);
        $order->__set("order_region", $order_region);
        $merchant_goods = Order_Model::getOrderItems($order_id);
        $express = Order::getOrderExpress($order_id);
        $this->v->assign("express", $express);
        $this->v->assign("order", $order);
        $this->v->assign("order_id", $order_id);
        $this->v->assign("merchant_goods", $merchant_goods);
        $this->setPageLeftMenu('order', 'list');
        $response->send($this->v);
    }
    
    /**
     * 修改订单的一些信息
     * @param Request $request
     * @param Response $response
     */
    public function edit_order_detail(Request $request, Response $response){
        $step = $request->get('step', '');
        $order_id  = $request->get('order_id', 0);
        $order = Order::load($order_id);
        if('price' == $step){
            $this->v->set_tplname('mod_order_detail');
        }else if("consignee" == $step){
            $this->v->set_tplname('mod_order_detail');
            if ($order){
                if ($order->country > 0)
                {
                    /* 取得省份 */
                    $this->v->assign('province_list', order::get_regions(1, $order->country));
                    if ($order->province > 0)
                    {
                        /* 取得城市 */
                        $this->v->assign('city_list', order::get_regions(2, $order->province));
                        if ($order->city > 0)
                        {
                            /* 取得区域 */
                            $this->v->assign('district_list', order::get_regions(3, $order->city));
                        }
                    }
                }
            }
        }
        $this->v->assign("order", $order);
        $response->send($this->v);
    }
    
    /**
     * 根据选择的父区域得到子区域
     * @param Request $request
     * @param Response $response
     */
    public function get_regions(Request $request, Response $response){
        $type = $request->get('type');
        $region = $request->get('region');
        if($type && $region){
            $region_list = Order::get_regions($type, $region);
            $response->sendJSON($region_list);
        }
    }
    
    /**
     * 修改稿订单金额
     * @param Request $request
     * @param Response $response
     * @return string[]
     */
    public function update_order_price(Request $request, Response $response){
        /* if($request->is_post()){
            
        } */
        $order_id  = $request->post('order_id', 0);
        $discount = $request->post('discount', 0);
        $order = Order::load($order_id);
        $commission = $order->commision;
        $order_amount = $order->order_amount;
        $now_price = doubleval($order_amount) - doubleval($discount);
        if($now_price < 0 || $now_price < $commission){
            $ret = ['result' => 'FAIL', 'msg' => '折扣后价格不能小于佣金！'];
            return $ret;
        }
        $order = new Order();
        $order->order_id = $order_id;
        $order->money_paid = $now_price;
        $order->save(Storage::SAVE_UPDATE);
        $ret = ['result' => 'SUCC'];
        $response->sendJSON($ret);
    }
    
    /**
     * 修改订单收货地址
     * @param Request $request
     * @param Response $response
     */
    public function update_order_consignee(Request $request, Response $response){
        $ret = ['result' => 'FAIL'];
        $order_id  = $request->post('order_id', 0);
        $province = $request->post('province', 0);
        $city = $request->post('city', 0);
        $district = $request->post('district', 0);
        $consigneer = $request->post('consigneer', '');
        $address = $request->post('address', '');
        $mobile = $request->port('mobile', '');
        $order = new Order();
        $order->order_id = $order_id;
        $order->province = $province;
        $order->city = $city;
        $order->district = $district;
        $order->consigneer = $consigneer;
        $order->address = $address;
        $order->mobile = $mobile;
        $order->save(Storage::SAVE_UPDATE);
        $ret = ['result' => 'SUCC'];
        $response->sendJSON($ret);
    }
	
}

/*----- END FILE: Order_Controller.php -----*/