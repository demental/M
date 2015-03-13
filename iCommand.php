<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @subpackage   iCommand.php
 * @author       Arnaud Sellenet <demental at github>
 * @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @version      0.1
 */

/**
 * Interface to provide mechanism for a CLI command.
 * Is to be used if you need to create commands in plugins (e.g. install/uninstall)
 */
interface iCommand {
  /**
   * Method that will be fired before Mreg::get('setup')->setUpEnv();
   * @return bool if false, command is aborted
   */
  public static function preSetup();
  /**
   * Main method fired after setupEnv
   * @return bool (success or fail)
   */
  public static function execute();
}