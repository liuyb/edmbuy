<?php
/**
 * Shipping公用Model
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Shipping extends StorageNode {
	
	protected static function meta() {
		return array(
				'table' => '`shp_shipping`',
				'key'   => 'shipping_id',
				'columns' => array(
						'shipping_id'    => 'shipping_id',
						'shipping_code'  => 'shipping_code',
						'shipping_type'  => 'shipping_type',
						'shipping_name'  => 'shipping_name',
						'shipping_desc'  => 'shipping_desc',
						'insure'         => 'insure',
						'support_cod'    => 'support_cod',
						'enabled'        => 'enabled',
						'shipping_print' => 'shipping_print',
						'print_bg'       => 'print_bg',
						'config_lable'   => 'config_lable',
						'print_model'    => 'print_model',
						'shipping_order' => 'shipping_order',
				)
		);
	}
	
}

/*----- END FILE: class.Shipping.php -----*/