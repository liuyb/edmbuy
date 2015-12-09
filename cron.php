#!/usr/bin/php
<?php
/**
 * cron 脚步执行入口
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
//~ require init.php
require (__DIR__.'/core/init.php');

SimPHP::I()->boot();

if (!defined('CRON_ROOT')) {
  define('CRON_ROOT', SIMPHP_ROOT . '/cron');
}
if (!defined('BR')) {
  define('BR', IS_CLI ? "\n" : "<br>");
}

class JobManager {

  // PHP commond path, maybe need changing here
  const PHP_CMD = '/usr/bin/php'; 
  //const PHP_CMD = '/usr/local/php-fcgi/bin/php';
  
  private function jobs() {
    $dir  = CRON_ROOT;
    $jobs = [];
    $_handler = opendir($dir);
    while ( false !== ($filename = readdir($_handler)) ) {
      if ( preg_match("/^(.+Job)\.php$/", $filename, $matches) && is_file("{$dir}/$filename") ) {
        $jobs[$matches[1]] = "{$dir}/{$filename}";
      }
    }
    return $jobs;
  }

  public function exec() {
    foreach($this->jobs() as $name => $file) {
      $pid = shell_exec(self::PHP_CMD." {$file} >> " . LOG_DIR . "/job_exec.log 2>&1 &");
      SystemLog::local_log('job_manager', "{$name} started, PID:" . str_replace('[1]', '', $pid));
    }
  }
  
  public function usage($msg = '') {
    echo (''==$msg ? "Usage: [php] [DIR PREFIX]/cron.php JOB_NAME [args...]" : $msg).BR;
    exit;
  }
  
  public function run($argc, $argv) {
    if ($argc < 2) {
      $this->usage();
    }
    $job  = $argv[1];
    $jobs = $this->jobs();
    if (!array_key_exists($job, $jobs)) {
      $this->usage("Job '{$job}' not exists.");
    }
    include $jobs[$job];
    $argv = array_slice($argv, 1);
    $argc = count($argv);
    $j = new $job;
    $j->log("{$job} Beginning...");
    $j->main($argc, $argv);
    $j->log("{$job} Finished!\n");
  }
  
}

if (!IS_CLI) {
  $argv = array('cron.php');
  if (isset($_GET['q']) && !empty($_GET['q'])) {
    $qarr = explode('/', $_GET['q']);
    $argv = array_merge($argv, $qarr);
  }
  $argc = count($argv);
}

(new JobManager)->run($argc, $argv);
 
/*----- END FILE: cron.php -----*/