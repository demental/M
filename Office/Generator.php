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
* @subpackage   M_Office
*/
/**
* M PHP Framework
*
* class allowing web user to launch DB_DataObject Generator PLUS adds new modules in the modules database table
*
* @package      M
* @subpackage   M_Office
* @author       Arnaud Sellenet <demental@sat2way.com>
* @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

class M_Office_Generator
{

/**
 * Static method
 **/
	function regenerateSchema(){
		$config =& PEAR::getStaticProperty('DB_DataObject', 'options');
	  require_once('M/DB/DataObject/Advgenerator.php');
	  if (!isset($_REQUEST['debug'])) {
	      DB_DataObject::debugLevel(5);
	  }
	  $generator = new DB_DataObject_Advgenerator();
	  $generator->start();
		$AuthOptions = PEAR::getStaticProperty('M_Office_auth', 'options');	
		$tablelist=$AuthOptions['tablelisttables'];
		if(!empty($tablelist)){
			if(is_array($GLOBALS['_DB_DATAOBJECT']['INI'][$_REQUEST['database']])){
				$tables = array_keys($GLOBALS['_DB_DATAOBJECT']['INI'][$_REQUEST['database']]);
				$invisibletables=$this->getOption('invisibletables');
				if(!is_array($invisibletables)){
					$invisibletables=array();
				}
				array_push($invisibletables,$tablelist);
				foreach($tables as $table){
					if (substr($table, -6) != '__keys' && !in_array($table,$invisibletables)) {
						$tablerecord=& DB_DataObject::factory($tablelist);
						if(PEAR::isError($tablerecord)){
							die($tablerecord->getMessage());
						}
						$tablerecord->name=$table;
						$tableDO=& DB_DataObject::factory($table);
						if(!$tablerecord->find(true)){
							$tablerecord->insert();
						}
						if(!empty($tableDO->fb_formHeaderText)){
							$tablerecord->frontname=$tableDO->fb_formHeaderText;
						} else {
							$tablerecord->frontname=$table;
						}
						$tablerecord->update();
					}
				}
			}
		}
		system('chmod -R 777 '.escapeshellarg($config['class_location']));
	  system('chmod -R 777 '.escapeshellarg($config['schema_location']));  
	}
}