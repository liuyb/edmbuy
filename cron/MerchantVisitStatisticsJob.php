<?php

/**
 * 商家访问记录统计
 * @author Jean
 *
 */
class MerchantVisitStatisticsJob extends CronJob{
    
    //商品统计
    private static $MODULE_GOODS = 1;
    
    //店铺统计
    private static $MODULE_SHOP = 2;
    
    //以前的数据不需要再统计，以120万开始
    private static $START_VID = 120;
    
    public function main($argc, $argv) {
        $this->log("begin merchant visit statistic...");
        $records = $this->getVisitingRecord();
        $count = count($records);
        $this->log("get wait parse visit records ".$count);
        $i = 0;
        $already_sync = 0;
        foreach ($records as $visit){
            $this->MerchantVisitRecordStatis($visit);
            if($i == 1000){
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
        $sql = "select visit_id from shp_merchant_visiting where merchant_id = -1";
        $vid = D()->query($sql)->result();
        //tb_visiting 记录太大，必须要有时间限制
        if(!$vid){
            $vid = self::$START_VID;
            D()->insert('`shp_merchant_visiting`', array('visit_id' => $vid, 'merchant_id' => '-1', 'add_time' => time()));
        }
        $this->log('old merchant visit id:'.$vid);
        $sql = "SELECT vid,targeturl FROM `tb_visiting` WHERE vid > $vid and `targeturl` REGEXP '\/item\/[0-9]*$|\/shop\/mc_[0-9a-z]*$' order by vid desc";
        
        $result = D()->query($sql)->fetch_array_all();
        
        if($result && count($result) > 0){
            $maxvid = $result[0]['vid'];           
            D()->update('`shp_merchant_visiting`', array('visit_id' => $maxvid, 'add_time' => time()), "merchant_id = '-1'");
        }
        
        return $result;
    }
    
    /**
     * 商品访问记录 统计
     * targeturl:http://e.edmbuy.com/item/1009
     * targeturl:http://e.edmbuy.com/shop/mc_aas1111
     * @param unknown $visit
     */
    private function MerchantVisitRecordStatis($visit){
        $targetUrl = $visit['targeturl'];
        list($merchant_id, $module) = $this->parseMchidByTargeUrl($targetUrl);
        if(!$merchant_id){
            return;
        }
        $insertarr = array('visit_id' => $visit['vid'], 'merchant_id' => $merchant_id, 'add_time' => time(), 'module' => $module);
        D()->insert('`shp_merchant_visiting`', $insertarr);
    }
    
    /**
     * 根据targetUrl解析出merchant_id 
     * @param unknown $targetUrl
     * @return unknown|mixed
     */
    private function parseMchidByTargeUrl($targetUrl){
        $merchant_id = '';
        $module = '';
        //返回$matchs[/item/1009,1009]
        $matchs = preg_match('/\/item\/(\d+)$/', $targetUrl, $ret);
        if($ret && count($ret) == 2){
            $goods_id = $ret[1];
            $sql = "select merchant_id from shp_goods where goods_id = '%d'";
            $merchant_id = D()->query($sql, $goods_id)->result();
            $module = self::$MODULE_GOODS;
        }else{
            //返回$matchs[/shop/mc_aaa111,mc_aaa111]
            $matchs = preg_match('/\/shop\/(mc_\w+)$/', $targetUrl, $mc);
            if($mc && count($mc) == 2){
                $merchant_id = $mc[1];
                $module = self::$MODULE_SHOP;
            }
        }
        return [$merchant_id, $module];
    }
}

?>