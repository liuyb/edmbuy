<?php
/**
 * 店铺控制器
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Shop_Controller extends MerchantController
{

    public function menu()
    {
        return [
            'shop/template/use' => 'template_ajax',
            'shop/template/getimg' => 'template_getimg',
            'shop/template/index' => 'template_index',
            'shop/carousel/upload' => 'carousel_upload',
            'shop/carousel/list' => 'carousel_index',
            'shop/carousel/add' => 'carousel_add',
            'shop/carousel/del' => 'carousel_del',
            'shop/settlement/manager' => 'settlement_manager',
            'shop/settlement/order/manager' => 'settlement_order_manager',
            'shop/settlement/list' => 'settlement_list',
            'shop/settlement/order' => 'settlement_orders',
            'shop/start' => 'shop_start',
            'shop/step/template' => 'shop_template_step',
            'shop/step/finish' => 'shop_finished_step',
            'shop/info' => 'shop_info',
            'shop/logo' => 'shop_logo_upload',
            'shop/qrcode' => 'shop_qrcode_upload',
            'shop/setup' => 'shop_info_save',
            'shop/need/pay' => 'shop_unpaid',
            'shop/region' => 'get_regions'
        ];

    }
    /**
     * 店铺资料
     * @param Request $request
     * @param Response $response
     */
    public function index(Request $request, Response $response)
    {
        Shop_Model::checkShopStatus($response);
        $this->setSystemNavigate('shop');
        $this->v->set_tplname("mod_shop_details");
        $this->setPageLeftMenu('shop', 'details');
        //查出店铺所有信息
        $result = Shop_Model::getShopByMerchantId();
        if(!$result || empty($result)){
            Fn::show_pcerror_message("数据不存在！");
        }
        $buyInfo =Shop_Model::getBuyDetail();
        $this ->v->assign("buyInfo",$buyInfo);
        $y = $buyInfo['term_time'];
        if($y){
            preg_match('/(\d+)(\w+)$/', $y, $matchs);
            if($matchs && count($matchs) == 3){
                $text = $matchs[2] == 'm' ? "月" : "年";
                $y = $matchs[1] . $text;
            }
        }else{
            $y = intval($y). "年";
        }
        $canRefund = 0;
        $paid_time = $buyInfo['paid_time'];
        if($paid_time){
            $gap = (time() - intval($paid_time)) / 86400;
            if($gap <= 3){
                $canRefund = 1;
            }
        }
        $this ->v->assign("canRefund", $canRefund);
        $this ->v->assign("year", $y);
        $this->v->assign('shop_info',$result);
        $response->send($this->v);
    }
    /**
     * 使用模板
     * @param Request $request
     * @param Response $response
     */
    public function template_index(Request $request, Response $response)
    {
        Shop_Model::checkShopStatus($response);
        $this->v->set_tplname("mod_shop_index");
        $this->setPageLeftMenu('shop', 'list');
        $response->send($this->v);

    }

    /**
     * 启用模板ajax数据
     * @param Request $request
     * @param Response $response
     */
    public function template_ajax(Request $request, Response $response)
    {
        $show_page = true;
        $type = $request->get("type");
        $v = new PageView('mod_shop_ajaxtep', '_page_box');
        if ($show_page) {
            if ($type == "carousel") {
                $this->carousel_ajax($request, $response);
            } else {
                //查询出用户当前正在使用的模板
                $tpl = Shop_Model::getMchTpl();
                $isusetpl = Shop_Model::getCurentTpl();
                if ($isusetpl['tpl_id']) {
                    $merchant_id =$GLOBALS['user']->uid;
                    $dir = Fn::gen_qrcode_dir($isusetpl['tpl_id'], 'shop', true);
                    $locfile = $dir . $merchant_id . '.png';
                    if (!file_exists($locfile)) {
                        mkdirs($dir);
                        $qrinfo =C('env.site.mobile')."/shop/{$merchant_id}";
                        include_once SIMPHP_INCS . '/libs/phpqrcode/qrlib.php';
                        QRcode::png($qrinfo, $locfile, QR_ECLEVEL_L, 7, 3);
                        $qrcode = str_replace(SIMPHP_ROOT, '', $locfile);
                        Shop_Model::updShopInformation($isusetpl['tpl_id'],$qrcode);
                    }
                      $qrcode = str_replace(SIMPHP_ROOT, '', $locfile);
                } else {
                    $qrcode = "";
                }
                $v->assign("current_tpl", $isusetpl);
                $v->assign("tpl", $tpl);
                $v->assign("dir", $qrcode);
                $response->send($v);
            }
        }
    }

    /**
     * 启用模板
     * @param $request
     * @param $response
     */
    public function template_getimg(Request $request, Response $response)
    {
        $tpl_id = $request->post("tpl_id");

        $dir = Fn::gen_qrcode_dir($tpl_id, 'shop', true);
        $locfile = $dir . $tpl_id . '.png';
        if (!file_exists($locfile)) {
            mkdirs($dir);
            $merchant_id =$GLOBALS['user']->uid;
            $qrinfo =C('env.site.mobile')."/shop/{$merchant_id}";
            include_once SIMPHP_INCS . '/libs/phpqrcode/qrlib.php';
            QRcode::png($qrinfo, $locfile, QR_ECLEVEL_L, 7, 3);
        }
        $qrcode = str_replace(SIMPHP_ROOT, '', $locfile);
        //得到img
        $img = Shop_Model::getImg($tpl_id);
        //更新店铺信息
        $result = Shop_Model::updShopInformation($tpl_id,$qrcode);
        if ($result !== false) {
            $res['retmsg'] = "启用模板成功！";
            $res['status'] = 1;
            $res['img'] = $img;
            $res['qrimg'] = $qrcode;
            $response->sendJSON($res);
        }
    }

    /**
     * @param $request
     * @param $response
     */
    public function carousel_ajax($request, $response)
    {
        //查出用户所有的轮播图
        $show_page = true;
        if ($show_page) {
            $v = new PageView('mod_shop_carousel', '_page_box');
            $carousel = Shop_Model::selCarousel();
            $this->v->assign("carousel", $carousel);
            $response->send($v);
        }

    }

    /**
     * 首页轮播图
     * @auth edm_hc
     * @param Request $request
     * @param Response $response
     */
    public function carousel_upload(Request $request, Response $response)
    {
        $ret = [
            'flag' => 'FAIL',
            'errMsg' => '上传失败，请稍后重试！'
        ];
        if ($request->is_post()) {
            $img = $_POST["img"];
            $upload = new AliyunUpload($img, 'carousel', '');
            $result = $upload->checkAndSaveImg(640, 320);
            $ret = $upload->buildUploadResult($result);
        }
        $response->sendJSON($ret);
    }

    /**
     * 处理首页轮播图删除
     * @param Request $request
     * @param Response $response
     */
    public function carousel_del(Request $request, Response $response)
    {
        $carousel_id = $request->post("carousel_id");
        Shop_Model::delCarouse($carousel_id);
        $ret['retmsg'] = "删除成功!";
        $ret['status'] = 1;
        $response->sendJSON($ret);
    }

    /**
     * 添加首页轮播图
     * @param Request $request
     * @param Response $response
     */
    public function carousel_add(Request $request, Response $response)
    {
        /**
         * 首先处理删除
         * 1.得到用户的所有图片id
         */
        $ids = Shop_Model::getCarouselId();//得到carousel_id
        $Arraydata = $request->post("imgArr");
        if (empty($Arraydata)) {
            $ret['retmsg'] = "不能删除所有轮播图！";
            $ret['status'] = 0;
            $response->sendJSON($ret);
        }
        $newid = [];
        foreach ($Arraydata as $val) {
            if ($val[0] > 0) {
                //删除轮播
                array_push($newid, $val[0]);
                Shop_Model::updCarouse($val[0], $val[1], $val[2], $val[3]);
            }
            if ($val[0] == 0) {
                //处理新增
                Shop_Model::addCarouse($val[1], $val[2], $val[3]);
            }
        }
        foreach ($ids as $id) {
            if (!in_array($id['carousel_id'], $newid)) {
                Shop_Model::delCarouse($val[0]);
            }
        }
        $res['retmsg'] = "操作成功!";
        $res['status'] = 1;
        $response->sendJSON($res);
    }


    public function settlement_manager(Request $request, Response $response)
    {
        Shop_Model::checkShopStatus($response);
        $this->v->set_tplname('mod_shop_settlement');
        $this->setPageLeftMenu('shop', 'settlement');
        $response->send($this->v);
    }

    public function settlement_order_manager(Request $request, Response $response)
    {
        Shop_Model::checkShopStatus($response);
        $this->v->set_tplname('mod_shop_settlement_order');
        $this->setPageLeftMenu('shop', 'settlement_order');
        $settle_id = $request->get('settle_id', 0);
        $this->v->assign('settle_id', $settle_id);
        $this->v->assign('settle', Settlement_Model::getSettlement($settle_id));
        $response->send($this->v);
    }

    /**
     * 结算管理列表
     * @param Request $request
     * @param Response $response
     */
    public function settlement_list(Request $request, Response $response)
    {
        $curpage = $request->get('curpage', 1);
        $status = $request->get('status', 1);
        $options = array("status" => $status);
        $pager = new Pager($curpage, $this->getPageSize());
        Settlement_Model::getSettlementList($pager, $options);
        $ret = $pager->outputPageJson();
        $response->sendJSON($ret);
    }

    /**
     * 结算订单列表
     * @param Request $request
     * @param Response $response
     */
    public function settlement_orders(Request $request, Response $response)
    {
        $curpage = $request->get('curpage', 1);
        $settle_id = $request->get('settle_id', 0);
        $start_date = $request->get('start_date', '');
        $end_date = $request->get('end_date', '');
        $options = array("start_date" => $start_date, "end_date" => $end_date);
        $pager = new Pager($curpage, $this->getPageSize());
        Settlement_Model::getSettlementDetail($pager, $settle_id, $options);
        $ret = $pager->outputPageJson();
        $response->sendJSON($ret);
    }

    public function shop_start(Request $request, Response $response)
    {
        $shop = Merchant::load($GLOBALS['user']->uid);
        if(!$shop || !$shop->uid){
            Fn::show_pcerror_message("数据不存在！");
        }
        if ($shop->is_completed) {
            //店铺已经存在，则跳转到店铺资料页面
            $response->redirect('/shop/details');
        }
        $incomplete = $request->get('incomplete', '');
        $this->v->set_tplname('mod_shop_start');
        $business_scope = Merchant::getBusinessScope();
        $this->v->assign('busi_scope', $business_scope);
        $this->v->assign('province_list', Order::get_regions(1, 1));
        $this->v->assign('shop', $shop);
        $this->v->assign('act', 'complete');
        $this->v->assign('incomplete', $incomplete);
        $response->send($this->v);
    }

    public function shop_template_step(Request $request, Response $response)
    {
        Shop_Model::checkShopStatus($response);
        $this->v->set_tplname('mod_shop_template');
        $response->send($this->v);
    }

    public function shop_finished_step(Request $request, Response $response)
    {
        Shop_Model::checkShopStatus($response);
        $this->v->set_tplname('mod_shop_finished');
        $response->send($this->v);
    }

    public function shop_info(Request $request, Response $response)
    {
        $this->v->set_tplname('mod_shop_infoform');
        $this->setPageLeftMenu('shop', 'details');
        $shop = Merchant::load($GLOBALS['user']->uid);
        if(!$shop->is_completed){
            $response->redirect('/shop/start');
        }
        $business_scope = Merchant::getBusinessScope();
        if ($shop->business_scope) {
            foreach ($business_scope as &$scope) {
                $needle = "," . $scope['cat_id'] . ",";
                $isSelected = strpos($shop->business_scope, $needle);
                if (is_numeric($isSelected)) {
                    $scope['selected'] = 1;
                }
            }
        }
        Func::assign_regions($this->v, $shop->province, $shop->city);
        $this->v->assign('busi_scope', $business_scope);
        $this->v->assign('shop', $shop);
        $this->v->assign('act', 'edit');
        $response->send($this->v);
    }

    /**
     * 商家LOGO上传
     * @param Request $request
     * @param Response $response
     */
    public function shop_logo_upload(Request $request, Response $response)
    {
        if ($request->is_post()) {
            $img = $_REQUEST["img"];
            $upload = new AliyunUpload($img, 'shoplogo', '', true, 320, 320);
            $result = $upload->checkAndSaveImg(320, 320);
            $ret = $upload->buildUploadResult($result);
            $response->sendJSON($ret);
        }
    }

    /**
     * 商家公众号二维码上传
     * @param Request $request
     * @param Response $response
     */
    public function shop_qrcode_upload(Request $request, Response $response)
    {
        if ($request->is_post()) {
            $img = $_REQUEST["img"];
            $upload = new AliyunUpload($img, 'shopqrcode', 'shoplogo');
            $result = $upload->saveImgData();
            $ret = $upload->buildUploadResult($result);
            $response->sendJSON($ret);
        }
    }

    /**
     * 创建商铺
     * @param Request $request
     * @param Response $response
     */
    public function shop_info_save(Request $request, Response $response)
    {
        if ($request->is_post()) {
            $shop_name = $request->post('shop_name', '');
            $shop_logo = $request->post('shop_logo', '');
            $tel = $request->post('tel', '');
            $province = $request->post('province', 0);
            $city = $request->post('city', 0);
            $district = $request->post('district', 0);
            $address = $request->post('address', '');
            $shop_desc = $request->post('shop_desc', '');
            $business_scope = $request->post('business_scope', '');
            //$shop_sign = $request->post('shop_sign');
            $shop_qrcode = $request->post('shop_qrcode', '');
            $slogan = $request->post('slogan');
            $act = $request->post('act', '');
            if (Shop_Model::isShopNameExists($shop_name)) {
                $ret = [
                    'result' => 'FAIL',
                    'msg' => '店铺名称已经存在！'
                ];
            } else {
                $shop = new Merchant();
                $shop->uid = $GLOBALS['user']->uid;
                $shop->facename = $shop_name;
                $shop->logo = $shop_logo;
                $shop->telphone = $tel;
                $shop->province = $province;
                $shop->city = $city;
                $shop->district = $district;
                $shop->address = $address;
                $shop->shop_desc = $shop_desc;
                $shop->business_scope = $business_scope;
                $shop->wxqr = $shop_qrcode;
                $shop->slogan = $slogan;
                $shop->is_completed = 1;
                if(!$shop->shop_template){
                    $shop->shop_template = Shop_Model::getDefaultTemplate();
                }
                $shop->save(Storage::SAVE_UPDATE);
                $flag = D()->affected_rows();
                if ($flag) {
                    $ret = [
                        'result' => 'SUCC',
                        'msg' => ($act == 'edit') ? 'UPDATE' : 'COMPLETE'
                    ];
                } else {
                    $ret = [
                        'result' => 'FAIL',
                        'msg' => '创建失败，请稍后重试！'
                    ];
                }
            }
            $response->sendJSON($ret);
        }
    }
    
    /**
     * 店铺没有支付跳转页面
     * @param Request $request
     * @param Response $response
     */
    function shop_unpaid(Request $request, Response $response){
        $this->v->set_tplname('mod_shop_unpaid');
        
        $dir = SIMPHP_ROOT . '/a/qrcode/shop/';
        $locfile = $dir . 'shop_paid.png';
        if (!file_exists($locfile)) {
            mkdirs($dir);
            $merchant_id =$GLOBALS['user']->uid;
            $qrinfo =C('env.site.mobile')."/user/pay/merchant/order?mid=$merchant_id";
            include_once SIMPHP_INCS . '/libs/phpqrcode/qrlib.php';
            QRcode::png($qrinfo, $locfile, QR_ECLEVEL_L, 7, 3);
        }
        $qrcode = str_replace(SIMPHP_ROOT, '', $locfile);
        $this->v->assign('qrcode', $qrcode);
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
}
