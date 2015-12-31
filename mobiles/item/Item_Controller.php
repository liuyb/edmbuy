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
		];
	}
	
	public function item(Request $request, Response $response)
	{
		$this->v->set_tplname('mod_item_item');
		$this->nav_no    = 1;
		$this->nav_flag1 = 'item';
		$this->v->set_page_render_mode(View::RENDER_MODE_GENERAL);
		
		if (1||$request->is_hashreq()) {
			$item_id = $request->arg(1);
			$this->v->assign('the_item_id', $item_id);
			
			$item = Items::load($item_id);
			if (!$item->is_exist()) {
				throw new ViewException($this->v, "查询商品不存在或已下架(商品id: {$item_id})");
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
					}
				}
				
				// assign item
				$this->v->assign('item', $item);
			}
		}
		else {
			
		}
		
		//$response->send($this->v);
		throw new ViewResponse($this->v);
	}
	
}
 
/*----- END FILE: Item_Controller.php -----*/