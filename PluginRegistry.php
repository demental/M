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
  private static $_instances = array();
  private static $_names = array();
  public static $plugins_dir = 'M/plugins/';
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
    $cleanName = FileUtils::sanitize($pluginName);
    $className = strtolower("Plugins_{$cleanName}_{$section}");
    if (M::resolve_class($className, 'plugins', function() use ($cleanName) { PluginRegistry::initPlugin($cleanName); })) return $className;
  }
  /**
   * Returns the paths potentially associated with a plugin
   */
  public static function getPaths($cleanName,$section) {
    return array(
      APP_ROOT.PROJECT_NAME.'/plugins/'.$cleanName.'/'.$section.'/',
      dirname(__FILE__).'/plugins/'.$cleanName.'/'.$section.'/'
    );
  }
  public static function initPlugin($pluginName)
  {
    require M::resolve_file("plugins/{$pluginName}/init.php",'plugins');
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
