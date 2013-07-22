<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @author       Arnaud Sellenet <demental@sat2way.com>
 * @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @version      0.1
 */

/**
 * Class to manage plugins (storing and creating)
 */

class PluginRegistry
{
  private static $_instances;
  private static $_names;
  public static $plugins_dir = 'M/Plugins/';
  public static final function getInstance($pluginName, $section = 'DB')
  {
    if(!self::$_instances[$pluginName][$section]) {
      self::$_instances[$pluginName][$section] = self::create($pluginName, $section);
    }
    return self::$_instances[$pluginName][$section];
  }
  /**
  * Creates a plugin instance, given its name and section
  * @param $pluginName string
  * @param $section string (default = DB)
  * @return DB_DataObject_Plugin
  * @throws Exception if plugin doesn't exist
  **/
  public static final function create($pluginName, $section = 'DB')
  {
    if(!$className = self::_load($pluginName,$section)) {
      throw new Exception(__('plugin "%s" does not exist',array($pluginName)));
    }
    $plugin = new $className($params);
    return $plugin;
  }
  /**
   * tries to include the requested plugin and returns the plugin class name, or false if plugin not found.
   * @param string $pluginName name of the plugin
   * @return string class name or false if not found
   */
  protected function _load($pluginName,$section)
  {
    $cleanName = strtolower(FileUtils::sanitize($pluginName));
    $className = 'DB_DataObject_Plugin_'.$cleanName;
    if(class_exists($className,false)) {
      return $className;
    }
    $classpaths = self::getPaths($cleanName,'DB');

    foreach($classpaths as $pluginPath) {
      $classpath = $pluginPath.$cleanName.'.php';
      if(file_exists($classpath)) {
        require_once $classpath;
        self::initPlugin($cleanName);
        return $className;
      }
    }

    return false;
  }
  /**
   * Returns the paths potentially associated with a plugin
   */
  public static function getPaths($cleanName,$section) {
    return array(
      APP_ROOT.PROJECT_NAME.'/Plugins/'.$cleanName.'/'.$section.'/',
      dirname(__FILE__).'/Plugins/'.$cleanName.'/'.$section.'/'
    );
  }
  public static function initPlugin($pluginName)
  {
    if(!$folder = self::path($pluginName)) return false;
    self::$_names[]=$pluginName;
    if(file_exists($folder.'init.php')) {
      include_once $folder.'init.php';
      return true;
    }
    return false;
  }
  public static function path($pluginName)
  {
    foreach(self::getPaths($pluginName,'') as $folder) {
      if(is_dir($folder)) return $folder;
    }
    return false;
  }
  public static function registeredPlugins()
  {
    return self::$_names;
  }
}