<?php
/**
 * 会员升级JOB 只涉及level变动
 * @author Jean
 *
 */
class MemberUpgradeJob extends CronJob{
    
    public function main($argc, $argv) {
        $members = $this->getNeedUpgradeMember();
        foreach ($members as $u){
            $user_id = $u['user_id'];
            $old_level = $u['old_level'];
            $new_level = $u['new_level'];
            $user = Users::load($user_id);
            if(!$user->is_exist()){
                continue;
            }
            //只针对米客米商升级
            if ($user->level > 1){
                continue;
            }
            $levle = $user->level;
            $newUser = new Users();
            $newUser->uid = $user->uid;
            if(!$levle){
                $newUser->level = 1;
            }else if($levle == 1){
                $newUser->level = 3;
            }
            $newUser->save(Storage::SAVE_UPDATE_IGNORE);
            
            D()->update('`tb_member_upgrade`', ['sync_flag' => 1], ['rid' => $u['rid']]);
        }
    }
    
    /**
     * 所有需要升级的会员记录
     */
    private function getNeedUpgradeMember(){
        $sql = "select * from tb_member_upgrade where sync_flag = 0";
        return D()->query($sql)->fetch_array_all();
    }
}

?>