<?php
/**
* M PHP Framework
* @package      M
*/
/**
* M PHP Framework
*
* Controller main file for office app
*
* @package      M
* @author       Arnaud Sellenet <demental at github>

* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

define('APP_NAME','{$APP_NAME}');
define('IN_ADMIN',1);
require '{$APP_RELATIVE_FILE_TO_ROOT}/M_startup.php';

require 'M/Office.php';

define('ROOT_ADMIN_URL',SITE_URL.'{$APP_RELATIVE_FILE_FROM_DOCROOT}');


Mreg::get('setup')->setUpEnv();
$frontend = new M_Office();

header('Content-type:text/html; charset=utf-8');
echo $frontend->display();