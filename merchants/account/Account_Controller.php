<?php
/**
 * 店铺控制器
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Account_Controller extends MerchantController
{

    public function menu()
    {
        return [
            'account/editpwd' => 'editpwd',
            'account/trade/detail' => 'trade_detail',
            'account/trade/list' => 'trade_list',
            'account/bind/bank' => 'bind_bank',
            'account/merchant/setbank' => 'setbank',
            'account/merchant/withdraw' => 'withdraw',
            'account/merchant/getcode' => 'getcode',
            'account/mechant/checkbank' => 'checkbank'
        ];

    }

    /**
     * 账户设置
     * @param Request $request
     * @param Response $response
     */
    public function index(Request $request, Response $response)
    {
        $this->setSystemNavigate('account');
        $this->v->set_tplname("mod_account_index");
        $this->setPageLeftMenu('account', 'list');
        //查出店铺所有信息
        //查询用户绑定的银行卡信息
        $bank_list = Account_Model::getBindCard();
//        $card_no =substr($bank_list['bank_no'],-4);
//        $bank_list['bank_no'] = $card_no;
        $this->v->assign('bank_list', $bank_list);
        $response->send($this->v);
    }

    /**
     * 修改密码
     * @param Request $request
     * @param Response $response
     */
    public function editpwd(Request $request, Response $response)
    {
        $this->v->set_tplname("mod_account_editpwd");
        $this->setPageLeftMenu('account', 'editpwd');
        $response->send($this->v);
    }

    /**
     * 设置密码
     * @param Request $request
     * @param Response $response
     */
    public function setpwd(Request $request, Response $response)
    {
        $password = $request->post("password", "");
        $pwd = $request->post("pwd", "");
        $confirm_pwd = $request->post("confirm_pwd", "");
        $password = trim($password, "");
        $pwd = trim($pwd, "");
        $confirm_pwd = trim($confirm_pwd, "");
        if ($confirm_pwd != $pwd) {
            $ret['retmsg'] = "两次输入的密码不一致!";
            $ret['status'] = 0;
            $response->sendJSON($ret);
        }

        $result = Account_Model::checkMerchantPwd($password);//校验密码
        if (!$result) {
            $ret['retmsg'] = "原始密码不正确!";
            $ret['status'] = 0;
            $response->sendJSON($ret);
        }
        Account_Model::setMerchantPwd($pwd);
        $ret['retmsg'] = "修改密码成功!";
        $ret['status'] = 1;
        $response->sendJSON($ret);
    }

    /***
     * 交易明细
     * @param Request $request
     * @param Response $response
     */
    public function  trade_detail(Request $request, Response $response)
    {
        $this->v->set_tplname("mod_account_trade_detail");
        $this->setPageLeftMenu('account', 'draw_detail');
        $response->send($this->v);
    }

    public function trade_list(Request $request, Response $response)
    {

        $curpage = $request->get('curpage', 1);
        $order_sn = $request->get('order_sn', '');
        $start_date = $request->get('start_date', '');
        $end_date = $request->get('end_date', '');
        $status = $request->get('order_status', 0);
        $simp_status = $request->get('simp_status', 0);
        $options = array("order_sn" => $order_sn,
            "start_date" => $start_date, "end_date" => $end_date, "status" => $status,
            'simp_status' => $simp_status
        );
        $pager = new Pager($curpage, $this->getPageSize());
        Account_Model::getPagedOrders($pager, $options);
        $ret = $pager->outputPageJson();
        $response->sendJSON($ret);
    }

    /**
     * 添加银行卡
     * @param Request $request
     * @param Response $response
     */
    public function bind_bank(Request $request, Response $response)
    {
        $v = new PageView('mod_account_bindbank', '_page_box');
        if (isset($_GET['rid'])) {
            $rid = $request->get("rid");
            $detail = Account_Model::getBankDetail($rid);
            Func::assign_regions($this->v, $detail['bank_province'], $detail['bank_city']);
            $v->assign('bank_detail', $detail);
            $v->assign('type', 1);
            $v->assign('rid', $rid);
        } else {
            $bank_detail['bank_uname'] = "";
            $bank_detail['card_num'] = "";
            $bank_detail['bank_no'] = "";
            $bank_detail['bank_branch'] = "";
            $bank_detail['bank_province'] = "";
            $bank_detail['bank_city'] = "";
            $bank_detail['bank_code'] = "";
            $bank_detail['bank_name'] = "";
            $this->v->assign('province_list', Order::get_regions(1, 1));
            $this->v->assign('bank_detail', $bank_detail);
            $this->v->assign('rid', 0);
            $this->v->assign('type', 0);
        }
        $bank_list = Account_Model::getCashingBank();
        $this->v->assign("bank_list", $bank_list);
        $response->send($v);
    }

    /**
     * 绑定银行卡
     * @param Request $request
     * @param Response $response
     */
    public function setbank(Request $request, Response $response)
    {
        $data['bank_uname'] = $request->post("bank_uname", "");
        $data['card_num'] = $request->post("card_num", "");
        $data['bank_no'] = $request->post("bank_no", "");
        $data['bank_branch'] = $request->post("bank_branch", "");
        $data['bank_province'] = $request->post("bank_province");
        $data['bank_city'] = $request->post("bank_city");
        $data['bank_code'] = $request->post("bank_code", "");
        $data['bank_name'] = $request->post("bank_name", "");
        $rid = $request->post("rid");
        $mobile = $request->post("mobile");
        $mobile_code = $request->post("mobile_code");
        Account_Model::checkRegMobile($mobile);
        if (!$mobile) {
            $ret['retmsg'] = "手机号码不存在!";
            $ret['status'] = 0;
            $response->sendJSON($ret);
        } elseif ($_SESSION['bind_bank'] != $mobile_code) {
            $ret['retmsg'] = "手机验证码错误!";
            $ret['status'] = 0;
            $response->sendJSON($ret);
        } elseif($_SESSION['mobile']!=$mobile){
            $ret['retmsg'] = "手机号码有误请重新获取!";
            $ret['status'] = 0;
            $response->sendJSON($ret);
        }
        $result = Account_Model::setBank($data, intval($rid));
        if ($result == 1) {
            $ret['retmsg'] = "修改银行卡成功!";
            $ret['status'] = 1;
            $response->sendJSON($ret);
        }elseif ($result == 2) {
            $ret['retmsg'] = "绑定银行卡成功!";
            $ret['status'] = 1;
            $response->sendJSON($ret);
        }
    }

    /**
     * 商家提取现
     * @param Request $request
     * @param Response $response
     */
    public function withdraw(Request $request, Response $response)
    {
        $this->v->set_tplname("mod_account_withdraw");
        $this->setPageLeftMenu('account', 'list');
        $bank_list = Account_Model::getBindCard();
        $this->v->assign('bank_list', $bank_list);
        $response->send($this->v);
    }

    /***
     * 绑定银行卡获取验证码
     * @param Request $request
     * @param Response $response
     */
    public function getcode(Request $request, Response $response)
    {
        $mobile = $request->post("mobile");
        $result = Account_Model::checkRegMobile($mobile);
        if (!$result) {
            $ret['retmsg'] = "与商家入驻绑定手机号码不匹配!";
            $ret['status'] = 0;
            $response->sendJSON($ret);
        }
        $type = "bind_bank";
        //todo 发送验证码
       // $result = Sms::sendSms($mobile,$type,true);
        if (true) {
            $_SESSION['mobile'] = $mobile;
           $_SESSION['bind_bank'] = "8888";
           $ret['retmsg'] = "发送验证码成功!";
            $ret['status'] = 1;
            $response->sendJSON($ret);
        }
    }

    /**
     * 校验银行
     * @param Request $request
     * @param Response $response
     */
    public function checkbank(Request $request, Response $response){
          $result =  Account_Model::checkBank();
        if($result){
            $ret['retmsg'] ="您已经绑定银行卡无需再绑定!";
            $ret['status'] = 0;
            $response->sendJSON($ret);
        }
            $ret['status'] = 1;
            $response->sendJSON($ret);
    }
}
