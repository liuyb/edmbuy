<?php
/**
 * Weixin Server Controller
 *
 * @author Gavin
 */
defined('IN_SIMPHP') or die('Access Denied');

class Weixin_Controller extends Controller {
  
  public function menu() {
    return [
      'weixin/%s' => 'index'
    ];
  }
  
  /**
   * hook init
   *
   * @param string $action
   * @param Request $request
   * @param Response $response
   */
  public function init($action, Request $request, Response $response)
  {
    
  }
  
  /**
   * default action 'index'
   *
   * @param Request $request
   * @param Response $response
   */
  public function index(Request $request, Response $response)
  {
    $t = $request->arg(1);
    $wx= new Weixin([],$t);
    if (!isset($_GET['echostr'])) {
      if($wx->checkSignature()){//签名检测
        $wx->responseMsg();
      }
      else {
        echo '';
      }
    }
    else { //接口验证
      $wx->valid();
    }
    exit;
  }

}

/*----- END FILE: Weixin_Controller.php -----*/