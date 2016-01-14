<?php
/**
 * 更新与应用同步用户信息次数
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
/*
 * 注意事项：由于数据量大，在用cron操作大表时，要注意内存的使用，尽可能少用对象操作的方式，用标量，并及时释放无用内存，否则会爆内存
 */
//@ini_set('memory_limit', '512M');
class UpdateUserSyncTimesJob extends CronJob {
	
	private $table = 'tb_api_log';
	
	public function main($argc, $argv) {
		
		$start = 0;
		$limit = 1000;
		
		$api_name = 'user/update';
		$total_record = $this->get_apilog_total($api_name);
		$this->log('total record: '.$total_record);
		$api_args = $this->get_apilog_list($api_name, $start, $limit);
		while (!empty($api_args)) {
			$this->log('current record: '. ($start + count($api_args)) . '/'.$total_record.'...');
			foreach ($api_args AS &$arg) {
				$arg = json_decode($arg['args'], TRUE);
				$unionid = isset($arg['unionid']) ? $arg['unionid'] : '';
				if ($unionid) {
					$user_id = $this->get_user_by($unionid);
					if ($user_id) {
						$this->update_synctimes($user_id);
					}
				}
			}
			$start += $limit;
			
			//unset以释放内存
			unset($api_args);
			
			$this->log('sleep 1 seconds...');
			sleep(1); //暂停1秒
			$api_args = $this->get_apilog_list($api_name, $start, $limit);
		}
		
	}
	
	private function get_apilog_total($api_name) {
		$sql = "SELECT COUNT(1) FROM ".$this->table . " WHERE `api`='{$api_name}'";
		return D()->query($sql)->result();
	}
	
	private function get_apilog_list($api_name, $start, $limit) {
		$sql = "SELECT `args` FROM ".$this->table . " WHERE `api`='{$api_name}' LIMIT {$start},$limit";
		return D()->query($sql)->fetch_array_all();
	}
	
	private function get_user_by($unionid) {
		$sql = "SELECT `user_id` FROM `shp_users` WHERE `unionid`='{$unionid}'";
		return D()->query($sql)->result();
	}
	
	/**
	 * 更新同步次数
	 * 支持inc是整形、'+N','-N'的方式
	 */
	private function update_synctimes($user_id, $inc = 1) {
		if (is_string($inc) && ($inc{0}=='+'||$inc{0}=='-')) {
			$setpart = "`synctimes`".$inc;
		}
		else {
			$setpart = intval($inc);
		}
		D()->query("UPDATE `shp_users` SET `synctimes`={$setpart} WHERE `user_id`={$user_id}");
		return true;
	}
	
}
 
/*----- END FILE: UpdateUserSyncTimesJob.php -----*/
