<?php
/**
 * Storage Node Model
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class OrderItems extends StorageNode {
	
	protected static function meta() {
		return array(
				'table' => '`shp_order_goods`',
				'key'   => 'rec_id',
				'columns' => array(
						'rec_id'         => 'rec_id',
						'order_id'       => 'order_id',
						'goods_id'       => 'goods_id',
						'goods_name'     => 'goods_name',
						'goods_sn'       => 'goods_sn',
						'product_id'     => 'product_id',
						'goods_number'   => 'goods_number',
						'market_price'   => 'market_price',
						'goods_price'    => 'goods_price',
						'goods_attr'     => 'goods_attr',
						'send_number'    => 'send_number',
						'is_real'        => 'is_real',
						'extension_code' => 'extension_code',
						'parent_id'      => 'parent_id',
						'is_gift'        => 'is_gift',
						'goods_attr_id'  => 'goods_attr_id',
				)
		);
	}
	
}
 
/*----- END FILE: class.OrderItems.php -----*/