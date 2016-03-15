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
    static function getOrderInfo($order_id)
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
        $result['goodsInfo'] = $goodsInfo;
        $result['ismBusiness'] = $ismBusiness;
        return $result;
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
        //判断是否为米商
        $sql = "select orders.goods_id ,orders.goods_number,orders.goods_price,
                info.shipping_status,info.shipping_confirm_time,
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
            $g['shipping_status'] = self::CheckOrderStatus($g['shipping_status'], $g['shipping_confirm_time']);
        }
        return $goodInfo;
    }

    /**
     * 获取订单的状态
     * @auth hc_edm
     * @param $shipping_status 订单状态
     */
    static function CheckOrderStatus($shipping_status, $shipping_confirm_time)
    {
        switch ($shipping_status) {
            case OS_CANCELED:
            case OS_INVALID:
            case OS_RETURNED:
            case OS_REFUND:
                $shipping_status = "已取消";
                break;
            case SS_UNSHIPPED:
            case SS_PREPARING:
            case SS_SHIPPED_ING:
                $shipping_status = "未发货";
                break;
            case SS_SHIPPED:
            case SS_SHIPPED_PART:
            case OS_SHIPPED_PART:
                $shipping_status = "已发货";
                break;
            case SS_RECEIVED:
                $nowDateTime = strtotime(date("Y-m-d"));
                $days = ceil(($nowDateTime - $shipping_confirm_time) / 3600 / 24);
                $shipping_status = "已签收第" . $days . "天";
                break;
        }
        return $shipping_status;
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
}