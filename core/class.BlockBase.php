<?php
/**
 * Block Base Class
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class BlockBase {

  public static function nocache( $params, $content, &$tpl ) {
    return $content;
  }

}

 
/*----- END FILE: class.BlockBase.php -----*/