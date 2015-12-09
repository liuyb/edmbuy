<?php
/**
 * Material Model 
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class News_Model extends Model {
	public static function getList($orderby='nid', $order='DESC', $limit=30) {
	   	$sql = "SELECT a.*,au1.admin_uname AS createdbyname,au2.admin_uname AS changedbyname FROM {news} As a  LEFT JOIN {admin_user} AS au1 ON a.createdby=au1.admin_uid LEFT JOIN {admin_user} AS au2 ON a.changedby=au2.admin_uid WHERE `status`='R' ORDER BY `%s` %s";
	    $sqlcnt = "SELECT COUNT(nid) AS rcnt FROM {news} WHERE `status`='R'";
	    $result = D()->pager_query($sql,$limit,$sqlcnt,0,$orderby,$order)->fetch_array_all();
	    return $result;
  	}

  	public static function getInfo($nid){
  		$sql = "SELECT * FROM {news} WHERE nid=%d";
  		$result = D()->get_one($sql, $nid);
  		return $result;
  	}
    public static function delete($ids) {
      if (!is_array($ids)) {
        $ids = array($ids);
      }
      
      $idstr = implode(',', $ids);
      if ($idstr) {
          
        $now = simphp_time();
      
        //~ update table {channel}
        D()->query("UPDATE `{news}` SET `status`='D',`changedby`=%d,`changed`=%d WHERE `nid` IN (%s)",
                    $_SESSION['logined_uid'], $now, $idstr);
      
        return $ids;
      }
      return [];
    }

}