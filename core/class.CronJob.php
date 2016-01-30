<?php
/**
 * Crontab job abstract class
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

abstract class CronJob {
  
  /**
   * abstract method, job main entry point
   */
  abstract public function main($argc, $argv);
  
  /**
   * save cron job log
   * @param string $string
   */
  final public function log($string) {
    SystemLog::local_log('CronJob_' . get_class($this), $string);
  }
  
  /**
   * Usage
   */
  final public function usage($string = '') {
  	echo "Usage: php cron.php ".get_class($this).(''==$string ? '' : ' '.$string)."\n";
  	exit;
  }
  
}


/*----- END FILE: class.CronJob.php -----*/