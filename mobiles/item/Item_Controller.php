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
			$item = Items::load($item_id);
			if (!$item->is_exist()) {
				$this->nav_no = 0;
				throw new ViewException($this->v, "查询商品不存在(商品id: {$item_id})");
			}
			elseif (!$item->is_on_sale) {
				$this->nav_no = 0;
				throw new ViewException($this->v, "查询商品未上架(商品id: {$item_id})");
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
				$item->item_thumb = $item->imgurl($item->item_thumb);
				$item->commision_show = $item->commision > 0 ? $item->commision : ($item->shop_price > $item->income_price ? $item->shop_price - $item->income_price : 0);
				$item->commision_show = number_format($item->commision_show*(1-PLATFORM_COMMISION),2);
				$this->v->assign('item', $item);
				
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
				
				//商家信息
				$merchant = Merchant::find_one(new Query('admin_uid', $item->user_id));
// 				Response::dump($merchant);
				$kefu_link = 'javascript:;';
				if ($merchant->is_exist() && preg_match('/^http(s?):\/\//i', $merchant->kefu)) {
					$kefu_link = $merchant->kefu;
				}
				$this->v->assign('kefu_link', $kefu_link);
			}
		}
		else {
			
		}
		
		throw new ViewResponse($this->v);
	}
	
}
 
/*----- END FILE: Item_Controller.php -----*/