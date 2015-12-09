<?php
/**
 * Error Handler Base Class
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class ErrorHandler {
  
  private static $shutdown;
  public static function handleException(Exception $e) {
    self::shutdown("Exception " . $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTrace());
  }

  public static function handleError($errno, $msg, $file, $line) {
    self::shutdown($msg, $file, $line, array_slice(debug_backtrace(), 0, -1));
  }

  public static function handleShutdown() {
    if ( ($err = error_get_last()) !== NULL) {
      self::shutdown(
        $err['message'],
        $err['file'],
        $err['line'],
        array_slice(debug_backtrace(), 0, -1)
      );
    }
  }

  private static function shutdown($msg, $file, $line, $backtrace) {
    return;
    if (self::$shutdown) exit();
    /*
    header('HTTP/1.1 500 Internal Server Error');
    $data = ['body' => 'We\'ll be back soon!', 'state' => '500'];
    if (ON_TEST) {
      $data['body'] = "\n{$msg}\n{$file} line:{$line}\n";
    }
    echo new View('message', $data);

    $fire_wall = new Firewall('error:' . md5($msg . $file . $line), 3600, 10);
    if (!$fire_wall->hit()) {
      $user = new User();
      $user->email = 'zhaojun@goo.do';
      Email::send($user, '线上错误', backtrace_html($backtrace) . debug_info_html("$msg in $file on $line"));
    }
    */
    self::$shutdown = true;
    exit();
  }
  
}
 
 
/*----- END FILE: class.ErrorHandler.php -----*/