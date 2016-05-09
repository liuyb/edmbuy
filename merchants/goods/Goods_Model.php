<?php
/**
 * 商品Model
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Goods_Model extends Model
{

    /**
     * 新增OR修改商品
     * @param Items $goods
     */
    static function insertOrUpdateGoods(Request $request, Items $goods)
    {
        $ret = true;
        D()->beginTransaction();
        $is_insert = false;
        if (!$goods->item_id) {
            $is_insert = true;
            $goods->add_time = simphp_gmtime();
            $goods->merchant_id = $GLOBALS['user']->uid;
            $goods->merchant_uid = $GLOBALS['user']->admin_uid;
        }
        $goods->last_update = simphp_gmtime();
        try {
            $goods->save(($is_insert ? Storage::SAVE_INSERT : Storage::SAVE_UPDATE));
            $goods_id = $is_insert ? D()->insert_id() : $goods->item_id;
            $other_cat = $request->post('other_cat', []);
            $other_cat = is_array($other_cat) ? $other_cat : [$other_cat];
            self::handle_other_cat($is_insert, $goods_id, $goods->shop_cat_id, array_unique($other_cat));

            self::handle_goods_gallery($is_insert, $goods_id, $request->post('gallery_list', []));

            self::handle_goods_attribute($is_insert, $goods_id, $request->post('attribute_list', []));

        } catch (Exception $e) {
            D()->rollback();
            $ret = false;
        }
        D()->commit();
        return $ret;
    }

    /**
     * 保存某商品的扩展分类
     *
     * @param int $goods_id
     *            商品编号
     * @param array $cat_list
     *            分类编号数组
     * @return void
     */
    private static function handle_other_cat($is_insert, $goods_id, $cat_id, array $cat_list)
    {
        array_filter($cat_list);//去空
        $add_list = $cat_list;
        if (!$is_insert) {
            /* 查询现有的扩展分类 */
            $exist_list = Goods_Atomic::get_goods_ext_category($goods_id);

            /* 删除不再有的分类 */
            $delete_list = array_diff($exist_list, $cat_list);
            if ($delete_list) {
                $sql = "DELETE FROM shp_shop_goods_cat WHERE goods_id = '$goods_id'
    	        AND cat_id " . Func::db_create_in($delete_list) . " and is_main = 0 ";
                D()->query($sql);
            }

            /* 添加新加的分类 */
            $add_list = array_diff($cat_list, $exist_list, array(
                0
            ));
        }
        //冗余主分类到扩展分类 主分类放在扩展分类前，保证主分类一定存在
        if ($cat_id) {
            if ($is_insert) {
                $sql = "INSERT IGNORE INTO shp_shop_goods_cat(goods_id, cat_id, is_main) VALUES ('$goods_id', '$cat_id', 1) ";
            } else {
                $sql = "UPDATE IGNORE shp_shop_goods_cat set cat_id = $cat_id where goods_id = $goods_id and is_main = 1 ";
            }
            D()->query($sql);
        }
        foreach ($add_list as $cat_id) {
            // 插入记录
            $sql = "INSERT IGNORE INTO shp_shop_goods_cat(goods_id, cat_id, is_main) VALUES ('$goods_id', '$cat_id', 0) ";
            D()->query($sql);
        }
    }

    /**
     * 商品相册处理
     * @param unknown $goods_id
     * @param array $gallery_list
     */
    private static function handle_goods_gallery($is_insert, $goods_id, array $gallery_list)
    {
        if (!$is_insert) {
            // 删除原数据
            Goods_Atomic::delete_goods_gallery($goods_id);
        }
        $sql = "INSERT INTO shp_goods_gallery(goods_id, img_url, img_desc, thumb_url, img_original) VALUES ";
        $batchs = [];
        foreach ($gallery_list as $gallery) {
            if (!$gallery || !$gallery['gallery_img']) {
                continue;
            }
            array_push($batchs, "('$goods_id', '".D()->escape_string($gallery['gallery_img'])."', '', '".D()->escape_string($gallery['gallery_thumb'])."',
                                '".D()->escape_string($gallery['origin_img'])."')");
        }
        if (count($batchs) > 0) {
            $batchs = implode(',', $batchs);
            $sql .= $batchs;
        }
        D()->query($sql);
    }

    //商品属性处理
    private static function handle_goods_attribute($is_insert, $goods_id, array $attribute_list)
    {
        if (!$is_insert) {
            // 删除原数据
            Goods_Atomic::delete_goods_attr($goods_id);
        }

        foreach ($attribute_list as $attr) {
            $sql = "INSERT INTO shp_goods_attr(goods_id, cat1_id,cat1_name,cat2_id,cat2_name,cat3_id,cat3_name,
                    attr1_id, attr1_value, attr2_id, attr2_value, attr3_id, attr3_value,
                    market_price, shop_price, income_price, cost_price, goods_number) " .
                "VALUES ('$goods_id', '" . self::setDefaultValueIfUnset($attr, 'cat_id1', 0) . "', '" . self::setDefaultValueIfUnset($attr, 'cat_value1', '') . "',
                        '" . self::setDefaultValueIfUnset($attr, 'cat_id2', 0) . "', '" . self::setDefaultValueIfUnset($attr, 'cat_value2', '') . "',
                        '" . self::setDefaultValueIfUnset($attr, 'cat_id3', 0) . "', '" . self::setDefaultValueIfUnset($attr, 'cat_value3', '') . "',
                        '" . self::setDefaultValueIfUnset($attr, 'attr_id1', 0) . "', '" . self::setDefaultValueIfUnset($attr, 'attr_value1', '') . "',
                        '" . self::setDefaultValueIfUnset($attr, 'attr_id2', 0) . "','" . self::setDefaultValueIfUnset($attr, 'attr_value2', '') . "',
                        '" . self::setDefaultValueIfUnset($attr, 'attr_id3', 0) . "','" . self::setDefaultValueIfUnset($attr, 'attr_value3', '') . "',
                        ".doubleval($attr['market_price']).",".doubleval($attr['shop_price']).",
                        ".doubleval($attr['income_price']).",".doubleval($attr['cost_price']).",".intval($attr['goods_number']).")";
            D()->query($sql);
        }
    }

    private static function setDefaultValueIfUnset($arr, $key, $default)
    {
        if ($arr && $key) {
            if (isset($arr[$key])) {
                return D()->escape_string($arr[$key]);
            }
        }
        return $default;
    }

    /**
     * 解析成页面需要的商品属性格式
     * array(cat_id=>1,cat_name=>商品,attrs=>array([attr1_id] => 2
     * [attr1_value] => 300g
     * [attr2_id] => 5
     * [attr2_value] => 黄色
     * [attr3_id] => 0
     * [attr3_value] =>
     * [market_price] => 9.00
     * [shop_price] => 9.00
     * [income_price] => 8.00
     * [cost_price] => 7.00
     * [goods_number] => 6))
     * @param unknown $goods_id
     */
    public static function get_goods_attrs($goods_id)
    {
        $result = Goods_Atomic::get_goods_attribute($goods_id);
        $cat_arr = [];
        foreach ($result as $item) {
            self::map_goods_attrs($cat_arr, $item, 1);
            self::map_goods_attrs($cat_arr, $item, 2);
            self::map_goods_attrs($cat_arr, $item, 3);
        }
        $ret_arr = [];
        $count = 1;
        if ($cat_arr && count($cat_arr) > 0) {
            foreach ($cat_arr as $type => $attrs) {
                $typeOBJ = explode('【~~】', $type);
                if (count($typeOBJ) == 0) {
                    continue;
                }
                //处理每个type下的重复属性
                $display_attrs = self::get_goods_select_attr($count, $attrs);
                array_push($ret_arr, array('cat_id' => $typeOBJ[0], 'cat_name' => $typeOBJ[1], 'attrs' => $attrs, 'display_attrs' => $display_attrs));
                $count++;
            }
        }
        return $ret_arr;
    }

    /**
     * 用map来构造商品属性规格
     * 商品type（颜色、重量）=> array(对应的属性列表)
     * @param unknown $cat_arr
     * @param unknown $item
     * @param unknown $index
     */
    private static function map_goods_attrs(&$cat_arr, $item, $index)
    {
        $cat_id = $item['cat' . $index . '_id'];
        $cat_name = $item['cat' . $index . '_name'];
        if ($cat_id && $cat_name) {
            $key = $cat_id . '【~~】' . $cat_name;
            if (isset($cat_arr[$key]) && $cat_arr[$key]) {
                array_push($cat_arr[$key], $item);
            } else {
                $cat_arr[$key] = [];
                array_push($cat_arr[$key], $item);
            }
        }
    }

    /**
     * 当前商品 选中的属性 唯一过滤
     * @param unknown $index
     * @param unknown $attrs
     */
    public static function get_goods_select_attr($cat_id, $attrs)
    {
        $ret = [];
        $map = [];
        foreach ($attrs as $at) {
            $at_id = $at['attr' . $cat_id . '_id'];
            $key = 'key_' . $at_id;
            if (isset($map[$key]) && $map[$key] > 0) {
                continue;
            }
            $map[$key] = '1';
            array_push($ret, array("attr_id" => $at_id, "attr_value" => $at['attr' . $cat_id . '_value']));
        }
        unset($map);
        return $ret;
    }

    /**
     * 删除指定商品
     * @param unknown $goods_id
     */
    static function batchDeleteGoods(array $goods_ids)
    {
        if (empty($goods_ids)) {
            return;
        }
        D()->beginTransaction();
        try {
            $affected = Goods_Atomic::batch_delete_goods($goods_ids);
            if ($affected) {
                Goods_Atomic::batch_delete_goods_cat($goods_ids);
                Goods_Atomic::batch_delete_goods_attr($goods_ids);
                Goods_Atomic::batch_delete_goods_gallery($goods_ids);
            }
        } catch (Exception $e) {
            D()->rollback();
        }
        D()->commit();
    }

    /**
     * 修改商品字段
     * @param array $goods_ids
     * @param unknown $field
     * @param unknown $val
     */
    static function batchUpdateGoods(array $goods_ids, $field, $val)
    {
        $merchant_id = $GLOBALS['user']->uid;
        if (empty($goods_ids)) {
            return 0;
        }
        $sql = "update shp_goods set ".D()->escape_string($field)." = ".D()->escape_string($val)." where goods_id ".Fn::db_create_in($goods_ids)." 
                and merchant_id = '%s' ";
        D()->query($sql, $merchant_id);
        return D()->affected_rows();
    }

    /**
     * 分页显示商品列表
     * @param Pager $pager
     * @param array $options
     */
    static function getPagedGoods(Pager $pager, array $options)
    {
        $muid = $GLOBALS['user']->uid;
        $where = "";
        $orderby = "";
        if ($options['goods_name']) {
            $where .= " and g.goods_name like '%%" . D()->escape_string(trim($options['goods_name'])) . "%%' ";
        }
        if ($options['start_date']) {
            $starttime = simphp_gmtime(strtotime($options['start_date'] . DAY_BEGIN));
            $where .= " and g.add_time >= $starttime ";
        }
        if ($options['end_date']) {
            $endtime = simphp_gmtime(strtotime($options['end_date'] . DAY_END));
            $where .= " and g.add_time <= $endtime ";
        }
        $groupbyWhere = $where;
        if ($options['is_sale']) {
            $where .= " and g.is_on_sale = " . (intval($options['is_sale']) - 1);
        }
        if ($options['orderby'] && $options['order_field']) {
            $od_field = D()->escape_string($options['order_field']);
            $od_by = D()->escape_string($options['orderby']);
            $orderby .= " order by $od_field $od_by,g.last_update $od_by ";
        } else {
            $orderby .= " order by g.last_update desc ";
        }
        $sql = "select count(*) from shp_goods g where merchant_id='%s' and g.is_delete=0 $where ";
        $count = D()->query($sql, $muid)->result();
        $pager->setTotalNum($count);
        $sql = "select g.*,c.cat_name from shp_goods g left join shp_shop_category c on g.shop_cat_id = c.cat_id where g.is_delete=0 and g.merchant_id='%s' $where $orderby  limit {$pager->start},{$pager->pagesize}";
        $goods = D()->query($sql, $muid)->fetch_array_all();
        //$goods = self::buildGoodsImg($goods);
        $pager->setResult($goods);
        $sql = "SELECT count(*) as count, is_on_sale as cat FROM shp_goods g where merchant_id='%s' and g.is_delete=0 $groupbyWhere group by is_on_sale";
        $result = D()->query($sql, $muid)->fetch_array_all();
        $pager->otherMap = $result;

    }

    /**
     * 对商品图片做处理
     * @param unknown $goods
     */
    static function buildGoodsImg($goods)
    {
        if (!empty($goods)) {
            foreach ($goods AS &$g) {
                $g['goods_img'] = Goods_Common::imgurl($g['goods_img']);
                $g['goods_thumb'] = Goods_Common::imgurl($g['goods_thumb']);
            }
        } else {
            $goods = [];
        }
        return $goods;
    }


    /**
     * 获取分页列表
     * @param $pager
     * @param $options
     * @return array|bool
     */
    static function getCatePageList(Pager $pager, array $options)
    {
        $merchant_id = $options['merchant_id'];
        if (empty($merchant_id)) {
            return false;
        }
        $sql = "select count(1) from shp_shop_category where merchant_id='%s' and parent_id = 0 and is_delete =0";
        $totalCount = D()->query($sql, $merchant_id)->result();
        $pager->setTotalNum($totalCount);
        $limit = $pager->start . "," . $pager->pagesize;
        $sql = "select cat_id, cat_name,parent_id,cat_thumb,sort_order from shp_shop_category where merchant_id ='%s' and parent_id = 0 and is_delete =0  limit {$limit}";
        $list = D()->query($sql, $merchant_id)->fetch_array_all();
        $data = self::getChirlList($merchant_id);
        $p = 0;
        for ($i = 0; $i < count($list); $i++) {
            for ($j = 0; $j < count($data); $j++) {
                if ($list[$i]['cat_id'] == $data[$j]['parent_id']) {
                    $list[$i]['childs'][$p] = $data[$j];
                    $list[$i]['flat'] = true;
                    $p++;
                }
            }
            $p = 0;//清零
        }
        $pager->result = $list;
        return $list;
    }

    /**
     * 获取所有的二级分类
     * @param $cat_id
     */
    static function getChirlList($merchant_id)
    {
        $sql = "select cat_id, cat_name,parent_id,cat_thumb,sort_order from shp_shop_category WHERE parent_id > 0 and merchant_id='%s' and is_delete = 0";
        $result = D()->query($sql, $merchant_id)->fetch_array_all();
        return $result;
    }

    /**
     * @auth hc_edm
     *根据merchant_id查询商家所有的商品分类
     * @param $cat_id
     * @return array|bool
     */
    static function getCatNameList($merchant_id)
    {
        if (empty($merchant_id)) {
            return false;
        }
        $sql = "select cat_name ,cat_id ,cat_thumb from shp_shop_category where merchant_id ='%s' and parent_id=0 and is_delete=0";
        $list = D()->query($sql, $merchant_id)->fetch_array_all();
        return $list;
    }


    /**
     * 新增一个分类
     * @param int $cat_id
     */
    static function addCategory($cateArr, $cat_id = 0)
    {
        /**
         * cat_id=0为新增加一个分类
         */
        $merchant_id = $GLOBALS['user']->uid;
        if (intval($cat_id) == 0) {
            $insertarr['parent_id'] = 0;
        } else {
            $insertarr['parent_id'] = $cat_id;
        }
        $insertarr['cat_name'] = $cateArr['cat_name'];
        $sql = "select cat_name from shp_shop_category where cat_name ='%s' and merchant_id = '%s' and is_delete = 0 ";
        $cat_name = D()->query($sql, $insertarr['cat_name'], $merchant_id)->result();
        if ($cat_name && !$cateArr['edit']) {
            return "分类名已存在！";
        }
        $tablename = "`shp_shop_category`";
        $insertarr['merchant_id'] = $merchant_id;
        $insertarr['cat_thumb'] = isset($cateArr['cate_thums']) ? $cateArr['cate_thums'] : '';
        $insertarr['sort_order'] = isset($cateArr['sort_order']) ? $cateArr['sort_order'] : 0;
        if ($cateArr['edit'] != 1) {
            return D()->insert($tablename, $insertarr);
        } else {
            unset($insertarr['merchant_id']);
            $whereArr['cat_id'] = $cateArr['cat_id'];
            return D()->update($tablename, $insertarr, $whereArr);
        }


    }


    /**
     * 删除一个分类
     * @auth hc_edm
     * @param $cat_id
     */
    static function delgoodsCategory($cat_id)
    {
        $merchant_id = $GLOBALS['user']->uid;
        /**
         * 先查询出是否有子分类
         */
        $sql = "select parent_id from shp_shop_category where cat_id = %d";
        $parent_id = D()->query($sql, $cat_id)->result();
        $where = array(
            'cat_id' => $cat_id,
        );
        $table = "`shp_shop_category`";

        if ($parent_id > 0) {
            /*
             * 如果删除的是二级分类
             */
            $result = self::updateCateStatus($cat_id, $table, $parent_id);
        } else {
            /**
             * 如果删除的是一级分类
             */
            $result = self::updateCateStatus($cat_id, $table);
        }
        if ($result) {
            return true;
        } else {
            return false;
        }

    }

    static function updateCateStatus($cat_id, $table, $parent_id = 0)
    {
        //查出关联表中所有的goods_id
        $where = "cat_id = {$cat_id}";
        if ($parent_id == 0) {//如果parent_id=0则代表是一级分类
            //求出所有的cat_id
            $cat_where = "parent_id ={$cat_id}";
            $sql = "select cat_id from shp_shop_category where $cat_where";
            $cat_ids = D()->query($sql)->fetch_array_all();
            $cats = "";
            foreach ($cat_ids as $id) {
                $cats .= $id['cat_id'] . ",";
            }
            $cats = rtrim($cats, ",");
            $where = "cat_id in($cat_id,{$cats})";
            if (empty($cats)) {
                $where = "cat_id ={$cat_id}";
            }
        }
        $sql = "select goods_id from shp_shop_goods_cat where {$where}";
        $goods_ids = D()->query($sql)->fetch_array_all();
        $ids = "";
        foreach ($goods_ids as $id) {
            $ids .= $id['goods_id'] . ",";
        }
        $ids = rtrim($ids, ",");
        $setarr['is_delete'] = 1;
//        $whereIds = "goods_id in({$ids})"; //得到条件
        //0代表没有分类
        $category = D()->update($table, $setarr, $where);//第一步更新分类表的cat_level
        //第二步更新goods_cat关联表
        //$cateTable = "`shp_shop_goods_cat`";
        // $goods_cat = D()->delete($cateTable, $whereIds);//删除goods_cat表中的记录
        //第三步更新shp_goods中的cat_id字段
        //
        $sql = "select cat_id from shp_goods where $where";
        $result = D()->query($sql)->fetch_array_all();
        if (!$result && $category) {
            return true;
        }
        $set['cat_id'] = 0;
        $goods = D()->update("`shp_goods`", $set, $where);
        return $category && $goods;
    }

    /**
     * 判断是否已经有了二级分类
     * @param $cat_id
     */
    static function IsHadCategory($cat_id)
    {
        $sql = "select parent_id from shp_shop_category WHERE  cat_id=%d";
        $parent_id = D()->query($sql, $cat_id)->get_one();
        return $parent_id;
    }

    /**根据cat_id获取分类信息
     * @param $cat_id
     */
    static function getOneCategory($cat_id)
    {
        $sql = "select cat_id,parent_id,cat_thumb,cat_name ,sort_order from shp_shop_category where cat_id = {$cat_id} and is_delete=0";
        return D()->query($sql)->get_one();
    }

    /**
     * 更新分类short_order
     * @param $cat_id
     * @param $short_order
     */
    static function updateShortOrder($cat_id, $short_order)
    {
        $tablename = "`shp_shop_category`";
        $setArr['sort_order'] = $short_order;
        $wherearr['cat_id'] = $cat_id;
        return D()->update($tablename, $setArr, $wherearr);

    }

    /**
     * 获取商家商品的评论列表
     * @param Pager $pager
     * @return mixed
     */
    static function getCommentList(Pager $pager, $current)
    {
        //comment_id ,id_value,content,comment_rank,user_name,add_time,status
        $merchant_id = $GLOBALS['user']->uid;
        $where = "and merchant_id='%s' ";
        $sql = "select count(1) from shp_comment where content <> '' ";
        if($current==2){
            $where .= "and comment_reply=''";
            $sql .= $where;
        } else {
            $sql .= $where;
        }
        $comment_count = D()->query($sql, $merchant_id)->result();
        $pager->setTotalNum($comment_count);
        $limit = "{$pager->start},{$pager->pagesize}";
        $current == 1 ? $where = "comment.merchant_id='{$merchant_id}' and comment.content <> ''" : $where = "comment.merchant_id='{$merchant_id}' and comment.comment_reply = '' and comment.content  <> ''";
        $sql = "select goods.goods_name as goods_name,goods.goods_thumb as goods_thumb ,goods.goods_img as goods_img ,comment.comment_id as comment_id,
              comment.id_value as id_value ,comment.comment_reply as comment_reply ,comment.content as content,comment.comment_rank as comment_rank,comment.user_name
              as user_name,comment.add_time as add_time,comment.status as status from shp_comment comment
              LEFT JOIN shp_goods goods on comment.id_value=goods.goods_id where {$where}
              order by add_time DESC limit {$limit}";
        $result = D()->query($sql)->fetch_array_all();
        $result = self::buildGoodsImg($result);
        $pager->result = $result;
        return $result;
    }

    /**
     * 商家回复
     * @param $merchart_content 回复类容
     */
    static function merchantRely($comment_id, $merchart_content)
    {
        $merchant_id = $GLOBALS['user']->uid;
//        public function update($tablename, Array $setarr, $wherearr, $flag = '')
        $table = "`shp_comment`";
        $setArr['comment_reply'] = $merchart_content;
        $whereArr['comment_id'] = $comment_id;
       return D()->update($table, $setArr, $whereArr);
    }

    /**
     * 查看评论类容
     * @param $comment_id
     */
    static function viewComment($comment_id)
    {
        $sql = "select goods.goods_name as goods_name,comm.comment_level as comment_level ,
              comm.service_level as service_level ,
              comm.shipping_level,comm.comment_rank as comment_rank ,comm.content as content,comm.comment_reply as comment_reply
              from shp_comment comm left join shp_goods goods on comm.id_value=goods.goods_id
              where comm.comment_id = %d and comment_type=0";
        $result = D()->query($sql, $comment_id)->get_one();
        switch($result['comment_level']){
            case 1:
                $result['comment_level'] ="好评";
            case 2:
                $result['comment_level'] ="中评";
            case 3:
                $result['comment_level'] ="差评";
            default:
                $result['comment_level'] ="好评";
        }
            return $result;
    }

    /**
     * 获取商品的属性列表
     */
    static function getGoodsAttrList()
    {
        $merchant_id = $GLOBALS['user']->uid;
        $sql = "select cat_id from shp_attribute where merchant_id ='%s' limit 1";
        $result = D()->query($sql, $merchant_id)->result();
        if (empty($result)) {
            return [];
        }
        $sql = "select distinct(ty.cat_id) ,ty.cat_name from shp_attribute attr  LEFT JOIN
                shp_goods_type ty on attr.cat_id = ty.cat_id where merchant_id = '%s'";
        $result = D()->query($sql, $merchant_id)->fetch_array_all();
        foreach ($result as $key => $val) {
            if (!empty($val['cat_id'])) {
                $result[$key]['attr_name'] = self::getAttrName($val['cat_id']);
            } else {
                unset($result[$key]);
            }
        }
        return $result;
    }

    /**
     * 根据cat_id拿到attrName
     * @param $cat_id
     */
    static function getAttrName($cat_id)
    {
        $merchant_id = $GLOBALS['user']->uid;
        $sql = "select attr_name from shp_attribute where cat_id ={$cat_id} and merchant_id = '{$merchant_id}' ORDER by sort_order ASC ";
        $result = D()->query($sql)->fetch_array_all();
        $str = "";
        foreach ($result as $val) {
            $str .= $val["attr_name"] . ",";
        }
        return rtrim($str, ",");
    }

    /**
     * 获取商品属性
     * @param $attrId
     */
    static function getGoodsAttr($attrId)
    {
        $merchant_id = $GLOBALS['user']->uid;
        $list['attr'] = [];
        if ($attrId != "new") {
            $sql = "select attr.attr_id as attr_id ,attr.attr_name as attr_name ,attr.sort_order as sort_order ,ty.cat_id as cat_id ,ty.cat_name as cat_name
                from shp_attribute  as attr  LEFT JOIN  shp_goods_type as ty on ty.cat_id = attr.cat_id  where
                attr.merchant_id = '%s' and ty.cat_id=%d ORDER by attr.sort_order ASC";
            $result = D()->query($sql, $merchant_id, $attrId)->fetch_array_all();
            $list['attr'] = $result;
        }
        $sql = "select cat_name , cat_id from shp_goods_type";
        $goods_type = D()->query($sql)->fetch_array_all();
        $list["type"] = $goods_type;
        return $list;
    }

    /**
     * 新增商品的属性
     * @param $cat_name
     */
    static function addGoodsAttr($attr_name, $cat_id)
    {
        $merchant_id = $GLOBALS['user']->uid;
        $tablename = "`shp_attribute`";

        $insertarr['attr_name'] = trim($attr_name);
        $insertarr['merchant_id'] = $merchant_id;
        $insertarr['cat_id'] = $cat_id;

//        $insertarr['sort_order'] = $sort_order;
        return D()->insert($tablename, $insertarr);
    }

    /**
     * 校验attr_name
     * @param $attrName
     */
    static function checkAttrName($cat_id, $attr_name)
    {
        $merchant_id = $GLOBALS['user']->uid;
        $where = "attr_name ".Fn::db_create_in($attr_name)."";
        $sql = "select attr_name from shp_attribute where cat_id = {$cat_id} and merchant_id = '%s' and $where";
        return D()->query($sql, $merchant_id)->result();
    }

    /**
     * 改变商品的short_order
     * @param $attr_id
     */
    static function updateGoodsShortOrder($attr_id, $sort_order, $attr_name)
    {
//        update($tablename, Array $setarr, $wherearr, $flag = '')
        $tablename = "`shp_attribute`";
        $setarr['sort_order'] = $sort_order;
        $setarr['attr_name'] = trim($attr_name);
        $wherearr['attr_id'] = $attr_id;
        D()->update($tablename, $setarr, $wherearr);
    }

    /**
     * attr_id
     * @param $attr_id
     */
    static function getAttrIds($cat_id)
    {
        $merchant_id = $GLOBALS['user']->uid;
        $sql = "select attr_id from shp_attribute where cat_id = {$cat_id} and merchant_id = '%s'";
        return D()->query($sql, $merchant_id)->fetch_column();
    }

    /**
     * 删除一个属性
     */
    static function delGoodsAttr($attr_id)
    {
        if(empty($attr_id) || count($attr_id) == 0){
            return 0;
        }
        $merchant_id = $GLOBALS['user']->uid;
        $where = " attr_id ".Fn::db_create_in($attr_id)." and merchant_id = '$merchant_id' ";
        $tablename = "`shp_attribute`";
        return D()->delete($tablename, $where);
    }

    /**
     * 检查是否可以删除
     * @param $cat_id
     */
    static function ckeckDelAttr($attr_id)
    {
        if(empty($attr_id) || count($attr_id) == 0){
            return 0;
        }
        $where = " ".Fn::db_create_in($attr_id)." ";
        $sql = "select count(1) from shp_goods_attr where attr1_id {$where} or attr2_id {$where} or attr3_id {$where}";
        return D()->query($sql)->result();
    }

    /**
     *删除属性
     */
    static function delAttr($cat_id)
    {
//        delete($tablename, $wherearr)
        $merchant_id = $GLOBALS['user']->uid;
        $tablename = "`shp_attribute`";
        $wherearr['cat_id'] = $cat_id;
        $wherearr['merchant_id'] = $merchant_id;
        D()->delete($tablename, $wherearr);
    }
}
