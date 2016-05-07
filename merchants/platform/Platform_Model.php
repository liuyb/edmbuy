<?php
/**
 * 店铺Model
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Platform_Model extends Model
{
    
    static function checkMerchantStatus($response){
        $merchant_id = $GLOBALS['user']->uid;
        $merchant = Merchant::load($merchant_id);
        //if()
    }
    
    /**
     * 检查商家是否已经上传了资料
     */
    static function checkMaterial()
    {
        $merchant_id = $GLOBALS['user']->uid;
        $sql = "select status from shp_shop_checked where merchant_id = '{$merchant_id}' limit 1";
        return D()->query($sql)->result();
    }

    /***
     * 判断商家是否已经修改过
     */
    static function checkIsModify()
    {
        $sql = "select count(1) from shp_shop_checked where merchant_id ='{$GLOBALS['user']->uid}' and is_modify = 1 and status = 1 ORDER by id DESC LIMIT 1";
        return D()->query($sql)->result();
    }

    /**
     * 上传个人资料
     * @$data Array
     */
    static function addPerMaterial($data)
    {
        if (!is_array($data)) {
            return false;
        }
        unset($data['type']);
        $merchant_id = $GLOBALS['user']->uid;
        $data['merchant_id'] = $merchant_id;
        $data['is_modify'] = 0;
//        insert($tablename, Array $insertarr, $returnid = TRUE, $flag = '')
        $tablename = "`shp_shop_personal`";
        $result = D()->insert($tablename, $data);
        if ($result) {
            return self::addMaterialStatus($data, 1);
        }
        return false;

    }
    /**
     * 上传企业资料
     */
    static function addComMaterial($data)
    {
        if (!is_array($data)) {
            return false;
        }
        unset($data['type']);
        $merchant_id = $GLOBALS['user']->uid;
        $data['merchant_id'] = $merchant_id;
        $data['is_modify'] = 0;
//        insert($tablename, Array $insertarr, $returnid = TRUE, $flag = '')
        $tablename = "`shp_shop_company`";
        $result = D()->insert($tablename, $data);
        if ($result) {
            return self::addMaterialStatus($data, 2);
        }
        return false;
    }

    /**
     * 增加资料审核记录
     * @param $data
     * @param $type
     * @return bool|false|int
     */
    static function addMaterialStatus($data, $type)
    {
        if (!is_array($data)) {
            return false;
        }
        $merchant_id = $GLOBALS['user']->uid;
        $tablename = "`shp_shop_checked`";
        $insert['merchant_id'] = $merchant_id;
        $insert['name'] = $data['company_name'];
        $insert['mobile'] = $data['mobile'];
        $insert['content'] = "";
        $insert['status'] = 1;
        $insert['type'] = $type;
        return D()->insert($tablename, $insert);
    }

    /**
     * 修改商家资料
     * @param $type 修改后的类型
     */
    static function modifyMaterial($type, $data)
    {
        $result = self::checkIsModify();
        if ($result) {
            return false;
        }
        if ($type == "company") {
            self::delMaterial("personal");
          return   self::addComMaterial($data);
        } elseif ($type == "personal") {
            self::delMaterial("company");
          return  self::addPerMaterial($data);
        }
    }


    /**
     * 删除商家资料(修改时候用)
     * @param $type
     */
    static function delMaterial($type){
        $merchant_id = $GLOBALS['user']->uid;
        if($type == "company"){
//            delete($tablename, $wherearr)
            $tablename = "`shp_shop_company`";
        }elseif($type == "personal"){
            $tablename = "`shp_shop_personal`";
        }
        $wherearr['merchant_id'] = $merchant_id;
        return D()->delete($tablename,$wherearr);

    }
    /**
     * 审核
     * @param $status 0 ,1
     * @param $content
     * @param $type
     */
    static function updMaterialStatus($data, $status, $type)
    {
        if (!is_array($data)) {
            return false;
        }
        $merchant_id = $GLOBALS['user']->uid;
        $sql ="select status from shp_shop_checked where id = {$data['id']}";
        $st = D()->query($sql)->result();
        if (!in_array($status, array(0,1, 2))) {
            return false;
        }
        if (empty($merchant_id)) {
            return false;
        }
        if ($type == 1) {
            $setarr['name'] = $data['real_name'];
        } elseif ($type == 2) {
            $setarr['name'] = $data['company_name'];
        }
        if($status==-1 && $st ==1){
            $tablename = "`shp_shop_checked`";
// update($tablename, Array $setarr, $wherearr, $flag = '')
            $where['id'] = $data['id'];
            $setarr['content'] = $data['content'];
            $setarr['mobile'] = $data['mobile'];
            $setarr['status'] = $status;
            D()->update($tablename, $setarr, $where);
                return true;
        }elseif($status==2 && $st ==1){
            $tablename = "`shp_shop_checked`";
// update($tablename, Array $setarr, $wherearr, $flag = '')
            $where['id'] = $data['id'];
            $setarr['content'] = $data['content'];
            $setarr['mobile'] = $data['mobile'];
            $setarr['status'] = $status;
            D()->update($tablename, $setarr, $where);
            $tablename = "`shp_merchant`";
            $setarr['merchant_type'] = $type;
            $where['merchant_id'] = $merchant_id;
            D()->update($tablename, $setarr, $where);
            return true;
        }
            return false; //其他类型的不能审核

    }
}
