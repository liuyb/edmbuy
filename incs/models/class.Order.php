<?php

defined('IN_SIMPHP') or die('Access Denied');

/**
 * 
 * @author Jean
 *
 */
class Order extends StorageNode{
    
    protected static function meta() {
        return array(
            'table' => '`shp_order_info`',
            'key'   => 'uid',   //该key是应用逻辑的列，当columns为array时，为columns的key，否则，则要设成实际存储字段
            'columns' => array( //columns同时支持'*','实际存储字段列表串',映射数组 三种方式
                'orderid'           => 'order_id',
                'ordersn'           => 'order_sn',
                'pay_trade_no'      => 'pay_trade_no',
                'userid'            => 'user_id',
                'order_status'      => 'order_status',
                'shipping_status'   => 'shipping_status',
                'pay_status'        => 'pay_status',
                'consignee'         => 'consignee',
                'country'           => 'country',
                'province'          => 'province',
                'city'              => 'city',
                'address'           => 'address',
                'zipcode'           => 'zipcode',
                'tel'               => 'tel',
                'mobile'            => 'mobile',
                'email'             => 'email',
                'besttime'          => 'best_time',
                'sign_building'     => 'sign_building',
                'postscript'        => 'postscript',
                'shippingid'        => 'shipping_id',
                'shippingname'      => 'shipping_name',
                'payid'             => 'pay_id',
                'payname'           => 'pay_name',
                'howoos'            => 'how_oos',
                'howsurplus'        => 'how_surplus',
                'packname'          => 'pack_name',
                'inv_payee'         => 'inv_payee',
                'inv_content'       => 'inv_content',
                'goods_amount'      => 'goods_amount',
                'shipping_fee'      => 'shipping_fee',
                'insure_fee'        => 'insure_fee',
                'pay_fee'           => 'pay_fee',
                'pack_fee'          => 'pack_fee',
                'card_fee'          => 'card_fee',
                'money_paid'        => 'money_paid',
                'surplus'           => 'surplus',
                'integral'          => 'integral',
                'integral_money'    => 'integral_money',
                'bonus'             => 'bonus',
                'order_amount'      => 'order_amount',
                'from_ad'           => 'from_ad',
                'referer'           => 'referer',
                'add_time'          => 'add_time',
                'confirm_time'      => 'confirm_time',
                'paytime'           => 'pay_time',
                'shipping_time'     => 'shipping_time',
                'shipping_confirm_time'     => 'shipping_confirm_time',
                'packid'            => 'pack_id',
                'cardid'            => 'card_id',
                'bonusid'           => 'bonus_id',
                'invoiceno'         => 'invoice_no',
                'extension_code'    => 'extension_code',
                'extension_id'      => 'extension_id',
                'tobuyer'           => 'to_buyer',
                'paynote'           => 'pay_note',
                'agencyid'          => 'agency_id',
                'invtype'           => 'inv_type',
                'tax'               => 'tax',
                'isseparate'        => 'is_separate',
                'parentid'          => 'parent_id',
                'discount'          => 'discount',
                'paydata1'          => 'pay_data1',
                'paydata2'          => 'pay_data2'
            ));
    }
    
}

?>