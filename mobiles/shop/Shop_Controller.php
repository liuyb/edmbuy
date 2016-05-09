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
        $this->nav_flag1 = "merchant";
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
            'shop/collect' => 'collect',
            'shop/goods'=>'getShopGoods',
            'shop/goods/list'=>'getShopGoodsList'
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
        $merchant_id = $request->arg(1);
        /* $result = Shop_Model::checkMerchantStatus($merchant_id);
        if (empty($result['shop_template'])) {
            $errmsg = "店铺信息不存在！";
            $this->v->assign('error', $errmsg);
        } */
        $merchant = Merchant::load($merchant_id);
        if(!$merchant->is_exist()){
            $errmsg = "店铺信息不存在！";
            $this->v->assign('error', $errmsg);
        }
        $isCollect = Shop_Model::checkIsCollect($merchant_id);
        $tpl_id = $merchant->shop_template;
        $num = Shop_Model::getCollectNum($merchant_id);

        $this->setPageView($request, $response, '_page_mpa');
        $this->v->set_tplname("mod_shop_index_{$tpl_id}");
        $this->nav_no = 3;
        $this->nav_flag1 = "merchant";
        $this->v->set_page_render_mode(View::RENDER_MODE_GENERAL);
        $pager = new PagerPull(1, 3);//pagesize设置成3 实际查询未4
        $recommend_info = Items::findGoodsListByCond($pager, ["merchant_id" => $merchant_id, "shop_recommend" => 1]); //推荐商品
        $goods_category = Shop_Model::getGoodsGroupByCategory($merchant_id);
        $shop_carousel = Shop_Model::getShopCarousel($merchant_id);//得到商家首页的轮播图以及地址
        //收藏次数
        $share_info = [
            'title' => '收藏了很久的特价商城，各种超划算！',
            'desc' => '便宜又实惠，品质保证，生活中的省钱利器！',
            'link' => U('', 'spm=' . Spm::user_spm(), true),
            'pic' => U('misc/images/napp/touch-icon-144.png', '', true),
        ];
        $this->v->assign("recommend_info", $recommend_info);
        $this->v->assign("goods_category", $goods_category);
        $this->v->assign('share_info', $share_info);
        $this->v->assign('shop_carousel', $shop_carousel);

        $this->v->assign('merchant_id',$merchant_id);
        $this->v->assign('isCollect', $isCollect);
        $this->v->assign('num', $num);
        $this->v->assign('shop_info', $merchant);
        throw new ViewResponse($this->v);
    }

    /**
     * 收藏店铺
     * @param Request $request
     * @param Response $response
     */
    public function collect(Request $request, Response $response)
    {
        if($request->is_post()){
            $merchant_id = $request->post('merchant_id', 0);
            $action = $request->post('action', 0);
            Shop_Model::collectShop($merchant_id, $action);
        }
        $response->sendJSON("");
    }
    
    /**
     * 店铺商品
     * @param Request $request
     * @param Response $respons
     */
    public function getShopGoods(Request $request, Response $response){
        $this->nav_no = 0;
        $this->topnav_no = 1;
        $this->v->set_page_render_mode(View::RENDER_MODE_GENERAL);
        $this->setPageView($request, $response, '_page_mpa');
        $this->v->set_tplname('mod_shop_goods');
        $merchant_id = $request ->get("merchant_id","");
        $shop_recommend = $request ->get("shop_recommend","");
        $shop_cat_id = $request ->get("shop_cat_id","");
        $this->v->assign('merchant_id',$merchant_id);
        $this->v->assign('shop_recommend',$shop_recommend);
        $this->v->assign('shop_cat_id',$shop_cat_id);
        $response->send($this->v);
    }
    
    /**
     * 店铺商品列表
     * @param Request $request
     * @param Response $response
     */
    public function getShopGoodsList(Request $request, Response $response)
    {
        $curpage = isset($_REQUEST['curpage']) ? $_REQUEST['curpage'] : 1;
        $merchant_id = $request->get("merchant_id","");
        $shop_recommend = $request->get('shop_recommend');
        $shop_cat_id = $request->get('shop_cat_id');
        $options = ["merchant_id" => $merchant_id];
        if($shop_recommend){
            $options['shop_recommend'] = $shop_recommend;
        }
        if($shop_cat_id){
            $options['shop_cat_id'] = $shop_cat_id;
        }
        $pager = new PagerPull($curpage, null);
        Items::findGoodsListByCond($pager, $options);
        $pageJson = $pager->outputPageJson();
        $response->sendJSON($pageJson);
    }

    /**
     * 商品推荐列表页
     * @param Request $request
     * @param Response $response
     */
    public function getcategory(Request $request, Response $response)
    {
        $merchant_id = $request ->get("merchant_id","");
        $this->nav_no = 3;
        //todo 移到公共文件
        $isCollect = Shop_Model::checkIsCollect($merchant_id);
        $num = Shop_Model::getCollectNum($merchant_id);
        $this->v->assign('isCollect', $isCollect);
        $this->v->assign('num', $num);

        $this->v->set_page_render_mode(View::RENDER_MODE_GENERAL);
        $this->setPageView($request, $response, '_page_mpa');
        $tpl_id= Shop_Model::checkMerchantStatus($merchant_id);
        $this->v->set_tplname("mod_shop_recoment_{$tpl_id['shop_template']}");
        $this->v->assign('tpl_id',$tpl_id['shop_template']);
        $this->v->assign('merchant_id',$merchant_id);
        $this->v->assign('category',true);
        $response->send($this->v);
    }

    /**
     * 商品推荐分类
     * @param Request $request
     * @param Response $response
     */
    public function category(Request $request, Response $response)
    {
        $search = $request->get("goods_name","");
        $this->nav_no = 0;
        $this->v->set_page_render_mode(View::RENDER_MODE_GENERAL);
        $curpage = isset($_REQUEST['curpage']) ? $_REQUEST['curpage'] : 1;
        $merchant_id = $request ->get("merchant_id","");
        $pager = new PagerPull($curpage, 5);
        $recoment = $request->get('type', "new_asc");
        Shop_Model::getGoodsCategory($merchant_id,$pager ,$recoment,true ,$search);
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

    /**
     * 推荐商品列表
     * @param Request $request
     * @param Response $respons
     */
    public function getrecoment(Request $request, Response $response){
        $merchant_id = $request ->get("merchant_id","");
        $this->nav_no = 3;
        //todo 移到公共文件
        $isCollect = Shop_Model::checkIsCollect($merchant_id);
        $num = Shop_Model::getCollectNum($merchant_id);
        $this->v->assign('isCollect', $isCollect);
        $this->v->assign('num', $num);

        $this->v->set_page_render_mode(View::RENDER_MODE_GENERAL);
        $this->setPageView($request, $response, '_page_mpa');
        $tpl_id= Shop_Model::checkMerchantStatus($merchant_id);
        $this->v->set_tplname("mod_shop_recoment_{$tpl_id['shop_template']}");
        $this->v->assign('tpl_id',$tpl_id['shop_template']);
        $this->v->assign('merchant_id',$merchant_id);
        $this->v->assign('category',false);
        $response->send($this->v);
    }

    /**
     * 商品分类列表
     * @param Request $request
     * @param Response $response
     */
    public function recoment(Request $request, Response $response){
        $search = $request->get("goods_name","");
        $this->nav_no = 0;
        $this->v->set_page_render_mode(View::RENDER_MODE_GENERAL);
        $curpage = isset($_REQUEST['curpage']) ? $_REQUEST['curpage'] : 1;
        $merchant_id = $request ->get("merchant_id","");
        $pager = new PagerPull($curpage, 5);
        $recoment = $request->get('type', "new_asc");
        Shop_Model::getShopRecommend($merchant_id,$pager ,$recoment,true ,$search);
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
