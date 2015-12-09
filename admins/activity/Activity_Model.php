<?php
/**
 * Material Model 
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Activity_Model extends Model {
	public static function getList($orderby='aid', $order='DESC', $limit=30) {
	   	$sql = "SELECT a.*,au1.admin_uname AS createdbyname,au2.admin_uname AS changedbyname FROM {activity} As a  LEFT JOIN {admin_user} AS au1 ON a.createdby=au1.admin_uid LEFT JOIN {admin_user} AS au2 ON a.changedby=au2.admin_uid WHERE `status`='R' ORDER BY `%s` %s";
	    $sqlcnt = "SELECT COUNT(aid) AS rcnt FROM {activity} WHERE `status`='R'";
	    $result = D()->pager_query($sql,$limit,$sqlcnt,0,$orderby,$order)->fetch_array_all();
	    return $result;
  	}

  	public static function getInfo($aid){
  		$sql = "SELECT * FROM {activity} WHERE aid=%d";
  		$result = D()->get_one($sql, $aid);
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
        D()->query("UPDATE `{activity}` SET `status`='D',`changedby`=%d,`changed`=%d WHERE `aid` IN (%s)",
                    $_SESSION['logined_uid'], $now, $idstr);
      
        return $ids;
      }
      return [];
    }


    public static function getRelatedList($page_size,$where){

      $sql = "SELECT column FROM {node} AS n";
      $column = " n.* ";
      $query_where = " WHERE 1 AND n.status='R' ";
      $sort = '';

      if(isset($where['aid'])&&$where['aid']!=''){
        $column .= ',ar.rank';
        if(isset($where['viewRelated'])&&$where['viewRelated']!=''){//只显示已联
          $sql .= " INNER JOIN {activity_related} ar  ON n.nid=ar.nid ";
          $sort = " ar.rank DESC ";
          $query_where .= " AND ar.aid={$where['aid']} ";
        }else{
          $sql .= " LEFT JOIN {activity_related} ar ON n.nid=ar.nid ";
          $query_where .= " AND (ar.aid={$where['aid']} OR ar.aid is NULL )";
        }

      }

      if(isset($where['type_id'])&&$where['type_id']!=''){
        $query_where .= " AND  n.type_id='{$where['type_id']}' ";
      }

      $sql .= $query_where; 
      $basesql = $sql;
      $sql = str_replace('column', $column, $sql);

      if($sort!=''){
        $sql .= ' ORDER BY '.$sort;
      }

      $sqlcnt = str_replace('column', ' count(n.nid) ', $basesql);

      return D()->pager_query($sql, $page_size, $sqlcnt)->fetch_array_all();
    }
    /**
     * 更新关联数据
     * @return [type] [description]
     */
    public static function relatedUpdate($aid,$rids,$act){

      if(!is_array($rids)){
        return [];
      }
      $data = [];
      foreach($rids as $val){
        if($act==0){
          $affected = D()->delete('activity_related', array('aid'=>$aid, 'nid'=>$val));
          if($affected>0){
            $data[] = $val;
          }
        }else{
          if(D()->get_one("SELECT * FROM {activity_related} WHERE aid={$aid} AND nid={$val}")){
            break;
          }
          D()->insert('activity_related', ['aid'=>$aid,'nid'=>$val]);
          $affected = D()->affected_rows();
          if($affected>0){
            $data[] = $val;
          }
        }
      }

      return $data;
    }
    /**
     * 更新关联表排序值
     * @param  [type] $aid  [description]
     * @param  [type] $nid  [description]
     * @param  [type] $rank [description]
     * @return [type]       [description]
     */
    public static function updateRelatedRank($aid, $nid, $rank){
      return D()->update('activity_related',['rank'=>$rank] , ['aid'=>$aid, 'nid'=>$nid]);
    }
}
