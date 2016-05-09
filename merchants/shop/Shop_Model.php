<?php
/**
 * 店铺Model
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Shop_Model extends Model
{

    /**
     * 根据tpl_id拿到原图
     * @param $tpl_id
     */
    static function getImg($tpl_id)
    {
        $sql = "select tpl_image from shp_shop_template where tpl_id = %d";
        return D()->query($sql, $tpl_id)->result();
    }

    /**
     * 更新店铺的信息
     * @param $tpl_id
     */
    static function updShopInformation($tpl_id, $shop_qcode)
    {
//        update($tablename, Array $setarr, $wherearr, $flag = '')
        $tablename = "`shp_merchant`";
        $whereArr['merchant_id'] = $GLOBALS['user']->uid;
        $setArr['shop_template'] = $tpl_id;
        $setArr['shop_qcode'] = $shop_qcode;
        return D()->update($tablename, $setArr, $whereArr);
    }

    /**
     * 判断用户是否配置了店铺信息 没有则跳转至店铺装修页面
     */
    static function checkShopStatus($response)
    {
        $merchant_id = $GLOBALS['user']->uid;
        $sql = "select count(1) from shp_merchant where merchant_id = '%s' and is_completed = 1";
        $result = D()->query($sql, $merchant_id)->result();
        if (!$result || $result == 0) {
            $response->redirect("/shop/start");
        }
    }

    /**
     * 查询用户所有的轮播图
     */
    static function selCarousel()
    {
        $merchant_id = $GLOBALS['user']->uid;
        $sql = "select carousel_id,link_url,carousel_img,sort from shp_shop_carousel where merchant_id = '%s'";
        return D()->query($sql, $merchant_id)->fetch_array_all();
    }

    /**
     * 得到carousel_id
     */
    static function getCarouselId()
    {
        $merchant_id = $GLOBALS['user']->uid;
        $sql = "select carousel_id from shp_shop_carousel where merchant_id = '%s'";
        return D()->query($sql, $merchant_id)->fetch_array_all();
    }

    /**
     * 删除轮播图
     * @param $carousel_id
     */
    static function delCarouse($carousel_id)
    {
//        delete($tablename, $wherearr)
        $tablename = "`shp_shop_carousel`";
        $wherearr['carousel_id'] = $carousel_id;
        D()->delete($tablename, $wherearr);
    }

    /**
     * 添加轮播图
     * @param $shor
     * @param $imgurl
     * @param $linkurl
     * @return false|int
     */
    static function  addCarouse($shor, $imgurl, $linkurl)
    {
//        insert($tablename, Array $insertarr, $returnid = TRUE, $flag = '')
        $tablename = "`shp_shop_carousel`";
        $insertarr['sort'] = $shor;
        $insertarr['carousel_img'] = $imgurl;
        $insertarr['link_url'] = $linkurl;
        $insertarr['merchant_id'] = $GLOBALS['user']->uid;
        return D()->insert($tablename, $insertarr);
    }

    /**
     * 更新轮播图
     * @param $carousel_id
     * @param $shor
     * @param $imgurl
     * @param $linkurl
     */
    static function updCarouse($carousel_id, $shor, $imgurl, $linkurl)
    {
//        update($tablename, Array $setarr, $wherearr, $flag = '')
        $tablename = "`shp_shop_carousel`";
        $setarr['sort'] = $shor;
        $setarr['carousel_img'] = $imgurl;
        $setarr['link_url'] = $linkurl;
        $wherearr['carousel_id'] = $carousel_id;
        return D()->update($tablename, $setarr, $wherearr);
    }

    /**
     * 得到模版
     */
    static function getMchTpl()
    {
        $sql = "select tpl_id ,tpl_name,tpl_thumb,tpl_image, enabled,sort_order  from shp_shop_template where enabled = 1 ";//
        return D()->query($sql)->fetch_array_all();
    }


    /**
     * 得到已经开启的模版id
     * @return mixed
     */
    static function getCurentTpl()
    {
        $merchant_id = $GLOBALS['user']->uid;
        //先判断用户有没有配置店铺信息
        $sql = "select tpl.tpl_id  as tpl_id ,tpl.tpl_image as tpl_image,tpl.tpl_name as tpl_name ,tpl.tpl_thumb as tpl_thumb from shp_merchant info join shp_shop_template as tpl on  info.shop_template = tpl.tpl_id  where info.merchant_id= '%s'";
        return D()->query($sql, $merchant_id)->get_one();

    }

    /**
     * 店铺名是否已经存在
     * @param unknown $shop_id
     * @param unknown $shop_name
     * @return boolean
     */
    static function isShopNameExists($shop_name)
    {
        $muid = $GLOBALS['user']->uid;
        $sql = "select count(1) from shp_merchant where facename = '%s' and merchant_id <> '%s' ";
        $result = D()->query($sql, $shop_name, $muid)->result();
        return $result > 0;
    }

    /**
     * 根据商家ID得到当前店铺
     */
    static function getShopByMerchantId()
    {
        $sql = "select * from shp_merchant where merchant_id = '%s' ";
        $result = D()->query($sql, $GLOBALS['user']->uid)->get_one();
        if ($result) {
            $business_scope = $result['business_scope'];
            $business_scope = trim($business_scope, ",");
            $where = "cat_id = {$business_scope}";
            if (strpos($business_scope, ",")) {
                $where = "cat_id in ({$business_scope})";
            };
            $sql = "select cat_name from shp_business_category where  {$where} order by cat_id DESC ";
            $list = D()->query($sql)->fetch_column();
            if (count($list) > 0) {
                $result["business_scope"] = implode(",", $list);
            } else {
                $result["business_scope"] = "";
            }
            $regionIds = [$result['province'], $result['city'], $result['district']];
            $adrrstr = Order::getOrderRegion($regionIds);
            $result['address'] = $adrrstr . $result['address'];
        }
        return $result;

    }

    /**
     * 拿系统默认模板
     * @return mixed
     */
    static function getDefaultTemplate()
    {
        $sql = "select tpl_id from shp_shop_template where is_default = 1 ";
        $result = D()->query($sql)->result();
        return $result;
    }

    /**
     * getBuyDetail
     */
    static function getBuyDetail()
    {
        $merchant_id = $GLOBALS['user']->uid;
        $sql = "select money_paid, start_time, end_time, term_time, paid_time from shp_merchant_payment 
                where merchant_id = '%s' and money_paid > 0 order by end_time desc limit 1";
        return D()->query($sql, $merchant_id)->get_one();

    }
}
