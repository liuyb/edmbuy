<?php
/**
 * 导入微信支付退款下载列表作业
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
class RefundImportJob extends CronJob {
	
	public function main($argc, $argv) {
		
		if ($argc < 2) {
			$this->usage('[file path]');
		}
		
		//$data_file = SIMPHP_ROOT . '/misc/wxdata/11030966REFUND2016-01-30.txt';
		$file = ltrim($argv[1],'/');
		$data_file = SIMPHP_ROOT . '/'.$file;
		if (!file_exists($data_file)) {
			$this->log('file not exists: '.$data_file);
			return;
		}
		
		$affect_rows = 0;
		$insert_rows = 0;
		$update_rows = 0;
		$fp = fopen($data_file, 'r');
		if ($fp) {
			while (!feof($fp)) {
				$row = fgets($fp);
				$row = trim($row);
				if (!preg_match('/^20/', $row)) {
					continue;
				}
				$ret = $this->parse_and_save($row);
				if (1==$ret) {
					$insert_rows++;
				}
				else {
					$update_rows++;
				}
				$affect_rows++;
			}
			fclose($fp);
		}
		$this->log("Finish, affected rows: {$affect_rows}, insert rows: {$insert_rows}, update rows: {$update_rows}");
	
	}
	
	private function parse_and_save($row) {
		//parsing
		$arr = explode("\t", $row);
		$refund_time    = $arr[0];
		$pay_refund_no  = str_replace('`', '', $arr[1]);
		$refund_sn      = str_replace('`', '', $arr[2]);
		$refund_status  = str_replace('"', '', mb_convert_encoding($arr[3],'UTF-8','GB18030'));
		$succ_time      = $arr[4];
		$refund_money   = $arr[5];
		$pay_trade_no   = str_replace('`', '', $arr[7]);
		$order_sn       = str_replace('`', '', $arr[8]);
		$trade_money	  = $arr[11];
	
		$data = [
				'order_sn'      => $order_sn,
				'pay_trade_no'  => $pay_trade_no,
				'refund_sn'     => $refund_sn,
				'pay_refund_no' => $pay_refund_no,
				'trade_money'   => $trade_money,
				'refund_money'  => $refund_money,
				'refund_status' => $refund_status,
				'refund_time'   => $refund_time,
				'succ_time'     => $succ_time,
		];
	
		$table = '`shp_order_refund`';
		$ext_rows = D()->from($table)->where("refund_sn='%s'", $refund_sn)->select()->get_one();
		if (empty($ext_rows)) { //未存在记录，插入
			D()->insert($table, $data);
			return 1;
		}
		else { //已存在记录，更新
			D()->update($table, $data, ['rec_id'=>$ext_rows['rec_id']]);
			return 2;
		}
	
	}
	
}
 
/*----- END FILE: RefundImportJob.php -----*/