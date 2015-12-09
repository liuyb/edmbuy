<?php
/**
 * Mall Model
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Activity_Model extends Model {

  
  public static function getList($orderby = 'created', $order = 'DESC', $limit=30) {
    
    
    $order = strtoupper($order);
    if (!in_array($order, ['DESC','ASC'])) {
      $order = 'DESC';
    }
    if (!in_array($orderby, ['created','changed'])) { //limit order fields
      $orderby = 'created';
    }
    
    $where = '';
    $where .= " AND `status`='R'";
    
    $sql = 'SELECT * FROM {activity} ';
    $sql .= " WHERE 1 {$where} ORDER BY {$orderby} {$order}";
    $sql_cnt = "SELECT COUNT(a.aid) FROM {activity} a WHERE 1 {$where}";
    
    $result = D()->pager_query($sql,$limit,$sqlcnt,0)->fetch_array_all();
    return $result;
  }

  /**
   * 获取最近参与的6人
   * @param  [type]  $aid   [description]
   * @param  string  $act   [description]
   * @param  integer $limit [description]
   * @return [type]         [description]
   */
  public static function getJoinListByAid($aid, $act='join',$limit=6){
  	$sql = "SELECT j.*,m.nickname,m.logo FROM {activity_join} AS j LEFT JOIN {member} AS m ON j.uid=m.uid WHERE j.aid=%d AND act='%s' LIMIT %d";
  	$recordes = D()->query($sql, $aid, $act,$limit)->fetch_array_all();
  	return $recordes;
  }
  //获取参与人数
  public static function getJoinNumByAid($aid, $act='join'){
    $sql = "SELECT count(jid) FROM {activity_join} WHERE aid=%d AND act='%s'";
    $count = D()->query($sql, $aid, $act)->result();
    return $count;
  }

  public static function getActivityByAid($aid){
  	$sql = "SELECT * FROM {activity} WHERE aid=%d AND `status`='R' ";
  	return D()->get_one($sql, $aid);
  }
  
  public static function getNodeTinyInfo($nid){
    if (!$nid) return false;
    $row = D()->get_one("SELECT * FROM {node} WHERE `nid`=%d ", $nid);
    return $row;
  }


  public static function joinActivity($data, $inc = 1){
    $inc = $inc > 0 ? 1 : -1;
    if($data['act']=='vote'){
      D()->query("UPDATE {activity} SET votecnt=votecnt+{$inc} WHERE aid=%d", $data['aid']);
    }elseif($data['act']=='join'){
      D()->query("UPDATE {activity} SET joincnt=joincnt+{$inc} WHERE aid=%d", $data['aid']);
    }
    $ret = 0;
    if ($inc > 0) {
      $ret = D()->insert('activity_join', $data);
    }
    else {
      $ret = D()->delete('activity_join', ['aid'=>$data['aid'], 'uid'=>$data['uid'], 'act'=>$data['act']]);
    }
    return $ret;
  }
  public static function isJoin($aid, $uid, $act){
  	$sql = "SELECT jid FROM {activity_join} WHERE aid=%d AND uid=%d AND `act`='%s' ";
  	$record = D()->get_one($sql, $aid, $uid, $act);
  	if(empty($record)){
  		return false;
  	}else{
  		return true;
  	}
  }

 public static function getRelated($aid,$type_id){
    $sql = "SELECT column FROM {activity_related} ar INNER JOIN {node} n ON ar.nid=n.nid ";
    $column = " ar.*,n.title,n.content ";
    $where = " WHERE ar.aid={$aid} AND n.status='R' ";
    $sort = " ORDER BY ar.rank DESC "; 
    
    if($type_id=='music'){
      $sql .= " INNER JOIN {node_music} nm  ON  n.nid=nm.tnid ";
      $column .= ',nm.singer_name,nm.music_url';

    }

    $sql = str_replace('column', $column, $sql);
    $sql .= $where;
    $sql .= $sort;
    
    return D()->query($sql)->fetch_array_all();
 }

}