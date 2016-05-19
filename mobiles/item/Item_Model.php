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
    const CATEGORY_MOML = 'moml';
    const CATEGORY_BEST = 'best';
    
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
        self::CATEGORY_MOML => '63',
        self::CATEGORY_BEST => '65'
    );
    
    //专区对应的描述信息
    static $prefe_share_title = array(
        self::CATEGORY_98 => '【98专区】消费满98元 即可成为米商',
        self::CATEGORY_FRUIT => '【水果专区】纯天然新鲜水果 绿色 安全 无公害',
        self::CATEGORY_TEA_WINE => '【茶酒专区】茶中臻品 酒香四溢',
        self::CATEGORY_FOOD => '【食品专区】严把质量关 让你吃的安全放心',
        self::CATEGORY_CLOTHING => '【春季特卖】2016春季必备清单',
        self::CATEGORY_RICE => '【精选好米】精选优质好米 绿色 健康 养生',
        self::CATEGORY_MOML => '【清真专区】助力回商 买卖全球',
        self::CATEGORY_BEST => '【精选专区】多米精选特价好货'
    );
    
    //专区对应的图片
    static $prefe_share_pic = array(
        self::CATEGORY_98 => 'themes/mobiles/img/98_02.png',
        self::CATEGORY_FRUIT => 'themes/mobiles/img/fruit3.png',
        self::CATEGORY_TEA_WINE => 'themes/mobiles/img/tea_wine.png',
        self::CATEGORY_FOOD => 'themes/mobiles/img/food001.png',
        self::CATEGORY_CLOTHING => 'themes/mobiles/img/dress.png',
        self::CATEGORY_RICE => 'themes/mobiles/img/receal.png',
    	self::CATEGORY_MOML => 'themes/mobiles/img/zq/moml.png',
        self::CATEGORY_BEST => 'themes/mobiles/img/zq/index_pref_best.png',
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
        $result = Items::findGoodsListByCond($pager, ['shop_price' => $price]);
        $pager->setResult($result);
    }
    
    /**
     * 获取当前商家的其他商品
     */
    static function findShopRecommendGoodsList(PagerPull $pager, array $options){
        $options['shop_recommend'] = 1;
        $result = Items::findGoodsListByCond($pager, $options);
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
        }else if($type == Item_Model::CATEGORY_MOML){
            $mod = 'mod_item_pref_moml';
        }else if($type == Item_Model::CATEGORY_BEST){
            $mod = 'mod_item_pref_best';
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
        $c->user_logo = $GLOBALS['user'] -> logo;
        $c->email = $GLOBALS['user'] -> email;
        $c->add_time = simphp_time();
        $c->ip_address = get_clientip();
        $c->status = 1;
        $order_goods = OrderItems::getOrderGoodsInfo($c->order_id, $c->id_value);
        if(!empty($order_goods)){
            $c->obj_attr = $order_goods['goods_attr'];
        }
        $c->save(Storage::SAVE_INSERT);
        OrderItems::updateCommentState($c->order_id, $c->id_value);
        unset($c);
    }
    
    /**
     * 获取商品评论
     * @param Comment $c
     * @param PagerPull $pager
     */
    static function getGoodsComment(Comment $c, PagerPull $pager, $category){
        $result = Comment::getGoodsComment($c, $pager, $category);
        $pager->setResult($result);
    }
    
	static function escape_kefu_link($string) {
		return str_replace(['&','='], ['%26','%3D'], $string);
	}
	
	/**
	 * 解析成页面需要的商品属性格式
	 * [0] => Array
        (
            [cat_id] => 1
            [cat_name] => 重量
            [attrs] => Array
                (
                    [0] => Array
                        (
                            [attr_id] => 2
                            [attr_value] => 300g
                            [shop_price] => 9.00
                            [goods_number] => 6
                        )

                    [1] => Array
                        (
                            [attr_id] => 1
                            [attr_value] => 200g
                            [shop_price] => 6.00
                            [goods_number] => 6
                        )

                )

        )
	 * @param unknown $goods_id
	 */
	public static function get_goods_attrs($goods_id)
	{
	    $result = Items::attrs($goods_id);
	    $cat_arr = [];
	    //多个属性值 对应的 属性数据  attr1+attr2+attr3 => goods_attr_id/shop_price...
	    $attrmap = [];
	    foreach ($result as $item) {
	        self::map_goods_attrs($cat_arr, $item, 1);
	        self::map_goods_attrs($cat_arr, $item, 2);
	        self::map_goods_attrs($cat_arr, $item, 3);
	        self::setAttrKeyValue($item, $attrmap);
	    }
	    $ret_arr = [];
	    $count = 1;
	    if ($cat_arr && count($cat_arr) > 0) {
	        foreach ($cat_arr as $type => $attrs) {
	            $typeOBJ = explode('【~~】', $type);
	            if (count($typeOBJ) == 0) {
	                continue;
	            }
	            //处理每个type下的重复属性
	            $display_attrs = self::get_goods_select_attr($count, $attrs);
	            array_push($ret_arr, array('cat_id' => $typeOBJ[0], 'cat_name' => $typeOBJ[1], 'attrs' => $display_attrs));
	            $count++;
	        }
	    }
	    return [$ret_arr, $attrmap];
	}
	
	/**
	 * 用map来构造商品属性规格
	 * 商品type（颜色、重量）=> array(对应的属性列表)
	 * @param unknown $cat_arr
	 * @param unknown $item
	 * @param unknown $index
	 */
	private static function map_goods_attrs(&$cat_arr, $item, $index)
	{
	    $cat_id = $item['cat' . $index . '_id'];
	    $cat_name = $item['cat' . $index . '_name'];
	    if ($cat_id && $cat_name) {
	        $key = $cat_id . '【~~】' . $cat_name;
	        if (isset($cat_arr[$key]) && $cat_arr[$key]) {
	            array_push($cat_arr[$key], $item);
	        } else {
	            $cat_arr[$key] = [];
	            array_push($cat_arr[$key], $item);
	        }
	    }
	}
	
	/**
	* 当前商品 选中的属性 唯一过滤 把 attr1/attr2/attr3 分离出来
	* @param unknown $index
	* @param unknown $attrs
	*/
	public static function get_goods_select_attr($cat_id, $attrs)
	{
	    $ret = [];
	    $map = [];
	    foreach ($attrs as $at) {
	        $at_id = $at['attr' . $cat_id . '_id'];
	        $key = 'key_' . $at_id;
	        if (isset($map[$key]) && $map[$key] > 0) {
	            continue;
	        }
	        $map[$key] = '1';
	        array_push($ret, array("attr_id" => $at_id, "attr_value" => $at['attr' . $cat_id . '_value']));
	    }
	    unset($map);
	    return $ret;
	}
	
	/**
	 * 多个属性值 对应的 属性数据  attr1+attr2+attr3 => goods_attr_id/shop_price...
	 */
	public static function setAttrKeyValue($item, &$attrmap){
	    $key = 'k_';
	    for($index = 1; $index <= 3; $index++){
	        $cat_id = $item['cat' . $index . '_id'];
	        if($cat_id){
	            $key .= $item["attr".$index."_id"] . "_";
	        }
	    }
	    $attrmap[$key] = array("shop_price" => $item['shop_price'], "goods_number" => $item['goods_number'], "goods_attr_id" => $item['goods_attr_id']);
	}
}
 
/*----- END FILE: Item_Model.php -----*/