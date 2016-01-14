<?php
defined('IN_SIMPHP') or die('Access Denied');
/**
 * 合伙人公用Model
 * @author Jean
 *
 */
class Partner extends StorageNode {
    
    /**
     * Partner level constant
     * @var constant
     */
    const Partner_LEVEL_1 = 1; //一层
    const Partner_LEVEL_2 = 2; //二层
    const Partner_LEVEL_3 = 3; //三层
    
    protected static function meta() {
        return array();
    }
    
    /**
     * 第一层总数
     * @param unknown $uid
     * @return mixed
     */
    static function findFirstLevelCount($uid){
        $sql = "SELECT count(1) FROM edmbuy.shp_users where `parent_id` = %d";
        $count = D()->query($sql, $uid)->result();
        return $count;
    }
    
    /**
     * 第二层总数
     * @param unknown $uid
     * @return mixed
     */
    static function findSecondLevelCount($uid){
        $sql = "SELECT count(1) FROM edmbuy.shp_users u where
                    exists (SELECT 1 FROM edmbuy.shp_users where `parent_id` = %d and u.`parent_id` = `user_id`)";
        $count = D()->query($sql, $uid)->result();
        return $count;
    }
    
    /**
     * 第三层总数
     * @param unknown $uid
     * @return mixed
     */
    static function findThirdLevelCount($uid){
        $sql = "SELECT count(1) FROM edmbuy.shp_users tu where
                 exists (
                	select 1 from
                	(
                		SELECT user_id FROM edmbuy.shp_users su where
                		exists (SELECT 1 FROM edmbuy.shp_users where `parent_id` = %d and su.`parent_id` = `user_id`)
                    )  tp where tu.`parent_id` = tp.`user_id`
                 )";
        $count = D()->query($sql, $uid)->result();
        return $count;
    }
    
    /**
     * 第一层列表
     * @param unknown $uid
     * @param Pager $pager
     */
    static function findFirstLevelList($uid, Pager $pager){
        $column = self::outputQueryColumn();
        $sql = "SELECT $column FROM edmbuy.shp_users where `parent_id` = %d limit %d,%d";
        $result = D()->query($sql, $uid, $pager->__get("start"), $pager->__get("pagesize"))->fetch_array_all();
        return $result;
    }
    
    /**
     * 第二层列表
     * @param unknown $uid
     * @param Pager $pager
     */
    static function findSecondLevelList($uid, Pager $pager){
        $column = self::outputQueryColumn();
        $sql = "SELECT $column FROM edmbuy.shp_users u where
                    exists (SELECT 1 FROM edmbuy.shp_users where `parent_id` = %d and u.`parent_id` = `user_id`)
                    limit %d,%d";
        $result = D()->query($sql, $uid, $pager->__get("start"), $pager->__get("pagesize"))->fetch_array_all();
        return $result;
    }
    
    /**
     * 第三层列表
     * @param unknown $uid
     * @param Pager $pager
     */
    static function findThirdLevelList($uid, Pager $pager){
        $column = self::outputQueryColumn();
        $sql = "SELECT $column FROM edmbuy.shp_users tu where
                 exists (
                	select 1 from
                	(
                		SELECT user_id FROM edmbuy.shp_users su where
                		exists (SELECT 1 FROM edmbuy.shp_users where `parent_id` = %d and su.`parent_id` = `user_id`)
                    )  tp where tu.`parent_id` = tp.`user_id`
                 ) limit %d,%d";
        $result = D()->query($sql, $uid, $pager->__get("start"), $pager->__get("pagesize"))->fetch_array_all();
        return $result;
    }
    
    /**
     * 米商列表展示需要的字段
     */
    static function outputQueryColumn(){
        $queryCols = " user_id as uid, logo, nick_name as nickname, level, province, city, childnum_1 as childnum1,
            parent_nick as parentnick, mobile_phone as mobilephone, wxqr ";
        return $queryCols;
    }
}

?>