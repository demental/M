<?php
/**
* M PHP Framework
* @package      M
* @subpackage   M_Office
*/
/**
* M PHP Framework
*
* Pregenerated DB_DataObject for M_Office built-in blackboard 
*
* @package      M
* @subpackage   M_Office
* @author       Arnaud Sellenet <demental at github>

* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

 /**
 * Table Definition for blackboard
 */

class DataObjects_Blackboard extends DB_DataObject_Pluggable 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'blackboard';                      // table name
    public $id;                              // int(4)  primary_key not_null
    public $date;                            // datetime()   not_null
    public $by;                              // varchar(36)  
    public $content;                         // mediumtext()   not_null

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('DataObjects_Blackboard',$k,$v); }

    function table()
    {
         return array(
             'id' =>  DB_DATAOBJECT_INT + DB_DATAOBJECT_NOTNULL,
             'date' =>  DB_DATAOBJECT_STR + DB_DATAOBJECT_DATE + DB_DATAOBJECT_TIME + DB_DATAOBJECT_NOTNULL,
             'by' =>  DB_DATAOBJECT_STR,
             'content' =>  DB_DATAOBJECT_STR + DB_DATAOBJECT_TXT + DB_DATAOBJECT_NOTNULL,
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
    public $fb_fieldsToRender = array('content');
    public function insert()
    {
      $this->date = date('Y-m-d H:i:s');
      return parent::insert();
    }
}