<?php
/**
 * 根据用户parent_id修正上三级关系，以及上一级昵称
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
class AdjustUserRelationJob extends CronJob {
	
	static $thetable = '`shp_users`';
	
	public function main($argc, $argv) {
		
		$start = 0;
		$limit = 1000;
		$i     = 0;
		
		$total = self::getTotal();
		$this->log('Adjusting user relation, total records: '.$total);
		$list  = self::getList($start, $limit);
		while (!empty($list)) {
			$this->log('Current records: '.count($list).'/'.$total);
			foreach ($list AS $it) {
				$_p = self::find_uplayer_users($it['parent_id']);
				if ($_p['parent_id2']) {
					D()->update(self::$thetable, $_p, ['user_id'=>$it['user_id']]);
				}
				$i++;
			}
			unset($list);
			$start += $limit;
			$list = self::getList($start, $limit);
		}
		
	}
	
	static function getTotal() {
		$total = D()->query("SELECT COUNT(1) FROM ".self::$thetable." WHERE `mobile`<>''")->result();
		return $total ? : 0;
	}
	
	static function getList($start = 0, $limit = 1000) {
		$sql   = "SELECT user_id,parent_id FROM ".self::$thetable." WHERE `mobile`<>'' LIMIT {$start},{$limit}";
		return D()->query($sql)->fetch_array_all();
	}
		
	static function find_uplayer_users($parent_id) {
		$p = ['parent_nick'=>'','parent_id2'=>0,'parent_id3'=>0];
		if ($parent_id) {
			$parent1_row = D()->from(self::$thetable)->where(['user_id'=>$parent_id])->select('nick_name,parent_id')->get_one();
			if (!empty($parent1_row)) {
				$p['parent_nick'] = $parent1_row['nick_name'];
				if ($parent1_row['parent_id']) {
					$p['parent_id2'] = $parent1_row['parent_id'];
					$parent_id3 = D()->from(self::$thetable)->where(['user_id'=>$p['parent_id2']])->select('parent_id')->result();
					if ($parent_id3) {
						$p['parent_id3'] = $parent_id3;
					}
				}				
			}
		}
		return $p;
	}
	
}
 
/*----- END FILE: AdjustUserRelationJob.php -----*/