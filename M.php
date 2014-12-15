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
    $hooks = Config::get(strtolower($className).'_hooks');
    if(is_array($hooks) && key_exists($methodName, $hooks)) {
      foreach($hooks[$methodName] as $hookClass) {
        $hookObject = new $hookClass();
        $hookObject->apply($params);
      }
    }
  }
  public static function addHook($namespace, $methodToHook, $hookClass, $hookClassfile)
  {
    $hooks = Config::get($namespace.'_hooks');
    if(!is_array($hooks[$methodToHook])) {
      $hooks[$methodToHook] = array();
    }

    $hooks[$methodToHook][]= $hookClass;
    Mreg::append('autoload', array(
      strtolower($hookClass) => $hookClassfile
    ));
    Config::set($namespace.'_hooks', $hooks);
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
  public static function addPaths($role, $paths)
  {
    switch($role) {
      case 'models':
        $options = & PEAR::getStaticProperty('DB_DataObject', 'options');
        $options['class_location'] .= ':'.implode(',',$paths);
        break;
      case 'lang':
        foreach($paths as $path) {
          T::addPath($path);
        }
        break;
      case 'templates':
        $moduloptions = & PEAR::getStaticProperty('Module','global');
        if(!is_array($moduloptions['template_dir'])) $moduloptions['template_dir'] = array();
        $moduloptions['template_dir'] = array_merge($moduloptions['template_dir'], $paths);
      break;

      case 'modules':
        $dispatchopt = & PEAR::getStaticProperty('Dispatcher', 'global');
        if(!is_array($dispatchopt['all']['modulepath'])) $dispatchopt['all']['modulepath'] = array();
        $dispatchopt['all']['modulepath'] = array_merge($dispatchopt['all']['modulepath'], $paths);
      break;
      default:
        Mreg::append("{$role}_paths", array($path));
      break;
    }
  }

  public static function addPath($role, $path)
  {
    self::addPaths($role, array($path));
  }

  public static function getPaths($role)
  {
    $paths = Mreg::get("{$role}_paths");
    if(is_array($paths)) $paths = array();
    return $paths;
  }
}
