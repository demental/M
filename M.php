<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @subpackage   M.php
 * @author       Arnaud Sellenet <demental@sat2way.com>
 * @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @version      0.1
 */

/**
 * This class is used to retreive specific environment variables
 * @todo : this makes the package not portable as mysql bin path and pear path are hardcoded.
 * find a way to guess these values or store them as environment specific path (i.e. the domain based config file)
 */

class M {
  public static function getPearpath()
  {
    return '/Applications/MAMP/bin/php5/lib/php/';
  }
  public static function getSQLbinpath()
  {
    return '/Applications/xampp/xamppfiles/bin/mysql';
  }
  public static function getDatabaseDSN()
  {
    $p = PEAR::getStaticProperty('DB_DataObject','options');
    return $p['database'];
  }
  public static function hook($className,$methodName,$params = array())
  {

    if(method_exists($className.'_hook',$methodName)) {
      return call_user_func_array(array($className.'_hook',$methodName),$params);
    }
  }
  public static function tablesWithPlugin($pluginName)
  {
    foreach(FileUtils::getAllFiles(APP_ROOT.PROJECT_NAME.'/DOclasses/','php') as $file) {
      $t = DB_DataObject::factory(strtolower(basename($file,'.php')));

      if(PEAR::isError($t)) continue;
      $plugs = $t->_getPluginsDef();
      if($plugs[$pluginName]) {
        $ret[] = $t->tableName();
      }
    }
    return $ret;
  }
}
