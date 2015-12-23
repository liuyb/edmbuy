<?php
/**
 * Feedback Model
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
class Feedback extends StorageNode {
	
	protected static function meta() {
		return array(
				'table'   => '`shp_feedback`',
				'key'     => 'msg_id',   //该key是应用逻辑的列，当columns为array时，为columns的key，否则，则要设成实际存储字段
				'columns' => '*'
		);
	}
	
}
 
/*----- END FILE: class.Feedback.php -----*/