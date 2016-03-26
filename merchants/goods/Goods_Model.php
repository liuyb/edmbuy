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
        foreach ($add_list as $cat_id) {
            // 插入记录
            $sql = "INSERT INTO shp_goods_cat(goods_id, cat_id, is_main) VALUES ('$goods_id', '$cat_id', 0) ";
            D()->query($sql);
        }
        //冗余主分类到扩展分类
        if($cat_id){
            if($is_insert){
                $sql = "INSERT INTO shp_goods_cat(goods_id, cat_id, is_main) VALUES ('$goods_id', '$cat_id', 1) ";
            }else{
                $sql = "UPDATE shp_goods_cat set cat_id = $cat_id where goods_id = $goods_id and is_main = 1 ";
            }
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
    }
    
    //商品属性处理
    private static function handle_goods_attribute($is_insert, $goods_id, array $attribute_list){
        if(!$is_insert){
            // 删除原数据
            Goods_Atomic::delete_goods_attr($goods_id);
        }
        
        foreach ($attribute_list as $attr) {
            $sql = "INSERT INTO shp_goods_attr(goods_id, attr_id, attr_value, attr2_id, attr2_value, attr3_id, attr3_value,
                    market_price, shop_price, income_price, cost_price, goods_number) " .
                    "VALUES ('$goods_id', '".self::setDefaultValueIfUnset($attr ,'attr_id1', 0)."', '".self::setDefaultValueIfUnset($attr ,'attr_value1', '')."', 
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
     * @auth hc_edm
     * 获取商家的分类列表
     */
    static function getCategoryList()
    {
        $merchant_id = $GLOBALS['user']->uid = "mc_56f279b356351";
        Cookie::set("merchant_id", $merchant_id);
        if (empty($merchant_id)) {
            return false;
        }
        $sql = "select cat_id, cat_name,parent_id,cat_url,sort_order from shp_category where merchant_id ='{$merchant_id}' order by sort_order ASC ";
        $list = D()->query($sql)->fetch_array_all();
        return $list;
    }

    /**@auth hc_edm
     * 判断是否有了二个分类
     * @param $cat_id
     */
    static function isHadCategory($cat_id)
    {
        $sql = "select parent_id from shp_category where cat_id = '%s' ";
        $result = D()->query($sql, $cat_id)->get_one();
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
        $sql = "select cat_name ,cat_id ,cat_url from shp_category where merchant_id = %d";
        $list = D()->query($sql, $merchant_id)->fetch_array_all();
        return $list;
    }

    /**
     * 新增一个分类
     * @param int $cat_id
     */
    static function addCategory($cateArr,$cat_id = 0)
    {
        /**
         * cat_id=0为新增加一个分类
         */
        $merchant_id = $GLOBALS['user']->uid;
        if (!$cat_id) {
            $insertarr['parent_id']=0;
        }else{
            $insertarr['parent_id']=$cat_id;
        }
        $insertarr['cat_name'] = $cateArr['cat_name'];
        $sql="select cat_name from shp_category where cat_name ='%s' and merchant_id = '%s' ";
        $cat_name=D()->query($sql,$insertarr['cat_name'], $merchant_id)->result();
        if($cat_name){
            return "分类名已存在！";
        }
        $tablename = "`shp_category`";
        $insertarr['merchant_id'] = $merchant_id;
        $insertarr['cate_thums'] = isset($cateArr['cate_thums']) ? $cateArr['cate_thums'] : '';
        $insertarr['sort_order'] = isset($cateArr['sort_order']) ? $cateArr['sort_order'] : 0;
        return D()->insert($tablename,$insertarr);
    
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
        if ($parent_id > 0) {
            $where=array(
                'cat_id'=>array('in',"$parent_id,$cat_id")
            );
        }
        $result= D()->delete('category', $where);
        var_dump(D()->getSqlFinal());exit;

    }
    
}
