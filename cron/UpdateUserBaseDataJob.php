<?php

/**
 * 修改用户一些基础数据的定时任务
 * 
 * @author Jean
 *
 */
class UpdateUserBaseDataJob extends CronJob {
    
    public function main($argc, $argv) {
        $this->sync_user_from_tym();
        $this->updateUserParentNick();
    }
    
    /**
     * 当前用户推荐人的昵称同步
     */
    private function updateUserParentNick(){
        $start = 0;
        $limit = 10000;
        $total_record = $this->get_empty_parentnick_total();
        $this->log('total empty parent nick record: '.$total_record);
		$parent_list = $this->get_empty_parentnick_list($start, $limit);
		while (!empty($parent_list)) {
			$this->log('current empty parent nick record: '. ($start + count($parent_list)) . '/'.$total_record.'...');
			foreach ($parent_list AS $item) {
			    $user_id = $item['user_id'];
			    $parent_id = $item['parent_id'];
				if ($user_id && $parent_id) {
					$this->update_empty_parentnick($user_id, $parent_id);
				}
			}
			$start += $limit;
			
			//unset以释放内存
			unset($parent_list);
			
			$this->log('sleep 1 seconds...');
			sleep(1); //暂停1秒
			$parent_list = $this->get_empty_parentnick_list($start, $limit);
		}
    }
    
    /**
     * 所有推荐人昵称为空的用户
     * @return mixed
     */
    private function get_empty_parentnick_total() {
        $sql = "SELECT COUNT(1) FROM edmbuy.shp_users WHERE isnull(parent_nick) or parent_nick = ''";
        return D()->query($sql)->result();
    }
    
    /**
     * 所有推荐人昵称为空的用户
     * @return mixed
     */
    
    private function get_empty_parentnick_list($start, $limit) {
        $sql = "SELECT user_id, parent_id FROM edmbuy.shp_users WHERE isnull(parent_nick) or parent_nick = '' limit %d,%d";
        return D()->query($sql, $start, $limit)->fetch_array_all();
    }
    
    /**
     * 更新推荐人的昵称
     * @return mixed
     */
    private function update_empty_parentnick($user_id, $parent_id){
        $sql = "update edmbuy.shp_users set  parent_nick  = ifnull((
        	select t.nick_name from (
        	   select nick_name from edmbuy.shp_users where user_id = $parent_id 
        	) t
        ), '') where user_id = $user_id";
        D()->query($sql);
    }
    
    /**
     * 如果存在没从甜玉米同步的数据，则执行同步
     */
    private function sync_user_from_tym(){
        $start = 0;
        $limit = 10000;
        $total_record = $this->get_notsynced_tymuser_total();
        $this->log('total not synced from tym user record: '.$total_record);
        $user_list = $this->get_notsynced_tymuser_list($start, $limit);
        while (!empty($user_list)) {
            $this->log('current synced from tym user record: '. ($start + count($user_list)) . '/'.$total_record.'...');
            foreach ($user_list AS $item) {
                $this->sync_user_data_from_tym($item);
            }
            $start += $limit;
            	
            //unset以释放内存
            unset($user_list);
            	
            $this->log('sleep 1 seconds...');
            sleep(1); //暂停1秒
            $user_list = $this->get_notsynced_tymuser_list($start, $limit);
        }
    }
    
    /**
     * 所有还没有从甜玉米同步过的用户数据
     * @return mixed
     */
    private function get_notsynced_tymuser_total() {
        $sql = "select count(*) from tb_tym_user 
                where exists (
                	select 1 from (
                		select mobile_phone from shp_users where app_userid = 0 and mobile_phone is not null
                    ) t where t.mobile_phone = mobile
                )";
        return D()->query($sql)->result();
    }
    
    /**
     * 所有还没有从甜玉米同步过的用户数据
     * @return mixed
     */
    private function get_notsynced_tymuser_list($start, $limit) {
        $sql = "select userid,nick,logo,qrcode,business_id,business_time,mobile from tb_tym_user 
                where exists (
                	select 1 from (
                		select mobile_phone from shp_users where app_userid = 0 and mobile_phone is not null
                    ) t where t.mobile_phone = mobile
                ) limit %d,%d";
        return D()->query($sql, $start, $limit)->fetch_array_all();
    }
    
    /**
     * 如果当前字段为空，则更新为从甜玉米拿到的对应用户字段
     * @return mixed
     */
    private function sync_user_data_from_tym($item){
        $user_id = $item['userid'];
        $nick = $item['nick'];
        $logo = $item['logo'];
        $qrcode = $item['qrcode'];
        $business_id = $item['business_id'];
        $business_time = $item['business_time'];
        $mobile = $item['mobile'];
        if (!$mobile){
            return;
        }
        $sql = "update shp_users set 
                app_userid = (case when app_userid = '' then $user_id else app_userid end),
                nick_name = (case when nick_name = '' then '$nick' else nick_name end),
                logo = (case when logo = '' then '$logo' else logo end),	
                wxqr = (case when wxqr = '' then '$qrcode' else wxqr end),	
                business_id = (case when business_id = '' then '$business_id' else business_id end),
                business_time = (case when business_time = '' then '$business_time' else business_time end) 
                where mobile_phone = '$mobile'";
        D()->query($sql);
    }
    
}
