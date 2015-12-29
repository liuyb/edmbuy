<?php
/**
 * Item gallery 共用Model
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class ItemsGallery extends StorageNode {
	
	protected static function meta() {
		return array(
				'table' => '`shp_goods_gallery`',
				'key'   => 'img_id',   //该key是应用逻辑的列，当columns为array时，为columns的key，否则，则要设成实际存储字段
				'columns' => array( //命名特点：'goods_%s'=>'item_%s'，其他不变
						'img_id'    => 'img_id',
						'item_id'   => 'goods_id',
						'img_url'   => 'img_url',
						'img_desc'  => 'img_desc',
						'thumb_url' => 'thumb_url',
						'img_original' => 'img_original',
				)
		);
	}
	
}
 
/*----- END FILE: class.ItemsGallery.php -----*/