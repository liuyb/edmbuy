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
			'shop/carousel'=>'carousel_upload'
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
		$response->send($this->v);
	}

	/**
	 * 首页轮播图
	 * @auth edm_hc
	 * @param Request $request
	 * @param Response $response
	 */
	public function carousel_upload(Request $request, Response $response){
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
	public function carousel_del (Request $request, Response $response){

	}
}
 
/*----- END FILE: Shop_Controller.php -----*/