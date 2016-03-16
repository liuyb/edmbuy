<?php
/**
 * 商品评论类
 * @author Jean
 *
 */
class Comment extends StorageNode {

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
                'comment_level'  => 'comment_level',//好评0、中评1、差评2
                'shipping_level'  => 'shipping_level',//发货速度
                'service_level' => 'service_level',//服务态度
                'comment_img'  => 'comment_img',
                'comment_thumb'  => 'comment_thumb',
                'comment_reply'  => 'comment_reply',//商家回复
                'obj_attr'       => 'obj_attr'//商品属性
            )
        );
    }
    
    static function getGoodsComment(Comment $c, PagerPull $pager){
        $where = '';
        $sql = "SELECT user_name, content, add_time, comment_level, shipping_level, service_level,comment_img,comment_thumb,obj_attr 
                FROM shp_comment where status = 1 and id_value=%d $where order by comment_rank,add_time desc limit %d,%d";
        $result = D()->query($sql, $c->id_value, $pager->start, $pager->realpagesize)->fetch_array_all();
        if (!empty($result)) {
            foreach ($result AS &$g) {
                $g['comment_img'] = self::transformCommentImg($g['comment_img']);
                $g['comment_thumb'] = self::transformCommentImg($g['comment_thumb']);
            }
        }
        else {
            $result = [];
        }
        return $result;
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