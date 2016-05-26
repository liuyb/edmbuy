<?php
/**
 * 每天跑一次的作业
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
class DailyJob extends CronJob {
	
	public function main($argc, $argv) {
	
		// 批量更新商品已成功购买的单品数
		$this->upGoodsPaidNum();
		
		
		require SIMPHP_ROOT . "/cron/RefundMonitorJob.php";
		$monitor = new RefundMonitorJob();
		$monitor->handleUserOvertime();
	}
	
	/**
	 * 批量更新商品已成功购买的单品数
	 */
	private function upGoodsPaidNum() {
		$this->log("update goods paid number...");
		
		$start = 0;
		$limit = 100;
		$inc_all = true;
		$total= $this->getGoodsTotal($inc_all);
		$list = $this->getGoodsIdList($inc_all, $start, $limit);
		$idx  = 0;
		while (!empty($list)) {
			
			foreach ($list AS $gid) {
				$idx++;
				if ($this->_upGoodsPaidNum($gid)) {
					$this->log("current records: {$gid}({$idx}/{$total}), OK");
				}
				else {
					$this->log("current records: {$gid}({$idx}/{$total}), FAIL");
				}
			}
			
			unset($list);
			$start += $limit;
			$list = $this->getGoodsIdList($inc_all, $start, $limit);
		}
		$this->log("update goods paid number finished.");
	}
	private function _upGoodsPaidNum($goods_id) {
		$ectb_goods       = Items::table();
		$ectb_order_goods = OrderItems::table();
		$ectb_pay_log     = PayLog::table();
		$sql =<<<HERESQL
UPDATE {$ectb_goods} g, (
				SELECT og.goods_id,SUM(og.goods_number) AS paid_goods_num
				FROM {$ectb_order_goods} og INNER JOIN {$ectb_pay_log} l ON og.order_id=l.order_id AND l.is_paid=1
				WHERE og.goods_id=%d
				GROUP BY og.goods_id
			) pgon
SET g.paid_goods_number = pgon.paid_goods_num
WHERE g.goods_id=%d AND g.goods_id = pgon.goods_id
HERESQL;
		D()->raw_query($sql, $goods_id, $goods_id);
		if (D()->affected_rows() > 0) {
			return true;
		}
		return false;
	}
	private function getGoodsTotal($inc_all = false) {
		$where = '1';
		if (!$inc_all) {
			$where = 'is_on_sale=1';
		}
		$count = D()->from(Items::table())
		->where($where)
		->select('COUNT(1)')
		->result();
		return $count;
	}
	private function getGoodsIdList($inc_all = false, $start = 0, $limit = 100) {
		$where = '1';
		if (!$inc_all) {
			$where = 'is_on_sale=1';
		}
		$list = D()->from(Items::table())
		->where($where)
		->order_by('goods_id ASC')
		->limit($start,$limit)
		->select('goods_id')
		->fetch_column('goods_id');
		return $list;
	}
	
}
 
/*----- END FILE: DailyJob.php -----*/