<?php
/**
* M PHP Framework
* @package      M
* @subpackage   tests
*/
/**
* M PHP Framework
*
* DB_DataObject used for unit testing
*
* @package      M
* @subpackage   tests
* @author       Arnaud Sellenet <demental at github>

* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

/**
 * Table Definition for notmigrated_withspecialnames
 */
require_once 'M/DB/DataObject/Pluggable.php';

class DataObjects_Notmigrated_withspecialnames extends DB_DataObject_Pluggable 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'notmigrated_withspecialnames';    // table name
    public $id;                              // int(4)  primary_key not_null unsigned
    public $long;                            // varchar(255)   not_null
    public $int;                             // mediumtext()   not_null
    public $pays;                            // char(2)   not_null
    public $testuser_id;                     // int(4)   not_null unsigned

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('DataObjects_Notmigrated_withspecialnames',$k,$v); }

    function table()
    {
         return array(
             'id' =>  DB_DATAOBJECT_INT + DB_DATAOBJECT_NOTNULL,
             'long' =>  DB_DATAOBJECT_STR + DB_DATAOBJECT_NOTNULL,
             'int' =>  DB_DATAOBJECT_STR + DB_DATAOBJECT_TXT + DB_DATAOBJECT_NOTNULL,
             'pays' =>  DB_DATAOBJECT_STR + DB_DATAOBJECT_NOTNULL,
             'testuser_id' =>  DB_DATAOBJECT_INT + DB_DATAOBJECT_NOTNULL,
         );
    }

    function keys()
    {
         return array('id');
    }

    function sequenceKey() // keyname, use native, native name
    {
         return array('id', true, false);
    }

    function defaults() // column default values 
    {
         return array(
             '' => null,
         );
    }

    function links() {
        // links generated from .links.ini file
        return array(

        );
    }
    function reverseLinks() {
        // reverseLinks generated from .links.ini file
        return array(

        );
    }
    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    public $internationalFields = array('long','int');    
}
