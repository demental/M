<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @subpackage   scm.php
 * @author       Arnaud Sellenet <demental@sat2way.com>
 * @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
 * @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @version      0.1
 */

/**
 * command to use SCM (currently only subversion is supported)
 */


class Command_Scm extends Command
{
  public function shortHelp()
  {
    $this->line('Source Code Management interface');
  }
  public function longHelp($params)
  {
    $this->line('Source Code Management interface. Only subversion for now');
  }
  public function execute($params)
  {
    # code...
  }
}
