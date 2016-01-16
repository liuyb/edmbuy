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
		if (0===$region_type) { //国家需精确匹配
			$sql .= "`region_name`='%s'";
		}
		elseif (1===$region_type) { //身份也是精确匹配,但是需要将末尾可能存在的"省"字去掉
			$region_name = preg_replace('/(省$)/u', '', $region_name); //先把可能存在末尾的"省"字去掉
			$sql .= "`region_name`='%s'";
		}
		else { //市级和区级，名称可能带"市"或"区"，也可能不带
			$w = 2==$region_type ? '市' : '区';
			$region_name = preg_replace('/('.$w.'$)/u', '', $region_name); //先把可能存在末尾的"市"或"区"字去掉
			$sql .= "`region_name` like '%s%' AND `parent_id`=%d"; //市和区在全国范围内都可能有重名的，需要parent_id来区分
		}
		$row = D()->raw_query($sql,$region_type,$region_name,$parent_id)->get_one();
		if (!empty($row)) return $row['region_id'];
		return 0;
	}

}
 
/*----- END FILE: class.Region.php -----*/