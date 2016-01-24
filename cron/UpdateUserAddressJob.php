<?php
/**
 * 更新与应用同步用户信息次数
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
class UpdateUserAddressJob extends CronJob {

	private $table = '`shp_user_address`';
	private $table_order = '`shp_order_info`';

	public function main($argc, $argv) {

		//更新shp_user_address表
		$start = 0;
		$limit = 100;
		$total = $this->getTotal();
		$this->log('update address table, total records: '.$total);
		$list  = $this->getList($start, $limit);
		while (!empty($list)) {
			$this->log('current records: '.count($list).'/'.$total);
			foreach ($list AS $it) {
				$updata = ['province'=>$it['province'], 'city'=>$it['city'], 'district'=>$it['district']];
				if (empty($it['province']) && !empty($it['province_name'])) {
					$updata['province']  = Region::getId($it['province_name'], 1);
				}
				if (empty($it['city']) && !empty($it['city_name'])) {
					$updata['city']      = Region::getId($it['city_name'], 2, $updata['province']);
				}
				if (empty($it['district']) && !empty($it['district_name'])) {
					$updata['district']  = Region::getId($it['district_name'], 3, $updata['city']);
				}
				D()->update($this->table, $updata, ['address_id'=>$it['address_id']]);
			}
			unset($list);
			$start += $limit;
			$list = $this->getList($start, $limit);
		}
		
		//更新shp_order_info表
		$start = 0;
		$limit = 100;
		$total = $this->getOrderTotal();
		$this->log('update order table, total records: '.$total);
		$list  = $this->getOrderList($start, $limit);
		while (!empty($list)) {
			$this->log('current records: '.count($list).'/'.$total);
			foreach ($list AS $it) {
				$updata = ['province'=>$it['province'], 'city'=>$it['city'], 'district'=>$it['district'], 'address'=>$it['address']];
				if (empty($it['province'])) {
					$addr = $this->getAddr($it['user_id']);
					if (!empty($addr)) {
						$updata = ['province'=>$addr['province'], 'city'=>$addr['city'], 'district'=>$addr['district'], 'address'=>$addr['address']];
					}
				}
				elseif (empty($it['city'])) {
					$addr = $this->getAddr($it['user_id'], ['province'=>$it['province']]);
					if (!empty($addr)) {
						$updata = ['city'=>$addr['city'], 'district'=>$addr['district'], 'address'=>$addr['address']];
					}
				}
				elseif (empty($it['district'])) {
					$addr = $this->getAddr($it['user_id'], ['province'=>$it['province'], 'city'=>$it['city']]);
					if (!empty($addr)) {
						$updata = ['district'=>$addr['district'], 'address'=>$addr['address']];
					}
				}
				
				D()->update($this->table_order, $updata, ['order_id'=>$it['order_id']]);
			}
			unset($list);
			$start += $limit;
			$list = $this->getOrderList($start, $limit);
		}
		
	}
	
	private function getTotal() {
		$total = D()->query("SELECT COUNT(1) FROM ".$this->table." WHERE province=0 OR city=0")->result();
		return $total ? : 0;
	}
	
	private function getOrderTotal() {
		$total = D()->query("SELECT COUNT(1) FROM ".$this->table_order." WHERE pay_status=2 AND (province=0 OR city=0)")->result();
		return $total ? : 0;
	}
	
	private function getList($start = 0, $limit = 1000) {
		$table = $this->table;
		$sql = "
SELECT *
FROM {$table}
WHERE province=0 OR city=0
LIMIT {$start},{$limit}
";
		return D()->query($sql)->fetch_array_all();
	}
	
	private function getOrderList($start = 0, $limit = 1000) {
		$table = $this->table_order;
		$sql = "
SELECT order_id,order_sn,pay_trade_no,user_id,order_status,shipping_status,pay_status,consignee,country,province,city,district,address,zipcode,tel,mobile,email,best_time
FROM {$table}
WHERE pay_status=2 AND (province=0 OR city=0)
LIMIT {$start},{$limit}
";
		return D()->query($sql)->fetch_array_all();
	}
	
	private function getAddr($user_id, Array $extra = []) {
		$sql = "SELECT * FROM ".$this->table." WHERE user_id=%d";
		if (!empty($extra)) {
			$extra_sql = '';
			foreach ($extra AS $k=>$v) {
				$extra_sql .= " AND `{$k}`='{$v}'";
			}
			$sql .= $extra_sql;
		}
		$row = D()->query($sql, $user_id)->get_one();
		return $row;
	}

}
 
/*----- END FILE: UpdateUserAddressJob.php -----*/