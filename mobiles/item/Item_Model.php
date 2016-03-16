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
        self::CATEGORY_RICE => '49'
    );
    
    //专区对应的描述信息
    static $prefe_share_title = array(
        self::CATEGORY_98 => '【98专区】消费满98元 即可成为米商',
        self::CATEGORY_FRUIT => '【水果专区】纯天然新鲜水果 绿色 安全 无公害',
        self::CATEGORY_TEA_WINE => '【茶酒专区】茶中臻品 酒香四溢',
        self::CATEGORY_FOOD => '【食品专区】严把质量关 让你吃的安全放心',
        self::CATEGORY_CLOTHING => '【春季特卖】2016春季必备清单',
        self::CATEGORY_RICE => '【精选好米】精选优质好米 绿色 健康 养生'
    );
    
    //专区对应的图片
    static $prefe_share_pic = array(
        self::CATEGORY_98 => 'themes/mobiles/img/98_02.png',
        self::CATEGORY_FRUIT => 'themes/mobiles/img/fruit3.png',
        self::CATEGORY_TEA_WINE => 'themes/mobiles/img/tea_wine.png',
        self::CATEGORY_FOOD => 'themes/mobiles/img/food001.png',
        self::CATEGORY_CLOTHING => 'themes/mobiles/img/dress.png',
        self::CATEGORY_RICE => 'themes/mobiles/img/receal.png'
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
     * 根据价格查询商品列表
     * @param PagerPull $pager
     * @param unknown $cat
     */
    static function findGoodsListByPrice(PagerPull $pager, $price){
        $result = Items::findGoodsListByPrice($pager, $price);
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
    
    /**
     * 用户发布评论
     * @param Comment $c
     */
    static function postGoodsComment(Comment $c){
        $c->user_id = $GLOBALS['user']->uid;
        $c->user_name = $GLOBALS['user'] -> nickname;
        $c->email = $GLOBALS['user'] -> email;
        $c->add_time = simphp_time();
        $c->ip_address = get_clientip();
        $c->status = 1;
        $c->save(Storage::SAVE_INSERT);
    }
    
    /**
     * 获取商品评论
     * @param Comment $c
     * @param PagerPull $pager
     */
    static function getGoodsComment(Comment $c, PagerPull $pager){
        $result = Comment::getGoodsComment($c, $pager);
        $pager->setResult($result);
    }
    
	static function escape_kefu_link($string) {
		return str_replace(['&','='], ['%26','%3D'], $string);
	}
	
}
 
/*----- END FILE: Item_Model.php -----*/