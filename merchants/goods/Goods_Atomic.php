<?php
/**
 * 商品模块提供的原子服务
 * @author Jean
 *
 */
class Goods_Atomic{
    
    /**
     * 查询现有的扩展分类
     * @param unknown $goods_id
     */
    public static function get_goods_ext_category($goods_id){
        $sql = "SELECT cat_id FROM shp_goods_cat WHERE goods_id = '$goods_id' and is_main = 0 ";
        $exist_list = D()->query($sql)->fetch_column();
        return $exist_list;
    }
    /**
     * 查询商品属性规格
     * @param unknown $goods_id
     */
    public static function get_goods_attribute($goods_id){
        $sql = "SELECT * FROM shp_goods_attr WHERE goods_id = '$goods_id' ";
        $result = D()->query($sql)->fetch_array_all();
        return $result;
    }
    
    /**
     * 获取系统 规定的商品规格类型  数据比较固定，采用缓存来实现。
     */
    public static function get_goods_type(){
        $data = Fn::read_static_cache('goods_type_data');
        if ($data === false){
            $sql = "SELECT * FROM shp_goods_type";
            $res = D()->query($sql)->fetch_array_all();
            //如果数组过大，不采用静态缓存方式
            if (count($res) <= 1000){
                Fn::write_static_cache('goods_type_data', $res);
            }
        }else{
            $res = $data;
        }
        return $res;
    }
    /**
     * 根据规格类型获取当前商铺的规格属性
     */
    public static function get_merchant_attribute($cat_id){
        $merchant_id = $GLOBALS['user']->uid;
        $sql = "SELECT attr_id,attr_name FROM shp_attribute where merchant_id = '$merchant_id' and cat_id=$cat_id order by sort_order asc ";
        return D()->query($sql)->fetch_array_all();
    }
    
    /**
     * 删除商品图片
     * @param unknown $goods_id
     */
    public static function delete_goods_gallery($goods_id){
        $sql = "DELETE FROM shp_goods_gallery WHERE goods_id = '$goods_id' ";
        D()->query($sql);
        return D()->affected_rows();
    }
    
    /**
     * 添加商品图片
     * @param unknown $goods_id
     * @param unknown $gallery
     */
    public static function add_goods_gallery($goods_id, $gallery){
        $sql = "INSERT INTO shp_goods_gallery(goods_id, img_url, img_desc, thumb_url, img_original) " .
            "VALUES ('$goods_id', '$gallery[gallery_img]', '', '$gallery[gallery_thumb]', '$gallery[origin_img]')";
        D()->query($sql);
    }
    
    /**
     * 删除商品属性
     * @param unknown $goods_id
     */
    public static function delete_goods_attr($goods_id){
        $sql = "DELETE FROM shp_goods_attr WHERE goods_id = '$goods_id' ";
        D()->query($sql);
    }
    
    /**
     * 批量删除商品
     * @param unknown $goods_ids
     */
    public static function batch_delete_goods($goods_ids){
        $sql = "DELETE FROM shp_goods where goods_id in ($goods_ids) ";
        D()->query($sql);
    }
    
    /**
     * 批量删除商品分类
     * @param unknown $goods_ids
     */
    public static function batch_delete_goods_cat($goods_ids){
        $sql = "DELETE FROM shp_goods_cat WHERE goods_id in ($goods_ids) ";
            D()->query($sql);
    }
    
    /**
     * 批量删除商品属性
     * @param unknown $goods_ids
     */
    public static function batch_delete_goods_attr($goods_ids){
        $sql = "DELETE FROM shp_goods_attr WHERE goods_id in ($goods_ids) ";
            D()->query($sql);
    }
    
    /**
     * 批量删除商品图片
     * @param unknown $goods_ids
     */
    public static function batch_delete_goods_gallery($goods_ids){
        $sql = "DELETE FROM shp_goods_gallery WHERE goods_id in ($goods_ids) ";
            D()->query($sql);
    }
}

?>