<?php
/**
 * UserAddress公用Model
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Region extends StorageNode {

	protected static function meta() {
		return array(
				'table' => '`shp_region`',
				'key'   => 'region_id',
				'columns' => array(
						'region_id'   => 'region_id',
						'parent_id'   => 'parent_id',
						'region_name' => 'region_name',
						'region_type' => 'region_type',
						'agency_id'   => 'agency_id',
				)
		);
	}
	
	/**
	 * 根据地区id来查找地区名称
	 * @param integer $region_id
	 * @return string
	 */
	static function getName($region_id) {
		$ectb = self::table();
		$sql  = "SELECT `region_name` FROM {$ectb} WHERE `region_id`=%d";
		$row  = D()->raw_query($sql,$region_id)->get_one();
		if (!empty($row)) return $row['region_name'];
		return false;
	}
	
	/**
	 * 根据地区名字来查找地区id
	 *
	 * @param string  $region_name 地区名字
	 * @param integer $region_type 地区类型：0:国家，1:省份，2:市级，3:区级
	 * @param integer $parent_id   当地区类型是市级和区级时，需要$parent_id来区分，因为有可能是重名的
	 * @return integer
	 */
	static function getId($region_name, $region_type = 1, $parent_id = 0) {
		$ectb = self::table();
		$sql  = "SELECT `region_id` FROM {$ectb} WHERE `region_type`=%d AND ";
		$sql .= "'%s' REGEXP CONCAT('^',`region_name`)";
		$row = D()->raw_query($sql,$region_type,$region_name)->fetch_array_all(); //先根据名字查找，如果结果多于1个，则再根据parent id来判定
		if (empty($row)) {
			return 0;
		}
		elseif (count($row)>1) { //多于一个，则再看parent_id
			$sql .= " AND `parent_id`=%d";
			$row = D()->raw_query($sql,$region_type,$region_name,$parent_id)->get_one();
			if (empty($row)) {
				return 0;
			}
		}
		else {
			$row = $row[0];
		}
		return $row['region_id'];
	}

}
 
/*----- END FILE: class.Region.php -----*/