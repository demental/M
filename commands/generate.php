<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @subpackage   generate.php
 * @author       Arnaud Sellenet <demental@sat2way.com>
 * @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @version      0.1
 */

/**
 * Command to invoke generators
 */

class Command_generate extends Command {
  public function shortHelp()
  {
    return $this->line('Invoke generators');
  }

  public function longHelp($params)
  {
    $this->shortHelp();
    $this->line('Usage: ');
    $this->line('generate migration [NAME_OF_MIGRATION] [FORMAT(php:default|sql)]');
  }

  public function execute($params)
  {
    $generator = array_shift($params);
    if(!method_exists($this, 'generate_'.$generator)) return $this->error('No '.$generator.' generator');
    call_user_func_array(array($this,'generate_'.$generator), $params);
  }

  public function generate_migration($name, $format = 'php', $pluginName = '')
  {
    if(!empty($pluginName)) {
      $basepath = PluginRegistry::initPlugin($pluginName);
    } else {
      $basepath = APP_ROOT;
    }
    $file = $basepath.'db/migrations/'.date('YmdHi').'_'.Strings::snake($name).'.'.$format;
    $classname = Strings::camel($name);
    $source = file_get_contents(dirname(__FILE__).'/generate/migration/'.$format.'.tpl');
    $source = str_replace('[MIGRATION_NAME]', $classname, $source);
    file_put_contents($file, $source);
    $this->line($file.' created');
  }
}
