<?php
/**
 * 导入甜玉米用户
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
set_time_limit(0);

class ImportTymUserJob extends CronJob {
	
	private $src_table = '`tym_user`';
	private $dst_table = '`tb_tym_user`';
	
	private $loopsleep = 1;
	
	public function main($argc, $argv) {
		
		$start = 0;
		$limit = 10000;
		
		$total_record = $this->get_total();
		$this->log('total record: '.$total_record);
		
		while ($start < $total_record) {
			$this->log('current record: '. ($start + $limit) . '/'.$total_record.'...');
			$sql = "
INSERT INTO `tb_tym_user`(`userid`,`mobile`,`openid`,`regtime`,`nick`,`logo`,`business_id`,`business_time`,`parent_userid`)
SELECT `cid`,`phone`,`mweixinid`,`operTime`,`name`,IFNULL(`picUrl`,'') AS `picUrl`,IFNULL(`openflag`,'') AS `openflag`,'' AS `business_time`,IFNULL(`registerUser`,0) AS `registerUser`
FROM `tym_user`
WHERE 1
LIMIT {$start},{$limit}
";
			D()->query($sql);
			$this->log('effect rows: '.D()->affected_rows());
			$start += $limit;
				
			$this->log("sleep ".$this->loopsleep." seconds...");
			sleep($this->loopsleep);
		}
		
		
	}
	
	private function get_total() {
		$sql = "SELECT COUNT(1) FROM ".$this->src_table . " WHERE 1";
		return D()->query($sql)->result();
	}
	
	private function get_list($start, $limit) {
		$sql = "SELECT * FROM ".$this->src_table . " WHERE 1 LIMIT {$start},{$limit}";
		return D()->query($sql)->fetch_array_all();
	}
	
	private function insert(Array $data = array()) {
		$userid = isset($data['userid']) ? $data['userid'] : 0;
		if (empty($userid)) return 0;
		D()->realtime_query = TRUE;
		$exist = D()->query("SELECT `userid` FROM ".$this->dst_table . " WHERE `userid`=%d", $userid)->result();
		if (!$exist) {
			$userid = D()->insert($this->dst_table, $data);
		}
		return $userid;
	}
	
}

/*----- END FILE: ImportTymUserJob.php -----*/