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
    static function insertOrUpdateGoods(Items $goods)
    {
        $ret = true;
        D()->beginTransaction();
        $is_insert = false;
        if (!$goods->item_id) {
            $is_insert = true;
            $goods->add_time = simphp_gmtime();
            $goods->merchant_id = $GLOBALS['user']->uid;
        }
        $goods->last_update = simphp_gmtime();
        try {
            $goods->save(($is_insert ? Storage::SAVE_INSERT : Storage::SAVE_UPDATE));
            $goods_id = $is_insert ? D()->insert_id() : $goods->item_id;
            $other_cat =  isset($_POST['other_cat']) ? $_POST['other_cat'] : [];
            $other_cat = is_array($other_cat) ? $other_cat : [$other_cat];
            self::handle_other_cat($is_insert, $goods_id,$goods->cat_id, array_unique($other_cat));

            if (isset($_POST['gallery_list'])) {
                self::handle_goods_gallery($is_insert, $goods_id, $_POST['gallery_list']);
            }

            if (isset($_POST['attribute_list'])) {
                self::handle_goods_attribute($is_insert, $goods_id, $_POST['attribute_list']);
            }

        } catch (Exception $e) {
            D()->rollback();
            $ret = false;
        }finally{
            D()->commit();
        }
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
        if(!$is_insert){
            /* 查询现有的扩展分类 */
            $exist_list = Goods_Atomic::get_goods_ext_category($goods_id);

            /* 删除不再有的分类 */
            $delete_list = array_diff($exist_list, $cat_list);
            if ($delete_list) {
                $sql = "DELETE FROM shp_goods_cat WHERE goods_id = '$goods_id'
    	        AND cat_id " . Func::db_create_in($delete_list). " and is_main = 0 ";
                D()->query($sql);
            }

            /* 添加新加的分类 */
            $add_list = array_diff($cat_list, $exist_list, array(
                0
            ));
        }
        //冗余主分类到扩展分类 主分类放在扩展分类前，保证主分类一定存在
        if($cat_id){
            if($is_insert){
                $sql = "INSERT IGNORE INTO shp_goods_cat(goods_id, cat_id, is_main) VALUES ('$goods_id', '$cat_id', 1) ";
            }else{
                $sql = "UPDATE shp_goods_cat set cat_id = $cat_id where goods_id = $goods_id and is_main = 1 ";
            }
            D()->query($sql);
        }
        foreach ($add_list as $cat_id) {
            // 插入记录
            $sql = "INSERT IGNORE INTO shp_goods_cat(goods_id, cat_id, is_main) VALUES ('$goods_id', '$cat_id', 0) ";
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
        if(!$is_insert){
            // 删除原数据
            Goods_Atomic::delete_goods_gallery($goods_id);
        }
        $sql = "INSERT INTO shp_goods_gallery(goods_id, img_url, img_desc, thumb_url, img_original) VALUES ";
        $batchs = [];
        foreach ($gallery_list as $gallery) {
            if(!$gallery || !$gallery['gallery_img']){
                continue;
            }
            array_push($batchs, "('$goods_id', '$gallery[gallery_img]', '', '$gallery[gallery_thumb]', '$gallery[origin_img]')");
        }
        if(count($batchs) > 0){
            $batchs = implode(',', $batchs);
            $sql .= $batchs;
        }
        D()->query($sql);
    }

    //商品属性处理
    private static function handle_goods_attribute($is_insert, $goods_id, array $attribute_list){
        if(!$is_insert){
            // 删除原数据
            Goods_Atomic::delete_goods_attr($goods_id);
        }

        foreach ($attribute_list as $attr) {
            $sql = "INSERT INTO shp_goods_attr(goods_id, cat1_id,cat1_name,cat2_id,cat2_name,cat3_id,cat3_name,
                    attr1_id, attr1_value, attr2_id, attr2_value, attr3_id, attr3_value,
                    market_price, shop_price, income_price, cost_price, goods_number) " .
                "VALUES ('$goods_id', '".self::setDefaultValueIfUnset($attr ,'cat_id1', 0)."', '".self::setDefaultValueIfUnset($attr ,'cat_value1', '')."',
                        '".self::setDefaultValueIfUnset($attr ,'cat_id2', 0)."', '".self::setDefaultValueIfUnset($attr ,'cat_value2', '')."',
                        '".self::setDefaultValueIfUnset($attr ,'cat_id3', 0)."', '".self::setDefaultValueIfUnset($attr ,'cat_value3', '')."',
                        '".self::setDefaultValueIfUnset($attr ,'attr_id1', 0)."', '".self::setDefaultValueIfUnset($attr ,'attr_value1', '')."',
                        '".self::setDefaultValueIfUnset($attr ,'attr_id2', 0)."','".self::setDefaultValueIfUnset($attr ,'attr_value2', '')."',
                        '".self::setDefaultValueIfUnset($attr ,'attr_id3', 0)."','".self::setDefaultValueIfUnset($attr ,'attr_value3', '')."',
                        $attr[market_price],$attr[shop_price],$attr[income_price],$attr[cost_price],$attr[goods_number])";
            D()->query($sql);
        }
    }

    private static function setDefaultValueIfUnset($arr, $key, $default){
        if($arr && $key){
            if(isset($arr[$key])){
                return $arr[$key];
            }
        }
        return $default;
    }
    
    /**
     * 解析成页面需要的商品属性格式
     * array(cat_id=>1,cat_name=>商品,attrs=>array([attr1_id] => 2
                    [attr1_value] => 300g
                    [attr2_id] => 5
                    [attr2_value] => 黄色
                    [attr3_id] => 0
                    [attr3_value] => 
                    [market_price] => 9.00
                    [shop_price] => 9.00
                    [income_price] => 8.00
                    [cost_price] => 7.00
                    [goods_number] => 6))
     * @param unknown $goods_id
     */
    public static function get_goods_attrs($goods_id){
        $result = Goods_Atomic::get_goods_attribute($goods_id);
        $cat_arr = [];
        foreach ($result as $item){
            self::map_goods_attrs($cat_arr, $item, 1);
            self::map_goods_attrs($cat_arr, $item, 2);
            self::map_goods_attrs($cat_arr, $item, 3);
        }
        $ret_arr = [];
        if($cat_arr && count($cat_arr) > 0){
            foreach ($cat_arr as $type => $attrs){
                $typeOBJ = explode('【~~】', $type);
                if(count($typeOBJ) == 0){
                    continue;
                }
                //处理每个type下的重复属性
                $display_attrs = self::get_goods_select_attr($typeOBJ[0], $attrs);
                array_push($ret_arr, array('cat_id' => $typeOBJ[0], 'cat_name' => $typeOBJ[1], 'attrs' => $attrs, 'display_attrs' => $display_attrs));
            }
        }
        return $ret_arr;
    }
    
    /**
     * 当前商品 选中的属性 唯一过滤
     * @param unknown $index
     * @param unknown $attrs
     */
    public static function get_goods_select_attr($cat_id, $attrs){
        $ret = [];
        $map = [];
        foreach ($attrs as $at){
            $at_id = $at['attr'.$cat_id.'_id'];
            $key = 'key_'.$at_id;
            if(isset($map[$key]) && $map[$key] > 0){
                continue;
            }
            $map[$key] = '1';
            array_push($ret, array("attr_id" =>$at_id, "attr_value" => $at['attr'.$cat_id.'_value']));
        }
        unset($map);
        return $ret;
    }
    
    /**
     * 用map来构造商品属性规格
     * 商品type（颜色、重量）=> array(对应的属性列表)
     * @param unknown $cat_arr
     * @param unknown $item
     * @param unknown $index
     */
    private static function map_goods_attrs(&$cat_arr, $item, $index){
        $cat_id = $item['cat'.$index.'_id'];
        $cat_name = $item['cat'.$index.'_name'];
        if($cat_id && $cat_name){
            $key = $cat_id.'【~~】'.$cat_name;
            if(isset($cat_arr[$key]) && $cat_arr[$key]){
                array_push($cat_arr[$key], $item);
            }else{
                $cat_arr[$key] = [];
                array_push($cat_arr[$key], $item);
            }
        }
    }

    /**
     * 删除指定商品
     * @param unknown $goods_id
     */
    static function batchDeleteGoods(array $goods_ids){
        if(empty($goods_ids)){
            return;
        }
        $goods_ids = implode(',', $goods_ids);
        D()->beginTransaction();
        try {
            Goods_Atomic::batch_delete_goods($goods_ids);
            Goods_Atomic::batch_delete_goods_cat($goods_ids);
            Goods_Atomic::batch_delete_goods_attr($goods_ids);
            Goods_Atomic::batch_delete_goods_gallery($goods_ids);
        }catch(Exception $e){
            D()->rollback();
        }finally {
            D()->commit();
        }
    }

    static function batchUpdateGoods(array $goods_ids, $field, $val){
        if(empty($goods_ids)){
            return 0;
        }
        $goods_ids = implode(',', $goods_ids);
        $sql = "update shp_goods set $field = $val where goods_id in ($goods_ids) ";
        D()->query($sql);
        return D()->affected_rows();
    }

    /**
     * 分页显示商品列表
     * @param Pager $pager
     * @param array $options
     */
    static function getPagedGoods(Pager $pager, array $options){
        $muid = $GLOBALS['user']->uid;
        $where = "";
        $orderby = "";
        if($options['goods_name']){
            $where .= " and g.goods_name like '%".htmlspecialchars($options['goods_name'])."%' ";
        }
        if($options['start_date']){
            $starttime = simphp_gmtime(strtotime($options['start_date'].DAY_BEGIN));
            $where .= " and g.add_time >= $starttime ";
        }
        if($options['end_date']){
            $endtime = simphp_gmtime(strtotime($options['end_date'].DAY_END));
            $where .= " and g.add_time <= $endtime ";
        }
        $groupbyWhere = $where;
        if($options['is_sale']){
            $where .= " and g.is_on_sale = ".(intval($options['is_sale']) - 1);
        }
        if($options['orderby'] && $options['order_field']){
            $orderby .= " order by $options[order_field] $options[orderby] ";
        }else{
            $orderby .= " order by g.last_update desc ";
        }
        $sql = "select count(*) from shp_goods g where merchant_id='".$muid."' $where ";
        $count = D()->query($sql, $muid)->result();
        $pager->setTotalNum($count);
        $sql = "select g.*,c.cat_name from shp_goods g left join shp_category c on g.cat_id = c.cat_id where g.merchant_id='".$muid."' $where $orderby  limit {$pager->start},{$pager->pagesize}";
        $goods = D()->query($sql)->fetch_array_all();
        $goods = self::buildGoodsImg($goods);
        $pager->result = $goods;
        $sql = "SELECT count(*) as count, is_on_sale as cat FROM shp_goods g where merchant_id='".$GLOBALS['user']->uid."' $groupbyWhere group by is_on_sale";
        $result = D()->query($sql)->fetch_array_all();
        $pager->otherMap = $result;

    }

    /**
     * 对商品图片做处理
     * @param unknown $goods
     */
    static function buildGoodsImg($goods){
        if (!empty($goods)) {
            foreach ($goods AS &$g) {
                $g['goods_img'] = Items::imgurl($g['goods_img']);
                $g['goods_thumb'] = Items::imgurl($g['goods_thumb']);
            }
        }
        else {
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
        $sql = "select count(1) from shp_category where merchant_id='{$merchant_id}' and parent_id = 0 and is_delete =0";
        $totalCount = D()->query($sql)->result();
        $pager->setTotalNum($totalCount);
        $limit = $pager->start . "," . $pager->pagesize;
        $sql = "select cat_id, cat_name,parent_id,cat_thumb,sort_order from shp_category where merchant_id ='{$merchant_id}' and parent_id = 0 and is_delete =0  limit {$limit}";
        $list = D()->query($sql)->fetch_array_all();
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
        $sql = "select cat_id, cat_name,parent_id,cat_thumb,sort_order from shp_category WHERE parent_id > 0 and merchant_id='{$merchant_id}' and is_delete = 0";
        $result = D()->query($sql)->fetch_array_all();
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
        $sql = "select cat_name ,cat_id ,cat_thumb from shp_category where merchant_id ='%s' and parent_id=0";
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
        $sql = "select cat_name from shp_category where cat_name ='%s' and merchant_id = '%s'";
        $cat_name = D()->query($sql, $insertarr['cat_name'], $merchant_id)->result();
        if ($cat_name && !$cateArr['edit']) {
            return "分类名已存在！";
        }
        $tablename = "`shp_category`";
        $insertarr['merchant_id'] = $merchant_id;
        $insertarr['cat_thumb'] = isset($cateArr['cate_thums']) ? $cateArr['cate_thums'] : '';
        $insertarr['sort_order'] = isset($cateArr['sort_order']) ? $cateArr['sort_order'] : 0;
        if ($cateArr['edit'] != 1) {
            return D()->insert($tablename, $insertarr);
        } else {
            unset($insertarr['merchant_id']);
            unset($insertarr['parent_id']);
            $whereArr['cat_id'] = $cat_id;
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
        $sql = "select parent_id from shp_category where cat_id = %d";
        $parent_id = D()->query($sql, $cat_id)->result();
        $where = array(
            'cat_id' => $cat_id,
        );
        $table = "`shp_category`";

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
            $sql = "select cat_id from shp_category where $cat_where";
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
        $sql = "select goods_id from shp_goods_cat where {$where}";
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
        //$cateTable = "`shp_goods_cat`";
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
        $sql = "select parent_id from shp_category WHERE  cat_id=%d";
        $parent_id = D()->query($sql, $cat_id)->get_one();
        return $parent_id;
    }

    /**根据cat_id获取分类信息
     * @param $cat_id
     */
    static function getOneCategory($cat_id)
    {
        $sql = "select cat_thumb,cat_name ,sort_order from shp_category where cat_id = {$cat_id}";
        return D()->query($sql)->get_one();
    }

    /**
     * 更新分类short_order
     * @param $cat_id
     * @param $short_order
     */
    static function updateShortOrder($cat_id,$short_order){
        $tablename = "`shp_category`";
        $setArr['sort_order']=$short_order;
        $wherearr['cat_id']=$cat_id;
        return D()->update($tablename,  $setArr, $wherearr);

    }

}
