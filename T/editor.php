<?php
/**
* M PHP Framework
* @package      M
* @subpackage   T_editor
*/
/**
* M PHP Framework
*
* Translation driver for editing purpose :
* - if a translation request is not present this driver adds it to the translations array
* - stores all used transation strings requested during the script
*
* @package      M
* @subpackage   T_editor
* @author       Arnaud Sellenet <demental at github>

* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

class T_editor extends T {
  public static $curStrings;
  public function translate( $string, $args = null )
  {
    if(empty($string)) {
      return;
    }
    self::$curStrings[]=$string;
      if(!key_exists($string,$this->strings)) {
        $this->strings[$string] = $string;
        if(is_array($args)) {
          return vsprintf($string,$args);
        }
        return $string;
      }
      $string = $this->strings[$string];
      if(is_array($args)) {
        return vsprintf($string,$args);
      }
      return $string;
  }
  private function getStringsFromXML ( $file )
  {
    if(!file_exists($file)) {
      if(!touch($file)) {
        throw new Exception('Unable to create file '.$file);
      }
    }
    return parent::getStringsFromXML($file);
  }
  public static function getCurStrings()
  {
    return array_unique(self::$curStrings);
  }
}