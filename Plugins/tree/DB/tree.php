<?php
/**
* M PHP Framework
* @package      M
* @subpackage   DB_DataObject_Plugin_tree
*/
/**
* M PHP Framework
*
* nested tree handling plugin
* The table needs the following fields :
* parent_id, left, right, level (depth)
* allows fast fetching across multiple levels, while table manipulation (insert - update - delete) is much slower
* accurate for big trees, not for small ones (eg categories with 2/3 levels)
*
* @package      M
* @subpackage   DB_DataObject_Plugin_tree
* @author       Arnaud Sellenet <demental@sat2way.com>
* @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

class DB_DataObject_Plugin_tree extends M_Plugin
{
        public $plugin_name='tree';
        private $_doRebuild = true;
  public function getEvents()
  {
    return array('postinsert','insert','update','postupdate','postdelete');
  }
        /**
         * DB_DataObject overloads
         **/
         function postinsert(&$obj) {
             $this->rebuild($obj);
         }
         function insert(&$obj) {
            $defs = $obj->_getPluginsDef();
            $defs = $defs['tree'];
            if(!$obj->{$defs['parent']}) {
                $obj->{$defs['parent']} = 0;
            }
        }
         function update($originaldo=false,&$obj) {
             $defs = $obj->_getPluginsDef();
             $defs = $defs['tree'];             
            if(!$obj->{$defs['parent']}) {
                $obj->{$defs['parent']} = 0;
            }
        }
         function postupdate(&$obj) {
             $this->rebuild($obj);
         }
         function postdelete(&$obj) {
            /*
             $desc = $this->getDescendants($obj,$obj->id);
             foreach($desc as $enfant) {
                 $enfant->delete();
             }*/
             $this->rebuild($obj);             
         }
        /**
         * A utility function to return an array of the fields
         * that need to be selected in SQL select queries
         *
         * @return  array   An indexed array of fields to select
         */
        function _getFields(&$obj)
        {
            $defs = $obj->_getPluginsDef();
            $defs = $defs['tree'];
            return array('id', $defs['parent'], $defs['sort'],
                         $defs['left'], $defs['right'], $defs['level']);
        }
 
        /**
         * Fetch the node data for the node identified by $id
         *
         * @param   int     $id     The ID of the node to fetch
         * @return  object          An object containing the node's
         *                          data, or null if node not found
         */
        function getNode(&$obj, $id)
        {
            $res = DB_DataObject::factory($obj->tableName());
            $res->selectAdd();
            $res->selectAdd(join(',', $this->_getFields($obj)));
            if($res->get($id)) {
                return $res;
            }
            return null;
        }
 
        /**
         * Fetch the descendants of a node, or if no node is specified, fetch the
         * entire tree. Optionally, only return child data instead of all descendant
         * data.
         *
         * @param   int     $id             The ID of the node to fetch descendant data for.
         *                                  Specify an invalid ID (e.g. 0) to retrieve all data.
         * @param   bool    $includeSelf    Whether or not to include the passed node in the
         *                                  the results. This has no meaning if fetching entire tree.
         * @param   bool    $childrenOnly   True if only returning children data. False if
         *                                  returning all descendant data
         * @return  array                   The descendants of the passed now
         */
        function &getDescendants(&$obj, $id = 0, $includeSelf = false, $childrenOnly = false)
        {
            $defs = $obj->_getPluginsDef();
            $defs = $defs['tree'];
 
            $node = $this->getNode($obj, $id);
            if (is_null($node)) {
                $nleft = 0;
                $nright = 0;
                $parent_id = 0;
            }
            else {
                $nleft = $node->{$defs['left']};
                $nright = $node->{$defs['right']};
                $parent_id = $node->{$defs['parent']};
            }
 
            if ($childrenOnly) {
                if ($includeSelf) {
                    $query = sprintf('select %s from %s where %s = %d or %s = %d order by %s',
                                     join(',', $this->_getFields($obj)),
                                     $obj->tableName(),
                                     'id',
                                     $parent_id,
                                     $defs['parent'],
                                     $parent_id,
                                     $defs['left']);
                }
                else {
                    $query = sprintf('select %s from %s where %s = %d order by %s',
                                     join(',', $this->_getFields($obj)),
                                     $obj->tableName(),
                                     $defs['parent'],
                                     $parent_id,
                                     $defs['left']);
                }
            }
            else {
                if ($nleft > 0 && $includeSelf) {
                    $query = sprintf('select %s from %s where %s >= %d and %s <= %d order by %s',
                                     join(',', $this->_getFields($obj)),
                                     $obj->tableName(),
                                     $defs['left'],
                                     $nleft,
                                     $defs['right'],
                                     $nright,
                                     $defs['left']);
                }
                else if ($nleft > 0) {
                    $query = sprintf('select %s from %s where %s > %d and %s < %d order by %s',
                                     join(',', $this->_getFields($obj)),
                                     $obj->tableName(),
                                     $defs['left'],
                                     $nleft,
                                     $defs['right'],
                                     $nright,
                                     $defs['left']
                                     );
                }
                else {
                    $query = sprintf('select %s from %s order by %s',
                                     join(',', $this->_getFields($obj)),
                                     $obj->tableName(),
                                     $defs['left']
                                     );
                }
            }
            
            $res = DB_DataObject::factory($obj->tableName());
            $res->query($query);
            return $res;
        }
 
        /**
         * Fetch the children of a node, or if no node is specified, fetch the
         * top level items.
         *
         * @param   int     $id             The ID of the node to fetch child data for.
         * @param   bool    $includeSelf    Whether or not to include the passed node in the
         *                                  the results.
         * @return  array                   The children of the passed node
         */
/*        function getChildren(&$obj, $id = 0, $includeSelf = false)
        {
            return $this->getDescendants($obj, $id, $includeSelf, true);
        }
 */
        /**
         * Fetch the path to a node. If an invalid node is passed, an empty array is returned.
         * If a top level node is passed, an array containing on that node is included (if
         * 'includeSelf' is set to true, otherwise an empty array)
         *
         * @param   int     $id             The ID of the node to fetch child data for.
         * @param   bool    $includeSelf    Whether or not to include the passed node in the
         *                                  the results.
         * @return  array                   An array of each node to passed node
         */
        function &getBranch(&$obj, $includeSelf = false)
        {
            $defs = $obj->_getPluginsDef();
            $defs = $defs['tree'];
            if(!$obj->{$defs['parent']}) {
                if($includeSelf) {
                    return $obj;
                } else {
                    return array();
                }
            }
            if ($includeSelf) {
                $query = sprintf('select %s from %s where %s <= %d and %s >= %d order by %s',
                                 join(',', $this->_getFields($obj)),
                                 $obj->tableName(),
                                 $defs['left'],
                                 $obj->{$defs['left']},
                                 $defs['right'],
                                 $obj->{$defs['right']},
                                 $defs['level']);
            }
            else {
                $query = sprintf('select %s from %s where %s < %d and %s > %d order by %s',
                                 join(',', $this->_getFields($obj)),
                                 $obj->tableName(),
                                 $defs['left'],
                                 $obj->{$defs['left']},
                                 $defs['right'],
                                 $obj->{$defs['right']},
                                 $defs['level']);
            }
 
            $result = DB_DataObject::factory($obj->tableName());
            $result->query($query);
            return $result;
        }
        
        /**
         * Check if one node descends from another node. If either node is not
         * found, then false is returned.
         *
         * @param   int     $descendant_id  The node that potentially descends
         * @param   int     $ancestor_id    The node that is potentially descended from
         * @return  bool                    True if $descendant_id descends from $ancestor_id, false otherwise
         */
        function isDescendantOf(&$obj, $descendant_id, $ancestor_id)
        {
            $defs = $obj->_getPluginsDef();
            $defs = $defs['tree'];
            $node = $this->getNode($obj, $ancestor_id);
            if (is_null($node)) {
                return false;
            }
            $result = DB_DataObject::factory($obj->tableName());
            $result->id = $descendant_id;
            $result->whereAdd(sprintf('%s > %d',$defs['left'],
            $node->{$defs['left']}
            ));
            $result->whereAdd(sprintf('%s < %d',$defs['right'],
            $node->{$defs['right']}
            ));
            return $result->count() > 0;
 
            return false;
        }
 
        /**
         * Check if one node is a child of another node. If either node is not
         * found, then false is returned.
         *
         * @param   int     $child_id       The node that is possibly a child
         * @param   int     $parent_id      The node that is possibly a parent
         * @return  bool                    True if $child_id is a child of $parent_id, false otherwise
         */
        function isChildOf(&$obj, $child_id, $parent_id)
        {
            $defs = $obj->_getPluginsDef();
            $defs = $defs['tree'];
            $result = DB_DataObject::factory($obj->tableName());
            $result->id = $child_id;
            $result->{$defs['parent']} = $parent_id;
            return $result->count()>0;
        }
        function doRebuild($bool) {
            $this->_doRebuild = false;
        }
        function isRoot(&$obj) {
            $defs = $obj->_getPluginsDef();
            $defs = $defs['tree'];
            return !$obj->{$defs['parent']};
        }
        /**
         * Find the number of descendants a node has
         *
         * @param   int     $id     The ID of the node to search for. Pass 0 to count all nodes in the tree.
         * @return  int             The number of descendants the node has, or -1 if the node isn't found.
         */
        function numDescendants(&$obj, $id)
        {
            $defs = $obj->_getPluginsDef();
            $defs = $defs['tree'];
            if ($id == 0) {
                $result = DB_DataObject::factory($obj->tableName());
                return (int)$result->count();
            }
            else {

                    return ($obj->{$defs['right']} - $obj->{$defs['left']} - 1) / 2;
                }
            return -1;
        }
        function hasChildren(&$obj) {
            return $this->numDescendants($obj,$obj->id)>0;
        }
        function &getParent(&$obj,$toRoot = true) {
            $defs = $obj->_getPluginsDef();
            $defs = $defs['tree'];
            $parent = DB_DataObject::factory($obj->tableName());
            $parent->whereAdd($defs['left'].' < '.$obj->{$defs['left']}.' AND '.$defs['right'].' > '.$obj->{$defs['right']});
            $parent->orderBy($defs['left'].' DESC');
            $parent->limit(0,1);
            if($parent->find(true)) {
                if(!$toRoot && $this->isRoot($parent)) {
                    return false;
                }
                return $parent;
            }
            return false;
        }
        function &getSiblings($obj) {
          if($parent = $this->getParent($obj,true)) {
            return $parent->getPlugin('tree')->getChildren($parent,$parent->id);
          }
          return $obj;
        }
        function &getPath(&$obj,$includeRoot = true,$includeSelf=false) {
            $defs = $obj->_getPluginsDef();
            $defs = $defs['tree'];
          $query = sprintf('select * from %s where %s <%s %d and %s >%s %d %s order by %s',
                           $obj->tableName(),
                           $defs['left'],
                           $includeSelf?'=':'',
                           $obj->{$defs['left']},
                           $defs['right'],
                           $includeSelf?'=':'',
                           $obj->{$defs['right']},
                           $includeRoot?'':' AND '.$defs['left'].'>0',
                           $defs['left']);

            $path = DB_DataObject::factory($obj->tableName());
            $path->query($query);
            return $path;
        }

        function getMaxLevel(&$obj) {
            $defs = $obj->_getPluginsDef();
            $defs = $defs['tree'];
            $o = DB_DataOBject::factory($obj->tableName());
            $o->query('SELECT max('.$defs['level'].') as '.$defs['level'].' from '.$obj->tableName().' WHERE gauche>'.$obj->{$defs['left']}.' AND droite<'.$obj->{$defs['right']});
            $res = $o->getDatabaseResult();


            return $res->fetchOne(0);
        }
        /**
         * Find the number of children a node has
         *
         * @param   int     $id     The ID of the node to search for. Pass 0 to count the first level items
         * @return  int             The number of descendants the node has, or -1 if the node isn't found.
         */
        function numChildren(&$obj, $id)
        {
            $defs = $obj->_getPluginsDef();
            $defs = $defs['tree'];
            $result = DB_DataObject::factory($obj->tableName());
            $result->{$defs['parent']} = $id;
            return (int)$result->count();
        }
 
        /**
         * Fetch the tree data, nesting within each node references to the node's children
         *
         * @return  array       The tree with the node's child data
         */
        function getTreeWithChildren($obj)
        {
            $defs = $obj->_getPluginsDef();
            $defs = $defs['tree'];
            $idField = 'id';
            $parentField = $defs['parent'];
            $result = DB_DataObject::factory($obj->tableName());
            $result->orderBy($defs['sort']);
            $result->find();
            // create a root node to hold child data about first level items
            $root = new stdClass;
            $root->$idField = 0;
            $root->children = array();
 
            $arr = array($root);
 
            // populate the array and create an empty children array
            while ($result->fetch()) {
                $arr[$result->$idField] = clone($result);
                $arr[$result->$idField]->children = array();
                $arr[0]->children[$result->$idField]=$result->$idField;
            }
 
            // now process the array and build the child data
            foreach ($arr as $id => $row) {
                if (isset($row->$parentField))
                    $arr[$row->$parentField]->children[$id] = $id;
            }
 
            return $arr;
        }
 
        /**
         * Rebuilds the tree data and saves it to the database
         */
        function rebuild(&$obj)
        {
            $defs = $obj->_getPluginsDef();
            $defs = $defs['tree'];
            if(!$this->_doRebuild) {return;}
            $n = 0; // need a variable to hold the running n tally
            $level = 0; // need a variable to hold the running level tally
            $roots = $this->getChildren($obj, 0);
            $data = array();

            while($roots->fetch()) {
                $this->_generateTreeData($obj, $roots->id, $data, $level, $n);
            }
            foreach ($data as $id => $row) {
                $record = DB_DataObject::factory($obj->tableName());
                $record->query(sprintf('UPDATE %s SET %s = %d, %s = %d, %s = %d WHERE %s = "%s"',
                                    $obj->tableName(),
                                    $defs['left'],
                                    $row['left'],
                                    $defs['right'],
                                    $row['right'],
                                    $defs['level'],
                                    $row['level'],
                                    'id',
                                    $id));
            }
        }
        /**
         * Returns the first child of $obj
         */
         public function &getFirstChild($obj) {
           $do = & DB_DataObject::factory($obj->tableName());
           $defs = $obj->_getPluginsDef();
           $defs = $defs['tree'];
           $do->whereAdd($defs['left'].' > '.$obj->{$defs['left']});
           $do->{$defs['parent']} = $obj->pk();
           $do->orderBy($defs['left'].' ASC');
           $do->limit(0,1);
           $do->find(true);
           return $do;
           
         }        
        /**
         * returns the dataobject with all the roots (multi-tree)
         **/
         function &getChildren(&$obj, $parent){
             $do = & DB_DataObject::factory($obj->tableName());
             $defs = $obj->_getPluginsDef();
             $defs = $defs['tree'];
             if(!$parent) {
                 $do->whereAdd($defs['parent'].' IS NULL OR '.$defs['parent'].'="0"');
             } else {
                 $do->{$defs['parent']} = $parent;
             }
             $do->orderBy($defs['sort']);
             $do->find();
             return $do;
         }
            function &getRoot(&$obj) {
                $obj = $this->getChildren($obj, 0);
                $obj->fetch();
                return $obj;
            }     
         /**
         * Generate the tree data. A single call to this generates the n-values for
         * 1 node in the tree. This function assigns the passed in n value as the
         * node's nleft value. It then processes all the node's children (which
         * in turn recursively processes that node's children and so on), and when
         * it is finally done, it takes the update n-value and assigns it as its
         * nright value. Because it is passed as a reference, the subsequent changes
         * in subrequests are held over to when control is returned so the nright
         * can be assigned.
         *
         * @param   array   &$arr   A reference to the data array, since we need to
         *                          be able to update the data in it
         * @param   int     $id     The ID of the current node to process
         * @param   int     $level  The nlevel to assign to the current node
         * @param   int     &$n     A reference to the running tally for the n-value
         */
        function _generateTreeData(&$obj,$parent, &$arr, $level, &$n)
        {
            $parentlevel = $level;
            $left = (int)$n;

            $n++;
            $children = $this->getChildren($obj,$parent);
            while($children->fetch()) {
                $chleft = $n;
                $this->_generateTreeData($obj,$children->id, $arr, $level+1,$n);
                $arr[$children->id] = array('left'=>$chleft,'right'=>$n,'level'=>$level+1);
                $n++;
            }
            $arr[$parent] = array('left'=>$left,'right'=>$n,'level'=>$parentlevel);            
        }
        function globalRebuild(&$obj) {
            $this->rebuild($obj);
        }
        function getGlobalMethods() {
          return array('globalRebuild'=>array('title'=>'Reconstruire arbre','plugin'=>$this->plugin_name));
        }
    }