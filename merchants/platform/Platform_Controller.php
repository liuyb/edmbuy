<?php
/**
 * 店铺控制器
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Platform_Controller extends MerchantController
{

    public function menu()
    {
        return [
            'platform/authent' => 'authent',
            'platform/fill/in' => 'fillIn',
            'platform/personal/material' => 'personal',
            'platform/company/material' => 'company',
            'platform/material/upload' => 'material_upload',
            'platform/material/submit' => 'submitMerchantMaterial',
        ];

    }

    /**
     * 平台首页
     * @param Request $request
     * @param Response $response
     */
    public function index(Request $request, Response $response)
    {
        $response->redirect('/platform/authent');
        $this->setSystemNavigate('platform');
        $this->v->set_tplname("mod_platform_index");
        $this->setPageLeftMenu('platform', 'list');
        $response->send($this->v);
    }

    /**
     * 资料认证
     * @param Request $request
     * @param Response $response
     */
    public function authent(Request $request, Response $response)
    {
        $this->v->set_tplname("mod_platform_authent");
        //判断用户是否已经填写资料
        $this->setPageLeftMenu('platform', 'authent');
        $merchant = Merchant::load($GLOBALS['user']->uid);
        $this->v->assign('merchant', $merchant);
        $response->send($this->v);

    }

    /**
     * 填写商家资料
     * @param Request $request
     * @param Response $response
     */
    public function fillIn(Request $request, Response $response)
    {
        Platform_Model::checkMerchantStatus($response);
        $this->v->set_tplname("mod_platform_fillin");
        $this->setPageLeftMenu('platform', 'authent');
        $response->send($this->v);
    }

    /**
     * 公司资料填写
     * @param Request $request
     * @param Response $response
     */
    public function company(Request $request, Response $response)
    {
        $show_page = true;
        if ($show_page) {
            $v = new PageView('mod_platform_company', '_page_box');
            $response->send($v);
        }
    }

    /**个人资料填写
     * @param Request $request
     * @param Response $response
     */
    public function personal(Request $request, Response $response)
    {
        $show_page = true;
        if ($show_page) {
            $v = new PageView('mod_platform_pensonal', '_page_box');
            $response->send($v);
        }
    }

    /**图片上传
     * @param Request $request
     * @param Response $response
     */
    public function material_upload(Request $request, Response $response)
    {
        $ret = [
            'flag' => 'FAIL',
            'errMsg' => '上传失败，请稍后重试！'
        ];
        if ($request->is_post()) {
            $img = $_POST["img"];
            $upload = new AliyunUpload($img, 'platform', 'goods');
            $result = $upload->saveImgData();
            $ret = $upload->buildUploadResult($result);
        }
        $response->sendJSON($ret);
    }

    /**
     * 保存商家上传的资料
     * @param Request $request
     * @param Response $response
     */
    public function submitMerchantMaterial(Request $request, Response $response)
    {
        $ret = ['flag' => 'FAIL'];
        $result = 0;
        if($request->is_post()){
            $data = $request->post("json");
            $type = $data['type'];
            if ($type == 'company') {
                $result = Platform_Model::addComMaterial($data);
            }elseif($type == 'personal'){
                $result = Platform_Model::addPerMaterial($data);
            }
        }
        if($result){
            $ret = ['flag' => 'SUC'];
        }else{
            $ret['retmsg'] = '提交失败，请稍后重试!';
        }
        $response->sendJSON($ret);
    }
}
