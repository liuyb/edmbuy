<?php
defined('IN_SIMPHP') or die('Access Denied');

/**
 * 首页数据查询模型
 * @author Jean
 *
 */
class Home_Model extends Model{
    
    /**
     * 获取店铺运营数据
     * @param unknown $muid
     * @param unknown $gap
     */
    static function getMerchantDataByTime($muid, $gap){
        $where = '';
        if($gap == 'yesterday'){
            $beginYesterday=mktime(0,0,0,date('m'),date('d')-1,date('Y'));
            $endYesterday=mktime(0,0,0,date('m'),date('d'),date('Y'))-1;
            $where .= " and (add_time >= $beginYesterday and add_time <= $endYesterday) ";
        }else if($gap > 0){
            $gap = mktime(0,0,0,date('m'),date('d')-intval($gap),date('Y'));
            $where .= " and add_time >= $gap ";
        }
        
        //商家新增访问记录
        $sql = "select count(*) from shp_merchant_visiting where merchant_id='%s' $where";
        $visit = D()->query($sql, $muid)->result();
        //所有未删除的订单
        $sql = "SELECT count(1) as totalOrder from shp_order_info where merchant_ids='%s' and is_separate=0 and is_removed=0 $where";
        $totalOrder = D()->query($sql, $muid)->result();
        //所有已支付的订单
        $sql = "SELECT ifnull(sum(money_paid),0) money_paid, ifnull(sum(commision),0) as commision, ifnull(sum(discount),0) as discount 
                from shp_order_info where merchant_ids='%s' and is_separate=0 and pay_status = ".PS_PAYED." $where";
        $result = D()->query($sql, $muid)->fetch_array();
        $result['totalOrder'] = $totalOrder;
        $result['income'] = number_format((doubleval($result['money_paid']) - doubleval($result['commision']) - doubleval($result['discount'])), 2);
        $result['visit'] = $visit;
        return $result;
    }
    
    /**
     * 创建美恰企业客服
     * @param unknown $merchant
     * @return string[]|string[]|boolean[]
     */
    static function createMqEnterprise($merchant){
        $ret = ['flag' => 'FAIL'];
        $merchant_id = $merchant->uid;
        $mqConfig = C('api.meiqia_edmbuy');
        $url = $mqConfig['createEntUrl'];
        $appKey = $mqConfig['appkey'];;
        $secretKey = $mqConfig['secretKey'];;
        
        $t = time();
        // 构造 API 请求
        $api_params_arr = ['timestamp' => $t, 'fullname' => $merchant->facename, 'appkey' => $appKey];
        $api_params = json_encode($api_params_arr);
        // 本段输出结果为一个字符串：
        // api_params_str = '{"timestamp": timestamp,"fullname": fullname,"appkey": appkey}'
        // 使用 secret_key 对 api_params_str 进行 HMAC 计算请求签名
        $sig = hash_hmac('sha1', (string)$api_params, $secretKey);
        // 将 appkey 和请求加密签名放到请求头部
        $headers = array(
            'X-Message-Digest:'.trim($sig),
            'X-App-Key:'.$appKey,
            'Content-type: application/json');
        // 最终构造的请求为
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $api_params);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $json = curl_exec($curl);
        
        $msg = '创建失败，请稍后重试！';
        if(curl_errno($curl)){
            $msg = curl_error($curl);
        }else{
            if($json){
                $json = json_decode($json);
                $create = self::createMerchantKefu($merchant_id, $json);
                if($create){
                    $ret['flag'] = 'SUCC';
                    $msg = '创建成功';
                }
            }
        }
        curl_close($curl);
        $ret['msg'] = $msg;
        return $ret;
    }
    
    /**
     * 创建商家客服系统
     * @param unknown $merchant_id
     * @param unknown $json
     * @return boolean
     */
    private static function createMerchantKefu($merchant_id, $json){
        if(empty($json) || !isset($json->agent_token)){
            return false;
        }
        $insertarr = array(
            'merchant_id' => $merchant_id,
            'agent_token' => $json->agent_token,
            'email' => $json->email,
            'ent_id' => $json->ent_id,
            'ent_token' => $json->ent_token,
            'add_time' => time()
            
        );
        $rid = D()->insert('`shp_merchant_kefu`', $insertarr, true, 'IGNORE');
        return $rid ? true : false;
    }
    
    /**
     * 根据企业信息获取一键登录美恰的链接
     * @param unknown $merchant_id
     * @return NULL|string
     */
    static function getMQkefuLink($merchant_id){
        $ret = self::getMerchantKefu($merchant_id);
        if(!$ret || empty($ret)){
            return '';
        }
        $mqConfig = C('api.meiqia_edmbuy');
        $ent_url = $mqConfig['entSignUrl'];
        $appKey = $mqConfig['appkey'];;
        $secretKey = $mqConfig['secretKey'];;
         
        $agent_token = $ret['agent_token'];
        $email = $ret['email'];
        $ent_id = $ret['ent_id'];
        $ent_token = $ret['ent_token'];
        $t = simphp_msec();
        $api_params = http_build_query(['agent_token' => $agent_token, 'appkey' => $appKey, 'ent_token' => $ent_token, 'timestamp' => $t]);
        $sig = hash_hmac('sha1', $api_params, $secretKey);
        $request_params = http_build_query(['ent_token' => $ent_token, 'appkey' => $appKey, 'signature' => $sig, 'agent_token' => $agent_token, 'timestamp' => $t]);
        $link = $ent_url.'?'.$request_params;
        return $link;
    }
    
    static function getMerchantKefu($merchant_id){
        $sql = "select * from shp_merchant_kefu where merchant_id = '$merchant_id' ";
        $result = D()->query($sql)->fetch_array();
        return $result;
    }
}
