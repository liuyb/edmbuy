<?php
/**
 * Yaml Class
 * 
 * Parses YAML strings to PHP arrays.
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

require_once SIMPHP_CORE . '/libs/yaml/sfYaml.php';

class Yaml
{
  /**
   * Loads YAML into a PHP array.
   *
   * The load method, when supplied with a YAML stream (string or file),
   * will do its best to convert YAML in a file into a PHP array.
   *
   *  Usage:
   *  <code>
   *   $array = Yaml::load('config.yml');
   *   print_r($array);
   *  </code>
   *
   * @param string $input Path of YAML file or string containing YAML
   * @return array The YAML converted to a PHP array
   */
  public static function load($input)
  {
    return sfYaml::load($input);
  }

  /**
   * Dumps a PHP array to a YAML string.
   *
   * The dump method, when supplied with an array, will do its best
   * to convert the array into friendly YAML.
   *
   * @param array   $array PHP array
   * @param string  $file YAML file path
   * @param integer $inline The level where you switch to inline YAML
   * @return mixed string when sucess or false when fail
   */
  public static function dump($data, $file = null, $inline = 2)
  {
    $yaml = sfYaml::dump($data, $inline);

    if (empty($file) || file_put_contents($yaml, $file)) {
      return $yaml;
    }

    return false;
  }
}

/*----- END FILE: class.Yaml.php -----*/