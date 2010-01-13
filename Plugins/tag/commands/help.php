<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @subpackage   help.php
 * @author       Arnaud Sellenet <demental@sat2way.com>
 * @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
 * @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @version      0.1
 */

/**
 * description for the tag plugin
 */

class Tag_Command_Help extends Command {
  public function execute($params)
  {
    $this->line('Adds database-wide tagging capacity');
  }
}