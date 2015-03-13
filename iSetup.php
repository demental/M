<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @subpackage   iSetup
 * @author       Arnaud Sellenet <demental at github>
 * @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @version      0.1
 */

/**
 *
 * Simple application setup interface
 * Used to "boot" the current application
 * e.g. fill the configuration values (Database and other stuff needed at startup)
 *
 */
interface iSetup {
	public function setUpEnv();
}