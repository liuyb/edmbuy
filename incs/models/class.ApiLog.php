<?php
/**
 * API log model
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
class ApiLog extends StorageNode {
	
	protected static function meta() {
		return array(
				'table'   => '{api_log}',
				'key'     => 'logId',
				'columns' => array(
						'appId'     => 'appid',
						'args'      => 'args',
						'resp'      => 'resp',
						'format'    => 'format',
						'sig'       => 'sig',
						'sigSrv'    => 'sig_srv',
						'ts'        => 'ts',
						'v'         => 'v',
						'ip'        => 'ip',
						'method'    => 'method',
						'reqTime'   => 'reqtime',
						'dealTime'  => 'dealtime',
				)
		);
	}
	
}
 
/*----- END FILE: class.ApiLog.php -----*/