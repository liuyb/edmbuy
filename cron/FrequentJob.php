<?php
/**
 * 需要经常跑，并且短时的作业(5分钟执行一次)
 *
 * @author Gavin<laigw.vip@gmail.com>
 */

class FrequentJob extends CronJob {
	
	public function main($argc, $argv) {
		
		// 批量更新上级昵称
		$this->upParentNick();
		
		// 批量更新用户的一级下级数
		$this->upChildNum1();
		
	}
	
	/**
	 * 批量更新上级昵称
	 */
	private function upParentNick() {
		$this->log("update parent nick...");
		$sql = "UPDATE `shp_users` a, `shp_users` b
SET a.parent_nick=b.nick_name
WHERE a.parent_id<>0 AND a.parent_id=b.user_id";
		D()->query($sql);
		$this->log("OK. affected rows: ".D()->affected_rows());
	}
	
	/**
	 * 批量更新用户的一级下级数
	 */
	private function upChildNum1() {
		$this->log("update child 1 num...");
		$sql = "UPDATE `shp_users` AS a, (SELECT ib.parent_id,COUNT(DISTINCT ib.user_id) AS chnum FROM `shp_users` AS ia INNER JOIN `shp_users` AS ib ON ib.parent_id=ia.user_id WHERE 1 GROUP BY ib.parent_id) AS b
SET a.childnum_1=b.chnum
WHERE a.user_id=b.parent_id";
		D()->query($sql);
		$this->log("OK. affected rows: ".D()->affected_rows());
	}
	
}
 
/*----- END FILE: FrequentJob.php -----*/