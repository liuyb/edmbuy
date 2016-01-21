<?php
/**
 * Partner Controller
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Partner_Controller extends MobileController {
	
	/**
	 * hook init
	 *
	 * @param string $action
	 * @param Request $request
	 * @param Response $response
	 */
	public function init($action, Request $request, Response $response)
	{
		$this->nav_flag1 = 'partner';
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
		      'partner/list' => 'partner_list',
		      'partner/list/ajax' => 'partner_list_ajax',
		      'partner/ajax' => 'partner_ajaxdata',
		      'partner/commission' => 'partner_commission',
		      'partner/commission/ajax' =>'partner_commission_ajax'
		];
	}
	
	/**
	 * default action 'index'
	 *
	 * @param Request $request
	 * @param Response $response
	 */
	public function index(Request $request, Response $response)
	{
		$this->v->set_tplname('mod_partner_index');
        $this->topnav_no = 0;
        $this->nav_no    = 1;
        
        if ($request->is_hashreq()) {
            global $user;
            $this->v->assign("childnum1", $user->childnum1);
            $this->v->assign("childnum2", $user->childnum2);
            $this->v->assign("childnum3", $user->childnum3);
        }
        
        throw new ViewResponse($this->v);
	}
	
	public function partner_list(Request $request, Response $response)
	{
	    $this->v->set_tplname('mod_partner_list');
	    $this->nav_no = 0;
	    $this->topnav_no = 1;
	    if ($request->is_hashreq()) {
	        $level = $_REQUEST['level'];
	        $levelCN = Partner_Model::TransLevelCN($level);
	        $total = isset($_REQUEST['count']) ? $_REQUEST['count'] : 1;;
            $this->v->assign("title", $levelCN."(".$total."人)");
            $this->v->assign("level", $level);
	    }
	
	    throw new ViewResponse($this->v);
	}
	
	public function partner_list_ajax(Request $request, Response $response){
	    $curpage = isset($_REQUEST['curpage']) ? $_REQUEST['curpage'] : 1;
	    $level = $_REQUEST['level'];
        $pager = new PagerPull($curpage, NULL);
        Partner_Model::showCurrentLevelList($level, $pager);
        $pageJson = $pager->outputPageJson();
        $ret = ["result" => $pager->result];
        $ret = array_merge($ret, $pageJson);
	    $response->sendJSON($ret);
	}
	
	public function partner_ajaxdata(Request $request, Response $response)
	{
	    $uid = $GLOBALS['user']->uid;
	    $firstLevelCount = Partner::findFirstLevelCount($uid);
	    $secondLevelCount = Partner::findSecondLevelCount($uid);
	    $thirdLevelCount = Partner::findThirdLevelCount($uid);
	    $Incomes = Partner_Model::getCommisionIncome($uid);
	    $inactiveIncome = 0.00;
	    $totalIncome = 0.00;
	    foreach ($Incomes as $item){
	        if(Partner::COMMISSION_VALID == $item['state']){
	            $totalIncome = $item['commision'];
	        }else if(Partner::COMMISSION_INVALID == $item['state']){
	            $inactiveIncome = $item['commision'];
	        }
	    }
	    $ret = ["firstLevelCount" => $firstLevelCount, 
	            "secondLevelCount" => $secondLevelCount,
	            "thirdLevelCount" => $thirdLevelCount,
	            "inactiveIncome" => $inactiveIncome,
	            "totalIncome" => $totalIncome
	    ];
	    $response->sendJSON($ret);
	}
	
	/**
	 * 佣金明细页面
	 * @param Request $request
	 * @param Response $response
	 * @throws ViewResponse
	 */
	public function partner_commission(Request $request, Response $response){
	    $this->v->set_tplname('mod_partner_commission');
	    $this->nav_no = 0;
	    $this->topnav_no = 1;
	    if ($request->is_hashreq()) {
	        $status = $_REQUEST['status'];
	        $this->v->assign("status", $status);
	    }
	    
	    throw new ViewResponse($this->v);
	}
	
	/**
	 * 通过ajax输出佣金明细
	 * @param Request $request
	 * @param Response $response
	 */
	public function partner_commission_ajax(Request $request, Response $response){
	    $curpage = isset($_REQUEST['curpage']) ? $_REQUEST['curpage'] : 1;
	    $level = isset($_REQUEST['level']) ? $_REQUEST['level'] : Partner::Partner_LEVEL_1;
	    $status = $_REQUEST['status'];
	    $needtotal = isset($_REQUEST['needtotal']) ? $_REQUEST['needtotal'] : 0;
        $pager = new PagerPull($curpage, NULL);
        $pager->needtotal= $needtotal;
        Partner_Model::showCurLevelCommistionList($level, $status, $pager);
        $pageJson = $pager->outputPageJson();
        $ret = ["result" => $pager->result,
                "otherMap" => $pager->otherMap
        ];
        $ret = array_merge($ret, $pageJson);
	    $response->sendJSON($ret);
	}
}

 
/*----- END FILE: Partner_Controller.php -----*/