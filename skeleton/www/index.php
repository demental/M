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
* @author       Arnaud Sellenet <demental@sat2way.com>
* @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

define('APP_NAME','{$APP_NAME}');

require '{$APP_RELATIVE_FILE_TO_ROOT}/M_Startup.php';

 /**
 *
 * Dispatching
 *
 **/
 
$module = empty($_REQUEST['module'])?'defaut':$_REQUEST['module'];
$action = empty($_REQUEST['action'])?'index':$_REQUEST['action'];


$d = new Dispatcher($module,$action);
$d->setConfig(PEAR::getStaticProperty('Dispatcher','global'));
$d->execute();


header('Content-type:text/html; charset=utf-8');
echo $d->display();