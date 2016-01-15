<?php
/**
 * UserAddress公用Model
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class UserAddress extends StorageNode {
	
	protected static function meta() {
		return array(
				'table' => '`shp_user_address`',
				'key'   => 'address_id',
				'columns' => array(
						'address_id'   => 'address_id',
						'address_name' => 'address_name',
						'user_id'      => 'user_id',
						'consignee'    => 'consignee',
						'email'        => 'email',
						'country'      => 'country',
						'country_name' => 'country_name',
						'province'     => 'province',
						'province_name'=> 'province_name',
						'city'         => 'city',
						'city_name'    => 'city_name',
						'district'     => 'district',
						'district_name'=> 'district_name',
						'address'      => 'address',
						'zipcode'      => 'zipcode',
						'tel'          => 'tel',
						'mobile'       => 'mobile',
						'sign_building'=> 'sign_building',
						'best_time'    => 'best_time',
				)
		);
	}
	
	/**
	 * hook save before
	 * @see StorageNode::save_before()
	 */
	protected function save_before(&$op_type){
		if (empty($this->country) && !empty($this->country_name)) {
			$this->country  = Region::getId($this->country_name, 0);
		}
		if (empty($this->province) && !empty($this->province_name)) {
			$this->province = Region::getId($this->province_name, 1, $this->country);
		}
		if (empty($this->city) && !empty($this->city_name)) {
			$this->city     = Region::getId($this->city_name,     2, $this->province);
		}
		if (empty($this->district) && !empty($this->district_name)) {
			$this->district = Region::getId($this->district_name, 3, $this->city);
		}
	}
}

/*----- END FILE: class.UserAddress.php -----*/