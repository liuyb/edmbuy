<?php
/**
 * 单机锁
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
class Locker {

  private static $file_handlers = array();

  private static function file($key) {
    return LOG_DIR . '/' . md5($key) . '.locker';	//需要避免传入的key可能包含特殊字符，作文件名有问题
  }

  public static function lock($key) {
    if(isset(self::$file_handlers[$key])) return false;
    self::$file_handlers[$key] = fopen(self::file($key), 'w+');
    return flock(self::$file_handlers[$key], LOCK_EX | LOCK_NB);	//独占锁、非阻塞
  }

  public static function unlock($key) {
    if (isset(self::$file_handlers[$key])) {
      fclose(self::$file_handlers[$key]);
      @unlink(self::file($key));
      unset(self::$file_handlers[$key]);
    }
  }

}
 
/*----- END FILE: class.Locker.php -----*/