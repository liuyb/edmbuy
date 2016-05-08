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
						'income_price'   => 'income_price',
						'goods_attr'     => 'goods_attr',
						'send_number'    => 'send_number',
						'is_real'        => 'is_real',
						'extension_code' => 'extension_code',
						'parent_id'      => 'parent_id',
						'is_gift'        => 'is_gift',
						'goods_attr_id'  => 'goods_attr_id',
				        'has_comment'    => 'has_comment',
				        'shipping_fee'   => 'shipping_fee'
				)
		);
	}
	
	static function getOrderGoodsInfo($order_id, $goods_id){
	    $ectb_order_goods = OrderItems::table();
	    $sql = "SELECT og.* FROM {$ectb_order_goods} og WHERE og.`order_id`=%d and og.`goods_id`=%d ";
	    return D()->query($sql, $order_id, $goods_id)->get_one();
	}
	
	//修改评论状态为已评论
	static function updateCommentState($order_id, $goods_id){
	    $sql = <<<HERESQL
	       update shp_order_goods set has_comment = 1 where order_id = %d and goods_id = %d ;
HERESQL;
	    D()->raw_query($sql,$order_id, $goods_id);
		if (D()->affected_rows() > 0) {
			return true;
		}
		return false;
	}
}
 
/*----- END FILE: class.OrderItems.php -----*/