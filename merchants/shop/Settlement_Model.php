<?php
/**
 * 结算管理相关的逻辑
 * @author Jean
 *
 */
class Settlement_Model extends Model{
    
    /**
     * 获取结算列表数据
     * @param Pager $pager
     * @param array $options
     */
    public static function getSettlementList(Pager $pager, array $options){
        $muid = $GLOBALS['user']->uid;
        $where = '';
        if(isset($options['status']) && $options['status']){
            $where .= " and stmt_status = ".intval($options['status'])."";
        }
        $sql = "select count(1) from shp_settlement where merchant_id='%s' $where ";
        $count = D()->query($sql, $muid)->result();
        $pager->setTotalNum($count);
        $sql = "SELECT * FROM shp_settlement where merchant_id='%s' $where order by end_time desc limit {$pager->start},{$pager->pagesize}";
        $result = D()->query($sql, $muid)->fetch_array_all();
        foreach ($result as &$item){
            $sdate = date('Y.m.d', $item['start_time']);
            $edate = date('Y.m.d', $item['end_time']);
            $item['date_range'] = $sdate .'-'. $edate;
        }
        $pager->setResult($result);
    }
    
    /**
     * 获取结算订单列表
     * @param Pager $pager
     * @param unknown $settle_id
     * @param array $options
     */
    public static function getSettlementDetail(Pager $pager, $settle_id, array $options){
        $muid = $GLOBALS['user']->uid;
        $where = '';
        if ($options['start_date']) {
            $starttime = simphp_gmtime(strtotime($options['start_date'] . DAY_BEGIN));
            $where .= " and od.add_time >= $starttime ";
        }
        if ($options['end_date']) {
            $endtime = simphp_gmtime(strtotime($options['end_date'] . DAY_END));
            $where .= " and od.add_time <= $endtime ";
        }
        $sql = "select count(1) from shp_order_info od, shp_order_settlement stmt
                where	od.order_id = stmt.order_id
                and		stmt.stmt_id = '%d' and merchant_ids = '%s' $where ";
        $count = D()->query($sql, $settle_id, $muid)->result();
        $pager->setTotalNum($count);
        $sql = "select * from shp_order_info od, shp_order_settlement stmt 
                where	od.order_id = stmt.order_id	
                and		stmt.stmt_id = '%d' and merchant_ids = '%s' $where order by od.add_time desc limit {$pager->start},{$pager->pagesize}";
        $result = D()->query($sql, $settle_id, $muid)->fetch_array_all();
        foreach ($result as &$item){
            $item['add_time'] = date('Y/m/d H:i:s', simphp_gmtime2std($item['add_time']));
        }
        $pager->setResult($result);
    }
}
