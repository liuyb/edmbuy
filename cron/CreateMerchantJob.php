<?php

/**
 * 内部直接创建商家
 * @author Jean
 *
 */
class CreateMerchantJob extends CronJob {
    
    
    public function main($argc, $argv) {
        if(count($argv) < 2){
            exit('invalid args...');
        }
        $mobile = $argv[1];
        $uid = ' ';
        if(count($argv) == 3){
            $uid = $argv[2];
        }
        $password = substr($mobile, -6);
        require SIMPHP_ROOT . "/mobiles/user/User_Model.php";
        $salt = User_Model::gen_salt();
        print_r('salt:'.$salt);
        $pass = User_Model::gen_password($password, $salt);
        print_r('password:'.$pass);
    }
}