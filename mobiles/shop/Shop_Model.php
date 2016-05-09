<?php
/**
 * 默认Model
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Shop_Model extends Model
{

    /**检查商家商铺是否可用并且返回商家信息
     * @param $merchant_id
     */
    static function checkMerchantStatus($merchant_id)
    {
        $time =date("Y-m-d H:i:s",time());
        $sql = "select mer.shop_template ,mer.wxqr,mer.shop_qcode,mer.facename ,mer.logo,mer.shop_desc from shp_merchant mer
				left JOIN shp_merchant_payment payment ON
				mer.merchant_id = payment.merchant_id where
				mer.merchant_id = '%s' and mer.is_completed =1 AND payment.money_paid > 0
				and payment.start_time <= '%s' AND payment.end_time >= '%s'";

        $result = D()->query($sql,$merchant_id,$time,$time)->get_one();
        return $result;
    }

    /**
     * 商城首页轮播图
     * @param $merchant_id
     */
    static function getShopCarousel($merchant_id){
        $sql = "select carousel_img,link_url from shp_shop_carousel where merchant_id = '%s' ORDER by sort DESC ";
        return D()->query($sql,$merchant_id)->fetch_array_all();
    }

    /**
     * 获取在售的推荐商品商品列表
     * @param PagerPull $pager
     */
    static function findGoodsRcoment($merchant_id,PagerPull $pager=null,$type="",$flat = true,$search="")
    {
        if($flat){
            $limit = 4;
            $order = "sort_order desc";
        }
        if(!$flat&&$type){
            switch($type){
                case "new_asc":
                    $order = "add_time asc";
                    break;
                case "new_desc":
                    $order = "add_time desc";
                    break;
                case "sale_asc":
                    $order = "shop_price asc";
                    break;
                case "sale_desc":
                    $order = "paid_order_count desc";
                    break;
                case "price_asc":
                    $order = "paid_order_count desc";
                    break;
                case "price_desc":
                    $order = "shop_price asc";
                    break;
                default :
                    $order = "sort_order desc";
            }
            $limit = "{$pager->start},{$pager->realpagesize}";
        }
    
        $where = "and shop_recommend = 1 and merchant_id = '%s'";
        if($search){
            $where .=" and goods_name like '%{$search}%'";
        }
        $sql = "select goods_id,goods_name,shop_price,market_price,goods_brief,
        goods_thumb,goods_img from shp_goods where is_on_sale = 1 and is_delete = 0 and goods_flag = 0 $where order by {$order} limit {$limit}";
        $goods = D()->query($sql,$merchant_id)->fetch_array_all();
        return self::buildGoodsImg($goods);
    }

    /**
     * 拿到推荐分类列表
     */
    static function getGoodsGroupByCategory($merchant_id, $limit = 4)
    {
        $sql = "select cat_id,cat_name from shp_shop_category where merchant_id = '%s' and is_delete = 0 order by sort_order desc ";
        $cats = D()->query($sql, $merchant_id)->fetch_array_all();
        foreach ($cats as &$cat){
            $sql = "select g.goods_id,g.goods_name,g.goods_brief, g.shop_price,g.market_price,g.goods_img
                    from shp_goods g where g.merchant_id = '%s' and g.shop_cat_id = %d and g.is_on_sale = 1 and g.is_delete = 0 and g.goods_flag = 0 
                    order by g.sort_order desc,g.add_time desc limit {$limit}";
            $result = D()->query($sql, $merchant_id, $cat['cat_id'])->fetch_array_all();
            $cat['goods'] = $result;
        }
        $newcats = [];
        foreach ($cats as $c){
            if(count($c['goods']) > 0){
                array_push($newcats, $c);     
            }
        }
        return $newcats;
        /* $order = "g.sort_order desc add_time desc";
        $where =" c.merchant_id = '%s' and g.is_on_sale = 1  and g.is_delete = 0 and g.goods_flag = 0 ";
        $sql = "select g.*, c.cat_name, c.cat_id from shp_goods g
        RIGHT JOIN shp_shop_category c ON c.cat_id = g.shop_cat_id
        where {$where} order by {$order} limit {$limit}";
        $goods = D()->query($sql, $merchant_id)->fetch_array_all();
        if(!$goods || count($goods) == 0){
            return [];
        }
        $goods = self::buildGoodsImg($goods);
        $categorys = [];
        foreach ($goods as $item){
            $key = $item['cat_id'].'【~~】'.$item['cat_name'];
            if(!isset($categorys[$key])){
                $categorys[$key] = [];
            }
            array_push($categorys[$key], $item); 
        }
        $result = [];
        foreach ($categorys as $cat => $items){
            $val = explode("【~~】", $cat);
            if(!$val || count($val) < 2){
                continue;
            }
            $cat_id = $val[0];
            $cat_name = $val[1];
            array_push($result, array('cat_id' => $cat_id, 'cat_name' => $cat_name, 'items' => $items));
        }
        return $result; */
    }

    /**
     *拿到收藏的次数
     */
    static function getCollectNum($merchant_id){
        $sql="select count(1) from shp_collect_shop shop where merchant_id ='%s'";
        return D()->query($sql,$merchant_id)->result();
    }

    /**
     * 查看当前用户是否已经收藏店铺
     */
    static function checkIsCollect($merchant_id){
        $user_id =$GLOBALS['user']->uid;
        $sql ="select count(1) from shp_collect_shop where user_id = %d AND merchant_id ='%s'";
        return D()->query($sql,$user_id,$merchant_id)->result();
    }

    /**
     * 收藏店铺
     * @param $merchant_id
     */
    static function collectShop($merchant_id, $action){
        if(!$action){
            return;
        }
        $user_id =$GLOBALS['user']->uid;
        if($action < 0){
            $sql = "delete from shp_collect_shop where user_id=%d and merchant_id = '%s'";
            D()->query($sql,$user_id,$merchant_id);
        }else{
            $sql="select count(1) from shp_collect_shop where user_id = %d AND merchant_id ='%s'";
            $result = D()->query($sql)->result();
            if($result && $result > 0){
                return;
            }
            $tablename = "`shp_collect_shop`";
            $insertarr['merchant_id'] = $merchant_id;
            $insertarr['user_id'] = $user_id;
            $insertarr['add_time'] = time();
            D()->insert($tablename,$insertarr);
        }
    }

    /**
     * 商品推荐列表
     * @param $pager
     * @param $recoment
     */
    static function findRcomentList($pager, $recoment){
        $result = Items::findRecomentListByType($pager ,$recoment);

    }


}

/*----- END FILE: Default_Model.php -----*/