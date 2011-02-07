<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @subpackage   extractlng.php
 * @author       Arnaud Sellenet <demental@sat2way.com>
 * @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @version      0.1
 */

/**
 * Extracts lang files
 */
 
class Command_extractlng extends Command {
  public function execute($params)
  {
    $lang = $params[0];
    if(!in_array($lang,Config::getAllLangs())) {
      throw new Exception('Specified lang is not part of this project handled languages');
    }
    foreach(FileUtils::getAllFiles(APP_ROOT.PROJECT_NAME.'/'.APP_NAME) as $file ) {
      $result = preg_match_all('`(?:__|_e)\(\'(.+)\'(?:,array\(.+\))?\)`sU',file_get_contents($file),$matches);
      foreach($matches[1] as $elem) {
        $nbfound++;
        __(str_replace("\'","'",$elem));
      }
    }
    $arr = T::getInstance($lang)->getStrings();
    T::getInstance($lang)->save(true);
  }
} 
