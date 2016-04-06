<?php
/**
 * 商品控制器
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Goods_Controller extends MerchantController
{

    public function menu()
    {
        return [
            'goods/info' => 'goods_info',
            'goods/publish' => 'goods_publish',
            'goods/gallery' => 'upload_goods_gallery',
            'goods/list' => 'get_goods_list',
            'goods/status' => 'update_goods_status',
            'goods/attribute' => 'get_attr_by_type',
            'goods/delete' => 'delete_goods',
            'goods/category/list' => 'goodsCategory',
            'goods/catetory' => 'addCatetory',
            'goods/delcatery' => 'deleCategory',
            'goods/update/shortorder' => 'updateShortOrder',
            'goods/simply/category' => 'doAddCategory',
            'goods/comment' => 'goodsComment',
            'goods/attribute/list' => 'goodsAttributeList'
        ];
    }

    /**
     * default action 'index'
     * @param Request $request
     * @param Response $response
     */
    public function index(Request $request, Response $response)
    {
        $this->v->set_tplname('mod_goods_index');
        $this->setPageLeftMenu('goods', 'list');
        $response->send($this->v);
    }

    /**
     * 商品列表
     * @param Request $request
     * @param Response $response
     */
    public function get_goods_list(Request $request, Response $response)
    {
        $curpage = $request->get('curpage', 1);
        $is_sale = $request->get('is_sale', 1);
        $goods_name = $request->get('goods_name', '');
        $start_date = $request->get('start_date', '');
        $end_date = $request->get('end_date', '');
        $orderby = $request->get('orderby', '');
        $order_field = $request->get('order_field', '');
        $options = array("is_sale" => $is_sale, "goods_name" => $goods_name,
            "start_date" => $start_date, "end_date" => $end_date,
            "orderby" => $orderby, "order_field" => $order_field
        );
        $pager = new Pager($curpage, 8);
        Goods_Model::getPagedGoods($pager, $options);
        $ret = $pager->outputPageJson();
        $ret['otherResult'] = $pager->otherMap;
        $response->sendJSON($ret);
    }

    /**
     * 增加修改商品
     * @param Request $request
     * @param Response $response
     */
    public function goods_info(Request $request, Response $response)
    {
        $this->v->set_tplname('mod_goods_info');
        $goods_id = $request->get('goods_id', 0);
        $selectedCat = 0;
        $options = Goods_Common::cat_list(0);
        if ($goods_id) {
            $goods = Items::load($goods_id);
            $selectedCat = $goods->cat_id;
            $gallery = ItemsGallery::find(new Query('item_id', $goods_id));
            $other_cat = Goods_Atomic::get_goods_ext_category($goods_id);
            $goods->item_desc = json_encode($goods->item_desc);
            $goods->other_cat = $other_cat;
            $other_cat_list = [];
            foreach ($other_cat AS $cat_id) {
                $other_cat_list[$cat_id] = Goods_Common::build_options($options, $cat_id);
            }
            $this->v->assign('goodsinfo', $goods);
            $this->v->assign('other_cat_list', $other_cat_list);
            $this->v->assign('gallery', $gallery);
            $specifis = Goods_Model::get_goods_attrs($goods_id);
            $this->v->assign('goods_attributes', $specifis);
            $this->v->assign('count_goods_attributes', count($specifis));
            $this->v->assign('goods_attr_html', Goods_Common::generateSpecifiTable($specifis));
        } else {
            $newitem = new Items();
            $newitem->per_limit_buy = 0;
            $newitem->shipping_fee = 0;
            $this->v->assign('goodsinfo', $newitem);
        }
        $this->v->assign('goods_type', Goods_Common::generateSpecifiDropdown(Goods_Atomic::get_goods_type()));
        $cat_list = Goods_Common::build_options($options, $selectedCat);
        $this->v->assign('cat_list', $cat_list);
        $this->setPageLeftMenu('goods', 'publish');
        $response->send($this->v);
    }

    /**
     * 发布商品
     * @param Request $request
     * @param Response $response
     */
    public function goods_publish(Request $request, Response $response)
    {
        /* 处理商品数据 */
        $goods_id = $request->post('goods_id', 0);
        $goods_name = htmlspecialchars($request->post('goods_name', ''));
        $market_price = $request->post('market_price', 0);
        $cost_price = $request->post('cost_price', 0);
        $shop_price = $request->post('shop_price', 0);
        $income_price = $request->post('income_price', 0);
        $commision = $shop_price > $income_price ? ($shop_price - $income_price) : 0;
        $goods_number = $request->post('goods_number', 0);
        $per_limit_buy = $request->post('per_limit_buy', 0);
        $catgory_id = $request->post('cat_id', 0);
        $goods_thumb = $request->post('goods_thumb', '');
        $goods_img = $request->post('goods_img', '');
        $original_img = $request->post('original_img', '');
        $shipping_fee = $request->post('shipping_fee', 0);
        $goods_desc = $request->post('goods_desc', '');

        $goods = new Items();
        $goods->item_id = $goods_id;
        $goods->cat_id = $catgory_id;
        $goods->item_name = $goods_name;
        $goods->item_number = $goods_number;
        $goods->market_price = $market_price;
        $goods->shop_price = $shop_price;
        $goods->income_price = $income_price;
        $goods->commision = $commision;
        $goods->cost_price = $cost_price;
        $goods->item_desc = $goods_desc;
        $goods->item_thumb = $goods_thumb;
        $goods->item_img = $goods_img;
        $goods->original_img = $original_img;
        $goods->per_limit_buy = $per_limit_buy;
        $goods->shipping_fee = $shipping_fee;

        $ret = false;
        if ($request->is_post() && $goods_name) {
            $ret = Goods_Model::insertOrUpdateGoods($goods);
        }
        $response->sendJSON(["result" => $ret ? 'SUCC' : 'FAIL']);
    }

    /**
     * 上传商品图片
     * @param Request $request
     * @param Response $response
     */
    public function upload_goods_gallery(Request $request, Response $response)
    {
        $ret = [
            'flag' => 'FAIL',
            'errMsg' => '上传失败，请稍后重试！'
        ];
        if ($request->is_post()) {
            $imgDIR = "/a/mch/goods/";
            $img = $_POST["img"];
            $upload = new Upload($img, $imgDIR);
            $result = $upload->saveImgData();
            $ret = $upload->buildUploadResult($result);
        }
        $response->sendJSON($ret);
    }

    /**
     * 删除商品
     * @param Request $request
     * @param Response $response
     */
    public function delete_goods(Request $request, Response $response)
    {
        $ret = ['result' => 'FAIL'];
        $goods_ids = $request->post('goods_ids');
        if ($request->is_post() && $goods_ids) {
            if (!is_array($goods_ids)) {
                $goods_ids = [$goods_ids];
            }
            Goods_Model::batchDeleteGoods($goods_ids);
            $ret = ['result' => 'SUCC'];
        }
        $response->sendJSON($ret);
    }

    /**
     * 更新商品状态
     * @param Request $request
     * @param Response $response
     */
    public function update_goods_status(Request $request, Response $response)
    {
        $goods_ids = $request->post('goods_ids');
        $status = $request->post('status');
        $statusVal = $request->post('statusVal');
        if ($request->is_post() && $goods_ids) {
            if (!is_array($goods_ids)) {
                $goods_ids = [$goods_ids];
            }
            if ($status == 'sale') {
                Goods_Model::batchUpdateGoods($goods_ids, 'is_on_sale', $statusVal);
            }
        }
        $response->sendJSON(['result' => 'SUCC']);
    }

    /**
     * 根据type获取属性值
     * @param Request $request
     * @param Response $response
     */
    public function get_attr_by_type(Request $request, Response $response)
    {
        $cat_id = $request->get('cat_id');
        $result = Goods_Atomic::get_merchant_attribute($cat_id);
        $response->sendJSON($result);
    }

    /**
     * 产品分类
     * @param Request $request
     * @param Response $response
     */
    public function goodsCategory(Request $request, Response $response)
    {
        $this->v->set_tplname("mod_goods_category");
        $this->setPageLeftMenu('goods', 'category');
        $response->send($this->v);
    }

    /**获取分类列表
     * @param Request $request
     * @param Response $response
     */
    public function getCateList(Request $request, Response $response)
    {
        $curpage = $request->get('current_page', 1);
        $this->setPageLeftMenu('goods', 'category');
        $pager = new Pager($curpage, 4);
        $options['merchant_id'] = $GLOBALS['user']->uid;
        Goods_Model::getCatePageList($pager, $options);
        $ret = $pager->outputPageJson();
        $response->sendJSON($ret);
    }

    /**
     * 新增一个分类页面
     * @param Request $request
     * @param Response $response
     */
    public function addCatetory(Request $request, Response $response)
    {
        $cat_id = $request->get('cat_id');
        /**
         * 将分类信息传到页面
         */
        //$this->v = new PageView('mod_goods_addcategory', '_page_box');
        $this->setPageView($request, $response, '_page_box');
        $this->v->set_tplname('mod_goods_addcategory');
        $edit = $request->get('edit', 0);
        $merchant_id = $GLOBALS['user']->uid;
        $list = Goods_Model::getCatNameList($merchant_id);
        $show_page = true;
        $this->v->assign('edit', 0);
        if ($show_page) {
            $catgory['cat_name'] = "";
            $catgory['sort_order'] = "";
            $catgory['cat_url'] = "";
            $catgory['parent_id'] = 0;
            $this->v->assign('catgory', $catgory);
            if ($edit == 1) {
                //查询出cat_name和cat_url
                $this->v->assign('edit', 1);
                $catgory = Goods_Model::getOneCategory($cat_id);
                $this->v->assign('catgory', $catgory);
            }
            //$v = new PageView('mod_goods_addcategory', '_page_front');
            //得到parent_id
            $parent_id = Goods_Model::IsHadCategory($cat_id);
            $this->v->assign('goodsList', $list);
            $this->v->assign('parent_id', $parent_id['parent_id']);
            $this->v->assign('cat_id', $cat_id);
            $response->send($this->v);
        }

    }


    /**
     * 新增保存一个分类
     * @param Request $request
     * @param Response $response
     */
    public function doAddCategory(Request $request, Response $response)
    {
        //先判断是否有了二级分类
        $parent_id = $request->post('parent_id', 0);
        $cat_id = $request->post('cat_id');
        $cateArr['cat_name'] = $request->post('cat_name', '');
        $cateArr['sort_order'] = $request->post('sort_order', 0);
        $cateArr['cate_thums'] = $request->post('cate_thums', '');
        $cateArr['edit'] = $request->post('edit');
        $cateArr['cat_id'] = $cat_id;
        /* if (empty($cateArr['cat_name']) ||
            empty($cateArr['sort_order']) ||
            empty($cateArr['cate_thums'])
        ) {
            $data['status'] = 0;
            $data['retmsg'] = "参数不能为空！";
            $response->sendJSON($data);
        } */
        if (strlen($cateArr['cat_name']) > 12 || empty($cateArr['cat_name'])) {
            $data['status'] = 0;
            $data['retmsg'] = "名称不能多于12个字且不能为空！";
            $response->sendJSON($data);
        }
        $result = Goods_Model::IsHadCategory($cat_id);
        if ($result['parent_id'] > 0 && !$cateArr['edit']) {
            $retmsg = "当前已是二级分类，不能增加子分类！";
            $data['status'] = 0;
            $data['retmsg'] = $retmsg;
        } else {
            $result = Goods_Model::addCategory($cateArr, $parent_id);//新增一个分类
            if (is_numeric($result)) {
                $data['status'] = 1;
                $data['retmsg'] = "编辑成功！";
                $data['result'] = $result;
            } else {
                $data['status'] = 0;
                $data['retmsg'] = "编辑失败！";
                if (is_string($result)) {
                    $data['retmsg'] = $result;
                }
            }
        }
        $response->sendJSON($data);
    }

    /**
     * 删除一个分类
     * @param Request $request
     * @param Response $response
     */
    public function deleCategory(Request $request, Response $response)
    {
        $cat_id = $request->post('cat_id');
        $result = Goods_Model::delgoodsCategory($cat_id);
        if ($result) {
            $data['status'] = 1;
            $data['retmsg'] = "删除分类成功！";
        } else {
            $data['status'] = 0;
            $data['retmsg'] = "删除分类失败！";
        }
        $response->sendJSON($data);
    }

    /**
     * 分类图片上传
     * @param Request $request
     * @param Response $response
     */
    public function upload_goods_categroy(Request $request, Response $response)
    {
        $imgDIR = "/a/mch/goodcategory/";
        $img = $_REQUEST["img"];
        $upload = new Upload($img, $imgDIR);
        $upload->has_thumb = true;
        $upload->thumbwidth = 200;
        $result = $upload->saveImgData();
        $ret = $upload->buildUploadResult($result);
        $response->sendJSON($ret);
    }

    /**更新分类shor_order
     * @param Request $request
     * @param Response $response
     */
    public function updateShortOrder(Request $request, Response $response)
    {
        $cat_id = $request->get("cat_id");
        $short_order = $request->get("short_order");
        $result = Goods_Model::updateShortOrder($cat_id, $short_order);
        if ($result) {
            $ret['retmsg'] = "操作成功!";
            $ret['status'] = 1;
        } else {
            $ret['retmsg'] = "操作失败!";
            $ret['status'] = 0;
        }
        $response->sendJSON($ret);
    }

    /**
     * 商品评价管理列表
     * @param Request $request
     * @param Response $response
     */
    public function goodsComment(Request $request, Response $response)
    {
        $this->v->set_tplname('mod_goods_comment');
        $this->setPageLeftMenu('goods', 'comment');
        $response->send($this->v);//获取评价列表
    }

    /**
     * ajax获取分页列表
     * @param Request $request
     * @param Response $response
     */
    public function ajaxGetCommentList(Request $request, Response $response)
    {
        $currentPage = $request->get('current_page', 1);
        $pager = new Pager($currentPage, 4);
        $current = $request->get("current");
        $list = Goods_Model::getCommentList($pager, $current);//获取分页列表
        $this->v->assign('commentList', $list);
        $ret = $pager->outputPageJson();
        $this->setPageView($request, $response, '_page_box');
        $this->v->set_tplname('mod_goods_ajaxcomment');
        $this->v->assign('page', $ret);
        $this->v->assign('current', $current);
        $response->send($this->v);
    }

    /**
     *
     * 回复评论
     * @param Request $request
     * @param Response $response
     */
    public function rplayCommone(Request $request, Response $response){
        $common_id=$request->post("common_id");
        $comtent=$request->post("content");
        if(strlen($comtent)>200){
            $ret['retmsg']="字数超出限制！";
            $ret['status']=1;
            $response->sendJSON($ret);
        }
        Goods_Model::merchantRely($common_id,$comtent);
        $ret['retmsg']="回复成功！";
        $ret['status']=1;
        $response->sendJSON($ret);
    }

    /**
     * 商家查看回复
     * @param Request $request
     * @param Response $response
     */
    public function viewrplay(Request $request, Response $response){
        $common_id=$request->post("common_id");
        $list=Goods_Model::viewComment($common_id);
        $ret['status']=1;
        $ret['common']=$list;
            $response->sendJSON($ret);
    }
    /**
     * 商品的属性列表管理
     * @param Request $request
     * @param Response $response
     */
    public function goodsAttributeList(Request $request, Response $response)
    {
        $this->v->set_tplname("mod_goods_goodsattrlist");
        $this->setPageLeftMenu('goods', 'attribute');
        $goodsAttr = Goods_Model::getGoodsAttrList();
        $this->v->assign('goodsAttr', $goodsAttr);
        $response->send($this->v);//获取评价列表
    }

    /**
     * 编辑商品的属性
     * @param Request $request
     * @param Response $response
     */
    public function editGoodsAttr(Request $request, Response $response)
    {
//        $this->setPageView($request, $response, '_page_box');
//        $this->v->set_tplname('mod_goods_addcategory');
        $this->setPageView($request, $response, "_page_box");
        $this->v->set_tplname("mod_goods_attredit");
        $attrId = $request->get("cat_id");
        $show_page = true;
        if ($show_page) {
            $goodsAttr = Goods_Model::getGoodsAttr($attrId);
            $this->v->assign('goodAttr', $goodsAttr);
            $this->v->assign('cat_id', $attrId);
            $response->send($this->v);
        }
    }

    /**
     * ajax获取属性列表
     * @param Request $request
     * @param Response $response
     */
    public function ajaxGetAttr(Request $request, Response $response){
            $this->setPageView($request, $response, "_page_box");
            $this->v->set_tplname("mod_goods_ajaxattr");
            $attrId = $request->post("cat_id");
            $show_page = true;
            if ($show_page) {
                $goodsAttr = Goods_Model::getGoodsAttr($attrId);
                $this->v->assign('goodAttr', $goodsAttr);
                $this->v->assign('cat_id', $attrId);
                $response->send($this->v);
            }
    }

    /**
     * 编辑保存商品的属性信息
     * @param Request $request
     * @param Response $response
     */
    public function saveGoodsAttr(Request $request, Response $response)
    {
        if($request->is_post()){
            $cat_id = $request->post("cat_id");
            $attrData = $request->post("attrDate");//前台的二维数组数据
            $sort_order = 1;
            $attr_names = [];
            foreach ($attrData as $val3) {
                if (empty($val3[0])) {
                    $str = "'$val3[1]'";
                    array_push($attr_names, $str);
                }
            }
            if (!empty($attr_names)) {
                $limit_name = implode(",", $attr_names);
                $result = Goods_Model::checkAttrName($cat_id, $limit_name);
                if ($result) {
                    $ret['status'] = 0;
                    $ret['retmsg'] = "属性名不能重复！";
                    $response->sendJSON($ret);
                }
            }
            $attr_ids = Goods_Model::getAttrIds($cat_id);//得到attr_id
            $new = [];
            foreach ($attrData as &$val1) {
                if (!empty($val1[0])) {
                    //处理删除(删除以前已经添加过的)
                    //先查询出以前的attr_id
                    array_push($new, "$val1[0]");
                }
                if (empty($val1[0])) {
                    $attr_id = Goods_Model::addGoodsAttr($val1[1], $cat_id);//新增
                    $val[0] = $attr_id;
                }
                $val1['sort_order'] = $sort_order;
                $sort_order++;
            }
            foreach ($attrData as $val2) {
                //编辑上移或者下移动改变short_order
                Goods_Model::updateGoodsShortOrder($val2[0], $val2['sort_order']);
            }
            $str = "";
            foreach ($attr_ids as $ids) {
                if (!in_array($ids['attr_id'], $new)) {
                    $str .= $ids['attr_id'] . ",";
                }
            }
            $str = rtrim($str, ",");
            if (!empty($str)) {
                Goods_Model::delGoodsAttr($str);
            }
            $view = true;
            if ($view) {
                $ret['status'] = 1;
                $ret['retmsg'] = "保存成功！";
            }
            $response->sendJSON($ret);
        }

    }

}
