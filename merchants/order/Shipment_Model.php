<?php
/**
 * 运费模板实现
 * @author Jean
 *
 */
class Shipment_Model extends Model{
    
    /**
     * 添加或修改运费模板
     * @param unknown $sp_id
     * @param array $params
     * @return boolean
     */
    static function addOrUpdateShipmentTpl($sp_id, array $params){
        $ret = true;
        D()->beginTransaction();
        try{
            if($sp_id){
                self::updateShipmentAtomic($sp_id, $params);
                self::deleteShipmentTemplateAtomic($sp_id);
                self::addShipmentTemplate($sp_id, $params);
            }else{
                $insert_id = self::addShipment($params);
                self::addShipmentTemplate($insert_id, $params);
            }
        }catch (Exception $e){
            D()->rollback();
            $ret = false;
        }
        D()->commit();
        return $ret;
    }
    
    /**
     * 添加运费模板
     * @param array $params
     * @return number
     */
    static function addShipment(array $params){
        $muid = $GLOBALS['user']->uid;
        $sql = "insert into shp_shipment(merchant_id,tpl_name,type,last_time) values('%s','%s',1,".time().")";
        D()->query($sql, $muid, $params['tpl_name']);
        return D()->insert_id();
    }
    /**
     * 运费模板列表
     * @param unknown $sp_id
     * @param array $params
     */
    static function addShipmentTemplate($sp_id, array $params){
        $template = $params['template'];
        if(!$template || count($template) == 0){
            return;
        }
        $sql = "insert into shp_shipment_tpl(sp_id,regions,region_json,n_num,n_fee,m_num,m_fee) values ";
        $batchs = [];
        foreach ($template as $tmp){
            $region_json = $tmp['region_json'];
            array_push($batchs, "(".intval($sp_id).", '".self::escape($tmp['regions'])."', '".$region_json."',
                        '".intval($tmp['n_num'])."', '".doubleval($tmp['n_fee'])."','".intval($tmp['m_num'])."','".doubleval($tmp['m_fee'])."')");
        }
        $batchs = implode(',', $batchs);
        $sql .= $batchs;
        D()->query($sql);
        return D()->affected_rows();
    }
    
    /**
     * 删除运费模板
     * @param unknown $sp_id
     * @return boolean
     */
    static function deleteShipment($sp_id){
        $ret = true;
        D()->beginTransaction();
        try{
            $affected = self::deleteShipmentAtomic($sp_id);
            if($affected){
                self::deleteShipmentTemplateAtomic($sp_id);
            }else{
                $ret = false;
                D()->rollback();
            }
        }catch (Exception $e){
            $ret = false;
            D()->rollback();
        }
        D()->commit();
        return $ret;
    }
    
    /**
     * 判断当前模板名称是否存在
     * @param unknown $sp_id
     * @param unknown $tpl_name
     * @return boolean
     */
    static function isShipTplNameExists($sp_id, $tpl_name){
        $muid = $GLOBALS['user']->uid;
        $where = '';
        if($sp_id){
            $where .= " and sp_id <> '".intval($sp_id)."' ";
        }
        $sql = "select count(1) from shp_shipment where merchant_id = '%s' and tpl_name = '%s' $where ";
        $result = D()->query($sql, $muid, $tpl_name)->result();
        return $result ? true : false;
    }
    
    /**
     * 查询运费模板
     * @param unknown $sp_id
     */
    static function getShipmentTpl($sp_id = 0){
        $muid = $GLOBALS['user']->uid;
        $where = '';
        if($sp_id){
            $where .= " and ss.sp_id='".intval($sp_id)."' ";
        }
        $sql = "SELECT * FROM shp_shipment ss,shp_shipment_tpl stp
        where ss.sp_id=stp.sp_id and ss.merchant_id='%s' $where order by ss.last_time desc ";
        $result = D()->query($sql, $muid)->fetch_array_all();
        if(!$result || count($result) == 0){
            return [];
        }
        $shipments = [];
        foreach ($result as $ret){
            $sp_id = $ret['sp_id'];
            $tpl_name = $ret['tpl_name'];
            if ($sp_id && $tpl_name) {
                $key = $sp_id . '【~~】' . $tpl_name . '【~~】' . $ret['last_time'];
                if(!isset($shipments[$key]) || !$shipments[$key]){
                    $shipments[$key] = [];
                }
                array_push($shipments[$key], $ret);
            }
        }
        $newresults = [];
        foreach ($shipments as $key => $item){
            $itemOBJ = explode('【~~】', $key);
            if (count($itemOBJ) == 0) {
                continue;
            }
            foreach ($item as &$rg){
                $regions_name = '';
                $region_json = $rg['region_json'];
                //针对存入的数据反编码
                $region_json = html_entity_decode($region_json);
                $region_json = strtr($region_json, array('【' => '{', '】' => '}'));
                //针对需要在hidden里面存放的数据实体编码
                $rg['region_json'] = htmlentities($region_json);
                $region_json_obj = json_decode($region_json);
                if($region_json_obj){
                    foreach ($region_json_obj as $json){
                        $regions_name .= $json->regions_name .';';
                    }
                    if($regions_name){
                        $regions_name = substr($regions_name, 0, strlen($regions_name) - 1);
                    }
                }
                $rg['regions_name'] = $regions_name;
            }
            array_push($newresults, array('sp_id' => $itemOBJ[0], 'tpl_name' => $itemOBJ[1], 'last_time' => date('Y-m-d H:i',$itemOBJ[2]), 'items' => $item));
        }
        return $newresults;
    }
    
    private static function updateShipmentAtomic($sp_id, array $params){
        $muid = $GLOBALS['user']->uid;
        $sql = "update shp_shipment set tpl_name='%s',last_time=".time()." where sp_id='%d' and merchant_id='%s' ";
        D()->query($sql, $params['tpl_name'], $sp_id, $muid);
    }
    
    private static function deleteShipmentAtomic($sp_id){
        $muid = $GLOBALS['user']->uid;
        $sql = "delete from shp_shipment where sp_id='%d' and merchant_id='%s' ";
        D()->query($sql, $sp_id, $muid);
        return D()->affected_rows();
    }
    
    private  static function deleteShipmentTemplateAtomic($sp_id){
        $muid = $GLOBALS['user']->uid;
        $sql = "delete from shp_shipment_tpl where sp_id='%d' ";
        D()->query($sql, $sp_id);
        return D()->affected_rows();
    }
    
}

?>