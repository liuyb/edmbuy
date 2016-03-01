<?php
/**
 * 默认Model 
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Default_Model extends Model {
  
    const CATEGORY_FOOD = 'food'; 
    const CATEGORY_DRINK = 'drink';
    const CATEGORY_WEAR = 'wear';
    
    private static $goods_category_mapping = array(
        self::CATEGORY_FOOD => '37,51',
        self::CATEGORY_DRINK => '36',
        self::CATEGORY_WEAR => '52'
        
    );
    
    /**
     * 获取商品列表
     * @param PagerPull $pager
     * @param $ispromote 是否推荐
     */
	static function findGoodsList(PagerPull $pager, $ispromote){
	    $result = Items::findGoodsList($pager, $ispromote);
	    $pager->setResult($result);
	}
	
	static function findGoodsListByCategory(PagerPull $pager, $category){
	    $categoryids = self::$goods_category_mapping[$category];
	    $result = Items::findGoodsListByCategory($pager, $categoryids);
	    $pager->setResult($result);
	}
	
	/**
	 * 获取后台添加的商品超值礼包 - 活动时间已开始未结束
	 */
	static function find_time_limit_activity(){
	    $sql = "select act_id, start_time, end_time from shp_goods_activity 
                where unix_timestamp(now()) between start_time and end_time and is_finished = 0 
                limit 1";
	    return D()->query($sql)->fetch_array();
	}
	
	/**
	 * 获取限时抢购商品列表
	 * @param unknown $package_id
	 */
	static function find_time_limit_goods_list($package_id){
	    $sql = "select g.goods_id,g.goods_name,g.goods_brief, g.shop_price,g.market_price,g.goods_img,g.goods_number 
	           from shp_goods g, shp_package_goods pg 
               where	g.goods_id = pg.goods_id 
               and 	g.is_on_sale = 1 and g.is_promote = 1 and g.is_delete = 0 
               and 	pg.package_id = %d";
	    $goods = D()->query($sql, $package_id)->fetch_array_all();
	    if (!empty($goods)) {
	        foreach ($goods AS &$g) {
	            $g['goods_img'] = Items::imgurl($g['goods_img']);
	        }
	    }
	    else {
	        $goods = [];
	    }
	    return $goods;
	}
    
}
 
/*----- END FILE: Default_Model.php -----*/