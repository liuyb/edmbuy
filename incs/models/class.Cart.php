<?php
/**
 * Cart Node Model
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Cart extends StorageNode {
	
	protected static function meta() {
		return array(
				'table' => '`shp_cart`',
				'key'   => 'rec_id',
				'columns' => array(
						'rec_id'        => 'rec_id',
						'user_id'       => 'user_id',
						'session_id'    => 'session_id',
						'goods_id'      => 'goods_id',
						'goods_sn'      => 'goods_sn',
						'product_id'    => 'product_id',
						'goods_name'    => 'goods_name',
						'market_price'  => 'market_price',
						'goods_price'   => 'goods_price',
						'goods_number'  => 'goods_number',
						'goods_thumb'   => 'goods_thumb',
						'goods_img'     => 'goods_img',
						'goods_attr'    => '	goods_attr',
						'is_real'       => '	is_real',
						'extension_code'=> '	extension_code',
						'parent_id'     => '	parent_id',
						'rec_type'      => '	rec_type',
						'is_gift'       => 'is_gift',
						'is_shipping'   => 'is_shipping',
						'is_immediate'  => 'is_immediate',
						'can_handsel'   => 'can_handsel',
						'goods_attr_id' => 'goods_attr_id',
				)
		);
	}
	
	/**
	 * Get cart owner sql
	 * @param integer|string $shop_uid user_id or session_id
	 * @return string
	 */
	protected static function getOwnerSql($shop_uid) {
		if (strlen($shop_uid) > 10) { //$shop_uid is session id
			$where = "`session_id`='%s'";
		}
		else { //$shop_uid is user_id
			$where = "`user_id`=%d";
		}
		return $where;
	}
	
	/**
	 * Get cart owner field
	 * @param integer|string $shop_uid user_id or session_id
	 * @return string
	 */
	protected static function getOwnerField($shop_uid) {
		return strlen($shop_uid) > 10 ? 'session_id' : 'user_id';
	}
	
	/**
	 * 获取购物用户id(支持session购物)
	 * @return string
	 */
	static function shopping_uid() {
		return $GLOBALS['user']->uid ? : session_id();
	}
	
	/**
	 * Get user cart num
	 * @param integer|string $shop_uid          user_id or session_id
	 * @param integer        $target_item_id    
	 * @return mixed(integer or boolean)
	 */
	static function getUserCartNum($shop_uid, $target_item_id = NULL) {
		if (!$shop_uid) return NULL;
    $where= self::getOwnerSql($shop_uid);
    if ($target_item_id) {
    	$where .= " AND `goods_id`=%d";
    }
		$num = D()->from(self::table())->where($where, $shop_uid, $target_item_id)->select("SUM(`goods_number`) AS num")->result();
		return $num;
	}
	
	/**
	 * Get user cart
	 * @param integer|string $shop_uid user_id or session_id
	 * @return array Cart node list
	 */
	static function getUserCart($shop_uid = NULL) {
		if (is_null($shop_uid)) $shop_uid = $GLOBALS['user']->uid;
		$list = self::find(new Query(self::getOwnerField($shop_uid), $shop_uid));
		if (!empty($list)) {
			foreach ($list AS &$g) {
				$g->goods_url   = Items::itemurl($g->goods_id);
				$g->goods_thumb = Items::imgurl($g->goods_thumb); //self::goods_picurl($g['goods_thumb']);
				$g->goods_img   = Items::imgurl($g->goods_img);   //self::goods_picurl($g['goods_img']);
			}
		}
		return $list;
	}
	
	/**
	 * 
	 * @param string $shopping_uid
	 * @param string $item_id
	 * @return mixed
	 */
	static function checkCartGoodsExist($shopping_uid, $item_id) {
		$where  = self::getOwnerSql($shopping_uid);
		if ($item_id) {
			$where .= " AND `goods_id`=%d";
		}
		$rec_id = D()->from(self::table())->where($where, $shopping_uid, $item_id)->select("rec_id")->result();
		return $rec_id;
	}
	

	/**
	 * 改变购物车中商品的购买数量
	 *
	 * @param integer $shopping_uid
	 * @param integer $item_id
	 * @param integer $inc
	 * @param boolean $is_cart_rec_id , when $is_cart_rec_id is true,  $item_id indicating the cart record id
	 * @param boolean $is_fixed_value , when $is_fixed_value is true, $inc is a fixed value, not an increment
	 * @return integer effected rows
	 */
	static function changeCartGoodsNum($shopping_uid, $item_id, $inc = 1, $is_cart_rec_id = false, $is_fixed_value = false) {
		$ectb = self::table();
		if ($is_cart_rec_id) {
			$where = "`rec_id`=%d";
		}
		else {
			$where = "`goods_id`=%d AND ".self::getOwnerSql($shopping_uid);
		}
	
		$inc = intval($inc);
		$setpart = "`goods_number`=`goods_number`+{$inc}";
		if ($is_fixed_value) {
			$setpart = "`goods_number`={$inc}";
		}
		$sql  = "UPDATE {$ectb} SET {$setpart} WHERE {$where}";
		D()->raw_query($sql, $item_id, $shopping_uid);
		return D()->affected_rows();
	}
	
	/**
	 * 添加商品到购物车
	 *
	 * @param $item_id      integer
	 * @param $num          integer
	 * @param $is_immediate integer
	 * @param $user_id      integer
	 * @return array
	 *   ['code' => >0, 'msg' => '添加成功']        //这时ret['code']即rec_id
	 *   ['code' => -1, 'msg' => '对应商品不存在']
	 *   ['code' => -2, 'msg' => '商品库存不足']
	 *   ['code' =>-10, 'msg' => '添加失败']
	 */
	static function addItem($item_id, $num = 1, $is_immediate = false, $user_id = NULL) {
		if (!$user_id) $user_id = $GLOBALS['user']->uid;
	
		$ret = ['code' => 0, 'msg' => '添加成功'];
		$exItem = Items::load($item_id);
		if ($exItem->is_exist()) {
			$sess_id = session_id();
			$shopping_uid = $user_id ? : $sess_id;
			$num = $num > 0 ? intval($num) : 1;
			if ($num > $exItem->item_number || self::getUserCartNum($shopping_uid,$item_id)>=$exItem->item_number) {
				$ret = ['code' => -2, 'msg' => '商品库存不足'];
				return $ret;
			}
			$cart_rec_id = self::checkCartGoodsExist($shopping_uid, $item_id);
			if ($cart_rec_id) { //商品已经在购物车中存在，则直接将购买数+1
				if (self::changeCartGoodsNum($shopping_uid, $cart_rec_id, $num, true, false)) {
					$ret['code'] = $cart_rec_id;
					$ret['added_num'] = $num;
				}
				else {
					$ret = ['code' => -10, 'msg' => '添加失败'];
				}
				return $ret;
			}
			else { //商品没在购物车中，需新加入
				$cart = new Cart();
				$cart->user_id     = $user_id;
				$cart->session_id  = $sess_id;
				$cart->goods_id    = $item_id;
				$cart->goods_sn    = $exItem->item_sn;
				$cart->product_id  = 0;
				$cart->goods_name  = $exItem->item_name;
				$cart->market_price= $exItem->market_price;
				$cart->goods_price = $exItem->shop_price;
				$cart->goods_number= $num;
				$cart->goods_thumb = $exItem->item_thumb;
				$cart->goods_img   = $exItem->item_img;
				$cart->goods_attr  = '';
				$cart->is_real     = $exItem->is_real;
				$cart->extension_code = $exItem->extension_code;
				$cart->parent_id   = 0;
				$cart->rec_type    = 0;
				$cart->is_gift     = 0;
				$cart->is_shipping = $exItem->is_shipping;
				$cart->is_immediate = $is_immediate ? 1 : 0;
				$cart->can_handsel = 0;
				$cart->goods_attr_id = '';
				$cart->save(Storage::SAVE_INSERT);
				
				if ($cart->id) {
					$ret['code'] = $cart->id;
					$ret['added_num'] = $num;
				}
				else {
					$ret = ['code' => -10, 'msg' => '添加失败'];
				}
				return $ret;
			}
		}
		else {
			$ret = ['code' => -1, 'msg' => '对应商品不存在'];
		}
		return $ret;
	}
	
	static function getGoods($cart_rec_ids, $shopping_uid = NULL, &$total_price = NULL) {
		if (!is_array($cart_rec_ids)) {
			$cart_rec_ids = [$cart_rec_ids];
		}
		if (empty($cart_rec_ids)) {
			return [];
		}
	
		if (!isset($shopping_uid)) {
			$shopping_uid = self::shopping_uid();
		}
	
		$ectb = self::table();
		$where_user = self::getOwnerSql($shopping_uid);
		$where_ids  = "'".implode("','", $cart_rec_ids)."'";
		$sql = "SELECT * FROM {$ectb} WHERE `rec_id` IN({$where_ids}) AND {$where_user}";
		$ret = D()->raw_query($sql,$shopping_uid)->fetch_array_all();
		if (!empty($ret)) {
			$total_price = 0;
			foreach ($ret As &$g) {
				$g['goods_url']   = Items::itemurl($g['goods_id']);
				$g['goods_thumb'] = Items::imgurl($g['goods_thumb']);
				$g['goods_img']   = Items::imgurl($g['goods_img']);
				$total_price     += $g['goods_price']*$g['goods_number'];
			}
		}
		return empty($ret) ? [] : $ret;
	}
	
}

/*----- END FILE: class.Cart.php -----*/