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
    
    //生效佣金状态
    const COMMISSION_VALID = 1;
    //未生效佣金状态
    const COMMISSION_INVALID = 0;
    
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
                    u.parent_id in (SELECT user_id FROM edmbuy.shp_users where `parent_id` = %d)";
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
                 tu.parent_id in (
                		SELECT user_id FROM edmbuy.shp_users su where
                		su.parent_id in (SELECT user_id FROM edmbuy.shp_users where `parent_id` = %d)
                    )";
        $count = D()->query($sql, $uid)->result();
        return $count;
    }
    
    /**
     * 第一层列表
     * @param unknown $uid
     * @param Pager $pager
     */
    static function findFirstLevelList($uid, PagerPull $pager){
        $column = self::outputLevelListQueryColumn();
        $sql = "SELECT $column FROM edmbuy.shp_users where `parent_id` = %d order by user_id desc limit %d,%d";
        $result = D()->query($sql, $uid, $pager->start, $pager->realpagesize)->fetch_array_all();
        return $result;
    }
    
    /**
     * 第二层列表
     * @param unknown $uid
     * @param Pager $pager
     */
    static function findSecondLevelList($uid, PagerPull $pager){
        $column = self::outputLevelListQueryColumn();
        $sql = "SELECT $column FROM edmbuy.shp_users u where
                u.parent_id in (SELECT user_id FROM edmbuy.shp_users where `parent_id` = %d)
                order by user_id desc limit %d,%d";
        $result = D()->query($sql, $uid, $pager->start, $pager->realpagesize)->fetch_array_all();
        return $result;
    }
    
    /**
     * 第三层列表
     * @param unknown $uid
     * @param Pager $pager
     */
    static function findThirdLevelList($uid, PagerPull $pager){
        $column = self::outputLevelListQueryColumn();
        $sql = "SELECT $column FROM edmbuy.shp_users tu where
                 tu.parent_id in (
                		SELECT user_id FROM edmbuy.shp_users su where
                		su.parent_id in (SELECT user_id FROM edmbuy.shp_users where `parent_id` = %d)
                    ) order by user_id desc limit %d,%d";
        $result = D()->query($sql, $uid, $pager->start, $pager->realpagesize)->fetch_array_all();
        return $result;
    }
    
    /**
     * 米商列表展示需要的字段
     */
    static function outputLevelListQueryColumn(){
        $queryCols = " user_id as uid, logo, nick_name as nickname, level, province, city, childnum_1 as childnum1,
            parent_nick as parentnick, mobile_phone as mobilephone, wxqr ";
        return $queryCols;
    }
    
    /**
     * 一层用户佣金明细汇总
     */
    static function findFirstLevelCommisionCount($uid, $status){
        $column = self::outputCommisionCountQueryColumn();
        $sql = self::constructLevel1CommissionSql($column, FALSE);
        $count = D()->query($sql, $uid, $status)->fetch_array();
        return $count;
    }
    
    /**
     * 一层用户佣金明细列表
     */
    static function findFirstLevelCommisionList($uid, $status, PagerPull $pager){
        $column = self::outputCommisionListQueryColumn();
        $sql = self::constructLevel1CommissionSql($column, TRUE);
        $rows = D()->query($sql, $uid, $status, $pager->start, $pager->realpagesize)->fetch_array_all();
        return $rows;
    }
    
    static function constructLevel1CommissionSql($column, $limit){
        $sql = "select $column from shp_user_commision c where c.user_id in (
            	   SELECT user_id FROM edmbuy.shp_users where parent_id = %d 
                ) and state = %d ";
        if($limit == true){
            $sql .= " order by paid_time desc limit %d,%d";
        }
        return $sql;
    }
    
    /**
     * 二层用户佣金明细汇总
     */
    static function findSecondLevelCommisionCount($uid, $status){
        $column = self::outputCommisionCountQueryColumn();
        $sql = self::constructLevel2CommissionSql($column, false);
        $count = D()->query($sql, $uid, $status)->fetch_array();
        return $count;
    }
    
    /**
     * 二层用户佣金明细列表
     */
    static function findSecondLevelCommisionList($uid, $status, PagerPull $pager){
        $column = self::outputCommisionListQueryColumn();
        $sql = self::constructLevel2CommissionSql($column, true);
        $rows = D()->query($sql, $uid, $status, $pager->start, $pager->realpagesize)->fetch_array_all();
        return $rows;
    }
    
    static function constructLevel2CommissionSql($column, $limit){
        $sql = "select $column from shp_user_commision c where c.user_id in (
                    SELECT u.user_id FROM edmbuy.shp_users u where
                    u.parent_id in (SELECT user_id FROM edmbuy.shp_users where `parent_id` = %d)
                ) and state = %d";
        if($limit){
            $sql .= " order by paid_time desc limit %d,%d";
        }
        return $sql;
    }
    
    /**
     * 三层用户佣金明细汇总
     */
    static function findThirdLevelCommisionCount($uid, $status){
        $column = self::outputCommisionCountQueryColumn();
        $sql = self::constructLevel3CommissionSql($column, false);
        $count = D()->query($sql, $uid, $status)->fetch_array();
        return $count;
    }
    
    /**
     * 三层用户佣金明细列表
     */
    static function findThirdLevelCommisionList($uid, $status, PagerPull $pager){
        $column = self::outputCommisionListQueryColumn();
        $sql = self::constructLevel3CommissionSql($column, true);
        $rows = D()->query($sql, $uid, $status, $pager->start, $pager->realpagesize)->fetch_array_all();
        return $rows;
    }
    
    static function constructLevel3CommissionSql($column, $limit){
        $sql = "select $column from shp_user_commision c where c.user_id in (
                    SELECT tu.user_id FROM edmbuy.shp_users tu where
                    tu.parent_id in (
                		SELECT user_id FROM edmbuy.shp_users su where
                		su.parent_id in (SELECT user_id FROM edmbuy.shp_users where `parent_id` = %d)
                    )  
                ) and state = %d";
        if($limit){
            $sql .= " order by paid_time desc limit %d,%d";
        }
        return $sql;
    }
    
    /**
     * 输出佣金明细汇总字段
     */
    static function outputCommisionCountQueryColumn(){
        $queryCols = " count(1) as totalNum, ifnull(sum(order_amount),0) as totalAmount, ifnull(sum(commision),0) totalCommision ";
        return $queryCols;
    }
    
    /**
     * 输出佣金明细明细字段
     */
    static function outputCommisionListQueryColumn(){
        $queryCols = " order_unick as nickname, paid_time as paytime,ifnull(order_amount,0) amount,ifnull(commision,0) commision ";
        return $queryCols;
    }
    
}

?>