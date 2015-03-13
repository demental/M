<?php
/**
* M PHP Framework
* @package      M
* @subpackage   T_reader
*/
/**
* M PHP Framework
*
* Translation driver for reading purpose
*
* @package      M
* @subpackage   T_reader
* @author       Arnaud Sellenet <demental at github>

* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

class T_reader extends T {
  public function translate( $string, $args = null )
  {
    if(empty($string)) {
      return;
    }
    if(!key_exists($string,$this->strings)) {
      return $args?vsprintf($string,$args):$string;
    }
    $string = $this->strings[$string];
    if(is_array($args)) {
      return vsprintf($string,$args);
    }
    return $string;
  }
}
