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
        $upass_enc = gen_salt_password($upass_raw, $admin['salt'], 32, false);
        if ($admin['password'] != $upass_enc) {
            return 0;
        }

        $output = $admin;
        return $admin;
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
            $where = "mobile='{$key}'";
        }
        $sql = "select merchant_id from shp_merchant where {$where} ";
        $result = D()->query($sql)->get_one();
        $admin_uid = $result['merchant_id'];
        if ($admin_uid ) {
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


        if ($type == 1) {
            $where['UPPER(idname)'] = $key;
        }
        if ($type == 2) {
            $where['email'] = $key;
        }
        if ($type == 3) {
            $where['mobile'] = $key;
        }

           $sql="select salt from shp_merchant where mobile='{$key}'";
            $salt=D()->query($sql)->result();
             $upass_enc = gen_salt_password($password, $salt, 32, $upper = false);
        $table="`shp_merchant`";
        $set['password'] = $upass_enc;
        $result = D()->update($table, $set, $where);
        return $result;
    }

    /**
     * 校验短信时间
     * @param $phone
     */
    static function checkSmsLimit($phone){
            $sql="select overdueTime from shp_usersms_log where receivePhone = {$phone} and result = 1 ORDER by id DESC  limit 1";
            $limit=D()->query($sql)->result();
             if(time() < $limit && $limit){
                    return false;
                }
                return true;

    }
}

/*----- END FILE: User_Model.php -----*/