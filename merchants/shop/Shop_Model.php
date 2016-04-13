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
        $carousel_ids = D()->query($sql,$merchant_id)->fetch_array_all();
        $ids=[];
        foreach($carousel_ids as $val){
            array_push($ids,$val['carousel_id']);//转一
        }
            return $ids;
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
	
}
 
/*----- END FILE: Shop_Model.php -----*/