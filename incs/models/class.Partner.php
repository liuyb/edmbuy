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
    
    //标记当前查询层级关系为代理
    const LEVEL_TYPE_AGENCY = 'agency';
    
    protected static function meta() {
        return array();
    }
    
    /**
     * 第一层总数
     * @param unknown $uid
     * @return mixed
     */
    static function findFirstLevelCount($uid, $options = ''){
        $where = self::setLevelQueryCondition($options);
        $sql = "SELECT count(1) FROM edmbuy.shp_users where `parent_id` = %d $where ";
        $count = D()->query($sql, $uid)->result();
        return $count;
    }
    
    /**
     * 第二层总数
     * @param unknown $uid
     * @return mixed
     */
    static function findSecondLevelCount($uid, $options = ''){
        $where = self::setLevelQueryCondition($options);
        $sql = "SELECT count(1) FROM edmbuy.shp_users u where parent_id2 = %d $where ";
        $count = D()->query($sql, $uid)->result();
        return $count;
    }
    
    /**
     * 第三层总数
     * @param unknown $uid
     * @return mixed
     */
    static function findThirdLevelCount($uid, $options = ''){
        $where = self::setLevelQueryCondition($options);
        $sql = "SELECT count(1) FROM edmbuy.shp_users tu where parent_id3 = %d $where ";
        $count = D()->query($sql, $uid)->result();
        return $count;
    }
    
    /**
     * 第一层列表
     * @param unknown $uid
     * @param Pager $pager
     */
    static function findFirstLevelList($uid, PagerPull $pager, $options = ''){
        $where = self::setLevelQueryCondition($options);
        $column = self::outputLevelListQueryColumn();
        $sql = "SELECT $column FROM edmbuy.shp_users where `parent_id` = '%d' $where order by user_id desc limit %d,%d";
        $result = D()->query($sql, $uid, $pager->start, $pager->realpagesize)->fetch_array_all();
        $result = self::rebuildLevelResult($result);
        $pager->setResult($result);
        return $result;
    }
    
    /**
     * 第二层列表
     * @param unknown $uid
     * @param Pager $pager
     */
    static function findSecondLevelList($uid, PagerPull $pager, $options = ''){
        $where = self::setLevelQueryCondition($options);
        $column = self::outputLevelListQueryColumn();
        $sql = "SELECT $column FROM edmbuy.shp_users u where parent_id2 = '%d' $where 
                order by user_id desc limit %d,%d";
        $result = D()->query($sql, $uid, $pager->start, $pager->realpagesize)->fetch_array_all();
        $result = self::rebuildLevelResult($result);
        $pager->setResult($result);
        return $result;
    }
    
    /**
     * 第三层列表
     * @param unknown $uid
     * @param Pager $pager
     */
    static function findThirdLevelList($uid, PagerPull $pager, $options = ''){
        $where = self::setLevelQueryCondition($options);
        $column = self::outputLevelListQueryColumn();
        $sql = "SELECT $column FROM edmbuy.shp_users tu where parent_id3 = '%d' $where order by user_id desc limit %d,%d";
        $result = D()->query($sql, $uid, $pager->start, $pager->realpagesize)->fetch_array_all();
        $result = self::rebuildLevelResult($result);
        $pager->setResult($result);
        return $result;
    }
    
    private static function setLevelQueryCondition($options){
        $where = '';
        if($options){
            if($options == Partner::LEVEL_TYPE_AGENCY){
                $where = ' and level '.Fn::db_create_in(Users::getAgentArray());
            }
        }
        return $where;
    }
    
    /**
     * 米商列表展示需要的字段
     */
    static function outputLevelListQueryColumn(){
        $queryCols = " user_id as uid, logo, nick_name as nickname, mobile AS mobilephone, level, province, city, childnum_1 as childnum1,
            parent_nick as parentnick, wxqr, reg_time ";
        return $queryCols;
    }
    
    static function rebuildLevelResult($result){
        foreach ($result as &$rs){
            $rs['reg_time'] = date('Y-m-d H:i', $rs['reg_time']);
        }
        return $result;
    }
    
    /**
     * 显示用户下不同层级佣金明细 总数
     */
    static function findCommisionByLevelCount($uid, $level, $status){
        $column = self::outputCommisionCountQueryColumn();
        $sql = self::constructCommissionSql($column, FALSE);
        $count = D()->query($sql, $uid, $level, (1==$status ? '1,2' : $status))->fetch_array();
        return $count;
    }
    
    /**
     * 显示用户下不同层级佣金明细 列表
     */
    static function findCommisionByLevelList($uid, $level, $status, PagerPull $pager){
        $column = self::outputCommisionListQueryColumn();
        $sql = self::constructCommissionSql($column, TRUE);
        $rows = D()->query($sql, $uid, $level, (1==$status ? '1,2' : $status), $pager->start, $pager->realpagesize)->fetch_array_all();
        foreach ($rows AS &$r) {
        	$r['paytime'] = date("Y-m-d | H:i:s",simphp_gmtime2std($r['paytime']));
        	$r['paytime'] = str_replace('|', '<br>', $r['paytime']);
        }
        return $rows;
    }
    
    static function constructCommissionSql($column, $limit){
        $sql = "select $column from shp_user_commision c where c.user_id = %d and parent_level = %d and state IN(%s) ";
        if($limit == true){
            $sql .= " order by paid_time desc limit %d,%d";
        }
        return $sql;
    }
    
    
    /**
     * 一层用户佣金明细汇总
     */
    /* static function findFirstLevelCommisionCount($uid, $status){
        $column = self::outputCommisionCountQueryColumn();
        $sql = self::constructLevel1CommissionSql($column, FALSE);
        $count = D()->query($sql, $uid, $status)->fetch_array();
        return $count;
    } */
    
    /**
     * 一层用户佣金明细列表
     */
   /*  static function findFirstLevelCommisionList($uid, $status, PagerPull $pager){
        $column = self::outputCommisionListQueryColumn();
        $sql = self::constructLevel1CommissionSql($column, TRUE);
        $rows = D()->query($sql, $uid, $status, $pager->start, $pager->realpagesize)->fetch_array_all();
        return $rows;
    }
    
    static function constructLevel1CommissionSql($column, $limit){
        $sql = "select $column from shp_user_commision c where c.order_uid in (
            	   SELECT user_id FROM edmbuy.shp_users where parent_id = %d 
                ) and state = %d and parent_level = 1 ";
        if($limit == true){
            $sql .= " order by paid_time desc limit %d,%d";
        }
        return $sql;
    } */
    
    /**
     * 二层用户佣金明细汇总
     */
   /*  static function findSecondLevelCommisionCount($uid, $status){
        $column = self::outputCommisionCountQueryColumn();
        $sql = self::constructLevel2CommissionSql($column, false);
        $count = D()->query($sql, $uid, $status)->fetch_array();
        return $count;
    } */
    
    /**
     * 二层用户佣金明细列表
     */
    /* static function findSecondLevelCommisionList($uid, $status, PagerPull $pager){
        $column = self::outputCommisionListQueryColumn();
        $sql = self::constructLevel2CommissionSql($column, true);
        $rows = D()->query($sql, $uid, $status, $pager->start, $pager->realpagesize)->fetch_array_all();
        return $rows;
    }
    
    static function constructLevel2CommissionSql($column, $limit){
        $sql = "select $column from shp_user_commision c where c.order_uid in (
                    SELECT u.user_id FROM edmbuy.shp_users u where
                    u.parent_id in (SELECT user_id FROM edmbuy.shp_users where `parent_id` = %d)
                ) and state = %d and parent_level = 2 ";
        if($limit){
            $sql .= " order by paid_time desc limit %d,%d";
        }
        return $sql;
    } */
    
    /**
     * 三层用户佣金明细汇总
     */
   /*  static function findThirdLevelCommisionCount($uid, $status){
        $column = self::outputCommisionCountQueryColumn();
        $sql = self::constructLevel3CommissionSql($column, false);
        $count = D()->query($sql, $uid, $status)->fetch_array();
        return $count;
    } */
    
    /**
     * 三层用户佣金明细列表
     */
   /*  static function findThirdLevelCommisionList($uid, $status, PagerPull $pager){
        $column = self::outputCommisionListQueryColumn();
        $sql = self::constructLevel3CommissionSql($column, true);
        $rows = D()->query($sql, $uid, $status, $pager->start, $pager->realpagesize)->fetch_array_all();
        return $rows;
    }
    
    static function constructLevel3CommissionSql($column, $limit){
        $sql = "select $column from shp_user_commision c where c.order_uid in (
                    SELECT tu.user_id FROM edmbuy.shp_users tu where
                    tu.parent_id in (
                		SELECT user_id FROM edmbuy.shp_users su where
                		su.parent_id in (SELECT user_id FROM edmbuy.shp_users where `parent_id` = %d)
                    )  
                ) and state = %d and parent_level = 3 ";
        if($limit){
            $sql .= " order by paid_time desc limit %d,%d";
        }
        return $sql;
    } */
    
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