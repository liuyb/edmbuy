<?php
/**
 * Node Model
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class User_Model extends Model
{

    static function checkAccessToken($token, $idfield = 'openid')
    {
        $record = D()->get_one("SELECT * FROM {access_token} WHERE token='%s'", $token);
        if (empty($record)) {
            return FALSE;
        } else {
            $now = simphp_time();
            if ($now > $record['lifetime']) {
                return FALSE;
            } else {
                return $record[$idfield];
            }
        }
    }

    /**
     * 检查用户信息完成度，nickname或logo没有的话都重定向请求OAuth2详细认证获取资料
     * @param array $uinfo
     * @return boolean
     */
    static function checkUserInfoCompleteDegree($uinfo, $refer = '/')
    {
        if (empty($uinfo['nickname']) || empty($uinfo['logo'])) { //只要两个其中一个为空，都请求OAuth2详细认证
            if (!isset($_SESSION['wxoauth_reqcnt'])) $_SESSION['wxoauth_reqcnt'] = 0;
            $_SESSION['wxoauth_reqcnt']++;
            if ($_SESSION['wxoauth_reqcnt'] < 4) { //最多尝试2次，避免死循环
                (new Weixin())->authorizing('http://' . Request::host() . '/user/oauth/weixin?act=&refer=' . $refer, 'detail');
            }
        }
        return true;
    }

    static function findUserInfoById($uid)
    {
        $user = Users::find_one(new Query('uid', $uid));
        return $user;
    }

    static function updateUserInfo(array $args)
    {
        if (!isset($args) || count($args) == 0) {
            return;
        }
        $uid = $GLOBALS['user']->uid;
        $user = new Users($uid);
        foreach ($args as $key => $val) {
            $user->$key = $val;
        }
        $user->save();
    }

    /**
     * 根据传入的订单状态列，统计该状态列数量
     * $sql = "select t1.c as status1,t2.c status2,t3.c status3 from
     * (SELECT count(1) c FROM edmbuy.shp_order_info where user_id=%d and pay_status = 0) t1,
     * (SELECT count(1) c FROM edmbuy.shp_order_info where user_id=%d and shipping_status = 0) t2,
     * (SELECT count(1) c FROM edmbuy.shp_order_info where user_id=%d and shipping_status = 1) t3";
     * @param unknown $uid
     * @param array $status 传入需要统计的订单状态列
     */
    static function findOrderStatusCountByUser($uid, array $status)
    {
        $sql = '';
        $field = '';
        $condition = '';
        $i = 0;
        foreach ($status as $statu => $val) {
            ++$i;
            if (is_array($val)) {
                foreach ($val as $item) {
                    $item_set = $item === SS_UNSHIPPED ? join(',', [SS_UNSHIPPED, SS_PREPARING, SS_SHIPPED_ING]) : join(',', [SS_SHIPPED, SS_SHIPPED_PART, OS_SHIPPED_PART]);
                    $pay_status = ($item === SS_UNSHIPPED || $item === SS_SHIPPED) ? ' AND pay_status=' . PS_PAYED : '';
                    $field .= "t$i.c as status$i,";
                    $condition .= "(SELECT count(1) c FROM edmbuy.shp_order_info where is_separate = 0 and user_id=$uid $pay_status and $statu IN ($item_set)) t$i ,";
                    ++$i;
                }
            } else {
                $field .= "t$i.c as status$i ,";
                $condition .= "(SELECT count(1) c FROM edmbuy.shp_order_info where is_separate = 0 and user_id=$uid and $statu = $val) t$i ,";
            }
        }
        $field = rtrim($field, ',');
        $condition = rtrim($condition, ',');
        $sql = "select $field from $condition";
        $rows = D()->query($sql)->fetch_array_all();
        if (is_array($rows) && count($rows) > 0) {
            return $rows[0];
        }
        return array();
    }

    /**
     * 根据user_id和订单order_sn查询订单和购买人信息
     * @auth hc_edm
     * @param $user_id
     * @param $order_sn
     */
    static function getOrderInfo($order_id, $user_id)
    {
        $result = [];
        $dbDrver = D();
        $sql = "select users.user_id,users.nick_name,users.logo,info.order_sn,info.order_id,info.money_paid,info.commision,info.is_separate ,info.parent_id
			  from shp_users users RIGHT JOIN
			  shp_order_info info on info.user_id=users.user_id
			  where info.order_id=" . $order_id;
        $userInfo = $dbDrver->get_one($sql);
        $result['userInfo'] = $userInfo;
        if (empty($userInfo)) {
            return ['userInfo' => null, 'goodsInfo' => null, 'ismBusiness' => null];
        }
        $is_separate = $userInfo['is_separate'];
        $goodsInfo = self::getGoodsList($userInfo, $is_separate);
        $ismBusiness = self::CheckmBusiness($userInfo['user_id']);
        /**
         * 获取用户的佣金
         */
        $commision = self::getUserCommision($order_id, $user_id);
        $result['goodsInfo'] = $goodsInfo;
        $result['ismBusiness'] = $ismBusiness;
        $result['commision'] = $commision;
        return $result;
    }

    /**
     * 获得用户的金额
     */
    static function getUserCommision($order_id, $user_id)
    {
        $sql = "select commision  from shp_user_commision where user_id={$user_id} and order_id = {$order_id}";
        $commsion = D()->query($sql)->result();
        return $commsion;
    }

    /**
     * 获取订单商品列表
     * @auth hc_adm
     * @param $userInfo 购买人基本信息
     * @param $is_separate
     */
    static function getGoodsList($userInfo, $is_separate = 0)
    {
        $dbDriver = D();
        $ids = "";
        if ($is_separate == 1) {
            //取出parent_id得到order_id
            $parent_id = $userInfo['order_id'];
            $sql = "select order_id from shp_order_info where parent_id=%d";
            $order_ids = $dbDriver->query($sql, $parent_id)->fetch_array_all();
            foreach ($order_ids as $values) {
                $ids .= $values['order_id'] . ",";
            }
            $ids = rtrim($ids, ",");
        }
        if (!empty($ids)) {
            $condition = "in(" . $ids . ")";
        } else {
            $res = $userInfo['order_id'];
            $condition = "=" . $res;
        }
        $sql = "select orders.goods_id ,orders.goods_number,orders.goods_price,
                info.order_status,info.shipping_status,info.pay_status,info.shipping_confirm_time,
                goods.goods_name,goods.goods_thumb
 				from shp_order_goods orders LEFT JOIN shp_goods goods on orders.goods_id = goods.goods_id
 				LEFT JOIN shp_order_info info on orders.order_id=info.order_id
				where orders.order_id " . $condition;
        $goodInfo = D()->query($sql)->fetch_array_all();
        if (empty($goodInfo)) {
            return [];
        }
        foreach ($goodInfo AS &$g) {
            $g['goods_thumb'] = Items::imgurl($g['goods_thumb']);
            $g['shipping_status'] = self::CheckOrderStatus($g['order_status'], $g['pay_status'], $g['shipping_status'], $g['shipping_confirm_time']);
        }
        return $goodInfo;
    }

    /**
     * 获取订单的状态
     * @auth hc_edm
     * @param $order_status 订单状态
     */
    static function CheckOrderStatus($order_status, $pay_status, $shipping_status, $shipping_confirm_time)
    {
        $msg = "";
        if ($pay_status == PS_PAYED) {
            if ($shipping_status == SS_RECEIVED) {
                $nowDateTime = simphp_gmtime();
                $days = ceil(($nowDateTime - $shipping_confirm_time) / 3600 / 24);
                if ($days > 7) {
                    $msg = "佣金已生效";
                } else {
                    $msg = "已签收" . $days . "天";
                }
            } elseif (in_array($shipping_status, [SS_SHIPPED, SS_SHIPPED_PART, OS_SHIPPED_PART])) {
                $msg = "已发货";
            } else {
                $msg = "未发货";
            }
        } else {
            $msg = "未支付";
            if ($order_status == OS_CANCELED) {
                $msg = "已取消";
            } elseif (in_array($order_status, [OS_REFUND, OS_REFUND_PART])) {
                $msg = "已退款";
            }
        }
        return $msg;
    }

    /**
     * @auth hc_edm
     * @param $order_ids用户订单号id
     */
    static function CheckmBusiness($user_id)
    {
        $sql = "select sum(money_paid) as money_paid from shp_order_info where user_id=" . $user_id . " and pay_status=" . PS_PAYED;
        $money_paids = D()->get_one($sql);
        if (empty($money_paids['money_paid'])) {
            return "差98元成为米商";
        }
        $money_paid = $money_paids['money_paid'];
        if ($money_paid >= 98) {
            return "已是米商";
        } else {
            $result = 98 - $money_paid;
            return "差" . $result . "元成为米商";
        }
    }

    /**
     * 检查商家入驻的手机号和邮箱
     * @param $mobile
     * @return mixed
     */
    static function ckeckMobile($mobile)
    {
        $sql = "select mobile  from shp_merchant where mobile ='%s' ";
        return D()->query($sql, $mobile)->result();
    }


    /**
     * 保存商家的注册信息
     * @param $mobile
     * @param $email
     * @param $inviteCode
     */
    static function saveMerchantInfo($mobile, $inviteCode, $password,$facename)
    {
        //insert($tablename, Array $insertarr, $returnid = TRUE, $flag = '')

        $add_time = time() - date('Z');
        $role_id = 1;
        $sql = "SELECT action_list FROM shp_role WHERE role_id ={$role_id}";
        $row = D()->query($sql)->get_one();
        $action_list = $row['action_list'];

        $sql = "SELECT nav_list FROM shp_admin_user WHERE action_list = 'all'";
        $row = D()->query($sql)->get_one();
        $nav_list = $row['nav_list'];

        $data_admin = array(
            'user_name' => $mobile,
            'email' => '',
            'password' => '',
            'ec_salt' => '',
            'add_time' => $add_time,
            'action_list' => $action_list,
            'nav_list' => $nav_list,
            'role_id' => $role_id,
        );
        $tablename = "`shp_merchant`";
        $table_admin = "`shp_admin_user`";
        $insertarr['merchant_id'] = self::gen_merchant_id();
        $salt = self::gen_salt();
        $password_enc = self::gen_password($password, $salt);
        $data_admin['password'] = $password_enc;
        $data_admin['ec_salt'] = $salt;
        $insertarr['password'] = $password_enc;
        $insertarr['salt'] = $salt;
        $insertarr['mobile'] = $mobile;
        $insertarr['invite_code'] = $inviteCode ? $inviteCode : "";
        $insertarr['email'] = '';
        $insertarr['password'] = $password_enc;
        $insertarr['role_id'] = $role_id;
        $insertarr['facename'] = $facename;
        $admin_uid = D()->insert($table_admin, $data_admin);
        if ($admin_uid !== false) {
            $insertarr['admin_uid'] = $admin_uid;
            $effnum = D()->insert($tablename, $insertarr);
            if ($effnum !== false) {
                D()->update($table_admin, array('merchant_id' => $insertarr['merchant_id']), array('user_id' => $admin_uid)); //更新merchant_id
                return $insertarr['merchant_id'];
            }
             return false;
        }
        return false;
    }

    static function gen_merchant_id()
    {
        return 'mc_' . uniqid();
    }

    static function gen_salt()
    {
        return substr(uniqid(), -6);
    }

    static function gen_password($raw_passwd, $salt = NULL)
    {
        if (!isset($salt)) $salt = gen_salt();
        return md5(md5($raw_passwd) . $salt);
    }

    /**
     * 校验邀请码
     * @param $inviteCode
     */
    static function checkInviteCode($inviteCode)
    {
        $inviteCode =intval($inviteCode);
        $sql = "select user_id ,level,nick_name from shp_users where user_id = {$inviteCode}";
        return D()->query($sql)->get_one();
    }



    /**
     * 注册成功更新商家信息
     * @param $merchant_id
     * @param $invite_code
     * @param $order_id
     */
    static function UpdataMerchantInfo($merchant_id, $order_id,$order_sn)
    {
//        update($tablename, Array $setarr, $wherearr, $flag = '')
        $tablename = "`shp_merchant_payment`";
        $setarr['order_id'] = $order_id;
        $setarr['order_sn'] = $order_sn;
        $setarr['money_paid'] = 0;
        $setarr['merchant_id'] = $merchant_id;
        $setarr['start_time'] = date("Y-m-d H:i:s", time()).'';
        $endDate = date("Y-m-d", strtotime("+1 year", time()))."23:59:59";
        $setarr['end_time'] = $endDate;
        $setarr['term_time'] = '1y';
        $setarr['discount'] = MECHANT_GOODS_AMOUNT - MECHANT_ORDER_AMOUNT;
        $setarr['goods_amount'] = MECHANT_GOODS_AMOUNT;
        $setarr['order_amount'] = MECHANT_ORDER_AMOUNT;
        $setarr['user_id'] = $GLOBALS['user']->uid;
        D()->insert($tablename, $setarr);
    }

    /**
     * 检验当前操作进行到了第几步骤
     */
    static function checkIsPaySuc(){
            $user_id = $GLOBALS['user']->uid;
            $time =date('Y-m-d H:i:s' ,time());
            $sql = "select count(1) from shp_merchant_payment where user_id = %d and start_time <= '{$time}' and end_time >='{$time}' and money_paid > 0";

           return D()->query($sql,$user_id)->result();
    }

    /**
     *拿字段的值
     */
    static function getMechantPaymentClums($clums){
        $user_id = $GLOBALS['user']->uid;
        $sql ="select {$clums} from shp_merchant_payment pay LEFT JOIN shp_merchant mer on pay.merchant_id = mer.merchant_id  where pay.user_id = %d";
        return D()->query($sql,$user_id)->get_one();
    }

    /**
     * 更新user表
     * @param $parent_id
     * @param $nick_name
     */
    static function updUserInvetCode($parent_id,$nick_name){
//        update($tablename, Array $setarr, $wherearr, $flag = '');
        $tablename ="`shp_users`";
        $setarr['parent_id'] = $parent_id;
        $setarr['nick_name'] = $nick_name;
        $wherearr['user_id'] = $GLOBALS['user']->uid;
        D()->update($tablename,$setarr,$wherearr);
    }

    /**
     * 得到用户的panrent_id
     */
    static function getParentId(){
        $sql = "select parent_id from shp_users where user_id = {$GLOBALS['user']->uid}";
        return D()->query($sql)->result();
    }
    
    /**
     * 我收藏的商家总数
     */
    static function getCollectShopCount(){
        $sql = "select count(m.merchant_id) from shp_merchant m join shp_collect_shop ss on m.merchant_id = ss.merchant_id where ss.user_id = '%d' ";
        $result = D()->query($sql, $GLOBALS['user']->uid)->result();
        return $result;
    }
    
    /**
     * 我收藏的商家列表
     * @param PagerPull $pager
     * @param array $options orderby(oc销量|cc收藏量) 
     */
    static function getCollectShopList(PagerPull $pager, array $options){
        $orderby = $options['orderby'] ? $options['orderby'] : 'ss.rec_id';
        $sql = "select m.merchant_id as merchant_id, m.facename as facename, m.logo as logo, ifnull(mo.oc, 0) oc, ifnull(cs.cc, 0) cc 
                from shp_merchant m join shp_collect_shop ss on m.merchant_id = ss.merchant_id 
                left join
                (select merchant_ids, count(order_id) oc from shp_order_info where is_separate = 0 and pay_status = ".PS_PAYED." and merchant_ids <> ''
                group by merchant_ids) mo
                on m.merchant_id = mo.merchant_ids
                left join
                (select count(1) as cc, merchant_id from shp_collect_shop group by merchant_id) cs
                on m.merchant_id = cs.merchant_id where ss.user_id = '%d' order by $orderby desc limit %d,%d";
        $result = D()->query($sql, $GLOBALS['user']->uid, $pager->start, $pager->realpagesize)->fetch_array_all();
        $pager->setResult($result);
    }
    
    /**
     * 我收藏的商品总数
     * @return mixed
     */
    static function getCollectGoodsCount(){
        $sql = "select count(g.goods_id) from shp_goods g join shp_collect_goods c on g.goods_id = c.goods_id and c.user_id = '%d' order by c.rec_id desc ";
        $result = D()->query($sql, $GLOBALS['user']->uid)->result();
        return $result;
    }
    
    /**
     * 获取我收藏的商品列表
     * @param PagerPull $pager
     */
    static function getCollectGoodsList(PagerPull $pager){
        $sql = "select g.* from shp_goods g join shp_collect_goods c on g.goods_id = c.goods_id and c.user_id = '%d' order by c.rec_id desc ";
        $goods = D()->query($sql, $GLOBALS['user']->uid, $pager->start, $pager->realpagesize)->fetch_array_all();
        $goods = Items::buildGoodsImg($goods);
        $pager->setResult($goods);
    }
}