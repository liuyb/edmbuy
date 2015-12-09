<?php
/**
 * 测试作业
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
class TestJob extends CronJob {
  
  
  public function main($argc, $argv) {
    
    echo "TestJob doing, argc={$argc}, argv=".BR;
    print_r($argv);
    echo BR;
    
  }
  
}
 
/*----- END FILE: TestJob.php -----*/