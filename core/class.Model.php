<?php
/**
 * SimPHP Model Base Class
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Model extends CBase {

    /**
     * 简化封装sql转义
     * @param unknown $text
     * @param unknown $server_mode
     * @return string
     */
    public static function escape($text, $server_mode = NULL){
        return D()->escape_string($text);
    }
}

/*----- END FILE: class.Model.php -----*/