<?php
/**
 * 默认Model 
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Default_Model extends Model {
  
    /**
     * 获取商品列表
     * @param PagerPull $pager
     * @param $ispromote 是否推荐
     */
	static function findGoodsList(PagerPull $pager, $ispromote){
	    $result = Items::findGoodsList($pager, $ispromote);
	    $pager->setResult($result);
	}
  
}
 
/*----- END FILE: Default_Model.php -----*/