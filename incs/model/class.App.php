<?php
/**
 * App Model
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
class App extends StorageNode {

	protected static function meta() {
		return array(
				'table'   => '{app}',
				'key'     => 'appId',
				'columns' => array(
					'appId'      => 'app_id',
					'appName'    => 'app_name',
					'appSecret'  => 'app_secret',
					'isInternal' => 'is_internal',
					'created'    => 'created',
					'changed'    => 'changed',
				)
		);
	}

}
 
/*----- END FILE: class.App.php -----*/