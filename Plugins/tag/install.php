<?php
/**
 * Installing necessary data for the tag plugin to work :
 *  - 3 tables
 *  - 3 DataObjects into the project's DB folder
 * @todo create a base class for plugin installers providing useful methods :
 *  - Retreiving database connection
 *  - Clearing config cache
 *  - Getting information about the project (mostly paths), while we use here the constant DB_FOLDER
 * it might be a better idea to have methods instead of constants ?
 */
$db->query(file_get_contents('M/Plugins/DB/src/tag.sql'));

foreach(array('Tag','Tag_record','Tag_history') as $table) {
  copy('M/Plugins/DB/src/'.$table.'.php',DB_FOLDER.'/'.$table.'.php');
}