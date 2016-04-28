<?php
/**
 * Block Class
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Block extends BlockBase {

	/**
	 * 检查页面是否有权限显示对应的入口
	 * @param string $params
	 * @param string $content
	 * @param string $tpl
	 * @return string
	 */
	static function checkperms($params, $content, &$tpl) {
		$perms = isset($params['perms']) ? $params['perms'] : '';
		$uid   = isset($params['uid']) ? $params['uid'] : 0;
		$site  = isset($params['site']) ? $params['site'] : 'admin';
	
		if (check_perms($perms, $uid, $site)) {
			return $content;
		}
		return '';
	}
	

}
 
/*----- END FILE: class.Block.php -----*/