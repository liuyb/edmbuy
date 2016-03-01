<?php
/**
 * Item model
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Item_Model extends Model {
	
    const CATEGORY_98 = '98';
    const CATEGORY_TEA_WINE = 'teawine';
    const CATEGORY_TEA = 'tea';
    const CATEGORY_WINE = 'wine';
    const CATEGORY_FRUIT = 'fruit';
    const CATEGORY_FOOD = 'food';
    const CATEGORY_IMPORT_FOOD = 'importfood';
    const CATEGORY_LOCAL_FOOD = 'localfood';
    const CATEGORY_SNACK_FOOD = 'snackfood';
    const CATEGORY_CLOTHING = 'clothing';
    const CATEGORY_SHOE = 'shoe';
    const CATEGORY_BAG = 'bag';
    const CATEGORY_RICE = 'rice';
    
    //专区对应的分类ID
    private static $goods_category_mapping = array(
        self::CATEGORY_98 => '57',
        self::CATEGORY_TEA => '38',
        self::CATEGORY_WINE => '40',
        self::CATEGORY_FRUIT => '51',
        self::CATEGORY_IMPORT_FOOD => '42',
        self::CATEGORY_LOCAL_FOOD => '43',
        self::CATEGORY_SNACK_FOOD => '46',
        self::CATEGORY_CLOTHING => '53',
        self::CATEGORY_SHOE => '54',
        self::CATEGORY_BAG => '55',
        self::CATEGORY_RICE => '49',
    );
    
    /**
     * 专区商品列表
     * @param PagerPull $pager
     * @param unknown $cat
     */
    static function findGoodsListByPref(PagerPull $pager, $cat){
        $cat = self::$goods_category_mapping[$cat];
        $result = Items::findGoodsListByPref($pager, $cat);
        $pager->setResult($result);
    }
    
    /**
     * 创建专区页面
     * @param unknown $type
     * @return string
     */
    static function createModByCategory($type){
        $mod = '';
        if($type == Item_Model::CATEGORY_98){
            $mod = 'mod_item_pref_98';
        }else if($type == Item_Model::CATEGORY_FRUIT){
            $mod = 'mod_item_pref_fruit';
        }else if($type == Item_Model::CATEGORY_TEA_WINE){
            $mod = 'mod_item_pref_teawine';
        }else if($type == Item_Model::CATEGORY_FOOD){
            $mod = 'mod_item_pref_food';
        }else if($type == Item_Model::CATEGORY_CLOTHING){
            $mod = 'mod_item_pref_clothing';
        }else if($type == Item_Model::CATEGORY_RICE){
            $mod = 'mod_item_pref_rice';
        }
        return $mod;
    }
    
	static function escape_kefu_link($string) {
		return str_replace(['&','='], ['%26','%3D'], $string);
	}
	
}
 
/*----- END FILE: Item_Model.php -----*/