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
        $sql = "select mer.shop_template ,mer.shop_qcode,mer.facename ,mer.logo,mer.shop_desc from shp_merchant mer
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
     * 得到商家推荐的商品
     */
    static function getShopRecommend($merchant_id){
       return Items::findGoodsRcoment($merchant_id);

    }

    /**
     * 拿到商品分类列表
     * @param $merchant_id
     */
    static function getGoodsCategory($merchant_id){
        return Items::getCategoryRcoment($merchant_id);
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
    static function collectShop($merchant_id){
//        insert($tablename, Array $insertarr, $returnid = TRUE, $flag = '');
        $user_id =$GLOBALS['user']->uid;
        $sql="select count(1) from shp_collect_shop where user_id = %d AND merchant_id ='%s'";
        $result = D()->query($sql)->get_one();
        if($result){
            return ;
        }
        $tablename = "`shp_collect_shop`";
        $insertarr['merchant_id'] = $merchant_id;
        $insertarr['user_id'] = $user_id;
        $insertarr['add_time'] = time();
        D()->insert($tablename,$insertarr);
    }

    /**
     * 拿到商品分类列表
     * @param PagerPull $pager
     * @param $category
     */


    static function findGoodsListByCategory(PagerPull $pager, $category)
    {
        $categoryids = self::$goods_category_mapping[$category];
        $result = Items::findGoodsListByCategory($pager, $categoryids);
        $pager->setResult($result);
    }



}

/*----- END FILE: Default_Model.php -----*/