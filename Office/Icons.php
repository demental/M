<?php
/**
* M PHP Framework
* @package      M
* @subpackage   M_Office
*/
/**
* M PHP Framework
*
* Helper to print interface icons
*
* @package      M
* @subpackage   M_Office
* @author       Arnaud Sellenet <demental@sat2way.com>

* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

class Icons {
    
    public static $iconList = array(
        'delete'=>'bin_closed.png',
        'edit'=>'application_edit.png');
    
    public function getIcon ($value)
    {
        return '<img src="'.SITE_URL.'images/icons/'.Icons::$iconList[$value].'" alt="'.$value.'" border="0" />';
    }
    
}

?>