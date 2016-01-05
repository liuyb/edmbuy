<?php
/**
 * spm Model
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
class Spm extends StorageNode {

	const FLAG_NUM = 5; //corresponding filed 'flag\d+' num
	
	protected static function meta() {
		return array(
				'table'   => '{spm}',
				'key'     => 'rid',
				'columns' => array(
						'rid'   => 'rid',
						'key'   => 'key',
						'flag1' => 'flag1',
						'flag2' => 'flag2',
						'flag3' => 'flag3',
						'flag4' => 'flag4',
						'flag5' => 'flag5',
						'data'  => 'data',
						'dtime' => 'dtime'
				)
		);
	}
	
	/**
	 * Gen spm
	 * @return string
	 */
	public function gen_spm() {
		$spm  = '';
		for($i=1; $i<=self::FLAG_NUM; $i++) {
			$field = 'flag'.$i;
			if ($this->$field) {
				$spm .= '.' . $this->$field;
			}
		}
		if (''!==$spm) {
			$spm = substr($spm, 1);
		}
		return $spm;
	}
	

	/**
	 * Gen spm key
	 * @param string $flag1
	 * @param ...
	 * @return string
	 */
	static function gen_key($flag1) {
		$args = func_get_args();
		return empty($args) ? '' : md5(join('.', $args));
	}
	
	/**
	 * Check url spm
	 * @param string $url
	 * @return string
	 */
	static function check_spm($url = NULL) {
		if (!isset($url)) $url = Request::url();
		$spm = '';
		$url_query = parse_url($url, PHP_URL_QUERY);
		if ($url_query) {
			parse_str($url_query, $query_arr);
			$spm = isset($query_arr['spm']) ? $query_arr['spm'] : '';
		}
		return $spm;
	}
	
	/**
	 * Parse a spm string to an array
	 * @param string $spm
	 * @return array
	 */
	static function parse_spam($spm) {
		$arr = explode('.', $spm);
		$ret = [];
		$i = 1;
		foreach ($arr AS $a) {
			$ret['flag'.$i] = $a;
			$i++;
		}
		return $ret;
	}

}
 
/*----- END FILE: class.Spm.php -----*/