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
            'order/consignee'    => 'update_order_consignee',
            'order/shipping/form' => 'goto_shipping',
            'order/shipping'    => 'update_shipping',
            'order/shipment/list' => 'shipment_list',
            'order/shipment/info' => 'shipment_info',
            'order/shipment/away' => 'shipment_delete',
            'order/shipment' => 'shipment_save'
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
        $pager = new Pager($curpage, 8);
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
        $order = Order_Model::getOrderDetail($order_id);
        $regionIds = [$order['province'], $order['city'], $order['district']];
        $order_region = Order_Model::getOrderRegion($regionIds);
        $order['order_region'] = $order_region;
        $merchant_goods = Order_Model::getOrderItems($order_id);
        $express = Order::getOrderExpress($order_id);
        $this->v->assign("expressList", Order::get_express_list($express));
        $this->v->assign("order", $order);
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
        $this->setPageView($request, $response, '_page_box');
        $step = $request->get('step', '');
        $order_id  = $request->get('order_id', 0);
        $order = Order::load($order_id);
        if('price' == $step){//修改价格 
            $order->actual_amount = Order::get_actual_orderobj_amount($order);
            $this->v->set_tplname('mod_order_price');
        }else if("consignee" == $step){//修改收货地址
            $this->v->set_tplname('mod_order_consignee');
            if ($order){
                /* 取得省份 */
                $this->v->assign('province_list', order::get_regions(1, 1));//$order->country 这里默认是中国 不动态取
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
        if($request->is_post()){
            $order_id  = intval($request->post('order_id', 0));
            $discount = doubleval($request->post('discount', 0));
            $order = Order::load($order_id);
            if($order->pay_status == PS_PAYED){
                $ret = ['result' => 'FAIL', 'msg' => '只有在未付款状态才能修改价格！'];
                return $ret;
            }
            if(!Order_Model::isOrderValid($order->order_status)){
                $ret = ['result' => 'FAIL', 'msg' => '订单已失效，不能修改价格！'];
                return $ret;
            }
            $commission = $order->commision;
            $now_price = Order::get_actual_orderobj_amount($order, $discount);
            if($now_price < 0 || $now_price < $commission){
                $ret = ['result' => 'FAIL', 'msg' => '折扣后价格不能小于佣金！'];
                return $ret;
            }
            $order = new Order();
            $order->order_id = $order_id;
            $order->order_amount = $now_price;
            $order->discount = $discount;
            $order->save(Storage::SAVE_UPDATE);
            $ret = ['result' => 'SUCC'];
        }else{
            $ret = ['result' => 'FAIL', 'msg' => '修改失败，非法请求！'];
        }
        $response->sendJSON($ret);
    }
    
    /**
     * 修改订单收货地址
     * @param Request $request
     * @param Response $response
     */
    public function update_order_consignee(Request $request, Response $response){
        $ret = ['result' => 'FAIL'];
        if($request->is_post()){
            $order_id  = intval($request->post('order_id', 0));
            $province = $request->post('province', 0);
            $city = $request->post('city', 0);
            $district = $request->post('district', 0);
            $consignee = $request->post('consignee', '');
            $address = $request->post('address', '');
            $mobile = $request->post('mobile', '');
            $order = new Order();
            $order->order_id = $order_id;
            $order->province = $province;
            $order->city = $city;
            $order->district = $district;
            $order->consignee = $consignee;
            $order->address = $address;
            $order->mobile = $mobile;
            $order->save(Storage::SAVE_UPDATE);
            $ret = ['result' => 'SUCC'];
        }
        $response->sendJSON($ret);
    }
    
    /**
     * 去发货
     */
    public function goto_shipping(Request $request, Response $response){
        $muid = $GLOBALS['user']->uid;
        $this->v->set_tplname('mod_order_shipping');
        $order_ids = $request->get('order_ids',0);
        $act = $request->get('act','');
        $fromOrder = $request->get('fromOrder',0);
        $sql = "select * from shp_order_info where pay_status = ".PS_PAYED." and merchant_ids='$muid' and order_id ".Fn::db_create_in($order_ids);
        $result = D()->query($sql)->fetch_array_all();
        $shipment_label = "批量发货";
        foreach ($result as &$order){
            $regionIds = [$order['province'], $order['city'], $order['district']];
            $order_region = Order_Model::getOrderRegion($regionIds);
            $order['order_region'] = $order_region;
        }
        if($result && count($result) > 0){
            if('edit' == $act){
                $order = $result[0];
                $ship_select = Order_Model::buildShippingDropdown($order['shipping_id']);
                $shipment_label = "发货";
            }else{
                $ship_select = Order_Model::buildShippingDropdown();
            }
            $this->v->assign('ship_select', $ship_select);
        }
        $this->v->assign('order_list', $result);
        $this->v->assign('fromOrder', $fromOrder);
        $this->v->assign('shipment_label', $shipment_label);
        $this->setPageLeftMenu('order', 'list');
        $response->send($this->v);
    } 
    
    /**
     * 修改发货信息
     * @param Request $request
     * @param Response $response
     */
    public function update_shipping(Request $request, Response $response){
        $ret = ['result' => 'FAIL'];
        if($request->is_post()){
            $order_ids = $request->post('order_ids');
            $ship_ids = $request->post('ship_ids');
            $ship_names = $request->post('ship_names');
            $invoice_nos = $request->post('invoice_nos');
            $order_ids = !is_array($order_ids) ? [$order_ids] : $order_ids;
            $ship_ids = !is_array($ship_ids) ? [$ship_ids] : $ship_ids;
            $invoice_nos = !is_array($invoice_nos) ? [$invoice_nos] : $invoice_nos;
            for($i = 0,$len = count($order_ids); $i < $len; $i++){
                $order_id = intval(isset($order_ids[$i]) ? $order_ids[$i] : 0);
                if(!$order_id){
                    continue;
                }
                $ship_id = intval(isset($ship_ids[$i]) ? $ship_ids[$i] : 0);
                $ship_name = isset($ship_names[$i]) ? $ship_names[$i] : '';
                $invoice_no = isset($invoice_nos[$i]) ? $invoice_nos[$i] : '';
                $order = new Order();
                $order->order_id = $order_id;
                $order->shipping_id = $ship_id;
                $order->shipping_name = $ship_name;
                $order->invoice_no = $invoice_no;
                $order->shipping_status = SS_SHIPPED;
                $order->shipping_time = simphp_gmtime();
                $order->save(Storage::SAVE_UPDATE);
            }
            $ret = ['result' => 'SUCC'];
        }
        $response->sendJSON($ret);
    }
    
    /**
     * 运费模板列表页面
     * @param Request $request
     * @param Response $response
     */
    public function shipment_list(Request $request, Response $response){
        $this->v->set_tplname('mod_order_shipment');
        $this->setPageLeftMenu('order', 'shipment');
        $ret = Shipment_Model::getShipmentTpl();
        $this->v->assign('result', $ret);
        $response->send($this->v);
    }
    
    /**
     * 跳转到新增修改运费模板页面
     * @param Request $request
     * @param Response $response
     */
    public function shipment_info(Request $request, Response $response){
        $this->v->set_tplname('mod_order_shipinfo');
        $this->setPageLeftMenu('order', 'shipment');
        $sp_id = $request->get('sp_id', 0);
        $tpl_name = "";
        if($sp_id && $sp_id > 0){
            $ret = Shipment_Model::getShipmentTpl($sp_id);
            if($ret && count($ret) > 0){
                $ret = $ret[0];
            }else{
                Fn::show_pcerror_message('模板数据不存在！');
            }
            $tpl_name = $ret['tpl_name'];
            $this->v->assign('result', $ret['items']);
        }
        $this->v->assign('sp_id', $sp_id);
        $this->v->assign('tpl_name', $tpl_name);
        $region_list = Order::get_regions(1, 1);
        $this->v->assign('region_list', $region_list);
        $response->send($this->v);
    }
    
    /**
     * 删除运费模板
     * @param Request $request
     * @param Response $response
     */
    public function shipment_delete(Request $request, Response $response){
        if($request->is_post()){
            $sp_id = $request->post('sp_id', -1);
            $result = Shipment_Model::deleteShipment(intval($sp_id));
        }
        if ($result) {
            $data['status'] = 1;
            $data['retmsg'] = "删除模板成功！";
        } else {
            $data['status'] = 0;
            $data['retmsg'] = "删除模板失败！";
        }
        $response->sendJSON($data);
    }
	
    /**
     * 新增修改运费模板
     * @param Request $request
     * @param Response $response
     */
    public function shipment_save(Request $request, Response $response){
        $ret = false;
        $retmsg = "非法的提交请求！";
        if($request->is_post()){
            $sp_id = $request->post('sp_id', 0);
            $tpl_name = $request->post('tpl_name','');
            $regions = $request->post('regions','');
            $region_json = $_POST('region_json','');
            $n_num = $request->post('n_num',0);
            $n_fee = $request->post('n_fee',0);
            $m_num = $request->post('m_num',0);
            $m_fee = $request->post('m_fee',0);
            if($tpl_name && $regions){
                if(Shipment_Model::isShipTplNameExists($sp_id, $tpl_name)){
                    $retmsg = "模板名称重复！";
                }else{
                    $params['tpl_name'] = $tpl_name;
                    $params['template'] = [];
                    if(is_array($regions)){
                        for($i = 0,$len = count($regions); $i < $len; $i++){
                            $rg = $region_json[$i];
                            if($rg){
                                $rg = strtr($rg, array('{' => '【', '}' => '】'));
                            }
                            array_push($params['template'], array("regions" => $regions[$i], "region_json" => htmlentities($rg),"n_num" => intval($n_num[$i]),
                                "n_fee" => doubleval($n_fee[$i]),"m_num" => intval($m_num[$i]),"m_fee" => doubleval($m_fee[$i])
                            ));
                        }
                    }else{
                        if($region_json){
                            $region_json = strtr($region_json, array('{' => '【', '}' => '】'));
                        }
                        array_push($params['template'], array("regions" => $regions, "region_json" => htmlentities($region_json), "n_num" => intval($n_num),
                            "n_fee" => doubleval($n_fee),"m_num" => intval($m_num),"m_fee" => doubleval($m_fee)
                        ));
                    }
                    $ret = Shipment_Model::addOrUpdateShipmentTpl($sp_id, $params);
                    $retmsg = $ret ? '操作成功' : '操作失败！';
                }
            }
        }
        $data['ret'] = $ret;
        $data['retmsg'] = $retmsg;
        $response->sendJSON($data);
    }
    
}

/*----- END FILE: Order_Controller.php -----*/