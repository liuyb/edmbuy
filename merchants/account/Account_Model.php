<?php
/**
 * 店铺Model
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Account_Model extends Model
{
    /**
     * 校验密码
     * @param $password
     * @return bool
     */
    static function checkMerchantPwd($password)
    {
        $merchant_id = $GLOBALS['user']->uid;
        $sql = "select salt from shp_merchant where merchant_id ='{$merchant_id}'";
        $salt = D()->query($sql)->result();
        $upass_enc = gen_salt_password($password, $salt, 32, $upper = false);
        $sql = "select count(1) from shp_merchant where merchant_id = '{$merchant_id}' AND password ='{$upass_enc}'";
        return D()->query($sql)->result();
    }

    /**
     * 重置登录密码
     * @param $password
     */
    static function setMerchantPwd($password)
    {
        $merchant_id = $GLOBALS['user']->uid;
        $sql = "select salt from shp_merchant where merchant_id ='{$merchant_id}'";
        $salt = D()->query($sql)->result();
        $upass_enc = gen_salt_password($password, $salt, 32, $upper = false);
        $table = "`shp_merchant`";
        $set['password'] = $upass_enc;
        $where['merchant_id'] = $merchant_id;
        D()->update($table, $set, $where);
    }

    /**
     * 结算列表
     * @param Pager $pager
     * @param array $options
     */
    static function getPagedOrders(Pager $pager, array $options)
    {
        $muid = $GLOBALS['user']->uid;
        $where = " and is_removed = 0 ";
        $orderby = "";
        if ($options['order_sn']) {
            $where .= " and o.order_sn like '%%" . D()->escape_string(trim($options['order_sn'])) . "%%' ";
        }
        if ($options['start_date']) {
            $starttime = simphp_gmtime(strtotime($options['start_date'] . DAY_BEGIN));
            $where .= " and o.add_time >= $starttime ";
        }
        if ($options['end_date']) {
            $endtime = simphp_gmtime(strtotime($options['end_date'] . DAY_END));
            $where .= " and o.add_time <= $endtime ";
        }
        if ($options['status']) {
            $statusSql = Order::build_order_status_sql(intval($options['status']), 'o');
            if ($statusSql) {
                $where .= $statusSql;
            }
        }
        if ($options['simp_status']) {
            $in=[0,1];
            if(in_array($options['simp_status'],$in)){
                $where .= " and sett.stmt_status is null or sett.stmt_status=1";
            }else{
                $where .= " and sett.stmt_status = " . intval($options['simp_status']) . "";
            }
        }
        $orderby .= " order by o.add_time desc ";
        $sql = " SELECT count(1) FROM shp_order_info o left join shp_users u on u.user_id = o.user_id
                 LEFT JOIN  shp_order_settlement stmt on o.order_id = stmt.order_id
                 LEFT JOIN  shp_settlement sett ON stmt.stmt_id = sett.stmt_id where o.merchant_ids='%s' and o.is_separate = 0 $where ";
        $count = D()->query($sql, $muid)->result();
        $pager->setTotalNum($count);
        $sql = "SELECT o.*, ifnull(u.nick_name, '') as nick_name ,sett.stmt_status FROM shp_order_info o left join shp_users u on u.user_id = o.user_id
                 LEFT JOIN  shp_order_settlement stmt on o.order_id = stmt.order_id
                 LEFT JOIN  shp_settlement sett ON  sett.stmt_id = stmt.stmt_id  where o.merchant_ids='%s' and o.is_separate = 0 $where  $orderby  limit {$pager->start},{$pager->pagesize}";
        $orders = D()->query($sql, $muid)->fetch_array_all();
        foreach ($orders as &$order) {
            self::rebuild_order_info($order);
        }
        $pager->setResult($orders);
    }

    static function rebuild_order_info(&$order)
    {
        $order['add_time'] = date('Y-m-d H:i', simphp_gmtime2std($order['add_time']));
        $order['order_status_text'] = Fn::get_order_text($order['pay_status'], $order['shipping_status'], $order['order_status']);
        $order['actual_order_amount'] = Order::get_actual_order_amount($order);
        if ($order['stmt_status'] == 1) {
            $order['stmt_status'] = "未生效";
        } elseif ($order['stmt_status'] == 0) {
            $order['stmt_status'] = "未生效";
        } elseif ($order['stmt_status'] == 2) {
            $order['stmt_status'] = "已生效";
        }
    }

    /**
     * 得到开户行列表
     */
    static function getCashingBank()
    {
        $sql = "select bank_code,bank_name from shp_cashing_bank where enabled = 1 and bank_code not in ('ALIPAY','WXPAY')";
        return D()->query($sql)->fetch_array_all();
    }

    /**
     * 绑定银行卡
     * @param $data
     */
    static function setBank($data, $rid = 0)
    {
        if (!is_array($data)) {
            return false;
        }
        $data['merchant_id'] = $GLOBALS['user']->uid;
        //先校验用户受否已经绑定过银行卡

        $tablename = "`shp_merchant_bank`";
        if ($rid > 0) {
            $where['rid'] = $rid;
            D()->update($tablename, $data, $where, 'IGNORE');
            return 1;
        }
        $data['timeline'] = time();
        D()->insert($tablename,$data, false, 'IGNORE');
        return 2;
    }

    static function checkBank()
    {
        $merchant_id = $GLOBALS['user']->uid;
        $sql = "select count(1) from shp_merchant_bank where merchant_id = '{$merchant_id}'";
        return D()->query($sql)->result();
    }

    /**
     * 得到用户绑定的银行卡信息
     */
    static function getBindCard()
    {
        $merchant_id = $GLOBALS['user']->uid;
        $sql = "select * from shp_merchant_bank WHERE merchant_id ='{$merchant_id}'";
        return D()->query($sql)->fetch_array_all();
    }

    /**
     * 商家银行卡信息
     * @param $rid
     */
    static function getBankDetail($rid)
    {
        $merchant_id = $GLOBALS['user']->uid;
        $sql = "select * from shp_merchant_bank WHERE merchant_id ='{$merchant_id}' AND rid = %d";
        return D()->query($sql, $rid)->get_one();
    }

    /**
     * 校验手机号码
     */
    static function checkRegMobile($mobile)
    {
        $merchant_id = $GLOBALS['user']->uid;
        $sql = "select count(1) from shp_merchant where merchant_id ='{$merchant_id}' and mobile = '{$mobile}'";
        return D()->query($sql)->result();
    }

}
