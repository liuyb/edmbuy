<?php
/**
 * 店铺控制器
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Shop_Controller extends MerchantController {

	public function menu()
	{
		return [
			'shop/carousel' => 'carousel_upload',
            'shop/carousel/add' => '/shop/carousel_add',
		    'shop/settlement/manager' => 'settlement_manager',
		    'shop/settlement/order/manager' => 'settlement_order_manager',
		    'shop/settlement/list' => 'settlement_list',
		    'shop/settlement/order' => 'settlement_orders'
		];
	}
	/**
     * default action 'index'
     * @param Request $request
     * @param Response $response
     */
    public function index(Request $request, Response $response)
    {
        $this->v->set_tplname('mod_shop_index');
        $this->setSystemNavigate('shop');
        $this->setPageLeftMenu('shop', 'list');
        //查出用户所有的轮播图
        $carousel = Shop_Model::selCarousel();
        $this->v->assign("carousel", $carousel);
        $response->send($this->v);
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

        foreach ($Arraydata as &$val) {
            if ($val['carousel_id'] > 0 && !in_array($val['carousel_id'], $ids)) {
                //删除轮播
                Shop_Model::delCarouse($val['carousel_id']);
            } elseif ($val['carousel_id'] == 0) {
                    //处理新增

                Shop_Model::Carouse();
            }
        }


    }
	
	public function settlement_manager(Request $request, Response $response){
	    $this->v->set_tplname('mod_shop_settlement');
	    $this->setPageLeftMenu('shop', 'settlement');
	    $response->send($this->v);
	}
	
	public function settlement_order_manager(Request $request, Response $response){
	    $this->v->set_tplname('mod_shop_settlement_order');
	    $this->setPageLeftMenu('shop', 'settlement_order');
	    $settle_id = $request->get('settle_id', 0);
	    $this->v->assign('settle_id', $settle_id);
	    $response->send($this->v);
	}
	
	/**
	 * 结算管理列表
	 * @param Request $request
	 * @param Response $response
	 */
	public function settlement_list(Request $request, Response $response){
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
	public function settlement_orders(Request $request, Response $response){
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
}
