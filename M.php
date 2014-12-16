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

class UnresolvedFileException extends Exception {}
class UnresolvedClassException extends Exception {}

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
    Config::set("{$namespace}_hooks", $hooks);
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
        Mreg::append("{$role}_paths", $paths);
      break;
    }
  }

  public static function addPath($role, $path)
  {
    self::addPaths($role, array($path));
  }

  public static function getPaths($role)
  {
    try {
      $paths = Mreg::get("{$role}_paths");
    } catch(Exception $e) {
      $paths = array();
    }
    return $paths;
  }

  public static function resolve_class($class, $role, $init_function = null)
  {
    if(class_exists($class)) return true;
    $file = strtolower(str_replace('_','/',$class)) . '.php';
    foreach(self::getPaths($role) as $path) {
      $full_path = $path.DIRECTORY_SEPARATOR.$file_name;
      if(FileUtils::file_exists_incpath($full_path)) {
        require_once(self::resolve_file($file, $role));
        if($init_function instanceOf Closure) $init_function();
        return true;
      }
    }
    throw new UnresolvedFileException("Could not load class {$class} in ".print_r(self::getPaths($role), true));
  }

  public static function resolve_file($file_name, $role)
  {
    foreach(self::getPaths($role) as $path) {
      $full_path = $path.DIRECTORY_SEPARATOR.$file_name;
      if(file_exists($full_path)) return realpath($full_path);
    }
    throw new UnresolvedFileException("Could not find {$file_name} in ".print_r(self::getPaths($role), true));
  }
}
