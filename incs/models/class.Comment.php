<?php
/**
 * 商品评论类
 * @author Jean
 *
 */
class Comment extends StorageNode {

    const COMMENT_VALID_STATUS = 1;
    
    protected static function meta() {
        return array(
            'table'   => '{comment}',
            'key'     => 'cid',
            'columns' => array(
                'cid'     => 'comment_id',
                'comment_type'   => 'comment_type',
                'id_value'    => 'id_value',//商品ID
                'email'     => 'email',
                'user_name' => 'user_name',
                'content' => 'content',
                'comment_rank' => 'comment_rank',
                'add_time' => 'add_time',
                'ip_address'  => 'ip_address',
                'status'  => 'status',//0 不允许显示，1 允许显示
                'parent_id' => 'parent_id',
                'user_id'  => 'user_id',
                'user_logo' => 'user_logo',
                'comment_level'  => 'comment_level',//好评1、中评2、差评3
                'shipping_level'  => 'shipping_level',//发货速度
                'service_level' => 'service_level',//服务态度
                'comment_img'  => 'comment_img',
                'comment_thumb'  => 'comment_thumb',
                'comment_reply'  => 'comment_reply',//商家回复
                'obj_attr'       => 'obj_attr',//商品属性
                'order_id'       => 'order_id'
            )
        );
    }
    
    /**
     * 获取商品评论的 好评、中评、差评、有图等汇总
     */
    static function getCommentGroupCount($goods_id){
        $sql = "SELECT comment_level,count(1) as total FROM edmbuy.shp_comment where status = %d and id_value = %d group by comment_level
                union
                select -1 as comment_level, count(1) as total FROM edmbuy.shp_comment where status = %d and id_value = %d and comment_img is not null;";
        $ret = D()->query($sql,Comment::COMMENT_VALID_STATUS, $goods_id,Comment::COMMENT_VALID_STATUS, $goods_id)->fetch_array_all();
        return $ret;
    }
    
    static function getGoodsComment(Comment $c, PagerPull $pager, $category){
        $where = '';
        if(is_numeric($category)){
            if($category == -1){
                $where .= ' and comment_img is not null';
            }else if($category){
                $where .= ' and comment_level='.$category;
            }
        }
        $sql = "SELECT user_name, content, add_time, comment_level, shipping_level, service_level,comment_img,comment_thumb,comment_reply,obj_attr 
                FROM shp_comment where status = %d and id_value=%d $where order by comment_rank,add_time desc limit %d,%d";
        $result = D()->query($sql, Comment::COMMENT_VALID_STATUS, $c->id_value, $pager->start, $pager->realpagesize)->fetch_array_all();
        if (!empty($result)) {
            foreach ($result AS &$g) {
                $g['comment_img'] = self::transformCommentImg($g['comment_img']);
                $g['comment_thumb'] = self::transformCommentImg($g['comment_thumb']);
                $g['user_name'] = self::confusedUsernameInComment($g['user_name']);
                $g['add_time'] = date('Y-m-d',$g['add_time']);
            }
        }
        else {
            $result = [];
        }
        return $result;
    }
    
    static function confusedUsernameInComment($username){
        if(!$username){
            return "";
        }
        $unarr = preg_split("//u", $username, -1, PREG_SPLIT_NO_EMPTY);
        return $unarr[0].'**'.$unarr[count($unarr) - 1];
    }
    
    /**
     * 评论表里面存的多张图片用,分开。
     */
    static function transformCommentImg($imgs){
        if(!$imgs || empty($imgs)){
            return;
        }
        $imgs = explode(",", $imgs);
        $realImgs = [];
        foreach ($imgs as $img){
            array_push($realImgs, Items::imgurl($img));
        }
        return implode(",", $realImgs);
    }
}