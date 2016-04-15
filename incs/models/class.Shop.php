<?php
defined('IN_SIMPHP') or die('Access Denied');

/**
 * 商家店铺信息表
 * @author Jean
 *
 */
class Shop extends StorageNode{
    
    protected static function meta() {
        return array(
            'table' => '`shp_shop_info`',
            'key'   => 'shop_id',
            'columns' => array(
                'shop_id'    => 'shop_id',
                'shop_name'  => 'shop_name',
                'shop_logo'  => 'shop_logo',
                'tel'  => 'tel',
                'province'  => 'province',
                'city'         => 'city',
                'district'    => 'district',
                'address'        => 'address',
                'shop_desc' => 'shop_desc',
                'business_scope' => 'business_scope',
                'shop_sign'   => 'shop_sign',
                'shop_qrcode'    => 'shop_qrcode',
                'shop_template' => 'shop_template',
                'merchant_id' => 'merchant_id',
                'add_time' => 'add_time',
                'update_time' => 'update_time'
            )
        );
    }
    
    /**
     * 经营范围列表 固定数据，采用静态缓存
     */
    static function getBusinessScope(){
        
        $data = Fn::read_static_cache('business_category_data');
        if ($data === false){
            $sql = "select cat_id,cat_name from shp_business_category order by sort_order desc ";
            $res = D()->query($sql)->fetch_array_all();
            //如果数组过大，不采用静态缓存方式
            if (count($res) <= 1000){
                Fn::write_static_cache('business_category_data', $res);
            }
        }else{
            $res = $data;
        }
        return $res;
    }
}

