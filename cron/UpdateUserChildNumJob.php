<?php

/**
 * 更新推荐人为当前用户所属的 一层 二层 三层 用户数量
 * @author Jean
 *
 */
class UpdateUserChildNumJob extends CronJob {
    
    public function main($argc, $argv) {
        $this->update_user_childnum();
    }
    
    /**
     * 更新推荐人为当前用户所属的 一层 二层 三层 用户数量
     */
    private function update_user_childnum(){
        $start = 0;
        $limit = 10000;
        $total_record = $this->get_user_count();
        $this->log('total user record: '.$total_record);
        $user_list = $this->get_userid_list($start, $limit);
        while (!empty($user_list)) {
            $this->log('current user record: '. ($start + count($user_list)) . '/'.$total_record.'...');
            foreach ($user_list AS $item) {
                $uid = $item['user_id'];
                if($uid){
                    $this->update_childnum_by_userid($uid);
                }
            }
            $start += $limit;
             
            //unset以释放内存
            unset($user_list);
             
            $this->log('sleep 1 seconds...');
            sleep(1); //暂停1秒
            $user_list = $this->get_userid_list($start, $limit);
        }
    }
    
    private function get_user_count(){
        $sql = "select count(*) from shp_users";
        return D()->query($sql)->result();
    }
    
    private function get_userid_list($start, $limit){
        $sql = "select user_id from shp_users limit %d,%d";
        return D()->query($sql, $start, $limit)->fetch_array_all();
    }
    
    private function update_childnum_by_userid($uid){
        $sql = "update shp_users set
        childnum_1 = (
            select t.c from
            (
                SELECT count(1) as c FROM edmbuy.shp_users where `parent_id` = $uid
            ) t
        ),
        childnum_2 = (
            select t.c from
            (
                SELECT count(1) as c FROM edmbuy.shp_users u where 
                u.parent_id in (SELECT user_id FROM edmbuy.shp_users where `parent_id` = $uid)
            ) t
        ),
        childnum_3 = (
            select t.c from
            (
                SELECT count(1) as c FROM edmbuy.shp_users tu where 
                 tu.parent_id in (
                		SELECT user_id FROM edmbuy.shp_users su where
                		su.parent_id in (SELECT user_id FROM edmbuy.shp_users where `parent_id` = $uid)
                    ) 
            ) t
        )
        where user_id = $uid";
        D()->query($sql);
    }
}

