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
						'can_handsel'   => 'can_handsel',
						'goods_attr_id' => 'goods_attr_id',
				)
		);
	}
	
	/**
	 * Get user cart num
	 * @param integer|string $user_id
	 * @return mixed(integer or boolean)
	 */
	static function getUserCartNum($user_id) {
		if (!$user_id) return NULL;
    $where= '';
    if (strlen($user_id) > 10) { //$user_id is session id
      $where = "`session_id`='%s'";
    }
    else { //$user_id is user_id
      $where = "`user_id`=%d";
    }
		$num = D()->from(self::table())->where($where, $user_id)->select("SUM(`goods_number`) AS num")->result();
		return $num;
	}
	
}

/*----- END FILE: class.Cart.php -----*/