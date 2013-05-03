<?php
/**
* M PHP Framework
* @package      M
* @subpackage   T_tracker
*/
/**
* M PHP Framework
*
* Translation driver to track usage. Goal is to find unused strings.
*
* @package      M
* @subpackage   T_reader
* @author       Arnaud Sellenet <demental@sat2way.com>

* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

class T_tracker extends T {
  public function init ( $lang ,$verbose = false)
  {
    parent::init($lang, $verbose);
    $file = T::getConfig('path').$this->locale."_usage.php";
    if(file_exists($file)) {
      $this->strings_usage = unserialize(file_get_contents($file));
    }
  }
  public function translate( $string, $args = null )
  {
    if(empty($string)) {
      return;
    }
    if(!key_exists($string,$this->strings)) {
      return $args?vsprintf($string,$args):$string;
    }
    $string = $this->strings[$string];
    $this->addUsage[$string];
    if(is_array($args)) {

      return vsprintf($string,$args);
    }
    return $string;
  }
  public function addusage($string)
  {
    if(!is_array($this->strings_usage)) {
      $this->strings_usage = $this->strings;
      array_walk($this->strings_usage, array($this, 'zeroify'));
    }
    $this->strings_usage[$string]++;
  }
  public function zeroify(&$item, $k)
  {
    $item = 0;
  }
  public function save($verbose = false, $destfile= '')
  {
    $destfile = T::getConfig('path').$this->locale."_usage.php";
    file_put_contents($destfile, serialize($this->strings_usage));
  }
}