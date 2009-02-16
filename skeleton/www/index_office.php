<?php
//
// +--------------------------------------------------------------------+
// | M PHP Framework                                                    |
// +--------------------------------------------------------------------+
// | Copyright (c) 2003-2009 Arnaud Sellenet demental.info              |
// | Web           http://m4php5.googlecode.com/                        |
// | License       GNU Lesser General Public License (LGPL)             |
// +--------------------------------------------------------------------+
// | This library is free software; you can redistribute it and/or      |
// | modify it under the terms of the GNU Lesser General Public         |
// | License as published by the Free Software Foundation; either       |
// | version 2.1 of the License, or (at your option) any later version. |
// +--------------------------------------------------------------------+
//

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
* @author       Arnaud Sellenet <demental@sat2way.com>
* @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
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