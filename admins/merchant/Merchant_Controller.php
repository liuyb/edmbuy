<?php
/**
 * 控制器
 *
 * @author afarliu
 */
defined('IN_SIMPHP') or die('Access Denied');

class Merchant_Controller extends AdminController
{

    private $_nav = 'merchant';

    /**
     * hook menu
     *
     * @return array
     */
    public function menu()
    {
        return [
				'merchant/%s/detail'=>'view_detail',
	            'merchant/check'=>'check',
                'merchant/recommend' => 'recommend'
		];
    }

    /**
     * hook init
     * 
     * @param string $action            
     * @param Request $request            
     * @param Response $response            
     */
    public function init($action, Request $request, Response $response)
    {
        $this->nav = 'merchant';
		parent::init($action, $request, $response);
    }

    /**
     * default action 'index'
     * 
     * @param Request $request            
     * @param Response $response            
     */
    public function index(Request $request, Response $response)
    {
        $this->v->set_tplname('mod_merchant_index');
        $this->nav_second = 'index';
        
        // 查询条件
        $query_conds = [];
        $searchinfo = [
            'name' => '',
            'verify' => ''
        ];
        $searchinfo['name'] = $request->get('name', '');
        $searchinfo['verify'] = $request->get('verify', '');
        $searchstr = 'name=' . $searchinfo['name'] . '&verify=' . $searchinfo['verify'];
        $this->v->assign('searchinfo', $searchinfo);
        $this->v->assign('searchstr', $searchstr);
        $query_conds = array_merge($query_conds, $searchinfo);
        
        // BEGIN list order
        //$orderinfo = $this->v->set_listorder('created', 'desc');
        //$extraurl = $searchstr . '&';
        //$extraurl .= $orderinfo[2];
        $this->v->assign('extraurl', $searchstr);
        $this->v->assign('qparturl', '#/merchant');
        $this->v->assign('backurl', '/merchant,' . $searchstr . '&p=' . (isset($_GET['p']) ? $_GET['p'] : ''));
        // END list order
        
        // Record List
        $limit = 30;
        $recordList = Merchant_Model::getMerchantList('created', 'desc', $limit, $query_conds);
        $recordNum = count($recordList);
        $totalNum = $GLOBALS['pager_totalrecord_arr'][0];
        
        $this->v->assign('recordList', $recordList)
            ->assign('recordNum', $recordNum)
            ->assign('totalNum', $totalNum);
        
        $response->send($this->v);
    }
    
    public function view_detail(Request $request, Response $response){
        $this->v->set_tplname('mod_merchant_detail');
        $this->nav_second = 'index';
        $backurl = $request->get('backurl','');
        $this->v->assign('backurl', $backurl);
        $mid = $request->arg(1);
        $detail = Merchant_Model::getMerchantDetail($mid);
        $detail['merchant_id'] = $mid;//payment不存在时 merchant_id 被覆盖
        $result = Merchant_Model::getMerchantMaterias($mid, $detail['merchant_type']);
        $this->v->assign('detail', $detail);
        $this->v->assign('materia', $result);
        $response->send($this->v);
    }
    
    public function check(Request $request, Response $response){
        $ret = ['flag' => 'FAIL', 'msg' => '审核失败'];
        if($request->is_post()){
            $mid = $request->post('mid');
            $verify = $request->post('verify');
            $merchant = Merchant::load($mid);
            if(!$merchant->is_exist()){
                $response->sendJSON($ret);
            }
            if($merchant->verify != Merchant::VERIFY_CHECKING){
                $ret = ['flag' => 'FAIL', 'msg' => '当前状态还不是待审核状态'];
                $response->sendJSON($ret);
            }
            $newMch = new Merchant();
            $newMch->uid = $mid;
            if($verify == Merchant::VERIFY_FAIL){
                $verify_fail_msg = $request->post('verify_fail_msg');
                $newMch->verify_fail_msg = $verify_fail_msg;
            }
            $newMch->verify = $verify;
            $newMch->save(Storage::SAVE_UPDATE);
            if(D()->affected_rows()){
                $ret = ['flag' => 'SUC', 'msg' => '审核通过'];
            }
            $response->sendJSON($ret);
        }
    }
    
    /**
     * 商家店铺推荐
     * @param Request $request
     * @param Response $response
     */
    public function recommend(Request $request, Response $response){
        $ret = ['flag' => 'FAIL', 'msg' => '操作失败'];
        if($request->is_post()){
            $mid = $request->post('mid');
            $type = $request->post('type');
            $merchant = Merchant::load($mid);
            if(!$merchant->is_exist()){
                $response->sendJSON($ret);
            }
            $newMch = new Merchant();
            $newMch->uid = $mid;
            $newMch->recommed_flag = $type;
            $newMch->save(Storage::SAVE_UPDATE);
            print_r(D()->getSqlFinal());
            if(D()->affected_rows()){
                $ret = ['flag' => 'SUC', 'msg' => '操作成功'];
            }
            $response->sendJSON($ret);
        }
    }
}