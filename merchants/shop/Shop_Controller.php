<?php
/**
 * 店铺控制器
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Shop_Controller extends MerchantController
{

    public function menu()
    {
        return [
            'shop/carousel' => 'carousel_upload',
            'shop/carousel/add' => 'carousel_add',
            'shop/carousel/del' => 'carousel_del'
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
        //查出用户所有的轮播图
        $carousel = Shop_Model::selCarousel();
        $this->v->assign("carousel", $carousel);
        $response->send($this->v);
    }

    /**
     * 首页轮播图
     * @auth edm_hc
     * @param Request $request
     * @param Response $response
     */
    public function carousel_upload(Request $request, Response $response)
    {
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
    public function carousel_del(Request $request, Response $response)
    {
        $carousel_id=$request->post("carousel_id");
        Shop_Model::delCarouse($carousel_id);
        $ret['retmsg']="删除成功!";
        $ret['status']=1;
        $response->sendJSON($ret);
    }

    /**
     * 添加首页轮播图
     * @param Request $request
     * @param Response $response
     */
    public function carousel_add(Request $request, Response $response)
    {
        /**
         * 首先处理删除
         * 1.得到用户的所有图片id
         */
        $ids = Shop_Model::getCarouselId();//得到carousel_id
        $Arraydata = $request->post("imgArr");
        if (empty($Arraydata)) {
            $ret['retmsg'] = "不能删除所有轮播图！";
            $ret['status'] = 0;
            $response->sendJSON($ret);
        }
        $newid=[];
        foreach ($Arraydata as $val) {

            if ($val[0] > 0) {
                //删除轮播
                array_push($newid,$val[0]);
            } elseif($val[0] > 0 && in_array($val[0], $ids)){

                Shop_Model::updCarouse($val[0],$val[1],$val[2],$val[3]);
            }
            if($val[0] == 0) {
                //处理新增
               Shop_Model::addCarouse($val[1],$val[2],$val[3]);
            }
        }
            foreach($ids as $id){
                if(!in_array($id['carousel_id'],$newid)){
                    Shop_Model::delCarouse($val[0]);
                }

            }
            $res['retmsg']="操作成功!";
            $res['status']=1;
            $response->sendJSON($res);


    }
}

/*----- END FILE: Shop_Controller.php -----*/