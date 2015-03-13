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
 * Table Definition for formtest
 */
require_once 'M/DB/DataObject/Pluggable.php';

class DataObjects_Formtest extends DB_DataObject_Pluggable 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'formtest';                        // table name
    public $id;                              // int(4)  primary_key not_null unsigned
    public $pays;                            // char(2)   not_null
    public $testuser_id;                     // int(4)   not_null unsigned

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('DataObjects_Formtest',$k,$v); }

    function table()
    {
         return array(
             'id' =>  DB_DATAOBJECT_INT + DB_DATAOBJECT_NOTNULL,
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
    public $i18nFields = array('titre','description');
    public $fb_fieldAttributes = array('titre'=>'size="50"','description'=>'class="red"');
}
