<?php
/**
 * 默认(一般首页)模块控制器，此控制器必须
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Shop_Controller extends MobileController
{

    /**
     * hook init
     *
     * @param string $action
     * @param Request $request
     * @param Response $response
     */
    public function init($action, Request $request, Response $response)
    {
        $this->nav_flag1 = 'home';
        parent::init($action, $request, $response);
    }

    /**
     * hook menu
     * @see Controller::menu()
     */
    public function menu()
    {
        return [
            'shop/mc_%s' => 'index',
            'shop/collect'=>'collect'
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
        /**
         * 检验商铺是否存在拿到模版id
         */
        $merchant_id = $request->arg(1);
        $_SESSION['merchant_id'] = $merchant_id;
        $result = Shop_Model::checkMerchantStatus($merchant_id);
        $tpl_id = $result['shop_template'];
        $this->setPageView($request, $response, '_page_mpa');
        $this->v->set_tplname("mod_shop_index_{$tpl_id}");
        $this->nav_no =3;
        $this->nav_flag1 ="merchant";
        $this->v->set_page_render_mode(View::RENDER_MODE_GENERAL);

        $recommend_info = Shop_Model::getShopRecommend($merchant_id);//推荐商品
        $goods_category = Shop_Model::getGoodsCategory($merchant_id);
        $shop_carousel = Shop_Model::getShopCarousel($merchant_id);//得到商家首页的轮播图以及地址
        //收藏次数
        $num = Shop_Model::getCollectNum($merchant_id);
        $isCollect = Shop_Model::checkIsCollect($merchant_id);
        $share_info = [
            'title' => '收藏了很久的特价商城，各种超划算！',
            'desc' => '便宜又实惠，品质保证，生活中的省钱利器！',
            'link' => U('', 'spm=' . Spm::user_spm(), true),
            'pic' => U('misc/images/napp/touch-icon-144.png', '', true),
        ];
        $this->v->assign("recommend_info", $recommend_info);
        $this->v->assign("goods_category", $goods_category);
        $this->v->assign('shop_info', $result);
        $this->v->assign('share_info', $share_info);
        $this->v->assign('shop_carousel', $shop_carousel);
        $this->v->assign('num', $num);
        $this->v->assign('isCollect', $isCollect);

        throw new ViewResponse($this->v);
    }

    /**
     * 收藏店铺
     * @param Request $request
     * @param Response $response
     */
    public function collect(Request $request, Response $response){
            $merchant_id =$_SESSION['merchant_id'];
            Shop_Model::collectShop($merchant_id);
    }

    /**
     * 首页面 商品列表展示
     * @param Request $request
     * @param Response $response
     */
    public function goods_list(Request $request, Response $response)
    {
        $curpage = isset($_REQUEST['curpage']) ? $_REQUEST['curpage'] : 1;
        $pager = new PagerPull($curpage, 50);
        $category = $request->get('category', Default_Model::CATEGORY_EAT);
        Default_Model::findGoodsListByCategory($pager, $category);
        $pageJson = $pager->outputPageJson();
        $ret = ["result" => $pager->result];
        if (!empty($ret['result'])) {
            foreach ($ret['result'] AS &$it) {
                $it['goods_name'] = str_replace(["\n", "\r"], [" ", ""], $it['goods_name']);
                $it['goods_brief'] = str_replace(["\n", "\r"], [" ", ""], $it['goods_brief']);
            }
        }
        $ret = array_merge($ret, $pageJson);
        $response->sendJSON($ret);
    }

}
