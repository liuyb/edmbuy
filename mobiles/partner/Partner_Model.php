<?php
/**
 * Partner Model
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Partner_Model extends Model {

    /**
     * 展现当前层级米商列表
     * @param unknown $level
     */
    static function TransLevelCN($level){
        $ret = "";
        switch ($level){
            case Partner::Partner_LEVEL_1 :
                $ret = '一级人脉';
                break;
            case Partner::Partner_LEVEL_2 :
                $ret = '二级人脉';
                break;
            case Partner::Partner_LEVEL_3 :
                $ret = '三级人脉';
                break;
        }
        return $ret;
    }
    
    /**
     * 展现当前层级米商列表
     * @param unknown $level
     */
    static function showCurrentLevelList($level, PagerPull $pager){
        switch ($level){
            case Partner::Partner_LEVEL_1 : 
                self::findFirstLevelList($pager);
            break; 
            case Partner::Partner_LEVEL_2 :
                self::findSecondLevelList($pager);
            break;
            case Partner::Partner_LEVEL_3 :
                self::findThirdLevelList($pager);
            break;
        }
    }
    
    /**
     * 第一层米商列表
     * @param Pager $pager
     */
    static function findFirstLevelList(PagerPull $pager){
        $uid = $GLOBALS['user']->uid;
        
        $result = Partner::findFirstLevelList($uid, $pager);
        $pager->setResult($result);
    }
    
    /**
     * 第二层米商列表
     * @param Pager $pager
     */
    static function findSecondLevelList(PagerPull $pager){
        $uid = $GLOBALS['user']->uid;
    
        $result = Partner::findSecondLevelList($uid, $pager);
        $pager->setResult($result);
    }
    
    /**
     * 第三层米商列表
     * @param Pager $pager
     */
    static function findThirdLevelList(PagerPull $pager){
        $uid = $GLOBALS['user']->uid;
    
        $result = Partner::findThirdLevelList($uid, $pager);
        $pager->setResult($result);
    }
    
    /**
     * 查询不同状态佣金收入
     * 佣金收入 => 状态
     */
    static function getCommisionIncome($uid){
        $sql = "select ifnull(sum(commision),0) as commision, state from shp_user_commision where user_id = %d group by state";
        $row = D()->query($sql, $uid)->fetch_array_all();
        return $row;
    }
    
    static function showCurLevelCommistionList($level, $status, Pager $pager){
        switch ($level){
            case Partner::Partner_LEVEL_1 :
                self::findFirstLevelCommisionList($status, $pager);
                break;
            case Partner::Partner_LEVEL_2 :
                self::findSecondLevelCommisionList($status, $pager);
                break;
            case Partner::Partner_LEVEL_3 :
                self::findThirdLevelCommisionList($status, $pager);
                break;
        }
    }
    
    /**
     * 第一层佣金明细
     * @param unknown $status
     * @param Pager $pager
     */
    static function findFirstLevelCommisionList($status, PagerPull $pager){
        $uid = $GLOBALS['user']->uid;
        if(isset($pager->needtotal) && $pager->needtotal == 1){
            $countArray = Partner::findFirstLevelCommisionCount($uid, $status);
            $pager->otherMap = $countArray;
        }
        $result = Partner::findFirstLevelCommisionList($uid, $status, $pager);
        $pager->setResult($result);
    }
    
    /**
     * 第二层佣金明细
     * @param unknown $status
     * @param Pager $pager
     */
    static function findSecondLevelCommisionList($status, PagerPull $pager){
        $uid = $GLOBALS['user']->uid;
        if(isset($pager->needtotal) && $pager->needtotal == 1){
            $countArray = Partner::findSecondLevelCommisionCount($uid, $status);
            $pager->otherMap = $countArray;
            
        }
        $result = Partner::findSecondLevelCommisionList($uid, $status, $pager);
        $pager->setResult($result);
    }
    
    /**
     * 第三层佣金明细
     * @param unknown $status
     * @param Pager $pager
     */
    static function findThirdLevelCommisionList($status, PagerPull $pager){
        $uid = $GLOBALS['user']->uid;
        if(isset($pager->needtotal) && $pager->needtotal == 1){
            $countArray = Partner::findThirdLevelCommisionCount($uid, $status);
            $pager->otherMap = $countArray;
        }
        $result = Partner::findThirdLevelCommisionList($uid, $status, $pager);
        $pager->setResult($result);
    }
    
}
 
/*----- END FILE: Partner_Model.php -----*/