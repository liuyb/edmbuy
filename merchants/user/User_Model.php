<?php
/**
 * User Model
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class User_Model extends Model
{

    /**
     * 检查login
     * @param string $uname
     * @param string $upass_raw
     * @param array $output
     * @return integer -1: no the user; 0: password error; 1: ok
     */
    static function check_logined($uname, $upass_raw, &$output = [])
    {
        $uname = strtolower($uname);
        $where = "LOWER(`idname`)='%s'";
        if (Fn::check_mobile($uname)) { //手机登录
            $where = "`mobile`='%s'";
        } elseif (Fn::check_email_address($uname)) { //邮箱登录
            $where = "LOWER(`email`)='%s'";
        }

        $admin = D()->get_one("SELECT * FROM `shp_merchant` WHERE {$where}", $uname);
        if (empty($admin)) {
            return -1;
        }

        //check db password
        $upass_enc = gen_salt_password($upass_raw, $admin['salt'], 32, false);var_dump($upass_enc);
        if ($admin['password'] != $upass_enc) {
            return 0;
        }

        $output = $admin;
        return 1;
    }

    /**
     * 用户检测邮箱 或者 电话 用户名
     * @auth hc_edm
     * @param unknown_type $key
     * @param unknown_type $type 1 用户名 2 邮箱 3 电话
     */
    static function userCheck($key, $type)
    {
        if ($type == 1) {
            $where = "UPPER('admin_uname')={$key}";
        }
        if ($type == 2) {
            $where = "email={$key}";
        }
        if ($type == 3) {
            $where = "mobile={$key}";
        }
        $sql = "select admin_id from shp_merchant where {$where} ";
        $result = D()->query($sql)->get_one();
        $admin_uid = $result['admin_id'];
        if ($admin_uid && $admin_uid > 0) {
            return $admin_uid;
        }
             return false;
    }

    /**
     * 重置登录密码
     * @param $cookiesPhone
     * @param $password
     * @param $user_name
     */
    static function forgetPassword($key, $password, $type)
    {
        $upass_enc = gen_salt_password($password, $salt = NULL, $len = 32, $upper = false);
        if ($type == 1) {
            $where['UPPER(admin_uname)'] = $key;
        }
        if ($type == 2) {
            $where['email'] = $key;
        }
        if ($type == 3) {
            $where['mobile'] = $key;
        }
        //  $sql="update shp_users set password = {$upass_enc} where {$where}";
        $set['admin_upass'] = $upass_enc;
        $result = D()->query('merchant', $set, $where);
        return $result;
    }
}

/*----- END FILE: User_Model.php -----*/