<?php
// ============================
// = DB_DataObject Datasource for Structures_DataGrid that makes Auto-join foreign records
// = Check if this is not deprecated as it's not been used for a while... (using DBDO Pager plugin now)
// ============================
require_once 'Structures/DataGrid/DataSource.php';

class Structures_DataGrid_DataSource_MyDataObj2
    extends Structures_DataGrid_DataSource
{
    /**
     * Reference to the MyDataObj
     *
     * @var object MyDataObj
     * @access private
     */
    var $_dataobject;

    /**
     * Total number of rows
     *
     * This property caches the result of DataObject::count(), that
     * can't be called after DataObject::fetch() (DataObject bug?).
     *
     * @var int
     * @access private
     */
     var $_rowNum = null;

    /**
     * Constructor
     *
     * @param object DB_DataObject
     * @access public
     */
    function Structures_DataGrid_DataSource_MyDataObj2()
    {
        parent::Structures_DataGrid_DataSource();

        $this->_addDefaultOptions(array(
                    'use_private_vars' => false,
                    'labels_property' => 'fb_fieldLabels',
                    'fields_property' => 'fb_fieldsToRender',
                    'sort_property' => 'fb_linkOrderFields',
                    ));

        $this->_setFeatures(array('multiSort' => true));
    }

    /**
     * Bind
     *
     * @param   object DB_DataObject    $dataobject     The DB_DataObject object
     *                                                  to bind
     * @param   array                   $options        Associative array of
     *                                                  options.
     * @access  public
     * @return  mixed   True on success, PEAR_Error on failure
     */
    function bind(&$dataobject, $options=array())
    {
        if ($options) {
            $this->setOptions($options);
        }

        if (is_subclass_of($dataobject, 'DB_DataObject')) {
            $this->_dataobject =& $dataobject;
            $this->_dataobjectC = clone($dataobject);
            $mergeOptions = array();
            // Merging the fields and fields_property options
            if (!$this->_options['fields']) {
                if ($fieldsVar = $this->_options['fields_property']
                    && isset($this->_dataobject->$fieldsVar)) {

                    $mergeOptions['fields'] = $this->_dataobject->$fieldsVar;
                    if (isset($this->_dataobject->fb_preDefOrder)) {
                        $ordered = array();
                        foreach ($this->_dataobject->fb_preDefOrder as
                                 $orderField) {
                            if (in_array($orderField,
                                         $mergeOptions['fields'])) {
                                $ordered[] = $orderField;
                            }
                        }
                        $mergeOptions['fields'] =
                            array_merge($ordered,
                                        array_diff($mergeOptions['fields'],
                                                   $ordered));
                    }
                    foreach ($mergeOptions['fields'] as $num => $field) {
                        if (strstr($field, '__tripleLink_') ||
                            strstr($field, '__crossLink_') ||
                            strstr($field, '__reverseLink_')) {
                            unset($mergeOptions['fields'][$num]);
                        }
                    }
                }
            }

            // Merging the labels and labels_property options
            if (!$this->_options['labels']
                && $labelsVar = $this->_options['labels_property']
                && isset($this->_dataobject->$labelsVar)) {

                $mergeOptions['labels'] = $this->_dataobject->$labelsVar;

            }

            if ($mergeOptions) {
                $this->setOptions($mergeOptions);
            }
            if(!is_array($this->_options['fields']) || count($this->_options['fields'])) {
                $this->_options['fields'] = array_keys($this->_dataobject->table());
            }

            $links = $this->_dataobject->links();
            $showlinks=array_intersect_key($links,array_flip($this->_options['fields']));
            $nolinks = array_diff(array_flip($links),$this->_options['fields']);
            $this->_dataobject->setAlias('do_maintable');
            $this->_dataobject->selectAdd();
            foreach($this->_options['fields'] as $f) {
                $this->_dataobject->selectAdd($this->_dataobject->tableName().'.'.$f);
            }

            $i=0;
            foreach($showlinks as $link=>$fields) {
              $finfo = explode(':',$fields);
              $ftable = & DB_DataObject::factory($finfo[0]);

              $this->_dataobject->joinAdd($ftable,'LEFT','table'.$i,$link);
              $this->_dataobject->selectAs($ftable,$link.'__%s','table'.$i);

              $i++;
            }
            return true;
        } else {
            return PEAR::raiseError('The provided source must be a DB_DataObject');
        }
    }

    /**
     * Fetch
     *
     * @param   integer $offset     Limit offset (starting from 0)
     * @param   integer $len        Limit length
     * @access  public
     * @return  array   The 2D Array of the records
     */
    function &fetch($offset=0, $len=null)
    {
				$eltTypes=$this->_dataobject->table();
        // Check to see if Query has already been submitted
        if ($this->_dataobject->_DB_resultid != '') {
            $this->_rowNum = $this->_dataobject->N;
            $joined = false;
        } else {
            // Caching the number of rows
            if (PEAR::isError($count = $this->count())) {
                return $count;
            } else {
                $this->_rowNum = $count;
            }

            // Sorting
            if (($sortProperty = $this->_options['sort_property'])
                      && isset($this->_dataobject->$sortProperty)) {
                foreach ($this->_dataobject->$sortProperty as $sort) {
                    $this->sort($sort);
                }
            }

            // Limiting
            if ($offset) {
                $this->_dataobject->limit($offset, $len);
            } elseif ($len) {
                $this->_dataobject->limit($len);
            }
            $result = $this->_dataobject->find();
            $joined = true;
        }

        // Retrieving data
        $records = array();
        if ($this->_rowNum) {
            require_once('DB/DataObject/FormBuilder.php');
            $links = $this->_dataobject->links();
$finfo=$links;
$linkedDo = array();
foreach($finfo as $key=>$table) {
  $finfo[$key] = explode(':',$table);
  $linkedDo[$key] = & DB_DataObject::factory($finfo[$key][0]);
}

            while ($this->_dataobject->fetch()) {
          //    print_r($this->_dataobject);
                // Determine Fields
                if (!$this->_options['fields']) {
                    if ($this->_options['use_private_vars']) {
                        $this->_options['fields'] =
                            array_keys(get_object_vars($this->_dataobject));
                    } else {
                        $this->_options['fields'] =
                            array_keys($this->_dataobject->toArray());
                    }
                    //$this->_options['fields'] =
                    //    array_filter(array_keys(get_object_vars($this->_dataobject)), array(&$this, '_fieldsFilter'));
                }
                $fieldList = $this->_options['fields'];

                // Build DataSet
                $rec = array();
                foreach ($fieldList as $fName) {
        //            $getMethod = 'get' . ucfirst($fName);
      //              if (method_exists($this->_dataobject, $getMethod)) {
    //                    //$rec[$fName] = $this->_dataobject->$getMethod(&$this);
  //                      $rec[$fName] = $this->_dataobject->$getMethod();
//                    } elseif (isset($this->_dataobject->$fName)) {
                        $rec[$fName] = $this->_dataobject->$fName;
          //          } else {
            //            $rec[$fName] = null;
              //      }
                }

                // Get Linked FormBuilder Fields

                foreach (array_keys($rec) as $field) {
                    if (isset($links[$field])){
                        if($cache[$field][$this->_dataobject->$field]) {
                            $rec[$field]=$cache[$field][$this->_dataobject->$field];
                        } elseif(isset($this->_dataobject->$field) && $joined) {

                            foreach($linkedDo[$field]->table() as $f=>$n) {

                                $f2=$field.'__'.$f;
                                $linkedDo[$field]->$f=$this->_dataobject->$f2;
                            }
                            foreach(array(array($linkedDo[$field],'toHtmlCell'),array($linkedDo[$field],'toHtml'),array($linkedDo[$field],'toString'),array($linkedDo[$field],'__toString'),array('DB_DataObject_FormBuilder','getDataObjectString')) as $m) {
                                if(method_exists($m[0],$m[1])){
                                    $rec[$field]=call_user_func($m,$linkedDo[$field]);
                                    break;
                                }
                            }
    					    if(empty($rec[$field])) {
                                $rec[$field] = $this->_dataobject->$field.' (missing)';
                            }

                        }
                    } elseif(is_array($this->_dataobject->fb_enumOptions) && key_exists($field,$this->_dataobject->fb_enumOptions)) {
                        $rec[$field]=$this->_dataobject->fb_enumOptions[$field][$this->_dataobject->$field];
                    } elseif(is_array($this->_dataobject->photoFields) && key_exists($field,$this->_dataobject->photoFields)) {
                            // TODO avoid framework dependencies
                            require_once 'misc/tags.php';
                            $rec[$field]=upload_image_tag($this->_dataobject->photoFields[$field][0]['path'].'/'.$this->_dataobject->$field);
                    } elseif($eltTypes[$field] & DB_DATAOBJECT_BOOL) {
												$rec[$field] = $this->_dataobject->$field ? '<span style="display:block;background:#070;color:#fff;text-align:center;font-weight:bold">Yes</span>':'<span style="display:block;background:#700;color:#fff;text-align:center;font-weight:bold">No</span>';
					}
                    if(is_array($this->_dataobject->$field)) {
                        $this->_dataobject->$field=print_r($this->_dataobject->$field,true);
                    }
                    $cache[$field][$this->_dataobject->$field]=$rec[$field];

                }
                $records[] = $rec;//$this->_dataobject->toArray();
            }
        }

        return $records;
    }

    /**
     * Count
     *
     * @access  public
     * @return  int         The number of records or a PEAR_Error
     */
    function count()
    {
        if (is_null($this->_rowNum)) {
            if ($this->_dataobject->N) {
                $this->_rowNum = $this->_dataobject->N;
            } else {

                $this->_dataobjectC->setAlias('counter');
                $test = $this->_dataobjectC->count();
                if ($test === false) {
                    return PEAR::raiseError('Can\'t count the number of rows');
                }
                $this->_rowNum = $test;
            }
        }

        return $this->_rowNum;
    }

    /**
     * Sorts the dataobject.  This MUST be called before fetch.
     *
     * @access  public
     * @param   mixed   $sortSpec   A single field (string) to sort by, or a
     *                              sort specification array of the form:
     *                              array(field => direction, ...)
     * @param   string  $sortDir    Sort direction: 'ASC' or 'DESC'
     *                              This is ignored if $sortDesc is an array
     */
    function sort($sortSpec, $sortDir = null)
    {
        if (is_array($sortSpec)) {
            foreach ($sortSpec as $field => $direction) {
                $this->_dataobject->orderBy("$field $direction");
            }
        } else {
            if (is_null($sortDir)) {
                $this->_dataobject->orderBy($sortSpec);
            } else {
                $this->_dataobject->orderBy("$sortSpec $sortDir");
            }
        }
    }

    // This function is temporary until DB_DO bug #1315 is fixed
    // This removeds and variables from the DataObject that begins with _ or fb_
    function _fieldsFilter($value)
    {
        if (substr($value, 0, 1) == '_') {
            return false;
        } else if (substr($value, 0, 3) == 'fb_') {
            return false;
        } else if ($value == 'N') {
            return false;
        } else {
            return true;
        }

    }

}
?>
