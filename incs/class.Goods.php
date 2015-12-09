<?php
/**
 * 与Goods相关常用方法
 *
 * @author afar<afarliu@163.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Goods {
  
  static function goods_url($goods_id, $with_prefix = FALSE) {
    return U('item/'.$goods_id,'',$with_prefix);
  }
  
  static function goods_picurl($goods_pic) {
    static $urlpre;
    if (!isset($urlpre)) $urlpre = C('env.site.shop').'/';
    return $urlpre.$goods_pic;
  }
  
  static function getBrandInfo($brand_id) {
    $row = D()->from(ectable('brand'))->where(['brand_id'=>intval($brand_id)])->select()->get_one();
    return $row;
  }
  
  static function getBrandList() {
    $list = D()->from(ectable('brand'))->where(['is_show'=>1])->order_by('`sort_order` ASC','`brand_name` ASC')->select()->fetch_array_all();
    return $list;
  }
  
  static function getCategoryName($cat_id) {
    $cat_id = intval($cat_id);
    if (empty($cat_id)) return false;
    $cat_name = D()->from(ectable('category'))->where(['cat_id'=>$cat_id])->select('cat_name')->result();
    return $cat_name;
  }
  
  static function getCategoryInfo($cat_id = 0, $just_show = TRUE, $just_ret_id = FALSE) {
    $where = ['cat_id' => intval($cat_id)];
    if ($just_show) {
      $where['is_show'] = 1;
    }
    $ret   = D()->from(ectable('category'))->where($where)->select("`cat_id`,`cat_name`,`parent_id`,`is_show`")->get_one();
    if ($just_ret_id && !empty($ret)) {
      return $ret['cat_id'];
    }
    return $ret;
  }
  
  static function getParentCatesChain($cat_id, Array &$output = []) {
    $currCateInfo = self::getCategoryInfo($cat_id, FALSE, FALSE);
    if (!empty($currCateInfo)) {
      if (0!=$currCateInfo['parent_id']) {
        self::getParentCatesChain($currCateInfo['parent_id'], $output);
      }
      $output[] = $currCateInfo;
    }
    return $output;
  }

  static function getCategory($parent_id = 0, $just_id = FALSE) {
    $ret = D()->from(ectable('category'))->where("`parent_id`=%d AND `is_show`=1", $parent_id)->order_by("`sort_order` ASC")
              ->select("`cat_id`,`cat_name`,`parent_id`,`is_show`")->fetch_array_all();
    if ($just_id && !empty($ret)) {
      foreach ($ret AS &$it) {
        $it = $it['cat_id'];
      }
    }
    return $ret;
  }
  
  static function getChildCategoryIds($parent_id = 0, Array &$output = array()) {
    $child_ids_cur = self::getCategory($parent_id, TRUE);
    if (!empty($child_ids_cur)) {
      $output = array_merge($output,$child_ids_cur);
      foreach ($child_ids_cur AS $child_id) {
        self::getChildCategoryIds($child_id, $output);
      }
    }
    return $output;
  }
  
  static function getGoodsList($type = '', $order = '', $start = 0, $limit = 10, Array $extra = array()) {
  
    $ectb = ectable('goods');
    $ectb_coll = ectable('collect_goods');
  
    $zonghe_order = '';
    if (''==$order||'zonghe'==$order) { //综合排序，算法：zonghe_order = (click_count * 1 + collect_count * 100 + paid_order_count * 1000)
      $zonghe_order = ",(g.click_count * 1 + g.collect_count * 100 + g.paid_order_count * 1000) AS zonghe_order";
    }
  
    $user_id= $GLOBALS['user']->ec_user_id;
    $user_id= intval($user_id);
    $fields = "g.`goods_id`,g.`cat_id`,g.`goods_sn`,g.`goods_name`,g.`click_count`,g.`collect_count`,g.`paid_order_count`,g.`brand_id`,g.`goods_number`,g.`market_price`,g.`shop_price`,g.`goods_thumb`,g.`goods_img`,g.`add_time`,g.`last_update`{$zonghe_order}";
    $fields.= ",IF(cg.goods_id is NULL, 0, 1) AS collected";
    $sqlpre = "SELECT {$fields} FROM {$ectb} g LEFT JOIN {$ectb_coll} cg ON cg.user_id={$user_id} AND g.goods_id=cg.goods_id WHERE g.`is_on_sale`=1 AND g.`goods_img`<>''";
    $ret    = [];
  
    if ('new_arrival'==$type) { //新品
      $sqlpre .= " AND g.`is_new`=1";
    }
    else { //按条件查询
  
      // 条件查询
      $cat_id_in = '';
      if (isset($extra['cat_ids']) && !empty($extra['cat_ids'])) {
        if (is_array($extra['cat_ids'])) {
          $cat_id_in = implode(',', $extra['cat_ids']);
          $sqlpre .= " AND g.`cat_id` IN({$cat_id_in})";
        }
        else {
          $sqlpre .= " AND g.`cat_id`=".$extra['cat_ids'];
        }
      }
      if (isset($extra['brand_id']) && !empty($extra['brand_id'])) {
        $sqlpre .= " AND g.`brand_id`=".$extra['brand_id'];
      }
      if (isset($extra['price_from']) && !empty($extra['price_from'])) {
        $sqlpre .= " AND g.`shop_price`>=".$extra['price_from'];
      }
      if (isset($extra['price_to']) && !empty($extra['price_to'])) {
        $sqlpre .= " AND g.`shop_price`<=".$extra['price_to'];
      }
  
    }
  
    // 排序
    $sql    = '';
    if (''==$order||'zonghe'==$order) { //综合排序
      $sql  = $sqlpre . " ORDER BY `zonghe_order` DESC";
    }
    elseif ('click'==$order) { //按点击数
      $sql  = $sqlpre . " ORDER BY g.`click_count` DESC";
    }
    elseif ('collect'==$order) { //按收藏数
      $sql  = $sqlpre . " ORDER BY g.`collect_count` DESC";
    }
    elseif ('paid'==$order) { //按订单数
      $sql  = $sqlpre . " ORDER BY g.`paid_order_count` DESC";
    }
    elseif ('price_low2top'==$order) { //价格从低到高
      $sql  = $sqlpre . " ORDER BY g.`shop_price` ASC";
    }
    elseif ('price_top2low'==$order) { //价格从高到低
      $sql  = $sqlpre . " ORDER BY g.`shop_price` DESC";
    }
    else { //默认按添加时间倒排
      $sql  = $sqlpre . " ORDER BY g.`add_time` DESC";
    }
  
    if (''!=$sql) {
      $sql.= " LIMIT %d, %d";
      $ret = D()->raw_query($sql,$start,$limit)->fetch_array_all();
    }
  
    if (!empty($ret)) {
      $purl = C('env.site.shop').'/';
      foreach ($ret AS &$it) {
        $it['goods_thumb'] = $purl . $it['goods_thumb'];
        $it['goods_img']   = $purl . $it['goods_img'];
      }
    }
  
    return $ret;
  }
  
  static function getGoodsInfo($goods_id, Array $ctrl = array('is_on_sale'=>1,'goods_img'=>1)) {
    if (empty($goods_id) || !is_numeric($goods_id)) {
      return FALSE;
    }
    $ectb  = ectable('goods');
    $where_ctrl = '';
    if (!empty($ctrl)) {
      if (isset($ctrl['is_on_sale']) && $ctrl['is_on_sale']) {
        $where_ctrl .= " AND `is_on_sale`=1";
      }
      if (isset($ctrl['goods_img']) && $ctrl['goods_img']) {
        $where_ctrl .= " AND `goods_img`<>''";
      }
    }
    $sql   = "SELECT * FROM {$ectb} WHERE `goods_id`=%d {$where_ctrl}";
    $ret   = D()->raw_query($sql,$goods_id)->get_one();
    return $ret;
  }
  
  static function getGoodsGallery($goods_id) {
    if (empty($goods_id) || !is_numeric($goods_id)) {
      return FALSE;
    }
    $ectb = ectable('goods_gallery');
    $sql  = "SELECT * FROM {$ectb} WHERE `goods_id`=%d ORDER BY `img_id` ASC";
    $ret  = D()->raw_query($sql,$goods_id)->fetch_array_all();
    return $ret;
  }
  
  static function getUserCartNum($userid_or_sessid = NULL, $target_goods_id = NULL) {
    if (is_null($userid_or_sessid)) $userid_or_sessid = $GLOBALS['user']->ec_user_id;
    $ectb = ectable('cart');
    $where= self::getCartOwnerSql($userid_or_sessid);
    if ($target_goods_id) {
      $where .= " AND `goods_id`=%d";
    }
    $sql  = "SELECT SUM(`goods_number`) AS num FROM {$ectb} WHERE {$where}";
    $ret  = D()->raw_query($sql,$userid_or_sessid,$target_goods_id)->result();
    return $ret;
  }
  
  static function getUserCart($userid_or_sessid = NULL) {
    if (is_null($userid_or_sessid)) $userid_or_sessid = $GLOBALS['user']->ec_user_id;
    $ectb = ectable('cart');
    $where= self::getCartOwnerSql($userid_or_sessid);
    $sql  = "SELECT * FROM {$ectb} WHERE {$where} ORDER BY `rec_id` DESC";
    $ret  = D()->raw_query($sql,$userid_or_sessid)->fetch_array_all();
    if (!empty($ret)) {
      foreach ($ret AS &$g) {
        $g['goods_url']   = self::goods_url($g['goods_id']);
        $g['goods_thumb'] = self::goods_picurl($g['goods_thumb']);
        $g['goods_img']   = self::goods_picurl($g['goods_img']);
      }
    }
    return $ret;
  }
  
  static function getCartOwnerSql($userid_or_sessid) {
    $where= '';
    if (strlen($userid_or_sessid) > 10) { //$userid_or_sessid is session id
      $where = "`session_id`='%s'";
    }
    else { //$userid_or_sessid is user_id
      $where = "`user_id`=%d";
    }
    return $where;
  }
  
  static function checkCartGoodsExist($userid_or_sessid, $goods_id) {
    $ectb = ectable('cart');
    $where= self::getCartOwnerSql($userid_or_sessid);
    $sql  = "SELECT `rec_id` FROM {$ectb} WHERE {$where} AND `goods_id`=%d";
    $rec_id = D()->raw_query($sql,$userid_or_sessid, $goods_id)->result();
    return $rec_id;
  }
  
  /**
   * 改变购物车中商品的购买数量
   * 
   * @param integer $userid_or_sessid
   * @param integer $goods_id
   * @param integer $inc
   * @param boolean $is_cart_rec_id , when $is_cart_rec_id is true,  $goods_id indicating the cart record id
   * @param boolean $is_fixed_value , when $is_fixed_value is true, $inc is a fixed value, not an increment
   */
  static function changeCartGoodsNum($userid_or_sessid, $goods_id, $inc = 1, $is_cart_rec_id = false, $is_fixed_value = false) {
    $ectb = ectable('cart');
    if ($is_cart_rec_id) {
      $where = "`rec_id`=%d";
    }
    else {
      $where = "`goods_id`=%d AND ".self::getCartOwnerSql($userid_or_sessid);
    }
    
    $inc = intval($inc);
    $setpart = "`goods_number`=`goods_number`+{$inc}";
    if ($is_fixed_value) {
      $setpart = "`goods_number`={$inc}";
    }
    $sql  = "UPDATE {$ectb} SET {$setpart} WHERE {$where}";
    D()->raw_query($sql, $goods_id, $userid_or_sessid);
    return D()->affected_rows();
  }
  
  /**
   * 添加商品到购物车
   * 
   * @param $goods_id integer
   * @param $num integer
   * @param $user_id integer
   * @return array
   *   ['code' => >0, 'msg' => '添加成功']        //这时ret['code']即rec_id
   *   ['code' => -1, 'msg' => '对应商品不存在']
   *   ['code' => -2, 'msg' => '商品库存不足']
   *   ['code' =>-10, 'msg' => '添加失败']
   */
  static function addToCart($goods_id, $num = 1, $user_id = NULL) {
    if (!$user_id) $user_id = $GLOBALS['user']->ec_user_id;
    
    $ret = ['code' => 0, 'msg' => '添加成功'];
    $goods_info = self::getGoodsInfo($goods_id, []);
    if (!empty($goods_info)) {
      $sess_id = session_id();
      if (!Member::checkECUserExist($user_id)) {
        $user_id = 0;
      }
      
      $userid_or_sessid = $user_id ?  : $sess_id;
      $num = $num > 0 ? intval($num) : 1;
      if ($num > $goods_info['goods_number'] || self::getUserCartNum($userid_or_sessid,$goods_id)>=$goods_info['goods_number']) {
        $ret = ['code' => -2, 'msg' => '商品库存不足'];
        return $ret;
      }
      $cart_rec_id = self::checkCartGoodsExist($userid_or_sessid, $goods_id);
      if ($cart_rec_id) { //商品已经在购物车中存在，则直接将购买数+1
        if (self::changeCartGoodsNum($userid_or_sessid, $cart_rec_id, $num, true, false)) {
          $ret['code'] = $cart_rec_id;
          $ret['added_num'] = $num;
        }
        else {
          $ret = ['code' => -10, 'msg' => '添加失败'];
        }
        return $ret;
      }
      else { //商品没在购物车中，需新加入
        $ecdata = [
          'user_id'      => $user_id,
          'session_id'   => $sess_id,
          'goods_id'     => $goods_id,
          'goods_sn'     => $goods_info['goods_sn'],
          'product_id'   => 0,
          'goods_name'   => $goods_info['goods_name'],
          'market_price' => $goods_info['market_price'],
          'goods_price'  => $goods_info['shop_price'],
          'goods_number' => $num,
          'goods_thumb'  => $goods_info['goods_thumb'],
          'goods_img'    => $goods_info['goods_img'],
          'goods_attr'   => '',
          'is_real'      => $goods_info['is_real'],
          'extension_code'=>$goods_info['extension_code'],
          'parent_id'    => 0,
          'rec_type'     => 0,
          'is_gift'      => 0,
          'is_shipping'  => $goods_info['is_shipping'],
          'can_handsel'  => 0,
          'goods_attr_id'=> '',
        ];
        $cart_rec_id = D()->insert(ectable('cart'), $ecdata, 1, TRUE);
        if ($cart_rec_id) {
          $ret['code'] = $cart_rec_id;
          $ret['added_num'] = $num;
        }
        else {
          $ret = ['code' => -10, 'msg' => '添加失败'];
        }
        return $ret;
      }
    }
    else {
      $ret = ['code' => -1, 'msg' => '对应商品不存在'];
    }
    return $ret;
  }
  
  /**
   * 删除购物车中的商品
   * 
   * @param $rec_ids mixed(array or integer)
   * @param $user_id
   * @return array
   *   ['code'=>  0,'msg'=>'没有要删除的记录']
   *   ['code'=> >0,'msg'=>'删除成功']
   *   ['code'=> -1,'msg'=>'删除失败']
   */
  static function deleteCartGoods($rec_ids, $user_id) {
    $ret = ['code'=>0,'msg'=>'没有要删除的记录'];
    if (empty($rec_ids)) {
      return $ret;
    }
    if (!is_array($rec_ids) && $rec_ids!=='all') {//单条记录方式
      $rec_ids = [$rec_ids];
    }
    
    $ectb = ectable('cart');
    $where_user = self::getCartOwnerSql($user_id);
    if (is_string($rec_ids) && $rec_ids=='all') {
      $where_ids = "1";
    }
    else {
      $where_ids  = "`rec_id` IN(".implode(",", $rec_ids).")";
    }
    $sql  = "DELETE FROM {$ectb} WHERE {$where_ids} AND {$where_user}";
    D()->raw_query($sql,$user_id);
    $effrows = D()->affected_rows();
    if ($effrows) {
      $ret = ['code'=>$effrows,'msg'=>'删除成功'];
    }
    else {
      $ret = ['code'=>-1,'msg'=>'删除失败'];
    }
    return $ret;
  }
  
  static function getCartsGoods($cart_rec_ids, $userid_or_sessid = NULL, &$total_price = NULL) {
    if (!is_array($cart_rec_ids)) {
      $cart_rec_ids = [$cart_rec_ids];
    }
    if (empty($cart_rec_ids)) {
      return [];
    }
    
    if (!isset($userid_or_sessid)) {
      $userid_or_sessid = $GLOBALS['user']->ec_user_id;
      if (!$userid_or_sessid) $userid_or_sessid = session_id();
    }
    
    $ectb = ectable('cart');
    $where_user = self::getCartOwnerSql($userid_or_sessid);
    $where_ids  = "'".implode("','", $cart_rec_ids)."'";
    $sql = "SELECT * FROM {$ectb} WHERE `rec_id` IN({$where_ids}) AND {$where_user}";
    $ret = D()->raw_query($sql,$userid_or_sessid)->fetch_array_all();
    if (!empty($ret)) {
      $total_price = 0;
      foreach ($ret As &$g) {
        $g['goods_url']   = self::goods_url($g['goods_id']);
        $g['goods_thumb'] = self::goods_picurl($g['goods_thumb']);
        $g['goods_img']   = self::goods_picurl($g['goods_img']);
        $total_price += $g['goods_price']*$g['goods_number'];
      }
    }
    return empty($ret) ? [] : $ret;
  }
  
  static function getRegionName($region_id) {
    $ectb = ectable('region');
    $sql  = "SELECT `region_name` FROM {$ectb} WHERE `region_id`=%d";
    $row  = D()->raw_query($sql,$region_id)->get_one();
    if (!empty($row)) return $row['region_name'];
    return false;
  }
  
  /**
   * 根据地区名字来查找地区id
   * 
   * @param string $region_name 地区名字
   * @param integer $region_type 地区类型：0:国家，1:省份，2:市级，3:区级
   * @param integer $parent_id 当地区类型是市级和区级时，需要$parent_id来区分，因为有可能是重名的
   */
  static function getRegionId($region_name, $region_type = 1, $parent_id = 0) {
    $ectb = ectable('region');
    $sql  = "SELECT `region_id` FROM {$ectb} WHERE `region_type`=%d AND ";
    if (0===$region_type) { //国家需精确匹配
      $sql .= "`region_name`='%s'";
    }
    elseif (1===$region_type) { //身份也是精确匹配,但是需要将末尾可能存在的"省"字去掉
      $region_name = preg_replace('/(省$)/u', '', $region_name); //先把可能存在末尾的"省"字去掉
      $sql .= "`region_name`='%s'";
    }
    else { //市级和区级，名称可能带"市"或"区"，也可能不带
      $w = 2==$region_type ? '市' : '区';
      $region_name = preg_replace('/('.$w.'$)/u', '', $region_name); //先把可能存在末尾的"市"或"区"字去掉
      $sql .= "`region_name` like '%s%' AND `parent_id`=%d"; //市和区在全国范围内都可能有重名的，需要parent_id来区分
    }
    $row = D()->raw_query($sql,$region_type,$region_name,$parent_id)->get_one();
    if (!empty($row)) return $row['region_id'];
    return 0;
  }
  
  /**
   * 获取用户收货地址列表
   * 
   * @param integer $ec_user_id
   * @return array
   */
  static function getUserAddress($ec_user_id) {
    $ectb = ectable('user_address');
    $sql  = "SELECT * FROM {$ectb} WHERE `user_id`=%d ORDER BY `address_id` DESC";
    $ret  = D()->raw_query($sql,$ec_user_id)->fetch_array_all();
    if (!empty($ret)) {
      foreach ($ret AS &$addr) {
        $contact_phone = !empty($addr['tel']) ? $addr['tel'] : $addr['mobile']; //遵循ecshop习惯，优先选择tel作为联系电话
        
        //填充地区名称
        if (empty($addr['country_name']) && !empty($addr['country'])) {
          $addr['country_name'] = self::getRegionName($addr['country']);
        }
        if (empty($addr['province_name']) && !empty($addr['province'])) {
          $addr['province_name'] = self::getRegionName($addr['province']);
          $addr['province_name'].= '省';
        }
        if (empty($addr['city_name']) && !empty($addr['city'])) {
          $addr['city_name'] = self::getRegionName($addr['city']);
          if (!preg_match('/(市$)/u', $addr['city_name'])) {
            $addr['city_name'].= '市';
          }
        }
        if (empty($addr['district_name']) && !empty($addr['district'])) {
          $addr['district_name'] = self::getRegionName($addr['district']);
          if (!preg_match('/(区$)/u', $addr['district_name'])) {
            $addr['district_name'].= '区';
          }
        }
        
        //添加额外属性，便于前端显示
        $addr['contact_phone']  = $contact_phone;
        $addr['show_consignee'] = $addr['consignee']."（{$contact_phone}）";
        $addr['show_address']   = $addr['province_name'].$addr['city_name'].$addr['district_name'].$addr['address'];
      }
    }
    return empty($ret) ? [] : $ret;
  }
  
  /**
   * 获取指定地址id的地址信息
   * 
   * @param integer $address_id
   * @return array
   */
  static function getAddressInfo($address_id) {
    $ectb = ectable('user_address');
    $sql  = "SELECT * FROM {$ectb} WHERE `address_id`=%d";
    $row  = D()->raw_query($sql,$address_id)->get_one();
    return !empty($row) ? $row : [];
  }
  
  /**
   * 保存用户收货地址
   * 
   * @param array $data 要保存的字段数据
   * @param integer $address_id 地址id，当为0时表示新插入，否则表示更新
   * @return boolean
   */
  static function saveUserAddress(Array $data, $address_id = 0) {
    
    //补全地区信息
    if (empty($data['country']) && !empty($data['country_name'])) {
      $data['country'] = self::getRegionId($data['country_name'], 0);
    }
    if (empty($data['province']) && !empty($data['province_name'])) {
      $data['province'] = self::getRegionId($data['province_name'], 1);
    }
    if (empty($data['city']) && !empty($data['city_name'])) {
      $data['city'] = self::getRegionId($data['city_name'], 2, $data['province']);
    }
    if (empty($data['district']) && !empty($data['district_name'])) {
      $data['district'] = self::getRegionId($data['district_name'], 3, $data['city']);
    }
    
    if (!$address_id) { //新插入
      $address_id = D()->insert(ectable('user_address'), $data, true, true);
    }
    else { //编辑
      D()->update(ectable('user_address'), $data, ['address_id'=>$address_id], true);
    }
    
    return $address_id;
  }
  
  /**
   * 获取支付方式信息
   * 
   * @param integer $pay_id
   * @return array
   */
  static function getPaymentInfo($pay_id) {
    $ectb = ectable('payment');
    $sql  = "SELECT * FROM {$ectb} WHERE `pay_id`=%d AND `enabled`=1";
    $row  = D()->raw_query($sql,$pay_id)->get_one();
    return !empty($row) ? $row : [];
  }
  
  /**
   * 获取配送方式信息
   * 
   * @param integer $shipping_id
   * @return array
   */
  static function getShippingInfo($shipping_id) {
    $ectb = ectable('shipping');
    $sql  = "SELECT * FROM {$ectb} WHERE `shipping_id`=%d AND `enabled`=1";
    $row  = D()->raw_query($sql,$shipping_id)->get_one();
    return !empty($row) ? $row : [];
  }
  
  /**
   * 改变商品表库存
   * 
   * @param integer $goods_id
   * @param integer $chnum, 大于0时增加库存，小于0时减少库存
   * @return boolean
   */
  static function changeGoodsStock($goods_id, $chnum = 1) {
    $ectb_goods = ectable('goods');
    $chnum = intval($chnum);
    D()->raw_query("UPDATE {$ectb_goods} SET `goods_number`=`goods_number`+%d WHERE `goods_id`=%d", $chnum, $goods_id);
    if (D()->affected_rows()) {
      return true;
    }
    return false;
  }
  
  /**
   * 获取一个订单下的商品列表
   * 
   * @param integer $order_id
   * @return array
   */
  static function getOrderGoods($order_id) {
    if (empty($order_id)) return [];
    
    $ectb_goods = ectable('goods');
    $ectb_order_goods = ectable('order_goods');
    
    $sql = "SELECT og.*,g.`goods_thumb` FROM {$ectb_order_goods} og INNER JOIN {$ectb_goods} g ON og.`goods_id`=g.`goods_id` WHERE og.`order_id`=%d ORDER BY og.`rec_id` DESC";
    $order_goods = D()->raw_query($sql, $order_id)->fetch_array_all();
    if (!empty($order_goods)) {
      foreach ($order_goods AS &$g) {
        $g['goods_url']   = self::goods_url($g['goods_id']);
        $g['goods_thumb'] = self::goods_picurl($g['goods_thumb']);
      }
    }
    else {
      $order_goods = [];
    }
    
    return $order_goods;
  }
  
  /**
   * 获取订单列表
   * 
   * @param integer $user_id
   * @return array
   */
  static function getOrderList($user_id) {
    if (empty($user_id)) return [];
    
    $start = 0;
    $limit = 50;
    
    $ectb_order = ectable('order_info');
    $ectb_goods = ectable('goods');
    $ectb_order_goods = ectable('order_goods');
    
    $sql = "SELECT * FROM {$ectb_order} WHERE `user_id`=%d ORDER BY `order_id` DESC LIMIT %d,%d";
    $orders = D()->raw_query($sql, $user_id, $start, $limit)->fetch_array_all();
    if (!empty($orders)) {
      foreach ($orders AS &$ord) {
        $ord['show_status_html'] = self::genStatusHtml($ord);
        $ord['order_goods'] = [];
        $sql = "SELECT og.*,g.`goods_thumb` FROM {$ectb_order_goods} og INNER JOIN {$ectb_goods} g ON og.`goods_id`=g.`goods_id` WHERE og.`order_id`=%d ORDER BY og.`rec_id` DESC";
        $order_goods = D()->raw_query($sql, $ord['order_id'])->fetch_array_all();
        if (!empty($order_goods)) {
          foreach ($order_goods AS &$g) {
            $g['goods_url']   = self::goods_url($g['goods_id']);
            $g['goods_thumb'] = self::goods_picurl($g['goods_thumb']);
          }
          $ord['order_goods'] = $order_goods;
        }
      }
    }
    else {
      $orders = [];
    }
    return $orders;
  }
  
  /**
   * 生成订单各种状态显示html
   * 
   * @param array $order
   * @return string
   */
  static function genStatusHtml(Array &$order) {
    
    $html = '';
    
    $order['active_order'] = 0; //便于区分订单显示样式
    $br = '<br/>';
    if (!in_array($order['order_status'], [OS_CANCELED,OS_INVALID,OS_RETURNED])) { //订单“活动中”
      $order['active_order'] = 1;
      if ($order['pay_status'] == PS_UNPAYED) { //未支付
        $html .= '<p class="order-status-txt">'.Fn::pay_status($order['pay_status']).'</p>';
        $html .= '<p class="order-status-op"><a href="javascript:;" class="btn btn-orange btn-order-topay" data-order_id="'.$order['order_id'].'">立即付款</a></p>';
        $html .= '<p class="order-status-op last"><a href="javascript:;" class="btn-order-cancel" data-order_id="'.$order['order_id'].'">取消订单</a></p>';
      }
      elseif ($order['pay_status']==PS_PAYED) { //已支付
        $html .= '<p class="order-status-txt">'.Fn::pay_status($order['pay_status']).$br.Fn::shipping_status($order['shipping_status']);
        if ($order['shipping_status']==SS_RECEIVED) {
          $html.= $br.'<span style="color:green">'.Fn::zonghe_status(CS_FINISHED).'</span>'; //订单完成
          $html.= '</p>';
          $order['active_order'] = 0;
        }
        elseif ($order['shipping_status']==SS_SHIPPED) {
          $html .= '</p><p class="order-status-op"><a href="javascript:;" class="btn btn-orange btn-ship-confirm" data-order_id="'.$order['order_id'].'">确认收货</a></p>';
        }
        else {
          $html.= '</p>';
        }
      }
      else { //支付中
        $html .= '<p class="order-status-txt">'.Fn::order_status(OS_CONFIRMED).$br.Fn::pay_status($order['pay_status']).'</p>';
      }
    }
    else {
      $html .= '<p>'.Fn::order_status($order['order_status']).'</p>';
    }
    
    return $html;
  }
  
  /**
   * 更新商品点击数
   *
   * @param integer $goods_id
   * @param integer $inc
   * @return boolean
   */
  static function addGoodsClickCnt($goods_id, $inc = 1) {
    if (!$goods_id) return false;
  
    $ectb = ectable('goods');
    D()->raw_query("UPDATE {$ectb} SET `click_count`=`click_count`+%d WHERE `goods_id`=%d", $inc, $goods_id);
    if (D()->affected_rows()==1) {
      return true;
    }
    return false;
  }
  
  /**
   * 更新商品收藏数
   *
   * @param integer $goods_id
   * @param integer $inc
   * @return boolean
   */
  static function changeGoodsCollectCnt($goods_id, $inc = 1) {
    if (!$goods_id) return false;
  
    $ectb = ectable('goods');
    D()->raw_query("UPDATE {$ectb} SET `collect_count`=`collect_count`+%d WHERE `goods_id`=%d", $inc, $goods_id);
    if (D()->affected_rows()==1) {
      return true;
    }
    return false;
  }
  
  /**
   * 更新某个商品的"订单数"
   * 
   * @param integer $goods_id
   * @return boolean
   */
  static function updateGoodsOrderCnt($goods_id) {
    $goods_id = intval($goods_id);
    if (empty($goods_id)) return false;
    
    $ectb_goods = ectable('goods');
    $ectb_order_goods = ectable('order_goods');
    $ectb_pay_log = ectable('pay_log');
    $sql =<<<HERESQL
UPDATE {$ectb_goods} g, (
				SELECT og.goods_id,COUNT(l.order_id) AS order_num
				FROM {$ectb_order_goods} og LEFT JOIN {$ectb_pay_log} l ON og.order_id=l.order_id AND l.is_paid=1
				WHERE og.goods_id={$goods_id}
				GROUP BY og.goods_id
			) pgon
SET g.paid_order_count = pgon.order_num
WHERE g.goods_id = pgon.goods_id
HERESQL;
    
    D()->raw_query($sql);
    if (D()->affected_rows() > 0) {
      return true;
    }
    return false;
  }
  
  /**
   * 更新订单下所有商品的"订单数"
   * 
   * @param integer $order_id
   * @return boolean
   */
  static function updateGoodsOrderCntByOrderid($order_id) {
    $order_id = intval($order_id);
    if (empty($order_id)) return false;
    
    $ectb_goods = ectable('goods');
    $ectb_order_goods = ectable('order_goods');
    $ectb_pay_log = ectable('pay_log');
    $sql =<<<HERESQL
UPDATE {$ectb_goods} g, (
				SELECT og.goods_id,COUNT(l.order_id) AS order_num
				FROM {$ectb_order_goods} og LEFT JOIN {$ectb_pay_log} l ON og.order_id=l.order_id AND l.is_paid=1
				WHERE og.goods_id IN(SELECT goods_id FROM {$ectb_order_goods} WHERE order_id={$order_id})
				GROUP BY og.goods_id
			) pgon
SET g.paid_order_count = pgon.order_num
WHERE g.goods_id = pgon.goods_id
HERESQL;
    
    D()->raw_query($sql);
    if (D()->affected_rows() > 0) {
      return true;
    }
    return false;
  }
  
  /**
   * 更新订单
   * 
   * @param array $updata
   * @param integer $order_id
   * @return boolean 
   */
  static function orderUpdate(Array $updata, $order_id) {
    if (!$order_id) return false;
    D()->update(ectable('order_info'), $updata, ['order_id'=>$order_id], true);
    if (D()->affected_rows()==1) {
      return true;
    }
    return false;
  }
  
  /**
   * 检查是否已经收藏
   */
  static function isCollected($goods_id, $ec_user_id) {
    
    if (!$ec_user_id) $ec_user_id = $GLOBALS['user']->ec_user_id;
    
    $rec_id = D()->from(ectable('collect_goods'))->where(['user_id'=>$ec_user_id, 'goods_id'=>$goods_id])->select('rec_id')->result();
    return $rec_id ? true : false;
    
  }
  
  /**
   * 收藏商品
   * 
   * @return string
   *   'collected'   : 之前已收藏
   *   'new_collect' : 成功新收藏
   *   'collect_fail': 收藏失败
   */
  static function goodsCollecting($goods_id, $ec_user_id) {
    
    if (!$ec_user_id) $ec_user_id = $GLOBALS['user']->ec_user_id;
    
    $ret = 'collected';
    if (!self::isCollected($goods_id, $ec_user_id)) { //未收藏
      $ins = [
        'user_id'  => $ec_user_id,
        'goods_id' => $goods_id,
        'add_time' => simphp_time(),
        'is_attention' => 0,
      ];
      $rid = D()->insert(ectable('collect_goods'), $ins, true, true);
      if ($rid) {
        self::changeGoodsCollectCnt($goods_id, 1);
        $ret= 'new_collect';
      }
      else {
        $ret= 'collect_fail';
      }
    }
    
    return $ret;
    
  }
  
  /**
   * 收藏商品
   * 
   * @param integer $rec_id
   * @return boolean
   */
  static function goodsCollectCancel($rec_id) {
    
    $ectb = ectable('collect_goods');
    $collect_goods_id = D()->from($ectb)->where(['rec_id'=>$rec_id])->select('goods_id')->result();
    if (!empty($collect_goods_id)) {
      $effrows = D()->delete($ectb, ['rec_id'=>$rec_id], true);
      if ($effrows==1) {
        self::changeGoodsCollectCnt($collect_goods_id, -1); //要减去相应的收藏数
        return true;
      }
    }
    return false;
    
  }

  /**
   * 获取用户收藏列表
   * 
   * @return array
   */
  static function getUserCollectList() {
     $user_id = $GLOBALS['user']->ec_user_id;
     if (!$user_id) return [];
     
     $ectb_collect = ectable('collect_goods');
     $ectb_goods   = ectable('goods');
     $list = D()->from("{$ectb_collect} cg INNER JOIN {$ectb_goods} g ON cg.`goods_id`=g.`goods_id`")
                ->where(['user_id'=>$user_id])
                ->order_by('cg.add_time DESC')
                ->select('cg.*','g.goods_name','g.goods_thumb')
                ->fetch_array_all();
     if (!empty($list)) {
       foreach ($list AS &$g) {
         $g['goods_url']   = self::goods_url($g['goods_id']);
         $g['goods_thumb'] = self::goods_picurl($g['goods_thumb']);
       }
       return $list;
     }
     
     return [];
     
  }
  
  /**
   * 搜索产品库，先搜索产品分类，如果为空则搜索品牌，如果再为空就搜索产品名称
   * 
   * @param string  $keyword
   * @param integer $limit
   * @return array
   */
  static function search($keyword, $limit = 10) {
    
    $result_final = [];
    $keyword = strtolower($keyword);
    
    // 先搜索分类名称
    $cat_ids = D()->from(ectable('category'))->where("`is_show`=1 AND LOWER(`cat_name`) like '%%%s%%'", $keyword)->select("`cat_id`")->fetch_column('cat_id');
    $all_cat_ids = [];
    if (!empty($cat_ids)) {
      foreach ($cat_ids AS $id) {
        $all_cat_ids = array_merge($all_cat_ids, [$id], self::getChildCategoryIds($id));
      }
    }
    $result_cat = [];
    if (!empty($all_cat_ids)) {
      $all_cat_ids = array_unique($all_cat_ids);
      $result_cat  = D()->from(ectable('goods'))->where("`cat_id` IN(%s)", implode(',', $all_cat_ids))->order_by("`add_time` DESC")->limit($limit)->select()->fetch_array_all();
    }
    
    if (empty($result_cat)) { //搜索分类产品结果为空，则继续搜索品牌
      $brand_ids = D()->from(ectable('brand'))->where("`is_show`=1 AND LOWER(`brand_name`) like '%%%s%%'", $keyword)->select("`brand_id`")->fetch_column('brand_id');
      $result_brand = [];
      if (!empty($brand_ids)) {
        $result_brand  = D()->from(ectable('goods'))->where("`brand_id` IN(%s)", implode(',', $brand_ids))->order_by("`add_time` DESC")->limit($limit)->select()->fetch_array_all();
      }
      
      if (empty($result_brand)) { //搜索品牌下的产品结果也为空，则继续搜索产品名称
        $result_final = D()->from(ectable('goods'))->where("`goods_name` like '%%%s%%'", $keyword)->order_by("`add_time` DESC")->limit($limit)->select()->fetch_array_all();
      }
      else {
        $result_final = $result_brand;
      }
    }
    else {
      $result_final = $result_cat;
    }
    
    return $result_final;
  }
  
}
 
/*----- END FILE: class.Goods.php -----*/