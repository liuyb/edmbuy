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
            'goods/delete'  => 'delete_goods'
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
        $pager = new Pager($curpage, 9);	    
	    Goods_Model::getPagedGoods($pager, $options);
	    $ret = $pager->outputPageJson();
	    $ret['otherResult'] = $pager->otherMap;
	    $response->sendJSON($ret);
	}
	
	public function goods_info(Request $request, Response $response)
	{
	    $this->v->set_tplname('mod_goods_info');
	     
	    $response->send($this->v);
	}
	
	public function goods_publish(Request $request, Response $response)
	{
	    print_r($_POST);
	    exit();
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
	    $goods_desc = $request->post('$goods_desc','');
	    
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
	    
	    if($goods_name){
    	    Goods_Model::insertOrUpdateGoods($goods);
	    }
	    
	    //$response->redirect('/goods');
	}
	
	public function upload_goods_gallery(Request $request, Response $response){
	    $imgDIR = "/a/mch/goods/";
	    $img = $_POST["img"];
        $upload = new Upload($img, $imgDIR);
        $upload->has_thumb = true;
        $upload->thumbwidth = 200;
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
	
}
 
/*----- END FILE: Goods_Controller.php -----*/