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
		      'partner/list/%d' => 'partner_list',
		      'partner/ajax' => 'partner_ajaxdata'
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
          $this->buildIndexData();
        }
        
        throw new ViewResponse($this->v);
	}
	
	public function partner_list(Request $request, Response $response)
	{
	    $this->v->set_tplname('mod_partner_list');
	    $this->topnav_no = 1;
	    if ($request->is_hashreq()) {
	        $level = $request->arg(2);
	        $levelnum = $level;
	        $curpage = isset($_REQUEST['curpage']) ? $_REQUEST['curpage'] : 1;
	        $pager = new Pager($curpage, NULL);
	        Partner_Model::showCurrentLevelList($level, $pager);
	        $total = $pager->__get("totalnum");
	        $list= $pager->__get("result");
	        $pager->outputPageVar($this->v);
            $this->v->assign("title", $level."(".$total.")");
            $this->v->assign("list", $list);
            $this->v->assign("level", $levelnum);
	    }
	
	    throw new ViewResponse($this->v);
	}
	
	public function partner_ajaxdata(Request $request, Response $response)
	{
	    $uid = $GLOBALS['user']->uid;
	    $firstLevelCount = Partner::findFirstLevelCount($uid);
	    $secondLevelCount = Partner::findSecondLevelCount($uid);
	    $thirdLevelCount = Partner::findThirdLevelCount($uid);
	    $inactiveIncome = Partner_Model::getInactiveIncome($uid);
	    $ret = ["firstLevelCount" => $firstLevelCount, 
	            "secondLevelCount" => $secondLevelCount,
	            "thirdLevelCount" => $thirdLevelCount,
	            "inactiveIncome" => $inactiveIncome
	    ];
	    $response->sendJSON($ret);
	}
	
	private function buildIndexData() {
	    $uid = $GLOBALS['user']->uid;
	    $currentUser = Partner_Model::findUserInfoById($uid);
	    
	    $firstLevelCount = $currentUser->childnum1;
	    $secondLevelCount = $currentUser->childnum2;
	    $thirdLevelCount = $currentUser->childnum3;
	    $this->v->assign("userLevel", $currentUser->level);
	    $this->v->assign("firstLevelCount", $firstLevelCount);
	    $this->v->assign("secondLevelCount", $secondLevelCount);
	    $this->v->assign("thirdLevelCount", $thirdLevelCount);
	}
	
}

 
/*----- END FILE: Partner_Controller.php -----*/