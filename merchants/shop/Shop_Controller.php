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
            'shop/carousel/upload' => 'carousel_upload',
            'shop/carousel/list' => 'carousel_index',
            'shop/carousel/add' => 'carousel_add',
            'shop/carousel/del' => 'carousel_del',
            'shop/settlement/manager' => 'settlement_manager',
            'shop/settlement/order/manager' => 'settlement_order_manager',
            'shop/settlement/list' => 'settlement_list',
            'shop/settlement/order' => 'settlement_orders',
            'shop/start' => 'shop_start',
            'shop/info' => 'shop_info',
            'shop/logo' => 'shop_logo_upload',
            'shop/qrcode' => 'shop_qrcode_upload',
            'shop/setup' => 'shop_info_save'
        ];
    }

    /**
     * 使用模板
     * @param Request $request
     * @param Response $response
     */
    public function index(Request $request, Response $response)
    {
        $result = Shop_Model::checkShopStatus();
        if (!$result) {
            $this->redirect("/manager");
        }
        $this->v->set_tplname('mod_shop_index');
        if (!empty($_SESSION['shop_type'])) {
            $this->v->assign("shop_type", $_SESSION['shop_type']);
        }
        $this->setSystemNavigate('shop');
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
        $_SESSION['shop_type'] = $type;
        $v->assign("shop_type", $type);
        if ($show_page) {
            if ($type == "carousel") {
                $this->carousel_ajax($request, $response);
            } else {
                //查询出用户当前正在使用的模板
                $tpl = Shop_Model::getMchTpl();
                $isusetpl = Shop_Model::getCurentTpl();
                if (!$isusetpl['is_use']) {
                    $qrinfo = C("port.merchant_url");
                    $dir = Fn::gen_qrcode_dir($isusetpl['tpl_id'], 'shop', true);
                    $locfile = $dir . $isusetpl['tpl_id'] . '.png';
                    if (!file_exists($locfile)) {
                        if (mkdirs($dir)) ;
                    }
                    include_once SIMPHP_INCS . '/libs/phpqrcode/qrlib.php';
                    QRcode::png($qrinfo, $locfile, QR_ECLEVEL_L, 7, 3);
                } else {
                    $locfile = $isusetpl['shop_qrcode'];
                }
                if (file_exists($locfile)) {
                    $qrcode = str_replace(SIMPHP_ROOT, '', $locfile);
                }
                $v->assign("tpl_image", $isusetpl['tpl_image']);
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
        //得到img
        $img = Shop_Model::getImg($tpl_id);
        //更新店铺信息
        Shop_Model::updShopInformation($tpl_id);
        $res['retmsg'] = "启用模板成功！";
        $res['status'] = 1;
        $res['img'] = $img;
        $response->sendJSON($res);
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
            $imgDIR = "/a/mch/shop/";
            $img = $_POST["img"];
            $upload = new Upload($img, $imgDIR);
            $upload->standardheight = 250;
            $result = $upload->saveImgData();
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
            } elseif ($val[0] > 0 && in_array($val[0], $ids)) {

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
        $this->v->set_tplname('mod_shop_settlement');
        $this->setPageLeftMenu('shop', 'settlement');
        $response->send($this->v);
    }

    public function settlement_order_manager(Request $request, Response $response)
    {
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
        $pager = new Pager($curpage, 8);
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
        $pager = new Pager($curpage, 8);
        Settlement_Model::getSettlementDetail($pager, $settle_id, $options);
        $ret = $pager->outputPageJson();
        $response->sendJSON($ret);
    }

    public function shop_start(Request $request, Response $response)
    {
        $shop = Shop_Model::getShopByMerchantId();
        if ($shop && $shop['shop_id']) {
            //店铺已经存在，则跳转到店铺资料页面
            $response->redirect('/aa');
        }
        $this->v->set_tplname('mod_shop_start');
        $business_scope = Shop::getBusinessScope();
        $this->v->assign('busi_scope', $business_scope);
        $this->v->assign('province_list', Order::get_regions(1, 1));
        $shop = new Shop();
        $this->v->assign('shop', $shop);
        $response->send($this->v);
    }

    public function shop_info(Request $request, Response $response)
    {
        $this->v->set_tplname('mod_shop_start');
        $shop_id = $request->get('shop_id', 0);
        $shop = Shop::load($shop_id);
        $merchant_id = $GLOBALS['user']->uid;
        if (!$shop || $shop->merchant_id != $merchant_id) {
            Fn::show_pcerror_message();
        }
        $business_scope = Shop::getBusinessScope();
        if ($shop->business_scope) {
            foreach ($business_scope as $scope) {
                if (strpos("," . $scope['cat_id'] . ",", $shop->business_scope) > 0) {
                    $scope['selected'] = 1;
                }
            }
        }
        $this->v->assign('busi_scope', $business_scope);
        $this->v->assign('province_list', Order::get_regions(1, 1));
        $this->v->assign('shop', $shop);
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
            $imgDIR = "/a/mch/shoplogo/";
            $img = $_REQUEST["img"];
            $upload = new Upload($img, $imgDIR);
            $result = $upload->saveImgData();
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
            $imgDIR = "/a/mch/shopqrcode/";
            $img = $_REQUEST["img"];
            $upload = new Upload($img, $imgDIR);
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
            $shop_id = $request->post('shop_id', 0);
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
            $shop_template = $request->post('shop_template', 0);
            if (Shop_Model::isShopNameExists($shop_id, $shop_name)) {
                $ret = [
                    'result' => 'FAIL',
                    'msg' => '店铺名称已经存在！'
                ];
            } else {
                $shop = new Shop();
                $shop->shop_id = $shop_id;
                $shop->shop_name = $shop_name;
                $shop->shop_logo = $shop_logo;
                $shop->tel = $tel;
                $shop->province = $province;
                $shop->city = $city;
                $shop->district = $district;
                $shop->address = $address;
                $shop->shop_desc = $shop_desc;
                $shop->business_scope = $business_scope;
                $shop->shop_qrcode = $shop_qrcode;
                $shop->shop_template = $shop_template;
                $flag = Shop_Model::insertOrUpdateShopinfo($shop);
                if ($flag) {
                    $ret = [
                        'result' => 'SUCC',
                        'msg' => '创建成功'
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

}

/*----- END FILE: Shop_Controller.php -----*/

