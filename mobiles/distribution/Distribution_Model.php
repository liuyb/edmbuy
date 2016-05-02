<?php

defined('IN_SIMPHP') or die('Access Denied');

/**
 * 多米分销Model
 * @author Jean
 *
 */
class Distribution_Model extends Model{
    
    /**
     * 获取联盟商家列表
     * @param PagerPull $pager
     * @param array $options orderby(oc销量|cc收藏量) 
     */
    static function getMerchantsList(PagerPull $pager, array $options){
        $orderby = $options['orderby'] ? $options['orderby'] : 'oc';
        $sql = "select m.merchant_id as merchant_id, m.facename as facename, m.logo as logo, ifnull(mo.oc, 0) oc, ifnull(cs.cc, 0) cc from shp_merchant m left join
                (select merchant_ids, count(order_id) oc from shp_order_info where is_separate = 0 and pay_status = ".PS_PAYED." and merchant_ids <> ''
                group by merchant_ids) mo
                on m.merchant_id = mo.merchant_ids
                left join
                (select count(1) as cc, merchant_id from shp_collect_shop group by merchant_id) cs
                on m.merchant_id = cs.merchant_id order by $orderby desc limit %d,%d";
        $result = D()->query($sql, $pager->start, $pager->realpagesize)->fetch_array_all();
        foreach ($result as &$mch){
            $recomm_goods = self::findGoodsRcoment($mch['merchant_id'], 3);
            if($recomm_goods && count($recomm_goods) == 3){
                $mch['recommend'] = $recomm_goods;
            }
        }
        $pager->setResult($result);
    }
    
    /**
     * 获取在售的推荐商品商品列表
     */
    static function findGoodsRcoment($merchant_id, $limit){
        $where = "and shop_recommend = 1 and merchant_id = '%s'";
        $sql = "select goods_id,goods_name,shop_price,market_price,goods_brief,
        goods_thumb,goods_img from shp_goods where is_on_sale = 1 and is_delete = 0 $where order by sort_order desc,last_update desc limit {$limit}";
        $goods = D()->query($sql,$merchant_id)->fetch_array_all();
        return Items::buildGoodsImg($goods);
    }
    
    /**
     * 我发展的代理列表
     * @param PagerPull $pager
     * @param unknown $uid
     */
    static function getChildAgentList(PagerPull $pager, $level, $uid){
        switch ($level){
            case Partner::Partner_LEVEL_1 : 
                Partner::findFirstLevelList($uid, $pager, Partner::LEVEL_TYPE_AGENCY);
            break; 
            case Partner::Partner_LEVEL_2 :
                Partner::findSecondLevelList($uid, $pager, Partner::LEVEL_TYPE_AGENCY);
            break;
            case Partner::Partner_LEVEL_3 :
                Partner::findThirdLevelList($uid, $pager, Partner::LEVEL_TYPE_AGENCY);
            break;
        }
    }
    
    /**
     * 我发展的店铺总数
     * @param unknown $invite_code
     * @return mixed
     */
    static function getChildShopCount($invite_code){
        $sql = "select count(merchant_id) from shp_merchant where invite_code = '%s'";
        return D()->query($sql, $invite_code)->result();
    }
    
    /**
     * 我发展的店铺列表
     * @param PagerPull $pager
     * @param unknown $invite_code
     */
    static function getChildShopList(PagerPull $pager, $level, $invite_code){
        switch ($level){
            case Partner::Partner_LEVEL_1 :
                self::findFirstLevelShopList($invite_code, $pager);
                break;
            case Partner::Partner_LEVEL_2 :
                self::findSecondLevelShopList($invite_code, $pager);
                break;
            case Partner::Partner_LEVEL_3 :
                self::findThirdLevelShopList($invite_code, $pager);
                break;
        }
    }
    
    //第一层总数
    static function findFirstLevelShopCount($uid){
        $sql = "select count(merchant_id) from shp_merchant where invite_code = '%s' ";
        $result = D()->query($sql, $uid)->result();
        return $result;
    }
    //第二层总数
    static function findSecondLevelShopCount($uid){
        $sql = "select count(merchant_id) from shp_merchant 
                where invite_code in (SELECT user_id FROM shp_users where `parent_id` = '%s' ) ";
        $result = D()->query($sql, $uid)->result();
        return $result;
    }
    //第三层总数
    static function findThirdLevelShopCount($uid){
        $sql = "select count(merchant_id) from shp_merchant where invite_code 
                 in (
                    SELECT user_id FROM shp_users su where
                    su.parent_id in (SELECT user_id FROM shp_users where `parent_id` = '%s' )
                    ) ";
        $result = D()->query($sql, $uid)->result();
        return $result;
    }
    
    /**
     * 第一层列表
     * @param unknown $uid
     * @param Pager $pager
     */
    static function findFirstLevelShopList($uid, PagerPull $pager){
        $column = self::outputLevelListQueryColumn();
        $sql = "select $column where m.invite_code = '%s' order by m.created desc limit %d,%d";
        $result = D()->query($sql, $uid, $pager->start, $pager->realpagesize)->fetch_array_all();
        $result = self::rebuildLevelResult($result);
        $pager->setResult($result);
        return $result;
    }
    
    /**
     * 第二层列表
     * @param unknown $uid
     * @param Pager $pager
     */
    static function findSecondLevelShopList($uid, PagerPull $pager){
        $column = self::outputLevelListQueryColumn();
        $sql = "select $column where m.invite_code in (SELECT user_id FROM shp_users where `parent_id` = '%s' ) order by m.created desc limit %d,%d";
        $result = D()->query($sql, $uid, $pager->start, $pager->realpagesize)->fetch_array_all();
        $result = self::rebuildLevelResult($result);
        $pager->setResult($result);
        return $result;
    }
    
    /**
     * 第三层列表
     * @param unknown $uid
     * @param Pager $pager
     */
    static function findThirdLevelShopList($uid, PagerPull $pager){
        $column = self::outputLevelListQueryColumn();
        $sql = "select $column where m.invite_code 
                 in (
                    SELECT user_id FROM shp_users su where
                    su.parent_id in (SELECT user_id FROM shp_users where `parent_id` = '%s' )
                    )   
                    order by m.created desc limit %d,%d";
        $result = D()->query($sql, $uid, $pager->start, $pager->realpagesize)->fetch_array_all();
        $result = self::rebuildLevelResult($result);
        $pager->setResult($result);
        return $result;
    }
    
    static function outputLevelListQueryColumn(){
        $queryCols = "m.merchant_id as merchant_id, m.facename as facename, m.logo as logo,m.created as created, 
                ifnull(mo.oc, 0) oc, ifnull(cs.cc, 0) cc from shp_merchant m left join
                (select merchant_ids, count(order_id) oc from shp_order_info where is_separate = 0 and pay_status = ".PS_PAYED." and merchant_ids <> ''
                group by merchant_ids) mo
                on m.merchant_id = mo.merchant_ids
                left join
                (select count(1) as cc, merchant_id from shp_collect_shop group by merchant_id) cs
                on m.merchant_id = cs.merchant_id";
        return $queryCols;
    }
    
    static function rebuildLevelResult($result){
        foreach ($result as &$rs){
            $rs['created'] = date('Y-m-d H:i', $rs['created']);
        }
        return $result;
    }
    
    /**
     * 代理 赠品礼包
     * @param unknown $type
     */
    static function getAgentPackage($type, $pid = 0){
        $where = '';
        if($pid){
            $where = ' and pid = '.$pid;
        }
        $sql = "select * from shp_premium_package where enabled = 1 and type = '%d' $where order by created desc ";
        $result = D()->query($sql, $type)->fetch_array_all();
        foreach ($result as &$rs){
            $goods_ids = $rs['goods_ids'];
            if(!$goods_ids){
                continue;
            }
            $rs['goodslist'] = self::getGoods(explode(',', $goods_ids));
        }
        return $result;
    }
    
    static function getGoods($goods_ids, &$total_price = NULL) {
        if (!$goods_ids || count($goods_ids) == 0) {
            return [];
        }
        $ret = D()->query("select * from shp_goods where goods_id ".Fn::db_create_in($goods_ids)." and is_delete=0 ")->fetch_array_all();
        if (!empty($ret)) {
            $total_price = 0;
            foreach ($ret As &$g) {
                $g['goods_url']   = Items::itemurl($g['goods_id']);
                $g['goods_thumb'] = Items::imgurl($g['goods_thumb']);
                $g['goods_img']   = Items::imgurl($g['goods_img']);
                $total_price     += $g['shop_price'];
            }
        }
        return empty($ret) ? [] : $ret;
    }
    
    /**
     * 根据主订单生成多个子订单，如果关联多个商家的话
     * @param integer $master_order_id
     * @param array $merchant_uids
     */
    static function genSubOrderForPermium($master_order_id, Array $merchant_uids, $agent_order_id) {
        if (count($merchant_uids) < 2) { //一个订单只有一个商家uid不需要分单
            return false;
        }
        $master_order = Order::load($master_order_id);
        if ($master_order->is_exist()) {
            foreach ($merchant_uids AS $m_uid) {
                $subOrder = $master_order->clone_one();
                $subOrder->order_sn = Fn::gen_order_no();
                $subOrder->goods_amount = 0;
                $subOrder->money_paid   = 0;
                $subOrder->order_amount = 0;
                $subOrder->commision    = 0;
                $subOrder->is_separate  = 0;
                $subOrder->parent_id    = $master_order_id;
                $subOrder->merchant_ids = Merchant::getMidByAdminUid($m_uid);
                $subOrder->order_status = OS_CONFIRMED;
                $subOrder->pay_status   = PS_PAYED;
                $subOrder->relate_order_id = $agent_order_id;
                $subOrder->save(Storage::SAVE_INSERT); //先生成一个克隆子订单
                 
                if ($subOrder->id) {
                    $orderIts = Order::getItems($master_order_id, $m_uid);
                    $goods_amount = 0;
                    $order_amount = 0;
                    $commision    = 0;
                    foreach ($orderIts AS $oit) {
                         
                        $OI = OrderItems::find_one(new AndQuery(new Query('order_id', $master_order_id),new Query('goods_id', $oit['goods_id'])));
                        if (!$OI->is_exist()) continue;
                         
                        $newOI = $OI->clone_one();
                        $newOI->order_id    = $subOrder->id;
                        $newOI->parent_id   = $master_order_id;
                        $newOI->save(Storage::SAVE_INSERT); //循环生成“订单-商品”关联记录
                         
                        $goods_amount += $oit['goods_price'] * $oit['goods_number'];
                        $commision    += $oit['commision'] * $oit['goods_number'];
                    }
    
                    //$order_amount = $goods_amount + $master_order->shipping_fee; //TODO: 邮费这里以后要处理的
                    $order_update = [];
                    $order_update['goods_amount'] = $goods_amount;
                    //$order_update['order_amount'] = $order_amount;
                    //$order_update['commision']    = $commision;
                    if (!empty($order_update)) {
                        D()->update(Order::table(), $order_update, ['order_id'=>$subOrder->id]);
                    }
    
                    //paylog也要生成(因为客户可能后期单独去付款)
                    //PayLog::insert($subOrder->id, $subOrder->order_sn, $order_amount, PAY_ORDER);
    
                    //将上级is_separate设为1(已分单)
                    D()->update(Order::table(), ['is_separate'=>1], ['order_id'=>$master_order_id]);
    
                    //关联订单和商家ID
                    Order::relateMerchant($subOrder->id, $m_uid);
    
                } //END if ($subOrder->id)
            } //END foreach ($merchant_uids AS $m_uid)
        } //END if ($master_order->is_exist())
        return false;
    }
    
}

