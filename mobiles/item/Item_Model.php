<?php
/**
 * Item model
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Item_Model extends Model {
	
	static function escape_kefu_link($string) {
		return str_replace(['&','='], ['%26','%3D'], $string);
	}
	
}
 
/*----- END FILE: Item_Model.php -----*/