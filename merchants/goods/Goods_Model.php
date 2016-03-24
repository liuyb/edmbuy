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
        D()->beginTransaction();
        $is_insert = false;
        if (! $goods->item_id) {
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
        }
        
        D()->commit();
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
        $add_list = $cat_list;
        if(!$is_insert){
            /* 查询现有的扩展分类 */
            $sql = "SELECT cat_id FROM shp_goods_cat WHERE goods_id = '$goods_id' and is_main = 0 ";
            $exist_list = D()->query($sql)->fetch_column();
            
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
            $sql = "DELETE FROM shp_goods_gallery WHERE goods_id = '$goods_id' ";
            D()->query($sql);
        }
        
        foreach ($gallery_list as $gallery) {
            $sql = "INSERT INTO shp_goods_gallery(goods_id, img_url, img_desc, thumb_url, img_original) " . 
                   "VALUES ('$goods_id', '$gallery[gallery_img]', '', '$gallery[gallery_thumb]', '$gallery[origin_img]')";
            D()->query($sql);
        }
    }
    
    //商品属性处理
    private static function handle_goods_attribute($is_insert, $goods_id, array $attribute_list){
        if(!$is_insert){
            // 删除原数据
            $sql = "DELETE FROM shp_goods_attr WHERE goods_id = '$goods_id' ";
            D()->query($sql);
        }
        
        foreach ($attribute_list as $attr) {
            $sql = "INSERT INTO shp_goods_attr(goods_id, attr_id, attr_value, attr_id2, attr_value2, attr_id3, attr_value3,
                    market_price, shop_price, income_price, cost_price, goods_number) " .
                    "VALUES ('$goods_id', '$attr[attr_id]', '$attr[attr_value]', '$attr[attr_id2]', '$attr[attr_value2]', '$attr[attr_id3]', '$attr[attr_value3]',
                    $attr[market_price],$attr[shop_price],$attr[income_price],$attr[cost_price],$attr[goods_number])";
            D()->query($sql);
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
            $sql = "DELETE FROM shp_goods where goods_id in ($goods_ids) ";
            D()->query($sql);
            $sql = "DELETE FROM shp_goods_cat WHERE goods_id in ($goods_ids) ";
            D()->query($sql);
            $sql = "DELETE FROM shp_goods_attr WHERE goods_id in ($goods_ids) ";
            D()->query($sql);
            $sql = "DELETE FROM shp_goods_gallery WHERE goods_id in ($goods_ids) ";
            D()->query($sql);
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
     * 获取商家的分类列表
     */
    static function getCategoryList(){
        $merchant_id = $GLOBALS['user']->uid;
        if(empty($merchant_id)){
            return false;
        }
        $sql="select merchant_id ,cat_name,parent_id ,cat_url from ";
    }
    
}
 
/*----- END FILE: Goods_Model.php -----*/