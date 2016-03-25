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
		    'item/merchant/recommend' => 'get_merchant_recommend'
		];
	}
	
	public function item(Request $request, Response $response)
	{
		$this->v->set_tplname('mod_item_item');
		$this->nav_no    = 1;
		$this->nav_flag1 = 'item';
		$this->v->set_page_render_mode(View::RENDER_MODE_GENERAL);
		//$this->backurl = '/';
		
		if (1||$request->is_hashreq()) {
			
			$item_id = $request->arg(1);
			$item = Items::load($item_id);
			if (!$item->is_exist()) {
				$this->nav_no = 0;
				throw new ViewException($this->v, "查询商品不存在(商品id: {$item_id})");
			}/*
			elseif (!$item->is_on_sale) {
				$this->nav_no = 0;
				throw new ViewException($this->v, "查询商品已下架(商品id: {$item_id})");
			}*/
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
					}
				}
				
				// assign item
				$item->item_thumb = $item->imgurl($item->item_thumb);
				$item->commision_show = $item->commision > 0 ? $item->commision : ($item->shop_price > $item->income_price ? $item->shop_price - $item->income_price : 0);
				$item->commision_show = number_format($item->commision_show*(1-PLATFORM_COMMISION),2);
				$this->v->assign('item', $item);
				$this->v->assign('item_desc', json_encode($item->item_desc));
				
				//商品规格、属性
				$item_attrs = Items::attrs($item->id);
				$attr_grp = [];
				foreach ($item_attrs AS $attr) {
					if (!isset($attr_grp[$attr['attr_id']])) {
						$attr_grp[$attr['attr_id']] = ['attr_name'=>$attr['attr_name'], 'attrs'=>array()];
					}
					$attr_grp[$attr['attr_id']]['attrs'][] = $attr;
				}
				$this->v->assign('attr_grp', $attr_grp);
				
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
				if ($spm && preg_match('/^user\.(\d+)$/', $spm, $matchspm)) {
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
				$merchant = Merchant::find_one(new Query('admin_uid', $item->merchant_uid));
				$kefu_link = 'javascript:;';
				if ($merchant->is_exist() && preg_match('/^http(s?):\/\//i', $merchant->kefu)) {
					$kefu_link = $merchant->kefu;
					//$kefu_link = 'https://eco-api.meiqia.com/dist/standalone.html?eid=4119';
					
					global $user;
					$kefu_meta = [
							'name'   => Item_Model::escape_kefu_link($user->nickname),
							'gender' => $user->sex ? ($user->sex==1?'男':'女') : '未知',
							'tel'    => $user->mobilephone,
							'comment'=> '多米号'.$user->uid,
					];
					$kefu_link .= '&metadata={';
					foreach ($kefu_meta AS $k => $v) {
						$kefu_link .= '"'.$k.'":"'.$v.'",';
					}
					$kefu_link = substr($kefu_link, 0, -1);
					$kefu_link .= '}';
					
				}
				$this->v->assign('kefu_link', $kefu_link);
			}
			//从商品详情页面点击返回链接逻辑
			$back = $request->get('back');
			$backhref = '';
			if(isset($back) && $back == 'index'){
			    $backhref = "location.href='/';";
			}else if(isset($back) && $back == 'pref'){
			    $category = $request->get('category','');
			    if(isset($category) && $category){
    			    $backhref = "location.href='/item/pref/show?type=".$category."';";
			    }
			}
			if(empty($backhref)){
			    $backhref = "goBack()";
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
	    $this->nav_no    = 0;
	    $type = $request->get('type');
	    $mod = Item_Model::createModByCategory($type);
	    $this->v->set_tplname($mod);
	    if($request->is_hashreq()){
	        //购物车数
	        $cartnum = Cart::getUserCartNum(Cart::shopping_uid());
	        $this->v->assign('cartnum', $cartnum);
	    }
	    //分享信息
	    $share_info = [
	        'title' => '难得的好商城，值得关注！',
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
	    $imgDIR = '/a/comment/';
	    if ($request->is_post()) {
	        $img = $_POST["img"];
            $upload = new Upload($img, $imgDIR);
            $upload->has_thumb = true;
            $upload->thumbwidth = 200;
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
    	    $shipping_level = $request->post('shipping_level',0);
    	    $service_level = $request->post('service_level',0);
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
	    $pager = new PagerPull($curpage, 10);
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
	    $merchant_uid = $request->get('merchant_uid');
	    $curpage = $request->get('curpage', 1);
	    $pager = new PagerPull($curpage, 20);
	    Item_Model::findGoodsListInSameMerchant($pager, ["merchant_uid"=>$merchant_uid]);
	    $pageJson = $pager->outputPageJson();
	    $ret = ["result" => $pager->result];
	    $ret = array_merge($ret, $pageJson);
	    $response->sendJSON($ret);
	}
	
}
 
/*----- END FILE: Item_Controller.php -----*/