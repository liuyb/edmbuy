<?php

/**
 * 商家访问记录统计
 * @author Jean
 *
 */
class MerchantVisitStatisticsJob extends CronJob{
    
    //商品统计
    private static $MODULE_GOODS = 1;
    
    private static $MODULE_SHOP = 2;
    
    public function main($argc, $argv) {
        $this->log("begin merchant visit statistic...");
        $records = $this->getVisitingRecord();
        $count = count($records);
        $this->log("get wait parse visit records ".$count);
        $i = 0;
        $already_sync = 0;
        foreach ($records as $visit){
            $this->goodsVisitRecord($visit);
            if($i == 100){
                $already_sync = $already_sync + $i;
                $this->log("already parse count ".$already_sync." wait parse count : ".($count - $already_sync));
                $i = 0;
                sleep(1);
            }
        }
    }
    
    /**
     * 获取还没有统计过的记录
     * 在shp_merchant_visiting 增加一条merchant_id为-1的记录来记录统计时间
     */
    private function getVisitingRecord(){
        $where = '';
        //获取最大已统计过的日期，以这个为开始统计条件
        $sql = "select add_time from shp_merchant_visiting where merchant_id = -1";
        $created = D()->query($sql)->result();
        $this->log('old merchant visiting time:'.$created);
        //tb_visiting 记录太大，必须要有时间限制
        if(!$created){
            $created = time();
            D()->insert('`shp_merchant_visiting`', array('visit_id' => 0, 'merchant_id' => '-1', 'add_time' => $created));
        }
        
        $sql = "SELECT * FROM tb_visiting where created > $created";
        
        $result = D()->query($sql)->fetch_array_all();
        
        D()->update('`shp_merchant_visiting`', array('add_time' => time()), "merchant_id = '-1'");
        return $result;
    }
    
    /**
     * 商品访问记录 统计
     * targeturl:http://e.edmbuy.com/item/1009
     * @param unknown $visit
     */
    private function goodsVisitRecord($visit){
        $targetUrl = $visit['targeturl'];
        //返回$matchs[/item/1009,1009]
        $matchs = preg_match('/\/item\/(\d+)$/', $targetUrl, $ret);
        if(!$ret || count($ret) != 2){
            return;
        }
        $goods_id = $ret[1];
        $sql = "select merchant_id from shp_goods where goods_id = '%d'";
        $merchant_id = D()->query($sql, $goods_id)->result();
        if(!$merchant_id){
            return;
        }
        $insertarr = array('visit_id' => $visit['vid'], 'merchant_id' => $merchant_id, 'add_time' => time(), 'module' => self::$MODULE_GOODS);
        D()->insert('`shp_merchant_visiting`', $insertarr);
    }
}

?>