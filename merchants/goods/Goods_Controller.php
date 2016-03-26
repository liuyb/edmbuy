<?php
/**
 * 商品控制器
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Goods_Controller extends MerchantController {

    public function menu()
    {
        return [
            'goods/info' => 'goods_info',
            'goods/publish' => 'goods_publish',
            'goods/gallery' => 'upload_goods_gallery',
            'goods/list'    => 'get_goods_list',
            'goods/status'  => 'update_goods_status',
            'goods/delete'  => 'delete_goods',
			'goods/category/list' => 'goodsCategory',
			'goods/catetory' => 'addCatetory',
            'goods/simply/category' => 'doAddCategory'
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
	    $this->v->assign("goods_sale", '1111');
	    $this->setPageLeftMenu('goods', 'list');
	    $response->send($this->v);
	}
	
	/**
	 * 商品列表
	 * @param Request $request
	 * @param Response $response
	 */
	public function get_goods_list(Request $request, Response $response){
	    $curpage = $request->get('curpage', 1);
	    $is_sale = $request->get('is_sale', 1);
	    $goods_name = $request->get('goods_name', '');
	    $start_date = $request->get('start_date','');
	    $end_date  = $request->get('end_date', '');
	    $orderby   = $request->get('orderby', '');
	    $order_field = $request->get('order_field', '');
	    $options = array("is_sale" => $is_sale, "goods_name" => $goods_name,
	        "start_date" => $start_date, "end_date" => $end_date,
	        "orderby"  => $orderby, "order_field" => $order_field
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
	    if($goods_id){
	        $goods = Items::load($goods_id);
    	    $selectedCat = $goods->cat_id;
    	    $gallery = ItemsGallery::find(new Query('item_id', $goods_id));
    	    $other_cat = Goods_Model::get_goods_ext_category($goods_id);
    	    $goods->other_cat = $other_cat;
    	    foreach ($other_cat AS $cat_id){
    	        $other_cat_list[$cat_id] = Goods_Common::build_options($options, $cat_id);
    	    }
    	    $this->v->assign('goodsinfo', $goods);
    	    $this->v->assign('other_cat_list', $other_cat_list);
    	    $this->v->assign('gallery', $gallery);
    	    $this->v->assign('goods_type', Goods_Atomic::get_goods_type());
	    }else{
	        $newitem = new Items();
	        $newitem->per_limit_buy = 0;
	        $newitem->shipping_fee = 0;
	        $this->v->assign('goodsinfo', $newitem);
	    }
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
	    $goods_id   = $request->post('goods_id', 0);
	    $goods_name = htmlspecialchars($request->post('goods_name', ''));
	    $market_price = $request->post('market_price',0);
	    $cost_price = $request->post('cost_price',0);
	    $shop_price = $request->post('shop_price',0);
	    $income_price = $request->post('income_price',0);
	    $commision    = $shop_price > $income_price ? ($shop_price - $income_price) : 0;
	    $goods_number = $request->post('goods_number',0);
	    $per_limit_buy = $request->post('per_limit_buy',0);
	    $catgory_id = $request->post('cat_id', 0);
	    $goods_thumb = $request->post('goods_thumb','');
	    $goods_img = $request->post('goods_img','');
	    $original_img = $request->post('original_img','');
	    $shipping_fee = $request->post('shipping_fee',0);
	    $goods_desc = $request->post('goods_desc','');
	    
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
	    if($goods_name){
    	    $ret = Goods_Model::insertOrUpdateGoods($goods);
	    }
        $response->sendJSON(["result" => $ret ? 'SUCC' :'FAIL']);	    
	}
	
	/**
	 * 上传商品图片
	 * @param Request $request
	 * @param Response $response
	 */
	public function upload_goods_gallery(Request $request, Response $response){
	    $imgDIR = "/a/mch/goods/";
	    $img = $_POST["img"];
        $upload = new Upload($img, $imgDIR);
        $result = $upload->saveImgData();
        $ret = $upload->buildUploadResult($result);
        $response->sendJSON($ret);
	}
	
	/**
	 * 删除商品
	 * @param Request $request
	 * @param Response $response
	 */
	public function delete_goods(Request $request, Response $response){
	    $ret = ['result' => 'FAIL'];
	    $goods_ids = $request->post('goods_ids');
	    if($goods_ids){
	        if(!is_array($goods_ids)){
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
	public function update_goods_status(Request $request, Response $response){
	    $goods_ids = $request->post('goods_ids');
	    $status = $request->post('status');
	    $statusVal = $request->post('statusVal');
	    if($goods_ids){
	        if(!is_array($goods_ids)){
	            $goods_ids = [$goods_ids];
	        }
	        if($status == 'sale'){
	            Goods_Model::batchUpdateGoods($goods_ids, 'is_on_sale', $statusVal);
	        }
	    }
	    $response->sendJSON(['result' => 'SUCC']);
	}

	/**
	 * 产品分类
	 * @param Request $request
	 * @param Response $response
	 */
	public function goodsCategory(Request $request, Response $response)
	{
		$this->v->set_tplname("mod_goods_category");
		//todo 获取分类列表以及分页数据源---根据session_uid查询
		$categoryList = Goods_Model::getCategoryList();
		$this->v->assign('category', $categoryList);
		$response->send($this->v);
	}

	/**
	 * 新增一个分类页面
	 * @param Request $request
	 * @param Response $response
	 */
	public function addCategory(Request $request, Response $response)
	{
		$this->v->set_tplname('mod_goods_index');
		$cat_id = $request->get('cat_id',0);
		/**
		 * 将分类信息传到页面
		 */
		$merchant_id=$GLOBALS['user']->uid;
		$list=Goods_Model::getCatNameList($merchant_id);
		$this->assign('catName',$list);
		$this->v->assign('cat_id',$cat_id);
		$response->send($this->v);
	}

	/**
	 * 新增保存一个分类
	 * @param Request $request
	 * @param Response $response
	 */
	public function doAddCategory(Request $request, Response $response)
	{
		//先判断是否有了二级分类
		$cat_id = $request->post('cat_id', 0);
		$cateArr['cat_name']=$request->post('cat_name');
		$cateArr['sort_order']=$request->post('sort_order', 0);
		$cateArr['cate_thums']=$request->post('cate_thums');
		$result = Goods_Model::IsHadCategory($cat_id);
		if ($result['parent_id'] > 0) {
			$retmsg = "当前已是二级分类，不能增加子分类！";
			$data['status'] = 0;
			$data['retmsg'] = $retmsg;
		} else {
			$result = Goods_Model::addCategory($cateArr, $cat_id);//新增一个分类
			if (is_numeric($result)) {
				$data['status'] = 1;
				$data['retmsg'] = "新增成功！";
				$data['result'] = $result;
			} else {
				$data['status'] = 0;
				$data['retmsg'] = "新增失败！";
				if(is_string($result)){
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
		$cat_id = $request->get('cat_id', 0);
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
}