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
    static function getImg($tpl_id){
        $sql ="select tpl_image from shp_shop_template where tpl_id = %d";
        return D()->query($sql ,$tpl_id)->result();
    }
    /**
     * 更新店铺的信息
     * @param $tpl_id
     */
    static function updShopInformation($tpl_id){
//        update($tablename, Array $setarr, $wherearr, $flag = '')
        $tablename = "`shp_shop_template`";
        $whereArr['merchant_id'] =$GLOBALS['user']->uid;
        $setArr['shop_template'] =$tpl_id;
        D()->update($tablename,$setArr,$whereArr);
    }

    /**
     * 判断用户是否配置了店铺信息
     */
    static function checkShopStatus(){
        $merchant_id =$GLOBALS['user']->uid;
        $sql="select count(1) from shp_shop_info where merchant_id = '%s'";
        return D()->query($sql,$merchant_id)->result();
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
        $sql = "select tpl_id ,tpl_name,tpl_thumb,tpl_image, enabled,sort_order from shp_shop_template  order BY enabled DESC";
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
        $sql = "select tpl.tpl_id ,info.shop_qrcode as shop_qrcode from shp_shop_template as tpl LEFT join shp_shop_info info on tpl.tpl_id = info.shop_template  where info.merchant_id= '%s' and tpl.enabled = 1";
        $qrcode = D()->query($sql, $merchant_id)->get_one();
        if ($qrcode) {
            $qrcode['is_use'] = true;
            return $qrcode;
        }
        $sql = "select tpl_id ,tpl_image from shp_shop_template where is_default =1";
        $qrcode = D()->query($sql)->get_one();
        $qrcode['is_use'] = false;
        return $qrcode;
    }


    /**
     * 增加或修改店铺信息
     * @param Shop $shop
     */
    static function insertOrUpdateShopinfo(Shop $shop)
    {
        $is_insert = $shop->shop_id ? false : true;
        $shop->merchant_id = $GLOBALS['user']->uid;
        if ($is_insert) {
            $shop->add_time = time();
            $defaultTmp = self::getDefaultTemplate();
            $shop->shop_template = $defaultTmp ? $defaultTmp : 0;
        }
        $shop->update_time = time();
        $shop->save($is_insert ? Storage::SAVE_INSERT : Storage::UPDATE);
        return D()->affected_rows();
    }

    /**
     * 店铺名是否已经存在
     * @param unknown $shop_id
     * @param unknown $shop_name
     * @return boolean
     */
    static function isShopNameExists($shop_id, $shop_name)
    {
        $where = '';
        if ($shop_id) {
            $where .= " and shop_id <> $shop_id ";
        }
        $sql = "select count(1) from shp_shop_info where shop_name = '%s' $where ";
        $result = D()->query($sql, $shop_name)->result();
        return $result > 0;
    }

    /**
     * 根据商家ID得到当前店铺
     */
    static function getShopByMerchantId()
    {
        $sql = "select * from shp_shop_info where merchant_id = '%s' ";
        $result = D()->query($sql, $GLOBALS['user']->uid)->get_one();
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
}

/*----- END FILE: Shop_Model.php -----*/

