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
                'merchant_id' => 'merchant_id'
            )
        );
    }
    
}

