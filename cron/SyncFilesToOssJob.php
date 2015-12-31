<?php
/**
 * 同步图片、文件到阿里云OSS
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
//阿里云OSS库
require SIMPHP_INCS . '/libs/aliyun_oss/OssCommon.php';

use OSS\OssClient;
use OSS\Core\OssException;

// 作业类
class SyncFilesToOssJob extends CronJob {
	
	public function main($argc, $argv) {
		
		// 从数据库获取要同步的文件信息列表
		$fileList = $this->getFileList();
		
		if (!empty($fileList)) {
			
			// 记下记录ID集合
			$rids = array_keys($fileList);
			
			// 立即锁定当前进程准备处理的记录
			$this->lockFiles($rids);
			
			// 获取OSS Client
			$ossClient = OssCommon::getOssClient();
			$bucket    = OssCommon::getBucketName();
			
			// 获取当前server id
			$cur_server_id = Config::get('env.server_id', 1);
			
			// 循环上传
			$this->log("Begin loop...Records Num: ".count($fileList));
			foreach ($fileList AS $it) {
				
				if ($it->sid == $cur_server_id) { //表示当前web服务器下生成的文件
					
					$local_file = SIMPHP_ROOT . '/../' . SHOP_PLATFORM . '/' . ltrim($it->oripath,'/'); //TODO 这里要改成下载的方式
					$remote_file= $it->oss_path();
					if (!file_exists($local_file)) {
						$this->log("[FAIL]rid: {$it->mid}, file: [{$it->sid}]{$it->oripath}, msg: file not found.");
						continue;
					}
					
					try {
						$ossClient->uploadFile($bucket, $remote_file, $local_file);
						$remote_file = OssCommon::getOssPath($remote_file);
						$this->log("[SUCC]rid: {$it->mid}, file: [{$it->sid}]{$it->oripath} >> {$remote_file}");
						
						//更新同步标志位
						$upMedia = new Media($it->mid);
						$upMedia->osspath = $remote_file;
						$upMedia->changed = simphp_dtime();
						$upMedia->synced  = 1;
						$upMedia->save(Storage::SAVE_UPDATE);
					}
					catch(OssException $e) {
						$this->log("[FAIL]rid: {$it->mid}, file: [{$it->sid}]{$it->oripath}, msg: ".$e->getErrorMessage());
					}
					catch(Exception $e) {
						
					}
					
				}
				
				
			}
			$this->log("End loop.");
			
			// 最后解锁当前进程处理的全部记录
			$this->unlockFiles($rids);
			
		} // END if (!empty($fileList))
		
	}
	
	private function getFileList() {
		return Media::find(new AndQuery(new Query('synced', 0), new Query('locked', 0)));
	}
	
	private function lockFiles(Array $rids = []) {
		if (empty($rids)) return false;
		D()->query("UPDATE `{media}` SET `locked`=1 WHERE `media_id` IN('%s')", implode("','", $rids));
	}
	
	private function unlockFiles(Array $rids = []) {
		if (empty($rids)) return false;
		D()->query("UPDATE `{media}` SET `locked`=0 WHERE `media_id` IN('%s')", implode("','", $rids));
	}
	
	private function updateSyncFlag($rid) {
		if (empty($rid)) return false;
		D()->query("UPDATE `{media}` SET `synced`=1 WHERE `media_id`='%s'", $rid);
	}
	
}
 
/*----- END FILE: SyncFilesToOssJob.php -----*/