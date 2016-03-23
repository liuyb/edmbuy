<?php
/**
 * 商品Model
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Goods_Model extends Model {
    /**
     * 获取商家的分类列表
     */
    static function getCategoryList(){
            $merchant_id = $GLOBALS['user']->uid;
            if(empty($merchant_id)){
                return false;
            }
            $sql="select merchant_id ,cat_name,parent_id ,cat_url from ";
    }
}
 
/*----- END FILE: Goods_Model.php -----*/