<?php
/**
 * Admin Node Model 
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Node_Model extends Model {
  
  public static function getNodeList($type_id,$orderby='nid', $order='DESC', $limit=30 , $where=[]) {
    $extra1 = $extra2 = "";
    switch ($type_id) {
      case 'card':
        $extra1 = "nc.*,";
        $extra2 = "INNER JOIN {node_card} nc ON n.nid=nc.tnid";
        break;
      case 'music':
        $extra1 = "nc.*,ms.source_name,";
        $extra2 = "INNER JOIN {node_music} nc ON n.nid=nc.tnid INNER JOIN {music_source} ms ON nc.source_id=ms.source_id";
        break;
      case 'gift':
        $extra1 = "nc.*,";
        $extra2 = "INNER JOIN {node_gift} nc ON n.nid=nc.tnid";
        break;
    }
    
    $sql    = "SELECT n.*,{$extra1}t.type_name,IFNULL(c.cate_name,'未分类') AS cate_name,au1.admin_uname AS createdbyname,au2.admin_uname AS changedbyname
               FROM {node} n
                 INNER JOIN {type} t ON n.type_id=t.type_id
                 LEFT JOIN {category} c ON n.cate_id=c.cate_id {$extra2}
                 INNER JOIN {admin_user} au1 ON n.createdby=au1.admin_uid
                 INNER JOIN {admin_user} au2 ON n.changedby=au2.admin_uid ";


    $_where = " WHERE n.`type_id`='%s' AND `status`<>'D' ";
    $_order =  " ORDER BY `%s` %s ";

    if(isset($where['keyword'])&&$where['keyword']!=''){
      $_where .= " AND n.search like '%{$where['keyword']}%' ";
    }
    if(isset($where['status'])&&$where['status']!=''){
      $_where .= " AND n.`status`='{$where['status']}' ";
    }

    $sqlcnt = "SELECT COUNT(nid) AS rcnt FROM {node} n ";
    
    $sql .= $_where.$_order;
    $sqlcnt .= $_where;

    $result = D()->pager_query($sql,$limit,$sqlcnt,0,$type_id,$orderby,$order)->fetch_array_all();
    return $result;
  }
  
  public static function getTypeIdByName($name) {
    switch ($name) {
      case 'word':
      case 'card':
      case 'music':
      case 'gift':
        return $name;
        break;
      case '':
      default:
        return 'word';
    }
  }
  public static function getTypeNameById($type_id) {
    switch ($type_id) {
      case 'word':
      case 'card':
      case 'music':
      case 'gift':
        return $type_id;
        break;
      case '':
      default:
        return 'word';
    }
  }
  
  public static function getCategoryList() {
    return D()->query("SELECT * FROM {category} WHERE `available`=1 ORDER BY `sortorder` ASC")->fetch_array_all();
  }
  
  public static function getTypeList() {
    return D()->query("SELECT * FROM {type} WHERE `available`=1 ORDER BY `sortorder` ASC")->fetch_array_all();
  }
  
  public static function getNodeInfo($nid) {
    $row = D()->get_one("SELECT n.*,t.type_name,IFNULL(c.cate_name,'未分类') AS cate_name FROM {node} n INNER JOIN {type} t ON n.type_id=t.type_id LEFT JOIN {category} c ON n.cate_id=c.cate_id WHERE n.`nid`=%d",$nid);
    if (!empty($row)) {
      $tb_node = self::getTypeNameById($row['type_id']);
      if (''!=$tb_node && 'word'!=$tb_node) {
        $tb_node = '{node_'.$tb_node.'}';
        $row_ext = D()->get_one("SELECT * FROM {$tb_node} WHERE tnid=%d",$row['nid']);
        $row     = array_merge($row, $row_ext);
      }
    }
    return $row;
  }
  
  public static function deleteNodes($ids) {
    if (!is_array($ids)) {
      $ids = array($ids);
    }
    
    $idstr = implode(',', $ids);
    if ($idstr) {
      	
      $now = simphp_time();
    
      //~ update table {channel}
      D()->query("UPDATE `{node}` SET `status`='D',`changedby`=%d,`changed`=%d WHERE `nid` IN (%s)",
                  $_SESSION['logined_uid'], $now, $idstr);
    
      return $ids;
    }
    return [];
  }
  
  public static function recommendNodes($ids, $isRecommend = TRUE) {
    if (!is_array($ids)) {
      $ids = array($ids);
    }
    
    $idstr = implode(',', $ids);
    if ($idstr) {
      	
      $flag = $isRecommend ? simphp_time() : 0;
      D()->query("UPDATE `{node}` SET `recommend`={$flag} WHERE `nid` IN (%s)", $idstr);
    
      return $ids;
    }
    return [];
  }
  
  public static function suspendNodes($ids, $isSuspend = TRUE) {
    if (!is_array($ids)) {
      $ids = array($ids);
    }
    
    $idstr = implode(',', $ids);
    if ($idstr) {
      	
      $flag = $isSuspend ? 'S' : 'R';
      D()->query("UPDATE `{node}` SET `status`='{$flag}' WHERE `nid` IN (%s)", $idstr);
    
      return $ids;
    }
    return [];
  }

  public static function getSourceList($source,$ret_idcolumn = FALSE){
    $source_list = array();

    if(in_array($source,array('gift','music'))){
      $table  = $source.'_source';
      $sql = "SELECT * FROM {{$table}} WHERE available=1 ORDER BY `sortorder` ASC";
      $scoure_list = D()->query($sql)->fetch_array_all();
      if ($ret_idcolumn) {
        foreach($scoure_list AS &$it) {
          $it = $it['source_id'];
        }
      }
    }

    return $scoure_list;
  }

  public static function getOrderList($where, $orderby='oid', $order='DESC', $limit=30) {
    $sql = "SELECT o.*,m.nickname,s.send_state FROM {order} AS o LEFT JOIN {member} AS m ON o.uid=m.uid LEFT JOIN {send} AS s ON o.order_no=s.order_no ";

    $order_str = " ORDER BY {$orderby} {$order} ";
    $where_str = ' WHERE 1';
    if(!empty($where)){
      if(isset($where['order_no'])){
        $where_str .= " AND o.order_no='{$where['order_no']}' ";
      }
      if(isset($where['nickname'])){
        $where_str .= " AND m.nickname='{$where['nickname']}' ";
      }
      if(isset($where['state'])){
        $where_str .= " AND o.state={$where['state']} ";
      }
    }

    $sqlcnt = "SELECT COUNT(o.oid) FROM {order} AS o LEFT JOIN {member} AS m ON o.uid=m.uid ";
    $sql .= $where_str.$order_str;
    $sqlcnt .= $where_str;
    $result = D()->pager_query($sql,$limit,$sqlcnt,0,$type_id,$orderby,$order)->fetch_array_all();
    return $result;
  }

  public static function getOrderById($oid){
    $sql = "SELECT o.*,m.username,m.nickname FROM {order} AS o  INNER JOIN {member} AS m ON o.uid=m.uid WHERE o.oid=%d";
    return D()->get_one($sql, $oid);
  }

  public static function getOrderByOrderno($order_no){
    $sql = "SELECT * FROM {order} WHERE order_no='%s' ";
    return D()->get_one($sql, $order_no); 
  }

  public static function getSendState($order_no){
    $sql = "SELECT * FROM {send} WHERE order_no='%s' ";
    return D()->get_one($sql, $order_no);
  }

  public static function addOrderSend($data){
    return D()->insert('send', $data);
  }

  public static function updateOrderSend($order_no, $data){
    return D()->update('send', $data, ['order_no'=>$order_no]);
  }

  public static function getTagByName($tag_name){
    $sql = "SELECT * FROM {tag} WHERE tag_name='%s' ";
    return D()->get_one($sql, $tag_name);
  }

  public static function addTag($data){
    $insert = ['tag_name'=>$data['tag_name'], 'timeline'=>time()];
    return D()->insert('tag', $insert);
  }

  public static function cateRTag($data){
    $insert = ['tag_id'=>$data['tag_id'], 'cate_id'=>$data['cate_id']];
    D()->insert('tag_cate', $insert);
    return D()->affected_rows();
  }

  public static function getCateRTags($cate_id){
    $sql = "SELECT tc.*,t.tag_name,tc.rank FROM {tag_cate} AS tc, {tag} AS t WHERE tc.cate_id=%d AND tc.tag_id=t.tag_id ORDER BY tc.rank DESC";
    return D()->query($sql, $cate_id)->fetch_array_all();
  }

  public static function delTag($data){
    D()->query('DELETE FROM {tag_cate} WHERE cate_id=%d AND tag_id=%d',$data['cate_id'], $data['tag_id']);
    return D()->affected_rows();
  }
  public static function nodeRTag($data){
    $insert = ['tag_id'=>$data['tag_id'], 'nid'=>$data['nid']];
    D()->insert('tag_node', $insert);
    return D()->affected_rows();
  }
  public static function delNodeRTag($nid){
    $sql = "DELETE FROM {tag_node}  WHERE nid=%d";
    return D()->query($sql, $nid)->affected_rows();
  }
  public static function updateTag($data){
    $tag_id = $data['tag_id'];
    unset($data['tag_id']);
    return D()->update('tag', $data, ['tag_id'=>$tag_id]);
  }

  public static function updateTagCate($data){
    return D()->update('tag_cate',['rank'=>$data['rank']], ['cate_id'=>$data['cate_id'], 'tag_id'=>$data['tag_id']]);
  }

  public static function importMusic($source, $url=''){
    switch ($source) {
      case 'hnyd':
          //$data[] = ['title'=>'','singer_name'=>'','source_id'=>'','music_url'=>''];
          $data = self::importMusic_hnyd($url);
          //保存数据
          foreach($data as $val){
            //查询歌曲是否已存在
            $hash = md5($val['music_url']);
            $sql = "SELECT * FROM {node_music} WHERE music_url_hash='%s' ";
            //exit("SELECT * FROM {node_music} WHERE music_url_hash='{$hash}' ");
            if(D()->get_one($sql, $hash)){
              break;
            }

            $now = simphp_time();
            $uid = $_SESSION['logined_uid'];
            $params = [
              'type_id'      => 'music',
              'cate_id'      => 0,
              'title'        => $val['title'],
              'content'      => '',
              'keyword'      => '',
              'tag'          => '',
              'createdby'    => $uid,
              'created'      => $now,
              'changedby'    => $uid,
              'changed'      => $now,
              'status'       => 'S',
              'recommend'    => 0,
              'sort'         => 0
            ];
            $nid = D()->insert('node', $params);
            if($nid>0){
              $params_ext = [
                'tnid' => $nid,
                'source_id' => $source,
                'singer_name'  => $val['singer_name'],
                'singer_link'  => '',
                'singer_fans'  => 0,
                'icon_url'  => '',
                'bg_url'  => '',
                'music_url'  => $val['music_url'],
                'music_url_hash' => $hash
              ];
              D()->insert('node_music', $params_ext);
            }
          }
        break;
      
      default:
        # code...
        break;
    }
  }
  public static function importMusic_hnyd($url){
    $opts = array(
      'http'=>array(
          'method'=>"GET",
          'timeout'=>10,
      )
    );
    //创建数据流上下文
    $context = stream_context_create($opts);
    $html = @file_get_contents($url,false,$context);
    if($res===FALSE){
      exit('网络异常');
    }else{
      require(SIMPHP_CORE."/libs/htmlparser/simple_html_dom.php");
      $dom = str_get_html($html);
      $elements = $dom->find('.dd_song');
      $data = [];
      foreach($elements as $node){
        $record = [];
        $record['music_url'] = $node->first_child()->getAttribute('s_src');
        $ele = $node->find('.song_info');
        $record['title'] = $ele[0]->first_child()->text();
        $record['singer_name'] = $ele[0]->last_child()->text();
        
        $data[] = $record;
      }
      return $data;
    }
  }
}
 
/*----- END FILE: Node_Model.php -----*/
