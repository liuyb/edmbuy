<?php
/**
 * Wxqrcode Node Model
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class Wxqrcode extends StorageNode {
	
	protected static function meta() {
		return array(
				'table' => '{weixin_qrcode}',
				'key'   => 'scene_id',
				'columns' => array(
						'scene_id'    => 'scene_id',
						'user_id'     => 'user_id',
						'scene_type'  => 'scene_type',
						'url'         => 'url',
						'img'         => 'img',
						'expire_seconds' => 'expire_seconds',
						'created'     => 'created',
						'ticket'      => 'ticket'
				));
	}
	
	/**
	 * hook save_after
	 * @see StorageNode::save_after()
	 * @param integer $op_type Storage::SAVE_INSERT or Storage::SAVE_UPDATE
	 * @param boolean $op_succ true: op success, fail: op fail
	 */
	protected function save_after($op_type, $op_succ)  {
		
	}
	
}
 
/*----- END FILE: class.Wxqrcode.php -----*/