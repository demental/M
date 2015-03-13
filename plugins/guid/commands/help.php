<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @subpackage   help.php
 * @author       Arnaud Sellenet <demental at github>
 * @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @version      0.1
 */

/**
 * Description of guid plugins
 */

class Guid_command_help extends Command {
  public function execute($params)
  {
    $this->line('The GUID plugin allows to create 36 characters UUID primary keys values.');
    $this->line('Requirements:');
    $this->line('* The table must have a CHAR(36) ascii_general_ci primary key');
    $this->line('* gui must be declared in the _getPluginsDef() method:');
    $this->line('  public function _getPluginsDef() {');
    $this->line('     return array(\'guid\'=>true);');
    $this->line('  }');
  }
}  
 