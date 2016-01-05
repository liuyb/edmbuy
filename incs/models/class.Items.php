<?php
/**
 * Item共用Model
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Items extends StorageNode {
	
	protected static function meta() {
		return array(
				'table' => '`shp_goods`',
				'key'   => 'item_id',   //该key是应用逻辑的列，当columns为array时，为columns的key，否则，则要设成实际存储字段
				'columns' => array( //命名特点：'goods_%s'=>'item_%s'，其他不变
					'item_id'     => 'goods_id',
					'user_id'     => 'user_id',
					'cat_id'      => 'cat_id',
					'origin_place_id' => 'origin_place_id',
					'item_sn'     => 'goods_sn',
					'item_name'   => 'goods_name',
					'item_name_style' => 'goods_name_style',
					'click_count'     => 'click_count',
					'collect_count'   => 'collect_count',
					'paid_order_count'=> 'paid_order_count',
					'brand_id'        => 'brand_id',
					'provider_name'   => 'provider_name',
					'item_number'     => 'goods_number',
					'item_weight'     => 'goods_weight',
					'market_price'    => 'market_price',
					'shop_price'      => 'shop_price',
					'income_price'    => 'income_price',
					'promote_price'   => 'promote_price',
					'promote_start_date' => 'promote_start_date',
					'promote_end_date'   => 'promote_end_date',
					'warn_number'     => 'warn_number',
					'booking_days'    => 'booking_days',
					'expiry_date'     => 'expiry_date',
					'keywords'        => 'keywords',
					'item_brief'      => 'goods_brief',
					'item_desc'       => 'goods_desc',
					'item_thumb'      => 'goods_thumb',
					'item_img'        => 'goods_img',
					'original_img'    => 'original_img',
					'is_real'         => 'is_real',
					'extension_code'  => 'extension_code',
					'is_on_sale'      => 'is_on_sale',
					'is_alone_sale'   => 'is_alone_sale',
					'is_shipping'     => 'is_shipping',
					'integral'        => 'integral',
					'add_time'        => 'add_time',
					'sort_order'      => 'sort_order',
					'is_delete'       => 'is_delete',
					'is_best'         => 'is_best',
					'is_new'          => 'is_new',
					'is_hot'          => 'is_hot',
					'is_promote'      => 'is_promote',
					'bonus_type_id'   => 'bonus_type_id',
					'last_update'     => 'last_update',
					'item_type'       => 'goods_type',
					'seller_note'     => 'seller_note',
					'give_integral'   => 'give_integral',
					'rank_integral'   => 'rank_integral',
					'suppliers_id'    => 'suppliers_id',
					'is_check'        => 'is_check',
				)
		);
	}
	
	protected $siteurl = '';
	
	/**
	 * Constructor
	 * @param int|string $id
	 */
	public function __construct($id = NULL) {
		parent::__construct($id);
		$this->siteurl = C('env.site.mobile');
	}
	
	/**
	 * add click count
	 */
	public function add_click_count($inc = 1) {
		D()->raw_query("UPDATE ".$this->table()." SET `click_count`=`click_count`+%d WHERE `goods_id`=%d", $inc, $this->id);
		return D()->affected_rows();
	}
	
	/**
	 * return current item url
	 * @param string $spm
	 * @return string
	 */
	public function url($spm = '') {
		return $this->siteurl . "/item/".$this->id.($spm ? "?spm={$spm}" : '');
	}
	
	/**
	 * full the img path
	 * @param string $img_path
	 * @return string
	 */
	static function imgurl($img_path) {
		static $urlpre;
		if (!isset($urlpre)) $urlpre = C('env.site.shop');
		$img_path = Media::path($img_path);
		return preg_match('/^http(s?):\/\//i', $img_path) ? $img_path : ($urlpre.$img_path);
	}
	
	/**
	 * get item attributes
	 * @param integer $item_id
	 * @return array
	 */
	static function attrs($item_id) {
		$sql = "SELECT ga.*,a.attr_name
FROM `shp_goods_attr` AS ga INNER JOIN `shp_attribute` AS a ON ga.attr_id=a.attr_id
WHERE ga.goods_id=%d
ORDER BY ga.attr_id ASC,ga.goods_attr_id ASC";
		$rows = D()->query($sql, $item_id)->fetch_array_all();
		return $rows;
	}
	
}
 
/*----- END FILE: class.Items.php -----*/