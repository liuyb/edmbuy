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
    static function showCurrentLevelList(&$level, Pager $pager){
        switch ($level){
            case Partner::Partner_LEVEL_1 : 
                $level = '一级米客';
                self::findFirstLevelList($pager);
            break; 
            case Partner::Partner_LEVEL_2 :
                $level = '二级米客';
                self::findSecondLevelList($pager);
            break;
            case Partner::Partner_LEVEL_3 :
                $level = '三级米客';
                self::findThirdLevelList($pager);
            break;
        }
    }
    
    static function findFirstLevelList(Pager $pager){
        $uid = $GLOBALS['user']->uid;
        
        $totalnum = Partner::findFirstLevelCount($uid);
        $result = Partner::findFirstLevelList($uid, $pager);
        $pager->setTotalNum($totalnum);
        $pager->__set("result", $result);
    }
    
    static function findSecondLevelList(Pager $pager){
        $uid = $GLOBALS['user']->uid;
    
        $totalnum = Partner::findSecondLevelCount($uid);
        $result = Partner::findSecondLevelList($uid, $pager);
        $pager->setTotalNum($totalnum);
        $pager->__set("result", $result);
    }
    
    static function findThirdLevelList(Pager $pager){
        $uid = $GLOBALS['user']->uid;
    
        $totalnum = Partner::findThirdLevelCount($uid);
        $result = Partner::findThirdLevelList($uid, $pager);
        $pager->setTotalNum($totalnum);
        $pager->__set("result", $result);
    }
    
    /**
     * 未生效收入
     */
    static function getInactiveIncome($uid){
        $sql = "select sum(order_amount) from shp_user_commision where user_id = %d";
        $amount = D()->query($sql, $uid)->result();
        return $amount;
    }
    
}
 
/*----- END FILE: Partner_Model.php -----*/