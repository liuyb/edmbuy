<?php
/**
 * Item共用Model
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Items extends StorageNode {
	
    //统一运费
    const SHIPPING_FEE = 1;
    
    //运费模板
    const SHIPPING_TEMPLATE = 2;
    
	protected static function meta() {
		return array(
				'table' => '`shp_goods`',
				'key'   => 'item_id',   //该key是应用逻辑的列，当columns为array时，为columns的key，否则，则要设成实际存储字段
				'columns' => array( //命名特点：'goods_%s'=>'item_%s'，其他不变
					'item_id'     => 'goods_id',
					'merchant_uid'=> 'merchant_uid',//兼容老数据 INT 格式
				    'merchant_id' => 'merchant_id',//新增 CHAR格式的 ID
					'cat_id'      => 'cat_id',
				    'shop_cat_id'=> 'shop_cat_id',
					'origin_place_id' => 'origin_place_id',
					'item_sn'     => 'goods_sn',
					'item_name'   => 'goods_name',
					'item_name_style' => 'goods_name_style',
					'click_count'     => 'click_count',
					'collect_count'   => 'collect_count',
					'paid_order_count'=> 'paid_order_count',
					'paid_goods_number'=> 'paid_goods_number',
					'brand_id'        => 'brand_id',
					'provider_name'   => 'provider_name',
					'item_number'     => 'goods_number',
					'item_weight'     => 'goods_weight',
					'market_price'    => 'market_price',
					'shop_price'      => 'shop_price',
					'income_price'    => 'income_price',
					'commision'       => 'commision',
					'promote_price'   => 'promote_price',
				    'cost_price'      => 'cost_price',
					'promote_start_date' => 'promote_start_date',
					'promote_end_date'   => 'promote_end_date',
					'warn_number'     => 'warn_number',
					'booking_days'    => 'booking_days',
					'expiry_date'     => 'expiry_date',
					'keywords'        => 'keywords',
					'item_brief'      => 'goods_brief',
					'item_desc'       => 'goods_desc',
					'item_thumb'      => 'goods_thumb',
					'item_img'        => 'goods_img',
					'original_img'    => 'original_img',
					'is_real'         => 'is_real',
					'extension_code'  => 'extension_code',
					'is_on_sale'      => 'is_on_sale',
					'is_alone_sale'   => 'is_alone_sale',
					'is_shipping'     => 'is_shipping',
					'integral'        => 'integral',
					'add_time'        => 'add_time',
					'sort_order'      => 'sort_order',
					'is_delete'       => 'is_delete',
					'is_best'         => 'is_best',
					'is_new'          => 'is_new',
					'is_hot'          => 'is_hot',
					'is_promote'      => 'is_promote',
					'bonus_type_id'   => 'bonus_type_id',
					'last_update'     => 'last_update',
					'item_type'       => 'goods_type',
					'seller_note'     => 'seller_note',
					'give_integral'   => 'give_integral',
					'rank_integral'   => 'rank_integral',
					'suppliers_id'    => 'suppliers_id',
					'is_check'        => 'is_check',
				    'per_limit_buy'   => 'per_limit_buy',
				    'shipping_fee'    => 'shipping_fee',
				    'shipping_template' => 'shipping_template',
				    'fee_or_template' => 'fee_or_template',
				    'shop_recommend'  => 'shop_recommend',
				    'goods_flag' => 'goods_flag' //标记商品是否类型 默认0位普通商品，其他分 店铺 跟 代理等
				)
		);
	}
	
	protected static $siteurl;
	
	/**
	 * Constructor
	 * @param int|string $id
	 */
	public function __construct($id = NULL) {
		parent::__construct($id);
		self::$siteurl = C('env.site.mobile');
	}
	
	/**
	 * add click count
	 */
	public function add_click_count($inc = 1) {
		D()->raw_query("UPDATE ".$this->table()." SET `click_count`=`click_count`+%d WHERE `goods_id`=%d", $inc, $this->id);
		return D()->affected_rows();
	}
	
	/**
	 * Return current item url, alias of self::itemurl
	 * @param string $spm
	 * @return string
	 * @see Items::itemurl
	 */
	public function url($spm = '') {
		return self::itemurl($this->id, $spm);
	}
	
	/**
	 * Return item url
	 * @param integer $item_id
	 * @param string $spm
	 * @return string
	 */
	static function itemurl($item_id, $spm = '') {
		if (!isset(self::$siteurl)) {
			self::$siteurl = C('env.site.mobile');
		}
		return self::$siteurl . "/item/".$item_id.($spm ? "?spm={$spm}" : '');
	}
	
	/**
	 * full the img path
	 * @param string $img_path
	 * @return string
	 */
	static function imgurl($img_path) {
		static $urlpre;
		if (!isset($urlpre)) $urlpre = C('env.site.shop');
		$img_path = Media::path($img_path);
		return preg_match('/^http(s?):\/\//i', $img_path) ? $img_path : ($urlpre.$img_path);
	}
	
	/**
	 * get item attributes
	 * @param integer $item_id
	 * @return array
	 */
	static function attrs($item_id) {
		$sql = "SELECT * from shp_goods_attr where goods_id = %d";
		$rows = D()->query($sql, $item_id)->fetch_array_all();
		return $rows;
	}
	
	/**
	 * 获得指定的商品属性信息
	 * 
	 * @param array       $arr        规格、属性ID数组
	 * @param type        $type       设置返回结果类型：pice，显示价格，默认；no，不显示价格
	 * @return string
	 */
	static function attrs_info($arr, $type = 'pice') {
	    if(!$arr){
	        return '';
	    }
		$sql = "select * from shp_goods_attr where goods_attr_id = %d";
		$attr= D()->query($sql, $arr)->get_one();
		$attr = Order::genGoodsAttrTxt($attr);
		return $attr;
	}
	
	/**
	 * 改变商品表库存
	 *
	 * @param integer $item_id
	 * @param integer $chnum, 大于0时增加库存，小于0时减少库存
	 * @return boolean
	 */
	static function changeStock($item_id, $chnum = 1, $goods_attr_id = 0) {
	    //规格存在时 改变规格里面的库存
	    if($goods_attr_id){
	        D()->raw_query("UPDATE shp_goods_attr SET `goods_number`=`goods_number`+%d WHERE `goods_attr_id`=%d", $chnum, $goods_attr_id);
	        if (D()->affected_rows()) {
	            return true;
	        }
	    }else{
    		$ectb_goods = self::table();
    		$chnum = intval($chnum);
    		D()->raw_query("UPDATE {$ectb_goods} SET `goods_number`=`goods_number`+%d WHERE `goods_id`=%d", $chnum, $item_id);
    		if (D()->affected_rows()) {
    			return true;
    		}
	    }
		return false;
	}
	
	/**
	 * 更新订单下所有商品的"订单数"
	 *
	 * @param integer $order_id
	 * @return boolean
	 */
	static function updateOrderCntByOrderid($order_id) {
		$order_id = intval($order_id);
		if (empty($order_id)) return false;
	
		$ectb_goods       = Items::table();
		$ectb_order_goods = OrderItems::table();
		$ectb_pay_log     = PayLog::table();
		$sql =<<<HERESQL
UPDATE {$ectb_goods} g, (
				SELECT og.goods_id,COUNT(l.order_id) AS order_num
				FROM {$ectb_order_goods} og INNER JOIN {$ectb_pay_log} l ON og.order_id=l.order_id AND l.is_paid=1
				WHERE og.goods_id IN(SELECT goods_id FROM {$ectb_order_goods} WHERE order_id={$order_id})
				GROUP BY og.goods_id
			) pgon
SET g.paid_order_count = pgon.order_num
WHERE g.goods_id = pgon.goods_id
HERESQL;
	
		D()->raw_query($sql);
		if (D()->affected_rows() > 0) {
			return true;
		}
		return false;
	}
	
	/**
	 * 更新订单下所有商品真正付费卖出的"单品数"
	 *
	 * @param integer $order_id
	 * @return boolean
	 */
	static function updatePaidNumByOrderid($order_id) {
		$order_id = intval($order_id);
		if (empty($order_id)) return false;
	
		$ectb_goods       = Items::table();
		$ectb_order_goods = OrderItems::table();
		$ectb_pay_log     = PayLog::table();
		$sql =<<<HERESQL
UPDATE {$ectb_goods} g, (
				SELECT og.goods_id,SUM(og.goods_number) AS paid_goods_num
				FROM {$ectb_order_goods} og INNER JOIN {$ectb_pay_log} l ON og.order_id=l.order_id AND l.is_paid=1
				WHERE og.goods_id IN(SELECT goods_id FROM {$ectb_order_goods} WHERE order_id={$order_id})
				GROUP BY og.goods_id
			) pgon
SET g.paid_goods_number = pgon.paid_goods_num
WHERE g.goods_id = pgon.goods_id
HERESQL;
	
		D()->raw_query($sql);
		if (D()->affected_rows() > 0) {
			return true;
		}
		return false;
	}
	
	/**
	 * 获取在售的商品列表
	 * @param PagerPull $pager
	 */
	static function findGoodsList(PagerPull $pager, $ispromote){
	    $where = "";
	    if($ispromote){
	        $where .= " and is_promote = 1 ";
	    }
	    $sql = "select goods_id,goods_name,shop_price,market_price,
	               goods_thumb,goods_img from shp_goods where is_on_sale = 1 and is_delete = 0 and goods_flag = 0 $where order by sort_order limit %d,%d";
	    $goods = D()->query($sql, $pager->start, $pager->realpagesize)->fetch_array_all();
	    return self::buildGoodsImg($goods);
	}
	
	/**
	 * 根据枚举出来的商品分类查询商品列表
	 * @param PagerPull $pager
	 * @param unknown $categoryids
	 */
	static function findGoodsListByCategory(PagerPull $pager, $categoryids){
	    $sql = "select g.goods_id,g.goods_name,g.goods_brief, g.shop_price,g.market_price,g.goods_img from shp_goods g, edmbuy.shp_category c 
	    where g.cat_id = c.cat_id 
	    and (c.cat_id in (%s) or c.parent_id in (%s)) 
	    and g.is_on_sale = 1 and g.is_promote = 1 and g.is_delete = 0 and g.goods_flag = 0 
	    order by g.sort_order desc, g.paid_order_count desc limit %d,%d";
	    $goods = D()->query($sql, $categoryids, $categoryids, $pager->start, $pager->realpagesize)->fetch_array_all();
	    return self::buildGoodsImg($goods);
	}
	
	/**
	 * 专区商品列表
	 * @param PagerPull $pager
	 * @param unknown $cat
	 */
	static function findGoodsListByPref(PagerPull $pager, $cat){
	    $sql = "select distinct g.goods_id,g.goods_name,g.goods_brief, g.shop_price,g.market_price,g.goods_img 
                from shp_goods g left join shp_goods_cat pg on	g.goods_id = pg.goods_id 
                where	g.is_on_sale = 1 and g.is_delete = 0 and g.goods_flag = 0
                and 	(g.cat_id = %d or pg.cat_id = %d)
                order by g.sort_order desc, g.paid_order_count desc limit %d,%d";
	    $goods = D()->query($sql, $cat, $cat, $pager->start, $pager->realpagesize)->fetch_array_all();
	    return self::buildGoodsImg($goods);
	}
	
	/**
	 * 根据不同条件获取商品列表
	 * @param PagerPull $pager
	 * @param unknown $cat
	 */
	static function findGoodsListByCond(PagerPull $pager, array $options){
	    $where = '';
	    if(isset($options['shop_price']) && $options['shop_price']){
	        //98专区 临时增加过滤处理方式 为 同时满足98 跟 在后头批发管理里面出现的商品 
	        $where .= " and g.shop_price = ".doubleval($options['shop_price'])." and goods_id in (select goods_id from shp_wholesale) ";
	    }
	    if(isset($options['merchant_id']) && $options['merchant_id']){
	        $where .= " and g.merchant_id = '".D()->escape_string($options['merchant_id'])."' ";
	    }
	    if(isset($options['shop_recommend']) && $options['shop_recommend']){
	        $where .= " and g.shop_recommend = ".intval($options['shop_recommend'])." ";
	    }
	    if(isset($options['shop_cat_id']) && $options['shop_cat_id']){
	        $where .= " and g.shop_cat_id = ".intval($options['shop_cat_id'])." ";
	    }
	    $sql = "select g.goods_id,g.goods_name,g.goods_brief, g.shop_price,g.market_price,g.goods_img
                from shp_goods g 
                where	g.is_on_sale = 1 and g.is_delete = 0 and g.goods_flag = 0 
                $where 
                order by g.sort_order desc, g.add_time desc limit %d,%d";
	    $goods = D()->query($sql, $pager->start, $pager->realpagesize)->fetch_array_all();
	    $goods = self::buildGoodsImg($goods);
	    $pager->setResult($goods);
	    return $goods;
	}
	
	/**
	 * 对商品图片做处理
	 * @param unknown $goods
	 */
	static function buildGoodsImg($goods){
	    if (!empty($goods)) {
	        foreach ($goods AS &$g) {
	            $g['goods_img'] = self::imgurl($g['goods_img']);
	            if (isset($g['goods_name'])) {
	            	$g['goods_name'] = str_replace(["\r","\n"], [""," "], $g['goods_name']);
	            }
	            if (isset($g['goods_brief'])) {
	            	$g['goods_brief'] = str_replace(["\r","\n"], [""," "], $g['goods_brief']);
	            }
	        }
	    }
	    else {
	        $goods = [];
	    }
	    return $goods;
	}
	
	/**
	 * 获取商品的邮费
	 * @param unknown $item
	 */
	static function getGoodsRealShipFee(&$item){
	    $template_fee = 0;
	    if(Items::SHIPPING_TEMPLATE == $item->fee_or_template){
	        $template_fee = D()->from('`shp_shipment_tpl`')->where("tpl_id = %d", $item->shipping_template)->select()->get_one();
	        if(!$template_fee || empty($template_fee)){
	            $template_fee = 0;
	        }else{
	            $template_fee = $template_fee['n_fee'];//temp
	        }
	    }else{
	        $template_fee = $item->shipping_fee;
	    }
	    $item->shipFee = $template_fee;
	    return $template_fee;
	}

	/**
	 * 用户 点击、取消 商品收藏
	 * @param unknown $user_id
	 * @param unknown $goods_id
	 * @param unknown $action
	 */
	static function changeGoodsCollect($user_id, $goods_id, $action){
	     if($action > 0){
	         $count = D()->query('select count(1) from shp_collect_goods where user_id=%d and goods_id = %d',$user_id,$goods_id)->result();
	         if($count){
	             return;
	         }
	         $sql = "insert ignore into shp_collect_goods(user_id, goods_id, add_time) values(%d, %d, %d)";
	         D()->query($sql, $user_id, $goods_id, time());
	     }else if($action < 0){
	         $sql = "delete from shp_collect_goods where user_id = %d and goods_id = %d";
	         D()->query($sql, $user_id, $goods_id);
	     }
	}
	
	/**
	 * 商品收藏数量
	 * @param unknown $goods_id
	 */
	static function getGoodsCollects($goods_id, $user_id = 0){
	    $where = '';
	    if($user_id){
	        $where .= " and user_id = $user_id ";
	    }
	    $sql = "select count(goods_id) from shp_collect_goods where goods_id = %d $where ";
	    $result = D()->query($sql, $goods_id)->result();
	    return $result;
	}
	
	/**
	 * 当前商品属性数据，规格存在时以规格为准
	 * @param unknown $goods_number
	 * @param unknown $spec_id
	 */
	static function getRealGoodsInfo($spec_id){
	    if(!$spec_id || $spec_id < 0){
	        return null;
	    }
	    $sql = "select market_price,shop_price,income_price,cost_price,goods_number from shp_goods_attr where goods_attr_id = %d";
		$attr = D()->query($sql, $spec_id)->get_one();
		if(!$attr || empty($attr)){
		    return null;
		}
		return $attr;
	}
	
	/**
	 * 如果当前商品时限购商品，根据当前用户购买过当前商品的数量，判断当前用户是否限购
	 * @param unknown $goods_id
	 */
	static function getgGodsWasBuy($goods_id){
	    $uid = $GLOBALS['user']->uid;
	    $sql = "select count(1) from shp_order_info oi join shp_order_goods og on oi.order_id = og.order_id 
                where og.goods_id = $goods_id and oi.user_id = %d and oi.pay_status=".PS_PAYED."";
        $result = D()->query($sql, $uid)->result(); 
        return $result;
	}
}
 
/*----- END FILE: class.Items.php -----*/