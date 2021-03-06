<?php
/**
 * Item Controller
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Item_Controller extends MobileController {
	
	/**
	 * hook init
	 *
	 * @param string $action
	 * @param Request $request
	 * @param Response $response
	 */
	public function init($action, Request $request, Response $response)
	{
		$this->nav_flag1 = 'item';
		parent::init($action, $request, $response);
	}
	
	/**
	 * hook menu
	 * @see Controller::menu()
	 */
	public function menu()
	{
		return [
				'item/%d' => 'item',
		    'item/promote' => 'item_promote',
		    'item/promote/product' => 'promote_product',
		    'item/pref/show' => 'pref_show',
		    'item/pref/goods' => 'pref_goods_list',
		    'item/comment/image' => 'upload_comment_pic',
		    'item/comment/list' => 'get_goods_comment',
		    'item/comment/page' => 'goods_comment',
		    'item/comment' => 'post_goods_comment',
		    'item/merchant/recommend' => 'get_merchant_recommend',
		    'item/goods/collect/num' => 'getGoodsCollects',
		    'item/goods/collect' => 'changeGoodsCollect'
		];
	}
	
	public function item(Request $request, Response $response)
	{
		$this->setPageView($request, $response, '_page_mpa');
		$this->v->set_tplname('mod_item_item');
		$this->nav_no    = 1;
		$this->nav_flag1 = 'item';
		$this->v->set_page_render_mode(View::RENDER_MODE_GENERAL);
		//$this->backurl = '/';
		
		if (1||$request->is_hashreq()) {
			
			$item_id = $request->arg(1);
			$item = Items::load($item_id);
			if($item->goods_flag && $item->goods_flag > 0){
			    //赠品跟系统商品不显示菜单
			    $this->nav_no = 0;
			}
			if (!$item->is_exist()) {
				$this->nav_no = 0;
				throw new ViewException($this->v, "查询商品不存在(商品id: {$item_id})");
			}
			elseif ($item->is_delete) {
				$this->nav_no = 0;
				throw new ViewException($this->v, "查询商品已删除");
			}
			else {
				// 更新访问数
				$item->add_click_count();
				
				// 相册
				$galleries = ItemsGallery::find(new Query('item_id', $item_id));
				foreach ($galleries AS $g) {
					$g->img_url = Items::imgurl($g->img_url);
				}
				$this->v->assign('galleries', $galleries);
				$this->v->assign('gallerynum', count($galleries));
				
				if (trim($item->item_desc)!='') {
					include (SIMPHP_CORE.'/libs/htmlparser/simple_html_dom.php');
					$dom = str_get_html($item->item_desc);
					$imgs= $dom->find('img');
					$imgs_src = [];
					if (!empty($imgs)) {
						foreach ($imgs AS $img) {
							$imgs_src[] = $img->getAttribute('src');
						}
					
						foreach($imgs_src as $psrc) {
							$src_parsed = Items::imgurl($psrc);
							if('/'==$psrc{0} && '/'!=$psrc{1}) { //表示本地上传图片
								$item->item_desc = str_replace('src="'.$psrc.'"', 'src="'.$src_parsed.'"', $item->item_desc);
							}
						}
						$default_src = emptyimg();
						$extra_js =<<<HEREDOC
<script type="text/javascript">
$(function(){
	$('#list1 .product_detail img').each(function(){
		var _orisrc = $(this).attr('src');
		$(this).attr('data-loaded','0').attr('src','{$default_src}');
		imgQueueLoad(this,_orisrc);
	});
});
</script>
HEREDOC;
						$item->item_desc .= $extra_js;
					}
				}
				
				// assign item
				$item->item_thumb = $item->imgurl($item->item_thumb);
				$item->commision_show = $item->commision > 0 ? $item->commision : ($item->shop_price > $item->income_price ? $item->shop_price - $item->income_price : 0);
				$item->commision_show = number_format($item->commision_show*(1-PLATFORM_COMMISION),2);
				$this->v->assign('item', $item);
				$this->v->assign('item_desc', htmlspecialchars_decode($item->item_desc));
				
				//运费信息
				Items::getGoodsRealShipFee($item);
				
				//商品规格、属性
				list($item_attrs, $attrmap) = Item_Model::get_goods_attrs($item->item_id);
				$this->v->assign('attr_grp', $item_attrs);
			    $attrmap = json_encode($attrmap);
				$this->v->assign('attrmap', $attrmap);
				
				//SEO信息
				$seo = [
						'title'   => $item->item_name . ' - '.L('appname'),
						'keyword' => $item->item_name,
						'desc'    => $item->item_brief
				];
				$this->v->assign('seo', $seo);
				
				//Spm信息
				$referee = false;
				$spm = Spm::check_spm();
				if ($spm && preg_match('/^user\.(\d+)(\.\w+)?$/i', $spm, $matchspm)) {
					$referee = Users::load($matchspm[1]);
					if (!$referee->is_exist() || !$referee->nickname) {
						$referee =  false;
					}
				}
				$this->v->assign('referee', $referee);
				
				//购物车数
				$cartnum = Cart::getUserCartNum(Cart::shopping_uid());
				$this->v->assign('cartnum', $cartnum);
				//商家信息
				$merchant = Merchant::load($item->merchant_id);
				$this->v->assign('merchant', $merchant);
				//商家客服
				$ent_id = Merchant::getMerchantKefu($item->merchant_id);
				$this->v->assign('ent_id', $ent_id);
			}
			//从商品详情页面点击返回链接逻辑
			$back = $request->get('back');
			$backhref = '';
			if(isset($back) && $back == 'index'){
			    $backhref = "location.href='".U()."';";
			}else if(isset($back) && $back == 'pref'){
			    $category = $request->get('category','');
			    if(isset($category) && $category){
    			    $backhref = "location.href='".U('item/pref/show','type='.$category)."';";
			    }
			}
			if(empty($backhref)){
			  //$backhref = "goBack('".U()."')";
				$backhref = backscript(true, U());
			}
			$this->v->assign('back', $backhref);
		}
		else {
			
		}
		
		throw new ViewResponse($this->v);
	}
	
	public function item_promote(Request $request, Response $response){
	  $this->setPageView($request, $response, '_page_mpa');  
		$this->v->set_tplname('mod_item_promote');
	    $this->topnav_no = 1;
	    $this->nav_no    = 0;
	    throw new ViewResponse($this->v);
	}
	
	public function promote_product(Request $request, Response $response){
	    $this->v->set_tplname('mod_item_promote_product');
	    $this->topnav_no    = 1;
	    throw new ViewResponse($this->v);
	}
	
	/**
	 * 专区页面
	 * @param Request $request
	 * @param Response $response
	 * @throws ViewResponse
	 */
	public function pref_show(Request $request, Response $response){
		$this->setPageView($request, $response, '_page_mpa');
		$this->extra_css = 'greybg';
	    $this->nav_no    = 0;
	    $type = $request->get('type');
	    $mod = Item_Model::createModByCategory($type);
	    $this->v->set_tplname($mod);
	    if(1||$request->is_hashreq()){
	        //购物车数
	        $cartnum = Cart::getUserCartNum(Cart::shopping_uid());
	        $this->v->assign('cartnum', $cartnum);
	    }
	    //分享信息
	    $share_info = [
	        'title' => '收藏了很久的特价商城，超划算！',
	        'desc'  => Item_Model::$prefe_share_title[$type],
	        'link'  => U('/item/pref/show?type='.$type, 'spm='.Spm::user_spm(), true),
	        'pic'   => U(Item_Model::$prefe_share_pic[$type],'',true),
	    ];
	    $this->v->assign('share_info', $share_info);
	    throw new ViewResponse($this->v);
	}
	
	/**
	 * 专区页面 商品列表展示
	 * @param Request $request
	 * @param Response $response
	 */
	public function pref_goods_list(Request $request, Response $response){
	    $curpage = isset($_REQUEST['curpage']) ? $_REQUEST['curpage'] : 1;
	    $pager = new PagerPull($curpage, 50);
	    $category = $request->get('category');
	    if(Item_Model::CATEGORY_98 == $category){
	       Item_Model::findGoodsListByPrice($pager, 98);
	    }else{
	       Item_Model::findGoodsListByPref($pager, $category);
	    }
	    $pageJson = $pager->outputPageJson();
	    $ret = ["result" => $pager->result];
	    $ret = array_merge($ret, $pageJson);
	    $response->sendJSON($ret);
	}
	
	//进入评论页面
	public function goods_comment(Request $request, Response $response){
	    $this->v->set_tplname('mod_item_comment');
	    $this->nav_no    = 1;
	    $this->topnav_no = 1;
	    $this->v->set_page_render_mode(View::RENDER_MODE_GENERAL);
	    $goods_id = $request->get('goods_id');
	    $order_id = $request->get('order_id');
	    $this->v->assign('goods_id', $goods_id);
	    $this->v->assign('order_id', $order_id);
	    throw new ViewResponse($this->v);
	}
	
	//上传评论图片
	public function upload_comment_pic(Request $request, Response $response){
	    if ($request->is_post()) {
	        $img = $_POST["img"];
            $upload = new AliyunUpload($img, 'comment', 'goods');
            $result = $upload->saveImgData();
            $ret = $upload->buildUploadResult($result);
	        $response->sendJSON($ret);
	    }
	}
	
	//提交评论
	public function post_goods_comment(Request $request, Response $response){
	    $ret = 'FAIL';
	    if ($request->is_post()) {
    	    $goods_id = $request->post('goods_id');
    	    $order_id = $request->post('order_id');
    	    $content = $request->post('content', '');
    	    $comment_img = $request->post('comment_img','');
    	    $comment_thumb = $request->post('comment_thumb','');
    	    $comment_level = $request->post('comment_level',1);
    	    $shipping_level = $request->post('shipping_level',5);
    	    $service_level = $request->post('service_level',5);
    	    if ($goods_id && $content){
    	        $c = new Comment();
    	        $c->content = $content;
    	        $c->comment_img = $comment_img;
    	        $c->comment_thumb = $comment_thumb;
    	        $c->comment_level = $comment_level;
    	        $c->shipping_level = $shipping_level;
    	        $c->service_level = $service_level;
    	        $c->id_value = $goods_id;
    	        $c->order_id = $order_id;
    	        $item = Items::load($goods_id);
    	        $c->merchant_id = $item->merchant_id;
    	        Item_Model::postGoodsComment($c);
    	        $ret = 'SUCC';
    	    }
	    }
	    $response->sendJSON($ret);
	}
	
	//获取商品的评论数据
	public function get_goods_comment(Request $request, Response $response){
	    $goods_id = $request->get('goods_id');
	    $category = $request->get('category', '');
	    $curpage = $request->get('curpage', 1);
	    $pager = new PagerPull($curpage, 20);
	    $c = new Comment();
	    $c->id_value = $goods_id;
	    Item_Model::getGoodsComment($c, $pager, $category);
	    $pageJson = $pager->outputPageJson();
	    $ret = ["result" => $pager->result];
	    $ret = array_merge($ret, $pageJson);
	    $ret['gather'] = Comment::getCommentGroupCount($goods_id);
	    $response->sendJSON($ret);
	}
	
	//查询店铺推荐
	public function get_merchant_recommend(Request $request, Response $response){
	    $merchant_id = $request->get('merchant_id');
	    $curpage = $request->get('curpage', 1);
	    $pager = new PagerPull($curpage, 10);
	    Item_Model::findShopRecommendGoodsList($pager, ["merchant_id"=>$merchant_id]);
	    $pageJson = $pager->outputPageJson();
	    $response->sendJSON($pageJson);
	}
	
	//获取商品收藏数
	public function getGoodsCollects(Request $request, Response $response){
	    $goods_id = $request->get('goods_id', 0);
	    $totalCollects = 0;//Items::getGoodsCollects($goods_id);
	    $myCollect = Items::getGoodsCollects($goods_id, $GLOBALS['user']->uid);
	    $response->sendJSON(['total' => $totalCollects, 'my' => $myCollect]);
	}
	
	//用户点击或取消收藏
	public function changeGoodsCollect(Request $request, Response $response){
	    if($request->is_post()){
	        $goods_id = $request->post('goods_id', 0);
	        $action = $request->post('action', 0);
	        Items::changeGoodsCollect($GLOBALS['user']->uid, $goods_id, $action);
	        $response->sendJSON("");
	    }
	}
	
}
 
/*----- END FILE: Item_Controller.php -----*/