<?php
/**
 * 店铺Model
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Shop_Model extends Model {

    /**
     * 查询用户所有的轮播图
     */
    static function selCarousel(){
        $merchant_id=$GLOBALS['user']->uid;
        $sql="select carousel_id,link_url,carousel_img,sort from shp_carousel where merchant_id = '%s'";
        return D()->query($sql,$merchant_id)->fetch_array_all();
    }

    /**
     * 得到carousel_id
     */
    static function getCarouselId(){
        $merchant_id=$GLOBALS['user']->uid;
        $sql="select carousel_id from shp_carousel where merchant_id = '%s'";
        return D()->query($sql,$merchant_id)->fetch_array_all();
    }

    /**
     * 删除轮播图
     * @param $carousel_id
     */
    static function delCarouse($carousel_id){
//        delete($tablename, $wherearr)
        $tablename="`shp_carousel`";
        $wherearr['carousel_id']=$carousel_id;
        D()->delete($tablename,$wherearr);
    }

    /**
     * 添加轮播图
     * @param $shor
     * @param $imgurl
     * @param $linkurl
     * @return false|int
     */
    static function  addCarouse($shor,$imgurl,$linkurl){
//        insert($tablename, Array $insertarr, $returnid = TRUE, $flag = '')
        $tablename="`shp_carousel`";
        $insertarr['sort']=$shor;
        $insertarr['carousel_img']=$imgurl;
        $insertarr['link_url']=$linkurl;
        $insertarr['merchant_id']=$GLOBALS['user']->uid;
        return  D()->insert($tablename,$insertarr);
    }

    /**
     * 更新轮播图
     * @param $carousel_id
     * @param $shor
     * @param $imgurl
     * @param $linkurl
     */
    static function updCarouse($carousel_id,$shor,$imgurl,$linkurl){
//        update($tablename, Array $setarr, $wherearr, $flag = '')
        $tablename="`shp_carousel`";
        $setarr['sort']=$shor;
        $setarr['carousel_img']=$imgurl;
        $setarr['link_url']=$linkurl;
        $wherearr['carousel_id'] =$carousel_id;
        return D()->update($tablename,$setarr,$wherearr);
    }
}

/*----- END FILE: Shop_Model.php -----*/