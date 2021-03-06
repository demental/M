<?php
/**
* M PHP Framework
* @package      M
*/
/**
* M PHP Framework
*
* Controller main file for default app
*
* @package      M
* @author       Arnaud Sellenet <demental at github>

* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

define('APP_NAME','{$APP_NAME}');

require '{$APP_RELATIVE_FILE_TO_ROOT}/M_Startup.php';


require(APP_ROOT.'app/'.APP_NAME.'/routing.php');
$result = Net_URL_Mapper::getInstance()->match($_GET['route']);
$getvalues = array_merge($result,$_GET);
$requestvalues = array_merge($_POST,$get);

$request = new MRequest($getvalues,$requestvalues);

$d = new Dispatcher($request);
$d->setConfig(PEAR::getStaticProperty('Dispatcher','global'));

$d->execute();

header('Content-type:text/html; charset=utf-8');
echo $d->display();
