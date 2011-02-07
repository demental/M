<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @subpackage   assetblock.php
 * @author       Arnaud Sellenet <demental@sat2way.com>
 * @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @version      0.1
 */

/**
 * This filter replaces [[CSS]] and [[JS]] with CSS and JS declarations
 */

class Mtpl_assetblock extends Mtpl_Filter
{
  public $_JSblock="[[JS]]";
  public $_CSSblock="[[CSS]]";
  
  public function execute(&$input)
  {
    $input = str_replace($this->_JSblock,Mtpl::getJSblock(),$input);
    $input = str_replace($this->_CSSblock,Mtpl::getCSSblock(),$input);
  }
}
