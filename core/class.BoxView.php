<?php
/**
 * Box View Class 
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

class BoxView extends View {
  
  /**
   * Constructor
   * 
   * @param string $tpl_name, Name of Template for rendering
   */
  public function __construct($tpl_name) {
    
    parent::__construct($tpl_name);
    
  }
  
  /**
   * filter render output
   * @param string $content
   * @return string
   */
  public function filter_output($content) {    
    return $content;
  }
  
}

/*----- END FILE: class.BoxView.php -----*/