<?php
/**
 * 微信消息发送作业
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
class WxmsgSendJob extends CronJob {
	
	private $table = '`shp_users`';
	
	public function main($argc, $argv) {
		
		$msg = "提现功能已上线，欢迎使用！";
		$mark= "点击“详情”查看";
		$url = "http://m.edmbuy.com/partner";
		
		$start = 0;
		$limit = 200;
		
		$record_last  = 0;
		$record_cnt   = 0;
		$done_records = []; //记录已经处理完成的记录ID
		$total = $this->getTotal();
		$list  = $this->getList($start, $limit);
		while (!empty($list)) {
			$record_last = $record_cnt;
			$record_cnt += count($list);
			$this->log('current records: '.($record_last+1).'~'.($record_cnt).'/'.$total);
			foreach ($list AS $row) {
				$kw1 = $row['nick_name'];
				$kw2 = WxTplMsg::human_dtime();
				//$str = 'user_id='.$row['user_id'].'&openid='.$row['openid'].'&subscribe='.$row['subscribe'].'&kw1='.$kw1.'&kw2='.$kw2;
				//$this->log('submit message: '.$str);
				//if( !in_array($row['user_id'], ['104','2667','7217','75414']) ) continue;
				$ret = WxTplMsg::submit_ok($row['openid'], $msg, $mark, $url, ['keyword1'=>$kw1,'keyword2'=>$kw2]);
				if ($ret) {
					array_push($done_records, $row['user_id']);
				}
			}
			
			unset($list);
			sleep(1);
			$this->log('sleeping 1 seconds...');
			
			$start += $limit;
			$list = $this->getList($start, $limit);
		}
		$this->log('success records: '.count($done_records).'/'.$total);
		
	}

	private function getTotal() {
		$count = D()->from($this->table)
		->where("subscribe=1 AND openid<>''")
		->select('COUNT(1)')
		->result();
		return $count;
	}
	
	private function getList($start = 0, $limit = 100) {
	
		$list = D()->from($this->table)
		->where("subscribe=1 AND openid<>''")
		->order_by('user_id ASC')
		->limit($start,$limit)
		->select("user_id,openid,nick_name,subscribe")
		->fetch_array_all();
		return $list;
	
	}
}
 
/*----- END FILE: WxmsgSendJob.php -----*/