<?php
/**
 * 订单Model
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Order_Model extends Model {


    /**
     * 分页显示订单列表
     * @param Pager $pager
     * @param array $options
     */
    static function getPagedOrders(Pager $pager, array $options){
        $muid = $GLOBALS['user']->uid;
        $where = "";
        $orderby = "";
        if($options['order_sn']){
            $where .= " and o.order_sn like '%".htmlspecialchars($options['order_sn'])."%' ";
        }
        if($options['start_date']){
            $starttime = simphp_gmtime(strtotime($options['start_date'].DAY_BEGIN));
            $where .= " and o.add_time >= $starttime ";
        }
        if($options['end_date']){
            $endtime = simphp_gmtime(strtotime($options['end_date'].DAY_END));
            $where .= " and o.add_time <= $endtime ";
        }
        if($options['buyer']){
            $where .= " and u.nick_name like '%".htmlspecialchars($options['buyer'])."%' ";
        }
        if($options['status']){
            $statusSql = Order::build_order_status_sql($options['status'], 'o');
            if($statusSql){
                $where .= $statusSql;
            }
        }
        if($options['orderby'] && $options['order_field']){
            $orderby .= " order by $options[order_field] $options[orderby] ";
        }else{
            $orderby .= " order by o.add_time desc ";
        }
        $sql = "SELECT count(1) FROM shp_order_info o left join shp_users u on u.user_id = o.user_id where merchant_ids='".$muid."' and is_separate = 0 $where ";
        $count = D()->query($sql)->result();
        $pager->setTotalNum($count);
        $sql = "SELECT o.*, IFNULL(u.nick_name,'') as nick_name FROM shp_order_info o left join shp_users u on u.user_id = o.user_id where o.merchant_ids='".$muid."' and is_separate = 0 $where $orderby  limit {$pager->start},{$pager->pagesize}";
        $orders = D()->query($sql)->fetch_array_all();
        foreach ($orders as &$order){
            self::rebuild_order_info($order);
        }
        $pager->setResult($orders);
    }

    /**
     * 拿到订单详情数据
     * @param unknown $order_id
     */
    static function getOrderDetail($order_id){
        $sql = "SELECT o.*, IFNULL(u.nick_name,'') as nick_name FROM shp_order_info o left join shp_users u on u.user_id = o.user_id where o.order_id=$order_id ";
        $order = D()->query($sql)->fetch_array();
        self::rebuild_order_info($order);
        return $order;
    }
    
    static function rebuild_order_info(&$order){
        $order['add_time'] = date('Y-m-d H:i', simphp_gmtime2std($order['add_time']));
        $order['order_status_text'] = Fn::get_order_text($order['pay_status'], $order['shipping_status'], $order['order_status']);
        $order['actual_order_amount'] = Order::get_actual_order_amount($order);
    }
    
    
    /**
     * 根据从Order里面获取的商品列表及商家信息
     * 组装成  商家/商品列表 集合
     * @param unknown $order_id
     */
    static function getOrderItems($order_id) {
        $order_goods = Order::getOrderItems($order_id);
        $merchant = null;
        foreach ($order_goods as $item){
            if($merchant != null){
                array_push($merchant['goods'], $item);
            }else{
                $merchant = $item;
                $merchant['goods'] = array($item);
            }
        }
        return $merchant;
    }
    
    /**
     * 根据订单里面的区域ID获取区域组合信息
     * @param unknown $order_id
     */
    static function getOrderRegion(array $regionIds) {
        if(!$regionIds || count($regionIds) == 0){
            return '';
        }
        $regionIds = join(',', $regionIds);
        $sql = "select region_name from shp_region where region_id in ($regionIds) order by region_id";
        $arr = D()->query($sql)->fetch_array_all();
        $region = "";
        foreach ($arr as $item){
            $region .= $item['region_name'];
        }
        return $region;
    }
    
    /**
     * 构建物流公司选择列表
     * @param number $selectedId
     * @return string|number
     */
    static function buildShippingDropdown($selectedId = 0){
        $ret = Order::get_shipping_list();
        $select = "";
        foreach ($ret as $ship){
            $selected = "";
            if($selectedId){
                if($selectedId == $ship['shipping_id']){
                    $selected = "selected";
                }
            }
            $select .= "<option value=".$ship['shipping_id']." ".$selected.">".$ship['shipping_name']."</option>";
        }
        return $select;
    }
    
    /**
     * 订单状态是否已经失效
     * @param unknown $order_status
     * @return boolean
     */
    static function isOrderValid($order_status){
        return !($order_status == OS_CANCELED || $order_status == OS_INVALID);
    }
    
}

/*----- END FILE: Order_Model.php -----*/